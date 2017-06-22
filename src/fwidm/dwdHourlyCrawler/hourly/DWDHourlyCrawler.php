<?php
/**
 * Created by PhpStorm.
 * User: fabianwidmann
 * Date: 20.06.17
 * Time: 09:50
 */

namespace FWidm\DWDHourlyCrawler\Hourly;


use DateTime;
use FWidm\DWDHourlyCrawler\DWDConfiguration;
use FWidm\DWDHourlyCrawler\DWDUtil;

class DWDHourlyCrawler
{
    private $controllers=array();

    /**
     * DWDHourlyCrawler constructor.
     * @param array $controllers
     */
    public function __construct(array $controllers)
    {
        $this->controllers = $controllers;
    }


//    public function getDataByDates(DWDStation $nearestStation, DateTime $dateFrom, DateTime $dateUntil)
//    {
//        $zipFilePath = $this->controller->retrieveFile($nearestStation);
//        $content = DWDUtil::getDataFileFromZip($zipFilePath, DWDConfiguration::getHourlyConfiguration()->zipExtractionPrefix);
//        if ($content == null)
//            throw new \Exception("Could not get data from the zip file at path=" . $zipFilePath);
//
//
//        $data = $this->controller->parseHourlyData($content, $dateUntil, $dateFrom);
//
//        //print_r(json_encode($data));
//        return $data;
//    }

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

            $stations = $hourlyController->getStations(true);
            $nearestStations = DWDStationsController::getNearestStations($stations, $coordinatesRequest);
            foreach ($nearestStations as $nearestStation) {

                $zipFilePath = $hourlyController->retrieveFile($nearestStation);
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
        //print_r(json_encode($data));
        return $data;
    }

    public function addController(DWDAbstractHourlyController $controller){
        $this->controllers[]=$controller;
    }

    public function clearControllers(){
        $this->controllers= array();
    }

    public function addControllers($hourlyControllers)
    {
        $this->controllers=$hourlyControllers;
    }
}