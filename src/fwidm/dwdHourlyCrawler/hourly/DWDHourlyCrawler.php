<?php
/**
 * Created by PhpStorm.
 * User: fabianwidmann
 * Date: 20.06.17
 * Time: 09:50
 */

namespace FWidm\DWDHourlyCrawler\Hourly;


use Carbon\Carbon;
use DateTime;
use FWidm\DWDHourlyCrawler\DWDConfiguration;
use FWidm\DWDHourlyCrawler\DWDUtil;
use FWidm\DWDHourlyCrawler\Model\DWDStation;
use Location\Coordinate;

class DWDHourlyCrawler
{
    private $controllers = array();

    /**
     * DWDHourlyCrawler constructor.
     * @param array $controllers
     */
    public function __construct(array $controllers)
    {
        $this->controllers = $controllers;
    }


    public function getDataByDates(Coordinate $coordinatesRequest, Carbon $timeAfter, Carbon $timeBefore)
    {
        $data = array();
        foreach ($this->controllers as $var => $hourlyController) {
            prettyPrint("GetStations");
            $stations = $this->getStations($hourlyController, true);
            if (isset($stations)) {
                $nearestStations = DWDStationsController::getNearestStations($stations, $coordinatesRequest);
                foreach ($nearestStations as $nearestStation) {

                    $zipFilePath = $this->retrieveFile($hourlyController, $nearestStation);

                    $content = isset($zipFilePath)
                        ? DWDUtil::getDataFileFromZip($zipFilePath, DWDConfiguration::getHourlyConfiguration()->zipExtractionPrefix)
                        : null;
                    //content can only be null if a station is listed as active but is not anymore.
                    if ($content == null) {
                        print('file for station=' . $nearestStation . ' could not be loaded, trying next station');
                        continue;
                    }


                    $data[$var] = $hourlyController->parseHourlyData($content, $timeAfter, $timeBefore);
                    if (isset($data))
                        break;

                }
            }
        }
        return $data;
    }

    /** Retrieve data from one of the nearest stations. This method retrieves all stations in a specific diameter around
     * the location. It then queries the stations one by one until one station's results could be found.
     *
     * This is important if a station's files are missing on the ftp.
     * @param array $nearestStations
     * @param DateTime $dateTime
     * @param int $timeMinuteLimit
     * @return array|null
     */
    public function getDataFailsafe($coordinatesRequest, DateTime $dateTime, $timeMinuteLimit = 30)
    {

        $data = array();

        foreach ($this->controllers as $var => $hourlyController) {


            $stations = $this->getStations($hourlyController, true);

            if (isset($stations)) {

                $nearestStations = DWDStationsController::getNearestStations($stations, $coordinatesRequest);
                foreach ($nearestStations as $nearestStation) {

                    $zipFilePath = $this->retrieveFile($hourlyController, $nearestStation);

                    $content = isset($zipFilePath)
                        ? DWDUtil::getDataFileFromZip($zipFilePath, DWDConfiguration::getHourlyConfiguration()->zipExtractionPrefix)
                        : null;

                    //content can only be null if a station is listed as active but is not anymore.
                    if ($content == null) {
                        print('file for station=' . $nearestStation . ' could not be loaded, trying next station');
                        continue;
                    }
                    //upper boundary for the time
                    $timeBefore = clone $dateTime;
                    //maybe replace this with simply rounding - min 0-29 = down, min 30+ up one hour.
                    $timeBefore->modify($timeMinuteLimit . ' minutes');

                    //lower boundary for the time
                    $timeAfter = clone $dateTime;
                    $timeAfter->modify('-' . $timeMinuteLimit . ' minutes');

                    $data[$var] = $hourlyController->parseHourlyData($content, $timeAfter, $timeBefore);
                    if (isset($data))
                        break;
                }
            }
        }
        //print_r(json_encode($data));
        return $data;
    }


    /** Retrieves a file for the controller by querying the nearest station.
     * @param DWDAbstractHourlyController $controller
     * @param DWDStation $nearestStation
     * @param bool $forceDownloadFile
     * @return string filePath
     */
    public function retrieveFile(DWDAbstractHourlyController $controller, DWDStation $nearestStation, $forceDownloadFile = false): string
    {
        $config = DWDConfiguration::getConfiguration();
        $ftpConfig = $config->ftp;


        $fileName = $controller->getFileName($nearestStation->getId());
        $ftpPath = $controller->getFileFTPPath($nearestStation->getId());
        $localPath = $controller->getFilePath($fileName);

        //get file.
        $ftp_connection = ftp_connect($ftpConfig->url);
        //ftp_set_option($ftp_connection, FTP_TIMEOUT_SEC, 9000);
        $files = array();
        if (file_exists($localPath)) {
            $lastModifiedStationFile = DateTime::createFromFormat('U', (filemtime($localPath)));
        }
        //check if the date on the old file is older than 1 day, else return the old path.
        // download can be forced with the optional parameter.
        if ($forceDownloadFile || !file_exists($localPath)
            || (isset($lastModifiedStationFile) && $lastModifiedStationFile->diff(new DateTime())->d >= 1)
        ) {
            //echo "<p>Controller::retrieveFile >> load new zip!</p>";

            if (ftp_login($ftp_connection, $ftpConfig->userName, $ftpConfig->userPassword)) {
                if (!is_dir($localPath)) {
                    mkdir($localPath, 0755, true);
                }

                //echo $localFilePath;

                if (ftp_get($ftp_connection, $localPath, $ftpPath, FTP_BINARY)) {
                    $files[] = $localPath;
                } else
                    return null;

                ftp_close($ftp_connection);
                return $localPath;
            }
        }

        return $localPath;
    }

    /**
     * Retrieves the correct stations file, can be filtered to only show stations that are flagges as active. Conditions
     * for this can be
     * @param bool $activeOnly
     * @return array
     */
    public function getStations(DWDAbstractHourlyController $controller, bool $activeOnly = false, bool $forceDownloadFile = false)
    {

        $stationsFTPPath = DWDConfiguration::getHourlyConfiguration()->parameters;//->airTemperature->stations;
        $stationsFTPPath = get_object_vars($stationsFTPPath)[$controller->getParameter()]->stations;

        $filePath = $controller->getStationFTPPath($stationsFTPPath);
        //Retrieve Stations
        if (file_exists($filePath)) {
            $lastModifiedStationFile = DateTime::createFromFormat('U', (filemtime($filePath)));
        }

        if ($forceDownloadFile || !file_exists($filePath)
            || (isset($lastModifiedStationFile) && $lastModifiedStationFile->diff(new DateTime())->d >= 1)
        ) {
            DWDStationsController::getStationFile($stationsFTPPath, $filePath);
        }

        $stations = DWDStationsController::parseStations($filePath);
        if ($activeOnly) {
            return array_filter($stations,
                function (DWDStation $station) {
                    return $station->isActive();
                });
        }
        return $stations;
    }


    public function addController(DWDAbstractHourlyController $controller)
    {
        $this->controllers[] = $controller;
    }

    public function clearControllers()
    {
        $this->controllers = array();
    }

    public function addControllers($hourlyControllers)
    {
        $this->controllers = $hourlyControllers;
    }
}