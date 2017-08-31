<?php
/**
 * Created by PhpStorm.
 * User: fabianwidmann
 * Date: 03.07.17
 * Time: 10:31
 */

namespace FWidm\DWDHourlyCrawler\Model;


use DateTime;
use FWidm\DWDHourlyCrawler\DWDConfiguration;
use FWidm\DWDHourlyCrawler\DWDUtil;

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
    public function __construct(int $stationId, DateTime $date, int $quality, $meanWindSpeed, $meanWindDirection)
    {
        $this->stationId = $stationId;
        $this->date = $date;
        $this->quality = $quality;
        $this->meanWindDirection = $meanWindDirection;
        $this->meanWindSpeed = $meanWindSpeed;
        $this->description=DWDConfiguration::getHourlyConfiguration()->parameters->wind->variables;
        $this->classification=DWDConfiguration::getHourlyConfiguration()->parameters->wind->classification;

    }

    private function calculateU($speed, $directionDeg)
    {
        // see: https://www.eol.ucar.edu/content/wind-direction-quick-reference
        return -1*$speed*sin(deg2rad($directionDeg));
    }

    private function calculateV($speed, $directionDeg)
    {
        // see: https://www.eol.ucar.edu/content/wind-direction-quick-reference
        return -1*$speed*cos(deg2rad($directionDeg));
    }

    function __toString()
    {

        return get_class($this).' [stationId='.$this->stationId.', date='.$this->date->format('Y-m-d').']';
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
        $vars['date']=$this->date->format(DateTime::ATOM);
        $vars['u']= $this->calculateU($this->meanWindSpeed,$this->meanWindDirection);
        $vars['v']= $this->calculateV($this->meanWindSpeed,$this->meanWindDirection);

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
        // TODO: Implement exportSingleVariables() method.
    }
}