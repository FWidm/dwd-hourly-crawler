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
    public function __construct(int $stationId, DateTime $date, int $quality, $indexObservationType, $cloudiness_eights)
    {
        $this->stationId = $stationId;
        $this->date = $date;
        $this->quality = $quality;
        $this->indexObservationType = $indexObservationType;
        $this->cloudiness_eights = $cloudiness_eights;
        $this->description=DWDConfiguration::getHourlyConfiguration()->parameters->cloudiness->variables;
        $this->classification=DWDConfiguration::getHourlyConfiguration()->parameters->cloudiness->classification;

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


    public function exportSingleVariables()
    {
        // TODO: Implement exportSingleVariables() method.
    }
}