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
use FWidm\DWDHourlyCrawler\Exceptions\DWDLibException;
use FWidm\DWDHourlyCrawler\Hourly\Services\AbstractHourlyService;
use FWidm\DWDHourlyCrawler\Model\DWDStation;
use Location\Coordinate;
use Monolog\Logger;

class DWDHourlyCrawler
{
    private $services = array();

    /**
     * DWDHourlyCrawler constructor.
     * @param array $services
     */
    public function __construct(array $services)
    {
        $this->services = $services;
        DWDUtil::initializeOutputFolder(DWDConfiguration::getHourlyConfiguration()->localBaseFolder);
    }

    /**
     * Retrieve all (active) Stations for a service.
     * @param bool $active
     * @return array
     */
    public function getAllStations(bool $active=false)
    {
        $stationsPerService = [];
        foreach ($this->services as $var => $hourlyService) {
            $stationsPerService[$var] = $this->getStations($hourlyService, $active);
        }
        return $stationsPerService;
    }

    /** Retrieve data from one of the nearest stations. This method retrieves all stations in a specific diameter around
     * the location. It then queries the stations one by one until one station's results could be found.
     *
     * This is important if a station's files are missing on the ftp.
     * @param array $nearestStations
     * @param DateTime $date
     * @param int $timeMinuteLimit
     * @return array [params, stations]
     */
    public function getDataInInterval($coordinatesRequest, DateTime $date, $timeMinuteLimit = 30, $sorted = true)
    {

        $parameters = [];
        $queriedStations = [];

        $date = Carbon::instance($date)->setTimezone('utc');
        foreach ($this->services as $var => $hourlyService) {
            $stations = $this->getStations($hourlyService, true);

            if (isset($stations)) {

                try {
                    $nearestStations = DWDStationsController::getNearestStations($stations, $coordinatesRequest);
                } catch (DWDLibException $exception) {
                    DWDUtil::log(self::class, "Failed to retrieve any nearest active stations. Retrying after forcedownloading new station infos.", Logger::WARNING);
                    $stations = $this->getStations($hourlyService, true, true);
                    $nearestStations = DWDStationsController::getNearestStations($stations, $coordinatesRequest);
                }


                foreach ($nearestStations as $nearestStation) {
                    /* @var $nearestStation DWDStation */
                    $zipFilePath = $this->retrieveFile($hourlyService, $nearestStation);
                    $content = isset($zipFilePath)
                        ? DWDUtil::getDataFromZip($zipFilePath, DWDConfiguration::getHourlyConfiguration()->zipExtractionPrefix)
                        : null;

                    //content can only be null if a station is listed as active but is not anymore.
                    if ($content == null) {
                        DWDUtil::log(self::class, 'file for station=' . $nearestStation . ' could not be loaded, trying next station');
                        continue;
                    }

                    $parameters[$var] = $this->retrieveData($content, $nearestStation, $coordinatesRequest, $hourlyService, $date, $timeMinuteLimit);

                    //addStation
                    if (count($parameters[$var]) > 0 && !isset($queriedStations['station-' . $nearestStation->getId()])) {
                        $queriedStations['station-' . $nearestStation->getId()] = $nearestStation;
                    }

                    if (count($parameters[$var])>0)
                        break;
                }
            }
        }
        if ($sorted && isset($parameters))
            ksort($parameters);
        return [$parameters, $queriedStations];
    }

    /**Get all data for the given day. The parameter day is converted to UTC!
     * @param Coordinate $coordinatesRequest
     * @param DateTime $day
     * @return array [params, stations]
     */
    public function getDataByDay(Coordinate $coordinatesRequest, DateTime $day)
    {
        $parameters = [];
        $queriedStations = [];
        $day = Carbon::instance($day)->setTimezone('utc');
        foreach ($this->services as $var => $hourlyService) {
            /* @var AbstractHourlyService $hourlyService */

            $stations = $this->getStations($hourlyService, true);
            if (isset($stations)) {
                try {
                    $nearestStations = DWDStationsController::getNearestStations($stations, $coordinatesRequest);
                } catch (DWDLibException $exception) {
                    $stations = $this->getStations($hourlyService, true, true);
                    $nearestStations = DWDStationsController::getNearestStations($stations, $coordinatesRequest);
                }

                foreach ($nearestStations as $nearestStation) {
                    /* @var $nearestStation DWDStation */
                    $zipFilePath = $this->retrieveFile($hourlyService, $nearestStation);
                    $content = isset($zipFilePath)
                        ? DWDUtil::getDataFromZip($zipFilePath, DWDConfiguration::getHourlyConfiguration()->zipExtractionPrefix)
                        : null;

                    //content can only be null if a station is listed as active but is not anymore.
                    if ($content == null) {
                        DWDUtil::log(self::class, 'file for station=' . $nearestStation . ' could not be loaded, trying next station');
                        continue;
                    }

                    $start = Carbon::instance($day)->startOfDay();
                    $end = Carbon::instance($day)->endOfDay();
                    $parameters[$var] = $hourlyService->parseHourlyData($content, $nearestStation, $coordinatesRequest, $start, $end);//$this->retrieveData2($content, $hourlyService, $start, $end);

                    //addStation
                    if (count($parameters[$var]) > 0 && !isset($queriedStations['station-' . $nearestStation->getId()])) {
                        $queriedStations['station-' . $nearestStation->getId()] = $nearestStation;

                    }
                    if (isset($parameters[$var]))
                        break;
                }
            }
        }
        return [$parameters,$queriedStations];
    }

    /**
     * Retrieves the correct stations file, can be filtered to only show stations that are flagges as active. Conditions
     * for this can be
     * @param bool $activeOnly
     * @return array
     */
    public function getStations(AbstractHourlyService $controller, bool $activeOnly = false, bool $forceDownloadFile = false)
    {
        $downloadFile = false || $forceDownloadFile;
        $stationsFTPPath = DWDConfiguration::getHourlyConfiguration()->parameters;
        $stationsFTPPath = get_object_vars($stationsFTPPath)[$controller->getParameter()]->stations;

        $filePath = $controller->getStationFTPPath($stationsFTPPath);
        //Retrieve Stations
        if (file_exists($filePath)) {
            $lastModifiedStationFile = Carbon::createFromTimestamp(filemtime($filePath));
            $diffInHours = Carbon::now()->diffInHours(Carbon::createFromTimestamp(filemtime($filePath)));
            DWDUtil::log(self::class, "last modified? " . $lastModifiedStationFile
                . "; difference to today (h)? " . $diffInHours);
            $downloadFile = $diffInHours >= 12; //redownload every 12h.
        } else
            $downloadFile = true;
        //todo: determine if this works - had a problem where this did not trigger redownloading of the file, which lead to the no active stations exception.
        if ($downloadFile) {
            DWDUtil::log(self::class, "Downloading station file=" . $filePath);
            DWDStationsController::getStationFile($stationsFTPPath, $filePath);
        }

        $stations = DWDStationsController::parseStations($filePath);
        DWDUtil::log(self::class, "Got stations... " . count($stations));

        //todo 31.8.2017:  rewrite the "active" part in a way that checks if the queried date is inside the "active" period of stations
        if ($activeOnly && count($stations) > 0) {
            $stations = array_filter($stations,
                function (DWDStation $station) {
                    return $station->isActive();
                });
        }
        DWDUtil::log(self::class, "Got stations after filtering... " . count($stations));

        return $stations;
    }

    /** Retrieves a file for the controller by querying the nearest station.
     * @param AbstractHourlyService $service
     * @param DWDStation $nearestStation
     * @param bool $forceDownloadFile
     * @return string filePath
     */
    public function retrieveFile(AbstractHourlyService $service, DWDStation $nearestStation, $forceDownloadFile = false)
    {
        $config = DWDConfiguration::getConfiguration();
        $ftpConfig = $config->ftp;


        $fileName = $service->getFileName($nearestStation->getId());
        $ftpPath = $service->getFileFTPPath($nearestStation->getId());
        $localPath = $service->getFilePath($fileName);
        DWDUtil::log(self::class, '$fileName=' . $fileName);
        DWDUtil::log(self::class, '$ftpPath=' . $ftpPath);
        DWDUtil::log(self::class, '$localPath=' . $localPath);

        //get file.
        $ftp_connection = ftp_connect($ftpConfig->url);
        //ftp_set_option($ftp_connection, FTP_TIMEOUT_SEC, 9000);
        $files = array();
        if (file_exists($localPath)) {
            $lastModifiedStationFile = Carbon::createFromFormat('U', (filemtime($localPath)));
        }
        //check if the date on the old file is older than 1 day, else return the old path.
        // download can be forced with the optional parameter.
        if ($forceDownloadFile || !file_exists($localPath)
            || (isset($lastModifiedStationFile) && $lastModifiedStationFile->diffInDays(Carbon::now()) >= 1)
        ) {
            //echo "<p>Controller::retrieveFile >> load new zip!</p>";
            $path = pathinfo($localPath);
            if (!is_dir($path['dirname'])) {

                mkdir($path['dirname'], 0755, true);
            }

            if (ftp_login($ftp_connection, $ftpConfig->userName, $ftpConfig->userPassword)) {
//                DWDUtil::log(self::class, 'File "' . $ftpPath . '"exists on server? ' . ftp_size($ftp_connection, $ftpPath));
                if (ftp_size($ftp_connection, $ftpPath) > -1 && ftp_get($ftp_connection, $localPath, $ftpPath, FTP_BINARY)) {
                    $files[] = $localPath;
                } else {
                    return null;
                }

                ftp_close($ftp_connection);
                return $localPath;
            }
        }

        return $localPath;
    }


    /**
     * DWD Hourly data is not really hourly, as such first try to query with the specified limit, then, if the limit is smaller than +-1.5h or +-3.5h
     * Query those values and return them.
     * @param $content - of the zip
     * @param AbstractHourlyService $hourlyController - the controller that should parse the data
     * @param DateTime $dateTime - the data for which we want to query
     * @param $timeMinuteLimit - limit in minutes that defines the range: currentDate+-limit = search range.
     * @return array of DWDAbstractParameter
     */
    private function retrieveData($content, DWDStation $nearestStation, Coordinate $coordinate, AbstractHourlyService $hourlyController, DateTime $dateTime, $timeMinuteLimit)
    {
        //custom time limit
        $timeBefore = Carbon::instance($dateTime);
        $timeAfter = Carbon::instance($dateTime);

        $timeBefore->addMinute($timeMinuteLimit);
        $timeAfter->addMinute(-$timeMinuteLimit);

        $data = $hourlyController->parseHourlyData($content, $nearestStation, $coordinate, $timeAfter, $timeBefore);

        // 3 hour interval -> +- 90min if custom failed.
        if (count($data) == 0 && $timeMinuteLimit < 90) {
            DWDUtil::log(self::class, "retrieving data for a +-90min time limit...");
            $data = $this->retrieveData($content, $nearestStation, $coordinate, $hourlyController, $dateTime, 90);
        } else      // 7 hour interval -> +-210min if custom and 3h limit failed.
            if (count($data) == 0 && $timeMinuteLimit < 210) {
                DWDUtil::log(self::class, "retrieving data for a +-210min time limit...");
                $data = $this->retrieveData($content, $nearestStation, $coordinate, $hourlyController, $dateTime, 210);
            }

        return $data;
    }

    public function addController(AbstractHourlyService $controller)
    {
        $this->services[] = $controller;
    }

    public function clearControllers()
    {
        $this->services = array();
    }

    public function replaceControllers(array $hourlyControllers)
    {
        $this->services = $hourlyControllers;
    }

}
