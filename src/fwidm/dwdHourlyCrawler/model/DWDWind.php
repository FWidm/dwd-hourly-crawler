<?php
/**
 * Created by PhpStorm.
 * User: fabianwidmann
 * Date: 03.07.17
 * Time: 10:31
 */

namespace FWidm\DWDHourlyCrawler\Model;


use Carbon\Carbon;
use DateTime;
use FWidm\DWDHourlyCrawler\DWDConfiguration;
use FWidm\DWDHourlyCrawler\DWDUtil;
use Location\Coordinate;

class DWDWind extends DWDAbstractParameter implements \JsonSerializable
{
    private $meanWindSpeed;
    private $meanWindDirection;


    /**
     * constructor.
     * @param $stationId
     * @param $date
     * @param $quality
     * @param $sunshineDuration
     */
    public function __construct(DWDStation $station, Coordinate $coordinate, int $stationId, DateTime $date, int $quality, $meanWindSpeed, $meanWindDirection)
    {
        $this->stationId = $stationId;
        $this->date = $date;
        $this->quality = $quality;
        $this->meanWindDirection = $meanWindDirection;
        $this->meanWindSpeed = $meanWindSpeed;
        $this->description = DWDConfiguration::getHourlyConfiguration()->parameters->wind->variables;
        $this->classification = DWDConfiguration::getHourlyConfiguration()->parameters->wind->classification;

        $this->latitude = $station->getLatitude();
        $this->longitude = $station->getLongitude();
        $this->distance = DWDUtil::calculateDistanceToStation($coordinate, $station, "km");
    }

    /**
     * @return mixed
     */
    public function getMeanWindSpeed()
    {
        return $this->meanWindSpeed;
    }

    /**
     * @return mixed
     */
    public function getMeanWindDirection()
    {
        return $this->meanWindDirection;
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
                    "name" => $this->description->meanWindSpeed,
                    "quality" => $this->quality,
                    "qualityType" => $this->description->qualityLevel,
                    "units" => $this->description->meanWindSpeedUnit,
                ],
                $this->classification,
                $this->distance, $this->longitude, $this->latitude, new Carbon($this->date),
                $this->meanWindSpeed, "mean wind speed"),
            new DWDCompactParameter($this->stationId,
                [
                    "name" => $this->description->meanWindDirection,
                    "quality" => $this->quality,
                    "qualityType" => $this->description->qualityLevel,
                    "units" => $this->description->meanWindDirectionUnit,
                ],
                $this->classification,
                $this->distance, $this->longitude, $this->latitude, new Carbon($this->date),
                $this->meanWindDirection, "mean wind direction"),
            new DWDCompactParameter($this->stationId,
                [
                    "name" => $this->description->u,
                    "quality" => $this->quality,
                    "qualityType" => $this->description->qualityLevel,
                    "units" => $this->description->uvUnit,
                ],
                $this->classification,
                $this->distance, $this->longitude, $this->latitude, new Carbon($this->date),
                $this->calculateU(), "mean calculated wind U vector"),
            new DWDCompactParameter($this->stationId,
                [
                    "name" => $this->description->v,
                    "quality" => $this->quality,
                    "qualityType" => $this->description->qualityLevel,
                    "units" => $this->description->uvUnit,
                ],
                $this->classification,
                $this->distance, $this->longitude, $this->latitude, new Carbon($this->date),
                $this->calculateV(), "mean calculated wind V vector"),
        ];
    }

    public function calculateU()
    {
        // see: https://www.eol.ucar.edu/content/wind-direction-quick-reference
        return -1 * $this->meanWindSpeed * sin(deg2rad($this->meanWindDirection));
    }

    public function calculateV()
    {
        // see: https://www.eol.ucar.edu/content/wind-direction-quick-reference
        return -1 * $this->meanWindSpeed * cos(deg2rad($this->meanWindDirection));
    }
}