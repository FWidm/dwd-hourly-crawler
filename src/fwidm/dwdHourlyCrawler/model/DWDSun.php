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

class DWDSun extends DWDAbstractParameter implements \JsonSerializable
{
    private $sunshineDuration;


    /**
     * constructor.
     * @param $stationId
     * @param $date
     * @param $quality
     * @param $sunshineDuration
     */
    public function __construct(int $stationId, DateTime $date, int $quality, $sunshineDuration)
    {
        $this->stationId = $stationId;
        $this->date = $date;
        $this->quality = $quality;
        $this->sunshineDuration = $sunshineDuration;
        $this->description=DWDConfiguration::getHourlyConfiguration()->parameters->sun->variables;

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


        return $vars;
    }

    /**
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }


}