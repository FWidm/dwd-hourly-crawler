<?php
/**
 * Created by PhpStorm.
 * User: fabianwidmann
 * Date: 17.06.17
 * Time: 09:39
 */

namespace FWidm\DWDHourlyCrawler\Hourly\Variables;


use FWidm\DWDHourlyCrawler\DWDConfiguration;

class DWDHourlyParameters
{
    private $variables;

    /**
     * DWDHourlyVariable constructor.
     * @param $variables
     */
    public function __construct()
    {
        $this->variables = [];
    }

    /**
     * @return array
     */
    public function getVariables(): array
    {
        return $this->variables;
    }


    public function addPressure()
    {
        $pressure = DWDConfiguration::getHourlyConfiguration()->parameters->pressure->name;
        if (!in_array($pressure, $this->variables))
            $this->variables[] = $pressure;
        return $this;
    }

    public function addAirTemperature()
    {
        $airTemperature = DWDConfiguration::getHourlyConfiguration()->parameters->airTemperature->name;
        if (!in_array($airTemperature, $this->variables))
            $this->variables[] = $airTemperature;
        return $this;
    }

    public function getVariableCount()
    {
        return count($this->variables);
    }

    public static function getValidValues():array {
        $conf=DWDConfiguration::getHourlyConfiguration()->parameters;

        $validVals= array();
        foreach ($conf as $item) {
            $validVals[]=$item->name;
        }

        return $validVals;
    }

    public function addCloudiness()
    {
        $cloudiness = DWDConfiguration::getHourlyConfiguration()->parameters->cloudiness->name;
        if (!in_array($cloudiness, $this->variables))
            $this->variables[] = $cloudiness;
        return $this;
    }


    public function addPrecipitation()
    {
        $precipitation = DWDConfiguration::getHourlyConfiguration()->parameters->precipitation->name;
        if (!in_array($precipitation, $this->variables))
            $this->variables[] = $precipitation;
        return $this;
    }

}