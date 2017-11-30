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
 * Date: 30.06.17
 * Time: 10:23
 */
class DWDSoilTemp extends DWDAbstractParameter implements \JsonSerializable
{

    private $soilTemp_2cm_deg;
    private $soilTemp_5cm_deg;
    private $soilTemp_10cm_deg;
    private $soilTemp_20cm_deg;
    private $soilTemp_50cm_deg;
    private $soilTemp_100cm_deg;


    public function __construct(DWDStation $station, Coordinate $coordinate, int $stationId, DateTime $date, $quality, $soilTemp_2cm_deg, $soilTemp_5cm_deg, $soilTemp_10cm_deg, $soilTemp_20cm_deg, $soilTemp_50cm_deg, $soilTemp_100cm_deg)
    {
        $this->stationId = $stationId;
        $this->date = $date;
        $this->quality = $quality;
        $this->soilTemp_2cm_deg = $soilTemp_2cm_deg;
        $this->soilTemp_5cm_deg = $soilTemp_5cm_deg;
        $this->soilTemp_10cm_deg = $soilTemp_10cm_deg;
        $this->soilTemp_20cm_deg = $soilTemp_20cm_deg;
        $this->soilTemp_50cm_deg = $soilTemp_50cm_deg;
        $this->soilTemp_100cm_deg = $soilTemp_100cm_deg;
        $this->description = DWDConfiguration::getHourlyConfiguration()->parameters->soilTemperature->variables;
        $this->classification = DWDConfiguration::getHourlyConfiguration()->parameters->soilTemperature->classification;

        $this->latitude = $station->getLatitude();
        $this->longitude = $station->getLongitude();
        $this->distance = DWDUtil::calculateDistanceToStation($coordinate, $station, "km");
    }

    /**
     * @return mixed
     */
    public function getSoilTemp2cmDeg()
    {
        return $this->soilTemp_2cm_deg;
    }

    /**
     * @return mixed
     */
    public function getSoilTemp5cmDeg()
    {
        return $this->soilTemp_5cm_deg;
    }

    /**
     * @return mixed
     */
    public function getSoilTemp10cmDeg()
    {
        return $this->soilTemp_10cm_deg;
    }

    /**
     * @return mixed
     */
    public function getSoilTemp20cmDeg()
    {
        return $this->soilTemp_20cm_deg;
    }

    /**
     * @return mixed
     */
    public function getSoilTemp50cmDeg()
    {
        return $this->soilTemp_50cm_deg;
    }

    /**
     * @return mixed
     */
    public function getSoilTemp100cmDeg()
    {
        return $this->soilTemp_100cm_deg;
    }



    function __toString()
    {
        return 'DWDSoilTemp [stationId=' . $this->stationId . ', date=' . $this->date->format('Y-m-d') . ']';
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
                    "name" => $this->description->soilTemp_2cm_deg,
                    "quality" => $this->quality,
                    "qualityType" => $this->description->qualityLevel,
                    "units" => $this->description->soilTemp_unit,
                ]
                , $this->classification,
                $this->distance, $this->longitude, $this->latitude, new Carbon($this->date),
                $this->soilTemp_2cm_deg, "soil temperature in 2cm"),
            new DWDCompactParameter($this->stationId,
                [
                    "name" => $this->description->soilTemp_5cm_deg,
                    "quality" => $this->quality,
                    "qualityType" => $this->description->qualityLevel,
                    "units" => $this->description->soilTemp_unit,
                ]
                , $this->classification,
                $this->distance, $this->longitude, $this->latitude, new Carbon($this->date),
                $this->soilTemp_5cm_deg, "soil temperature in 5cm"),
            new DWDCompactParameter($this->stationId,
                [
                    "name" => $this->description->soilTemp_10cm_deg,
                    "quality" => $this->quality,
                    "qualityType" => $this->description->qualityLevel,
                    "units" => $this->description->soilTemp_unit,
                ]
                , $this->classification,
                $this->distance, $this->longitude, $this->latitude, new Carbon($this->date),
                $this->soilTemp_10cm_deg, "soil temperature in 10cm"),
            new DWDCompactParameter($this->stationId,
                [
                    "name" => $this->description->soilTemp_20cm_deg,
                    "quality" => $this->quality,
                    "qualityType" => $this->description->qualityLevel,
                    "units" => $this->description->soilTemp_unit,
                ]
                , $this->classification,
                $this->distance, $this->longitude, $this->latitude, new Carbon($this->date),
                $this->soilTemp_20cm_deg, "soil temperature in 20cm"),
            new DWDCompactParameter($this->stationId,
                [
                    "name" => $this->description->soilTemp_50cm_deg,
                    "quality" => $this->quality,
                    "qualityType" => $this->description->qualityLevel,
                    "units" => $this->description->soilTemp_unit,
                ]
                , $this->classification,
                $this->distance, $this->longitude, $this->latitude, new Carbon($this->date),
                $this->soilTemp_50cm_deg, "soil temperature in 50cm"),
            new DWDCompactParameter($this->stationId,
                [
                    "name" => $this->description->soilTemp_100cm_deg,
                    "quality" => $this->quality,
                    "qualityType" => $this->description->qualityLevel,
                    "units" => $this->description->soilTemp_unit,
                ], $this->classification,
                $this->distance, $this->longitude, $this->latitude, new Carbon($this->date),
                $this->soilTemp_100cm_deg, "soil temperature in 100cm"),
        ];
    }
}