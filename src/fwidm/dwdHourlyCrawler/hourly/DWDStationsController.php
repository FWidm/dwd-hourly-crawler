<?php

namespace FWidm\DWDHourlyCrawler\Hourly;

use Carbon\Carbon;
use FWidm\DWDHourlyCrawler\DWDConfiguration;
use FWidm\DWDHourlyCrawler\Model\DWDStation;
use Error;
use Location\Coordinate;
use Location\Distance\Vincenty;
use DateTime;

/**
 * Created by PhpStorm.
 * User: Fabian-Desktop
 * Date: 12.06.2017
 * Time: 14:54
 */
class DWDStationsController
{

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

            }
        }
        return $nearestStation;
    }


    /**Get all stations in an x km radius.
     * @param $stations
     * @param int $radiusKM
     * @return array
     */
    public static function getNearestStations($stations, Coordinate $coordinatesRequest, int $radiusKM = 20)
    {
        $calculator = new Vincenty();
        $nearestStation = array();

        foreach ($stations as $activeStation) {
            if (true && is_object($activeStation) && $activeStation instanceof DWDStation) {

                $coordinatesStation = new Coordinate($activeStation->getLatitude(), $activeStation->getLongitude());
                //distance in meters!
                $diff = $calculator->getDistance($coordinatesRequest, $coordinatesStation);
                if ($diff <= $radiusKM * 1000) {
                    $nearestStation[intval($diff)] = $activeStation;

                }
                //sort by keys -> lowest distance first.
                ksort($nearestStation);
            }
        }
        return $nearestStation;

    }

    public static function getStationFile($stationFtpPath, $outputPath)
    {
        $ftpConfig = DWDConfiguration::getFTPConfiguration();

        $ftp_connection = ftp_connect($ftpConfig->url);

        $login_result = ftp_login($ftp_connection, $ftpConfig->userName, $ftpConfig->userPassword);
        if ($login_result) {
            $result = ftp_get($ftp_connection, $outputPath, $stationFtpPath, FTP_BINARY);

            ftp_close($ftp_connection);

            if (!isset($result)) {
                throw new Error("Could not retrieve data from ftp location: " . $stationFtpPath);
            }
        }

    }

    public static function parseStations($filePath)
    {

        $stationConf = DWDConfiguration::getStationConfiguration();
        $handle = fopen($filePath, "r");
        $stations = array();
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
                $from = Carbon::createFromFormat($stationConf->dateFormat, $split[1]);
                $until = Carbon::createFromFormat($stationConf->dateFormat, $split[2]);

                $station = new DWDStation($split[0], $from, $until,
                    $split[3], $split[4], $split[5], $name, $county,
                    $stationConf->activeRequirementDays);
                $stations[] = $station;

            }

            fclose($handle);
        } else {
            print("Error opening the file " . $stationConf->localFile);
        }
        return $stations;
    }

    static function getStationFiles()
    {
        $ftpConfig = DWDConfiguration::getFTPConfiguration();
        $stationConfig = DWDConfiguration::getStationConfiguration();

        $ftp_connection = ftp_connect($ftpConfig->url);

        $login_result = ftp_login($ftp_connection, $ftpConfig->userName, $ftpConfig->userPassword);
        $localFile = $_SERVER['DOCUMENT_ROOT'] . $stationConfig->localFile;

        if ($login_result && ftp_get($ftp_connection,
                $localFile,
                $stationConfig->ftpFile,
                FTP_BINARY)
        ) {
        } else {
            throw new Error("An Error occured while trying to get the file: " . print_r(error_get_last()));
        }
        //todo: close ftp before?
        ftp_close($ftp_connection);
    }

    static function retrieveStations()
    {

        $stationConf = DWDConfiguration::getStationConfiguration();

        $handle = fopen($_SERVER['DOCUMENT_ROOT'] . $stationConf->localFile, "r");
        $stations = array();
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
//                print_r($split);
//                echo '<br>';
                $name = explode(" ", $split[count($split) - 1]);


                //Some files have spaces at the beginning, some do not. -> shift reading by +1

                //last position is an empty space, the field before that contains the county
                $county = $name[count($name) - 1];
                // merge all other contents of the name, glue them together with spaces.
                $nameSlice = array_slice($name, 0, count($name) - 1);
                $name = implode(" ", $nameSlice);


                $from = DateTime::createFromFormat("Ymd", $split[1]);
                $until = DateTime::createFromFormat("Ymd", $split[2]);
                $station = new DWDStation($split[0], $from, $until,
                    $split[3], $split[4], $split[5], $name, $county,
                    $stationConf->activeRequirementDays);
                $stations[] = $station;

            }

            fclose($handle);
        } else {
            print("Error opening the file " . $stationConf->localFile);
        }
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