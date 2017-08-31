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
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    function jsonSerialize()
    {
        $vars = get_object_vars($this);
        //replace standard format by ISO DateTime::ATOM Format.
        $vars['date'] = $this->date->format(DateTime::ATOM);


        return $vars;
    }

    /**
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }


    public function exportSingleVariables()
    {
        return [
            new DWDCompactParameter($this->stationId, $this->description, $this->classification,
                $this->distance, $this->longitude, $this->latitude, new Carbon($this->date),
                $this->soilTemp_2cm_deg, "soil temperature in 2cm"),
            new DWDCompactParameter($this->stationId, $this->description, $this->classification,
                $this->distance, $this->longitude, $this->latitude, new Carbon($this->date),
                $this->soilTemp_5cm_deg, "soil temperature in 5cm"),
            new DWDCompactParameter($this->stationId, $this->description, $this->classification,
                $this->distance, $this->longitude, $this->latitude, new Carbon($this->date),
                $this->soilTemp_10cm_deg, "soil temperature in 10cm"),
            new DWDCompactParameter($this->stationId, $this->description, $this->classification,
                $this->distance, $this->longitude, $this->latitude, new Carbon($this->date),
                $this->soilTemp_20cm_deg, "soil temperature in 20cm"),
            new DWDCompactParameter($this->stationId, $this->description, $this->classification,
                $this->distance, $this->longitude, $this->latitude, new Carbon($this->date),
                $this->soilTemp_50cm_deg, "soil temperature in 50cm"),
            new DWDCompactParameter($this->stationId, $this->description, $this->classification,
                $this->distance, $this->longitude, $this->latitude, new Carbon($this->date),
                $this->soilTemp_100cm_deg, "soil temperature in 100cm"),
        ];
    }
}