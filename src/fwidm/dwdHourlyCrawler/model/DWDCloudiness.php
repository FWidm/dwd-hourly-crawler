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
class DWDCloudiness extends DWDAbstractParameter implements \JsonSerializable
{

    //whether a human or a machine made the observation?
    private $indexObservationType;
    //n/8 as a means to measure cloudiness
    private $cloudiness_eights;


    /**
     * DWDPressure constructor.
     * @param $stationId
     * @param $date
     * @param $quality
     * @param $indexObservationType
     * @param $cloudiness_eights
     */
    public function __construct(DWDStation $station, Coordinate $coordinate, int $stationId, DateTime $date, int $quality, $indexObservationType, $cloudiness_eights)
    {
        $this->stationId = $stationId;
        $this->date = $date;
        $this->quality = $quality;
        $this->indexObservationType = $indexObservationType;
        $this->cloudiness_eights = $cloudiness_eights;
        $this->description = DWDConfiguration::getHourlyConfiguration()->parameters->cloudiness->variables;
        $this->classification = DWDConfiguration::getHourlyConfiguration()->parameters->cloudiness->classification;
        $this->latitude = $station->getLatitude();
        $this->longitude = $station->getLongitude();
        $this->distance = DWDUtil::calculateDistanceToStation($coordinate, $station, "km");
    }


    /**
     * @return mixed
     */
    public function getIndexObservationType()
    {
        return $this->indexObservationType;
    }

    /**
     * @return mixed
     */
    public function getCloudinessEights()
    {
        return $this->cloudiness_eights;
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


    public function exportSingleVariables(): array
    {
        return [
            new DWDCompactParameter($this->stationId,
                [
                    "name" => $this->description->cloudinessEights,
                    "quality" => $this->quality,
                    "qualityType" => $this->description->qualityLevel,
                    "units" => $this->description->cloudinessEightsUnit,
                    "observationType" => $this->indexObservationType,
                    "observationTypeUnit" => $this->description->indexObservationType,
                ],
                $this->classification,
                $this->distance, $this->longitude, $this->latitude, new Carbon($this->date),
                $this->cloudiness_eights, "cloudiness eights")];
    }
}