<?php

namespace FWidm\DWDHourlyCrawler\Hourly;

use Carbon\Carbon;
use FWidm\DWDHourlyCrawler\DWDConfiguration;
use FWidm\DWDHourlyCrawler\DWDUtil;
use FWidm\DWDHourlyCrawler\Exceptions\DWDLibException;
use FWidm\DWDHourlyCrawler\Model\DWDStation;
use Location\Coordinate;
use Location\Distance\Vincenty;
use Location\Formatter\Coordinate\DecimalDegrees;

/**
 * Created by PhpStorm.
 * User: Fabian-Desktop
 * Date: 12.06.2017
 * Time: 14:54
 */
class DWDStationsController
{
    public const kmToMeters = 1000;

    /**Return the nearest stations from a stations array.
     * @param $stations - array of DWDStation
     * @param $coordinatesRequest
     * @return DWDStation|null
     * @throws DWDLibException - if stations is empty
     */
    public static function getNearestStation($stations, $coordinatesRequest)
    {
        $calculator = new Vincenty();
        $nearestStation = null;
        $nextDist = INF;
        foreach ($stations as $activeStation) {
            if (is_object($activeStation) && $activeStation instanceof DWDStation) {

                $coordinatesStation = new Coordinate($activeStation->getLatitude(), $activeStation->getLongitude());
                $diff = $calculator->getDistance($coordinatesRequest, $coordinatesStation);
                if ($diff < $nextDist) {
                    $nearestStation = $activeStation;
                    $nextDist = $diff;
                }

            } else
                throw new DWDLibException("Stations parameter does contain an object that is no instance of DWDStation");
        }
        return $nearestStation;
    }


    /**Get all stations in an x km radius.
     * @param $stations - array of DWDStation
     * @param int $radiusKM - default 20km
     * @return array - of nearest stations in the given radius
     */
    public static function getNearestStations($stations, Coordinate $coordinatesRequest, int $radiusKM = 200)
    {
        //todo: make radius loadable from the config.
        DWDUtil::log(self::class, "Getting nearest stations from a list of " . count($stations) . ", around coordinates: " . $coordinatesRequest->format(new DecimalDegrees()));
        $calculator = new Vincenty();
        $nearestStations = array();

        foreach ($stations as $activeStation) {
            if (is_object($activeStation) && $activeStation instanceof DWDStation) {

                $coordinatesStation = new Coordinate($activeStation->getLatitude(), $activeStation->getLongitude());
                //distance in meters!
                $diff = $calculator->getDistance($coordinatesRequest, $coordinatesStation);
                if ($diff <= $radiusKM * DWDStationsController::kmToMeters) {
                    $nearestStations[intval($diff)] = $activeStation;

                }
                //sort by keys -> lowest distance first.
                ksort($nearestStations);
            }
        }
        DWDUtil::log(self::class, "Got nearest stations :" . count($nearestStations));
        if (count($nearestStations) < 1) {
            throw new DWDLibException("No Stations near the given Coordinates are available inside of a 200km radius around coordinates: " . $coordinatesRequest->format(new DecimalDegrees()));
        }
        return $nearestStations;

    }

    /**Tries to download the station file from the given path.
     * @param $stationFtpPath - path/to/the/station_file.txt
     * @param $outputPath - location of the resulting file
     * @throws DWDLibException - if result is empty
     */
    public static function getStationFile($stationFtpPath, $outputPath)
    {
        $ftpConfig = DWDConfiguration::getFTPConfiguration();

        $ftp_connection = ftp_connect($ftpConfig->url);

        $login_result = ftp_login($ftp_connection, $ftpConfig->userName, $ftpConfig->userPassword);
        if ($login_result && ftp_size($ftp_connection, $stationFtpPath) > -1) {
            $result = ftp_get($ftp_connection, $outputPath, $stationFtpPath, FTP_BINARY);
//            DWDUtil::log(self::class, "out=" . $outputPath);
//            DWDUtil::log(self::class, "ftp=" . $stationFtpPath);
            ftp_close($ftp_connection);

            if (!isset($result)) {
                throw new DWDLibException("Could not retrieve data from ftp location: " . $stationFtpPath);
            }
        }

    }

    /**
     * Parse the station files into station objects.
     * @param $filePath - path to the station file
     * @return array - of stations
     * @throws DWDLibException - if zip opening fails or  zip does not exist
     */
    public static function parseStations($filePath)
    {
        if (DIRECTORY_SEPARATOR == '\\')
            $filePath = str_replace('/', '\\', $filePath);
        ini_set('display_errors', 1);
        error_reporting(E_ALL);
        $stationConf = DWDConfiguration::getStationConfiguration();
        $stations = array();

        if (file_exists($filePath)) {
            $handle = fopen($filePath, "r");
            if ($handle) {
                //skips the first N lines of input, requires the file handle.
                self::skipDescriptionLines($stationConf->skipLines, $handle);
                while (($line = fgets($handle)) !== false) {
                    $line = mb_convert_encoding($line, "UTF-8", "iso-8859-1");

                    // eliminate multiple spaces, replace by single space
                    $output = preg_replace('!\s+!', ' ', $line);
                    //remove trailing and leading spaces.
                    $output = trim($output, ' ');

                    // process the line read - split by spaces
                    // stationId, from, until, stationHeight, lat, long, station name, state
                    // Station name can contain spaces and needs further processing, thus we limit to keep station name + state
                    // in one field
                    $split = explode(" ", $output, 7);
                    $name = explode(" ", $split[count($split) - 1]);
                    //last cell contains the county name.
                    $county = $name[count($name) - 1];
                    // merge all other contents of the name, glue them together with spaces.
                    $nameSlice = array_slice($name, 0, count($name) - 1);
                    $name = implode(" ", $nameSlice);

                    //evtl. array_filter
                    $from = Carbon::createFromFormat($stationConf->dateFormat, $split[1], 'UTC');
                    $until = Carbon::createFromFormat($stationConf->dateFormat, $split[2], 'UTC');

                    $station = new DWDStation($split[0], $from, $until,
                        $split[3], $split[4], $split[5], $name, $county,
                        $stationConf->activeRequirementDays);
                    $stations[] = $station;

                }

                fclose($handle);
            } else {
                throw new DWDLibException("Error opening the file: " . $filePath);
            }
        } else
            throw new DWDLibException("File does not exist - path: " . $filePath);

        return $stations;
    }

    /**
     * drops the specified amount of lines ($lineCount) from the file in $handle
     * @param $lineCount
     * @param $handle
     */
    static function skipDescriptionLines($lineCount, $handle)
    {
        for ($i = 0; $i < $lineCount; $i++) {
            fgets($handle); //drop the content of those lines
        }
    }
}