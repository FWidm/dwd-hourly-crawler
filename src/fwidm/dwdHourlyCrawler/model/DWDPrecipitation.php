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
class DWDPrecipitation extends DWDAbstractParameter implements \JsonSerializable
{


    //mm
    private $precipitationHeight_mm;

    private $precipitationIndex;

    private $precipitationWRType;


    /**
     * DWDPressure constructor.
     * @param $stationId
     * @param $date
     * @param $quality
     * @param $pressureSeaLevel
     * @param $pressureStationLevel
     */
    public function __construct(DWDStation $station, Coordinate $coordinate, int $stationId, DateTime $date, int $quality, $pressureSeaLevel, $pressureStationLevel, $precipitationWRType)
    {
        $this->stationId = $stationId;
        $this->date = $date;
        $this->quality = $quality;
        $this->precipitationHeight_mm = $pressureSeaLevel;
        $this->precipitationIndex = $pressureStationLevel;
        $this->precipitationWRType = $precipitationWRType;
        $this->description = DWDConfiguration::getHourlyConfiguration()->parameters->precipitation->variables;
        $this->classification = DWDConfiguration::getHourlyConfiguration()->parameters->precipitation->classification;
        $this->latitude = $station->getLatitude();
        $this->longitude = $station->getLongitude();
        $this->distance = DWDUtil::calculateDistanceToStation($coordinate, $station, "km");
    }

    /**
     * @return mixed
     */
    public function getPrecipitationHeightMm()
    {
        return $this->precipitationHeight_mm;
    }

    /**
     * @return mixed
     */
    public function getPrecipitationIndex()
    {
        return $this->precipitationIndex;
    }

    /**
     * @return mixed
     */
    public function getPrecipitationWRType()
    {
        return $this->precipitationWRType;
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
                    "name" => $this->description->hourlyPrecipitation,
                    "quality" => $this->quality,
                    "qualityType" => $this->description->qualityLevel,
                    "units" => $this->description->hourlyPrecipitationUnit,
                ],
                $this->classification,
                $this->distance, $this->longitude, $this->latitude, new Carbon($this->date),
                $this->precipitationHeight_mm, "precipitation height"),
            new DWDCompactParameter($this->stationId, [
                "name" => $this->description->precipitationIndex,
                "quality" => $this->quality,
                "qualityType" => $this->description->qualityLevel,
                "units" => $this->description->precipitationIndexUnit,
            ],
                $this->classification, $this->distance, $this->longitude, $this->latitude, new Carbon($this->date),
                $this->precipitationIndex, "precipitation index"),
            new DWDCompactParameter($this->stationId,
                [
                    "name" => $this->description->precipitationWRType,
                    "quality" => $this->quality,
                    "qualityType" => $this->description->qualityLevel,
                    "units" => $this->description->precipitationWRTypeUnit,
                ],
                $this->classification,
                $this->distance, $this->longitude, $this->latitude, new Carbon($this->date),
                $this->precipitationWRType, "precipitation wr type"),
        ];
    }
}