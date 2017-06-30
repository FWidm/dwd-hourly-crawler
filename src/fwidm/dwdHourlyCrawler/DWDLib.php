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
use FWidm\DWDHourlyCrawler\Hourly\DWDHourlySoilTempController;
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

    /**
     * Retrieve all values for the parameters between the interval timeAfter and timeBefore for a specific location.
     * @param DWDHourlyParameters $hourlyParameters
     * @param DateTime $dateFrom
     * @param DateTime $dateUntil
     * @param $latitude
     * @param $longitude
     * @return array
     */
    public function getHourlyDataByDates(DWDHourlyParameters $hourlyParameters, DateTime $timeAfter, DateTime $timeBefore, $latitude, $longitude)
    {
        $coordinatesRequest = new Coordinate($latitude, $longitude);
        if (!empty($hourlyParameters) && $hourlyParameters->getVariableCount() > 0) {
            $hourlyControllers = $this->getHourlyController($hourlyParameters);

            $data = array();
            foreach ($hourlyControllers as $var => $hourlyController) {
                $this->crawler = new DWDHourlyCrawler($hourlyController);


                $crawler = new DWDHourlyCrawler($hourlyControllers);
                $data = $crawler->getDataByDates($coordinatesRequest, $timeAfter, $timeBefore);
                return $data;
            }

            return $data;
        } else
            throw new DWDLibException("hourlyParameters are empty. Please create a new 'DWDHourlyParameters' object and add the needed variables.");
    }

    /**
     * Retrieve values for the parameters in a time frame between detetime +- timeLimitMinutes (default: 30) for the specific location.
     * @param DWDHourlyParameters $hourlyParameters
     * @param DateTime $dateTime
     * @param $latitude
     * @param $longitude
     * @param int $timeLimitMinutes - optional parameter that limit
     * @return array - returns an array that contains measurements and the station information
     * @throws DWDLibException - if no parameters ar specified
     */
    public function getHourlyFailsafe(DWDHourlyParameters $hourlyParameters, DateTime $dateTime, $latitude, $longitude, $timeLimitMinutes = 30): array
    {
        $coordinatesRequest = new Coordinate($latitude, $longitude);
        if (!empty($hourlyParameters) && $hourlyParameters->getVariableCount() > 0) {

            $hourlyControllers = $this->getHourlyController($hourlyParameters);

            $crawler = new DWDHourlyCrawler($hourlyControllers);
            $data = $crawler->getDataFailsafe($coordinatesRequest, $dateTime, $timeLimitMinutes);

            return $data;
        } else
            throw new DWDLibException("hourlyParameters are empty. Please create a new 'DWDHourlyParameters' object and add the needed variables.");
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
                        $controllers[$conf->pressure->name] = new DWDHourlyPressureController('pressure');
                        break;
                    case $conf->airTemperature->name:
                        $controllers[$conf->airTemperature->name] = new DWDHourlyAirTemperatureController('airTemperature');
                        break;
                    case $conf->cloudiness->name:
                        $controllers[$conf->cloudiness->name] = new DWDHourlyCloudinessController('cloudiness');
                        break;
                    case $conf->precipitation->name:
                        $controllers[$conf->precipitation->name] = new DWDHourlyPrecipitationController('precipitation');
                        break;
                    case $conf->soilTemperature->name:
                        $controllers[$conf->soilTemperature->name] = new DWDHourlySoilTempController('soilTemperature');
                        break;
                    default:
                        print('Unknown variable: var=' . $var . '<br>');
                }
            }
        }
        return $controllers;
    }


}