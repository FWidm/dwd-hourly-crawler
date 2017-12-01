<?php

namespace FWidm\DWDHourlyCrawler;

use DateTime;
use Error;
use FWidm\DWDHourlyCrawler\Exceptions\DWDLibException;
use FWidm\DWDHourlyCrawler\Hourly\DWDHourlyCrawler;
use FWidm\DWDHourlyCrawler\Hourly\Services\AbstractHourlyService;
use FWidm\DWDHourlyCrawler\Hourly\Services\HourlyAirTemperatureService;
use FWidm\DWDHourlyCrawler\Hourly\Services\HourlyCloudinessService;
use FWidm\DWDHourlyCrawler\Hourly\Services\HourlyPrecipitationService;
use FWidm\DWDHourlyCrawler\Hourly\Services\HourlyPressureService;
use FWidm\DWDHourlyCrawler\Hourly\Services\HourlySoilTempService;
use FWidm\DWDHourlyCrawler\Hourly\Services\HourlySolarService;
use FWidm\DWDHourlyCrawler\Hourly\Services\HourlySunService;
use FWidm\DWDHourlyCrawler\Hourly\Services\HourlyWindService;
use FWidm\DWDHourlyCrawler\Hourly\Variables\DWDHourlyParameters;
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
     * DWDLib constructor.
     */
    public function __construct($baseDirectory = null)
    {
        DWDConfiguration::setBaseDir($baseDirectory);
    }


    /**
     * Retrieve values for the parameters in a time frame between detetime +- timeLimitMinutes (default: 30) for the specific location.
     * @param DWDHourlyParameters $hourlyParameters
     * @param DateTime $dateTime
     * @param $latitude
     * @param $longitude
     * @return array - returns an array that contains measurements ('values') and the station ('stations')information
     * @throws DWDLibException - if no parameters ar specified
     */
    public function getHourlyDataInDay(DWDHourlyParameters $hourlyParameters, DateTime $day, $latitude, $longitude)
    {
        $coordinatesRequest = new Coordinate($latitude, $longitude);
        if (!empty($hourlyParameters) && $hourlyParameters->getVariableCount() > 0) {

            $services = $this->createServices($hourlyParameters);

            $crawler = new DWDHourlyCrawler($services);
            return $crawler->getDataByDay($coordinatesRequest, $day);
        } else
            throw new DWDLibException("hourlyParameters are empty. Please create a new 'DWDHourlyParameters' object and add the needed variables.");
    }

    /** Create a new instance of the appropriate controller.
     * @param DWDHourlyParameters $variables
     * @return AbstractHourlyService
     * @throws Error
     * @internal param $var
     */
    private function createServices(DWDHourlyParameters $variables): array
    {
        $conf = DWDConfiguration::getHourlyConfiguration()->parameters;
        $controllers = array();
        if (!empty($variables->getVariables())) {
            foreach ($variables->getVariables() as $var) {

                switch ($var) {
                    case $conf->pressure->name:
                        $controllers[$conf->pressure->name] = new HourlyPressureService('pressure');
                        break;
                    case $conf->airTemperature->name:
                        $controllers[$conf->airTemperature->name] = new HourlyAirTemperatureService('airTemperature');
                        break;
                    case $conf->cloudiness->name:
                        $controllers[$conf->cloudiness->name] = new HourlyCloudinessService('cloudiness');
                        break;
                    case $conf->precipitation->name:
                        $controllers[$conf->precipitation->name] = new HourlyPrecipitationService('precipitation');
                        break;
                    case $conf->soilTemperature->name:
                        $controllers[$conf->soilTemperature->name] = new HourlySoilTempService('soilTemperature');
                        break;
                    case $conf->solar->name:
                        $controllers[$conf->solar->name] = new HourlySolarService('solar');
                        break;
                    case $conf->sun->name:
                        $controllers[$conf->sun->name] = new HourlySunService('sun');
                        break;
                    case $conf->wind->name:
                        $controllers[$conf->wind->name] = new HourlyWindService('wind');
                        break;
                    default:
                        print('Unknown variable: var=' . $var . '<br>');
                }
            }
        }
        return $controllers;
    }

    /**
     * Retrieve values for the parameters in a time frame between detetime +- timeLimitMinutes (default: 30) for the specific location.
     * @param DWDHourlyParameters $hourlyParameters
     * @param DateTime $dateTime
     * @param $latitude
     * @param $longitude
     * @param int $timeLimitMinutes - optional parameter that limit
     * @return array - returns an array that contains measurements ('values') and the station ('stations')information
     * @throws DWDLibException - if no parameters ar specified
     */
    public function getHourlyInInterval(DWDHourlyParameters $hourlyParameters, DateTime $dateTime, $latitude, $longitude, $timeLimitMinutes = 30): array
    {
        $coordinatesRequest = new Coordinate($latitude, $longitude);
        if (!empty($hourlyParameters) && $hourlyParameters->getVariableCount() > 0) {

            $services = $this->createServices($hourlyParameters);

            $crawler = new DWDHourlyCrawler($services);

            return $crawler->getDataInInterval($coordinatesRequest, $dateTime, $timeLimitMinutes);
        } else
            throw new DWDLibException("hourlyParameters are empty. Please create a new 'DWDHourlyParameters' object and add the needed variables.");
    }


}