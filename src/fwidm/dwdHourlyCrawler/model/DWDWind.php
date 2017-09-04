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

    private function calculateU($speed, $directionDeg)
    {
        // see: https://www.eol.ucar.edu/content/wind-direction-quick-reference
        return -1 * $speed * sin(deg2rad($directionDeg));
    }

    private function calculateV($speed, $directionDeg)
    {
        // see: https://www.eol.ucar.edu/content/wind-direction-quick-reference
        return -1 * $speed * cos(deg2rad($directionDeg));
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
        $vars['u'] = $this->calculateU($this->meanWindSpeed, $this->meanWindDirection);
        $vars['v'] = $this->calculateV($this->meanWindSpeed, $this->meanWindDirection);

        return $vars;
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
                $this->calculateU($this->meanWindSpeed, $this->meanWindDirection), "mean calculated wind U vector"),
            new DWDCompactParameter($this->stationId,
                [
                    "name" => $this->description->v,
                    "quality" => $this->quality,
                    "qualityType" => $this->description->qualityLevel,
                    "units" => $this->description->uvUnit,
                ],
                $this->classification,
                $this->distance, $this->longitude, $this->latitude, new Carbon($this->date),
                $this->calculateV($this->meanWindSpeed, $this->meanWindDirection), "mean calculated wind V vector"),
        ];
    }
}