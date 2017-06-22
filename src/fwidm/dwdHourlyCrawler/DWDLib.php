<?php

namespace FWidm\DWDHourlyCrawler;

use DateTime;
use FWidm\DWDHourlyCrawler\Exceptions\DWDLibException;
use FWidm\DWDHourlyCrawler\Hourly\DWDHourlyCloudinessController;
use FWidm\DWDHourlyCrawler\Hourly\DWDHourlyAirTemperatureController;
use FWidm\DWDHourlyCrawler\Hourly\DWDHourlyCrawler;
use FWidm\DWDHourlyCrawler\Hourly\DWDHourlyPrecipitationController;
use FWidm\DWDHourlyCrawler\Hourly\DWDHourlyPressureController;
use FWidm\DWDHourlyCrawler\Hourly\DWDAbstractHourlyController;
use FWidm\DWDHourlyCrawler\Hourly\Variables\DWDHourlyParameters;
use Error;
use Location\Coordinate;

/**
 * Created by PhpStorm.
 * User: Fabian-Desktop
 * Date: 12.06.2017
 * Time: 14:47
 */
class DWDLib
{


//    public function getHourlyDataByDates(DWDHourlyParameters $variables, DateTime $dateFrom, DateTime $dateUntil, $latitude, $longitude)
//    {
//        $coordinatesRequest = new Coordinate($latitude, $longitude);
//        $hourlyControllers = array();
//        if (!empty($variables) && $variables->getVariableCount() > 0) {
//            $hourlyControllers = $this->getHourlyController($variables);
//        }
//        $data = array();
//        foreach ($hourlyControllers as $var => $hourlyController) {
//            $this->crawler = new DWDHourlyCrawler($hourlyController);
//
//            if (isset($hourlyController)) {
//                $stations = $hourlyController->getStations(true);
//                $nearestStation = DWDStationsController::getNearestStation($stations, $coordinatesRequest);
//                //echo "nearest: " . json_encode($nearestStation) . "<br>";
//                $data[$var] = $this->crawler->getDataByDates($nearestStation, $dateFrom, $dateUntil);
//            }
//        }
//
//        return $data;
//    }

    public function getHourlyFailsafe(DWDHourlyParameters $variables, DateTime $dateTime, $latitude, $longitude, $timeLimitMinutes = 30)
    {
        $coordinatesRequest = new Coordinate($latitude, $longitude);
        if (!empty($variables) && $variables->getVariableCount() > 0) {
            $hourlyControllers = $this->getHourlyController($variables);
            $crawler=new DWDHourlyCrawler($hourlyControllers);
            $data=$crawler->getDataFailsafe($coordinatesRequest,$dateTime,$timeLimitMinutes);

            return $data;
        }
        else
            throw new DWDLibException("Parameters are empty. Please create a new 'DWDHourlyParameters' object.");
    }


    /** Create a new instance of the appropriate controller.
     * @param DWDHourlyParameters $variables
     * @return DWDAbstractHourlyController
     * @throws Error
     * @internal param $var
     */
    private function getHourlyController(DWDHourlyParameters $variables): array
    {
        $conf = DWDConfiguration::getHourlyConfiguration()->parameters;
        $controllers = array();
        if (!empty($variables->getVariables())) {
            foreach ($variables->getVariables() as $var) {

                switch ($var) {
                    case $conf->pressure->name:
                        $controllers[$conf->pressure->name] = new DWDHourlyPressureController();
                        break;
                    case $conf->airTemperature->name:
                        $controllers[$conf->airTemperature->name] = new DWDHourlyAirTemperatureController();
                        break;
                    case $conf->cloudiness->name:
                        $controllers[$conf->cloudiness->name] = new DWDHourlyCloudinessController();
                        break;
                    case $conf->precipitation->name:
                        $controllers[$conf->precipitation->name] = new DWDHourlyPrecipitationController();
                        break;
                    default:
                        print('Unknown variable: var=' . $var . '<br>');
                }
            }
        }
        return $controllers;
    }


}