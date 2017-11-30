<?php

namespace FWidm\DWDHourlyCrawler\Model;

use Carbon\Carbon;
use DateTime;
use FWidm\DWDHourlyCrawler\DWDConfiguration;
use FWidm\DWDHourlyCrawler\DWDUtil;
use Location\Coordinate;

/**
 * Created by PhpStorm.
 * User: fabianwidmann
 * Date: 12.06.17
 * Time: 10:23
 */
class DWDAirTemperature extends DWDAbstractParameter implements \JsonSerializable
{

    //°C
    private $temperature2m_degC;
    //%
    private $relativeHumidity_percent;

    /**
     * DWDPressure constructor.
     * @param $stationId
     * @param $date
     * @param $quality
     * @param $temperature2m
     * @param $relativeHumidity
     */
    public function __construct(DWDStation $station, Coordinate $coordinate, int $stationId, DateTime $date, int $quality, $temperature2m, $relativeHumidity)
    {
        $this->stationId = $stationId;
        $this->date = $date;
        $this->quality = $quality;
        $this->temperature2m_degC = $temperature2m;
        $this->relativeHumidity_percent = $relativeHumidity;
        $this->description = DWDConfiguration::getHourlyConfiguration()->parameters->airTemperature->variables;
        $this->classification = DWDConfiguration::getHourlyConfiguration()->parameters->airTemperature->classification;
        $this->latitude = $station->getLatitude();
        $this->longitude = $station->getLongitude();
        $this->distance = DWDUtil::calculateDistanceToStation($coordinate, $station, "km");

    }

    /**
     * @return mixed
     */
    public function getTemperature2mDegC()
    {
        return $this->temperature2m_degC;
    }

    /**
     * @return mixed
     */
    public function getRelativeHumidityPercent()
    {
        return $this->relativeHumidity_percent;
    }


    function __toString()
    {

        return get_class($this) . ' [stationId=' . $this->stationId . ', date=' . $this->date->format('Y-m-d') . ']';
    }


    /**
     * @return int
     */
    public function getStationId(): int
    {
        return $this->stationId;
    }


    /**
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }


    public function exportSingleVariables():array
    {
        return [
            new DWDCompactParameter($this->stationId,
                [
                    "name" => $this->description->temperature2m,
                    "quality" => $this->quality,
                    "qualityType" => $this->description->qualityLevel,
                    "units" => $this->description->temperature2mUnit,
                ],
                $this->classification,
                $this->distance, $this->longitude, $this->latitude, new Carbon($this->date),
                $this->temperature2m_degC, "2 metre temperature"),
            new DWDCompactParameter($this->stationId,
                [
                    "name" => $this->description->relativeHumidity,
                    "quality" => $this->quality,
                    "qualityType" => $this->description->qualityLevel,
                    "units" => $this->description->relativeHumidityUnit,
                ]
                , $this->classification,
                $this->distance, $this->longitude, $this->latitude, new Carbon($this->date),
                $this->relativeHumidity_percent, "relative humidity in percent"),
        ];
    }
}