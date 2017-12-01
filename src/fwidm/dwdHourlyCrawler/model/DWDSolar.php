<?php
/**
 * Created by PhpStorm.
 * User: Fabian-Desktop
 * Date: 01.07.2017
 * Time: 09:45
 */

namespace FWidm\DWDHourlyCrawler\Model;


use Carbon\Carbon;
use DateTime;
use FWidm\DWDHourlyCrawler\DWDConfiguration;
use FWidm\DWDHourlyCrawler\DWDUtil;
use Location\Coordinate;

class DWDSolar extends DWDAbstractParameter implements \JsonSerializable
{

    private $sumLongwaveRadiation;
    private $sumDiffuseRadiation;
    private $sumIncomingRadiation;
    private $sumSunshineDuration;
    private $zenith;


    /**
     * DWDSolar constructor.
     * @param $quality
     * @param $sumLongwaveRadiation
     * @param $sumDiffuseRadiation
     * @param $sumIncomingRadiation
     * @param $sumSunshineDuration
     * @param $zenith
     */
    public function __construct(DWDStation $station, Coordinate $coordinate, int $stationId, DateTime $date, $quality, $sumLongwaveRadiation, $sumDiffuseRadiation, $sumIncomingRadiation, $sumSunshineDuration, $zenith)
    {
        $this->stationId = $stationId;
        $this->date = $date;
        $this->sumLongwaveRadiation = $sumLongwaveRadiation;
        $this->sumDiffuseRadiation = $sumDiffuseRadiation;
        $this->sumIncomingRadiation = $sumIncomingRadiation;
        $this->sumSunshineDuration = $sumSunshineDuration;
        $this->zenith = $zenith;
        $this->quality = $quality;
        $this->description = DWDConfiguration::getHourlyConfiguration()->parameters->solar->variables;
        $this->classification = DWDConfiguration::getHourlyConfiguration()->parameters->solar->classification;

        $this->latitude = $station->getLatitude();
        $this->longitude = $station->getLongitude();
        $this->distance = DWDUtil::calculateDistanceToStation($coordinate, $station, "km");
    }

    /**
     * @return mixed
     */
    public function getSumLongwaveRadiation()
    {
        return $this->sumLongwaveRadiation;
    }

    /**
     * @return mixed
     */
    public function getSumDiffuseRadiation()
    {
        return $this->sumDiffuseRadiation;
    }

    /**
     * @return mixed
     */
    public function getSumIncomingRadiation()
    {
        return $this->sumIncomingRadiation;
    }

    /**
     * @return mixed
     */
    public function getSumSunshineDuration()
    {
        return $this->sumSunshineDuration;
    }

    /**
     * @return mixed
     */
    public function getZenith()
    {
        return $this->zenith;
    }

    function __toString()
    {
        return 'DWDSolar [stationId=' . $this->stationId . ', date=' . $this->date->format('Y-m-d') . ']';
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
                    "name" => $this->description->sumDiffuseRadiation,
                    "quality" => $this->quality,
                    "qualityType" => $this->description->qualityLevel,
                    "units" => $this->description->radiationUnit,
                ],
                $this->classification,
                $this->distance, $this->longitude, $this->latitude, new Carbon($this->date),
                $this->sumDiffuseRadiation, "sum of diffuse radiation"),
            new DWDCompactParameter($this->stationId, [
                "name" => $this->description->sumIncomingRadiation,
                "quality" => $this->quality,
                "qualityType" => $this->description->qualityLevel,
                "units" => $this->description->radiationUnit,
            ],
                $this->classification,
                $this->distance, $this->longitude, $this->latitude, new Carbon($this->date),
                $this->sumIncomingRadiation, "sum of incoming radiation"),
            new DWDCompactParameter($this->stationId,
                [
                    "name" => $this->description->sumLongWaveRadiation,
                    "quality" => $this->quality,
                    "qualityType" => $this->description->qualityLevel,
                    "units" => $this->description->radiationUnit,
                ],
                $this->classification,
                $this->distance, $this->longitude, $this->latitude, new Carbon($this->date),
                $this->sumLongwaveRadiation, "sum of longwave radiation"),
            new DWDCompactParameter($this->stationId,
                [
                    "name" => $this->description->sumSunshineDuration,
                    "quality" => $this->quality,
                    "qualityType" => $this->description->qualityLevel,
                    "units" => $this->description->sumSunshineDurationUnit,
                ],
                $this->classification,
                $this->distance, $this->longitude, $this->latitude, new Carbon($this->date),
                $this->sumSunshineDuration, "sunshine duration"),
            new DWDCompactParameter($this->stationId,
                [
                    "name" => $this->description->zenith,
                    "quality" => $this->quality,
                    "qualityType" => $this->description->qualityLevel,
                    "units" => $this->description->zenithUnit,
                ],
                $this->classification,
                $this->distance, $this->longitude, $this->latitude, new Carbon($this->date),
                $this->zenith, "zenith"),
        ];
    }
}