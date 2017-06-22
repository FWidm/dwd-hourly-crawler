<?php
namespace FWidm\DWDHourlyCrawler\Model;

use DateTime;
use FWidm\DWDHourlyCrawler\DWDConfiguration;

/**
 * Created by PhpStorm.
 * User: fabianwidmann
 * Date: 12.06.17
 * Time: 10:23
 */
class DWDAirTemperature extends DWDAbstractParameter implements \JsonSerializable
{
    //QN_9
    private $quality_qn9;
    //°C
    private $temperature2m_degC;
    //%
    private $relativeHumidity_percent;

    private $paramDescription;

    /**
     * DWDPressure constructor.
     * @param $stationId
     * @param $date
     * @param $quality
     * @param $temperature2m
     * @param $relativeHumidity
     */
    public function __construct(int $stationId, DateTime $date, int $quality, $temperature2m, $relativeHumidity)
    {
        $this->stationId = $stationId;
        $this->date = $date;
        $this->quality_qn9 = $quality;
        $this->temperature2m_degC = $temperature2m;
        $this->relativeHumidity_percent = $relativeHumidity;
        $this->paramDescription=DWDConfiguration::getHourlyConfiguration()->parameters->airTemperature->variables;
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