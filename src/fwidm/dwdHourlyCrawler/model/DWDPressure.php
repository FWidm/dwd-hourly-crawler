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
class DWDPressure extends DWDAbstractParameter implements \JsonSerializable
{

    //Pascal / p
    private $pressureSeaLevel_hPA;
    //Pascal / p0
    private $pressureStationLevel_hPA;

    /**
     * DWDPressure constructor.
     * @param $stationId
     * @param $date
     * @param $quality
     * @param $pressureSeaLevel
     * @param $pressureStationLevel
     */
    public function __construct(DWDStation $station, Coordinate $coordinate, int $stationId, DateTime $date, int $quality, $pressureSeaLevel, $pressureStationLevel)
    {
        $this->stationId = $stationId;
        $this->date = $date;
        $this->quality = $quality;
        $this->pressureSeaLevel_hPA = $pressureSeaLevel;
        $this->pressureStationLevel_hPA = $pressureStationLevel;

        $this->description = DWDConfiguration::getHourlyConfiguration()->parameters->pressure->variables;
        $this->classification = DWDConfiguration::getHourlyConfiguration()->parameters->pressure->classification;

        $this->latitude = $station->getLatitude();
        $this->longitude = $station->getLongitude();
        $this->distance = DWDUtil::calculateDistanceToStation($coordinate, $station, "km");
    }

    /**
     * @return mixed
     */
    public function getPressureSeaLevelHPA()
    {
        return $this->pressureSeaLevel_hPA;
    }

    /**
     * @return mixed
     */
    public function getPressureStationLevelHPA()
    {
        return $this->pressureStationLevel_hPA;
    }


    function __toString()
    {

        return 'DWDPressure [stationId=' . $this->stationId . ', date=' . $this->date->format('Y-m-d') . ']';
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


    public function exportSingleVariables(): array
    {
        return [
            new DWDCompactParameter($this->stationId,
                [
                    "name" => $this->description->pressureSeaLevel,
                    "quality" => $this->quality,
                    "qualityType" => $this->description->qualityLevel,
                    "units" => $this->description->pressureSeaLevelUnit,
                ]
                , $this->classification,
                $this->distance, $this->longitude, $this->latitude, new Carbon($this->date),
                $this->pressureSeaLevel_hPA, "mean sea level pressure"),
            new DWDCompactParameter($this->stationId,
                [
                    "name" => $this->description->pressureStationLevel,
                    "quality" => $this->quality,
                    "qualityType" => $this->description->qualityLevel,
                    "units" => $this->description->pressureStationLevelUnit,
                ]
                , $this->classification,
                $this->distance, $this->longitude, $this->latitude, new Carbon($this->date),
                $this->pressureStationLevel_hPA, "station level pressure"),
        ];
    }
}