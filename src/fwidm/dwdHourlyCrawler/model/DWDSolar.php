<?php
/**
 * Created by PhpStorm.
 * User: Fabian-Desktop
 * Date: 01.07.2017
 * Time: 09:45
 */

namespace FWidm\DWDHourlyCrawler\Model;


use DateTime;
use FWidm\DWDHourlyCrawler\DWDConfiguration;

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
    public function __construct(int $stationId, DateTime $date, $quality, $sumLongwaveRadiation, $sumDiffuseRadiation, $sumIncomingRadiation, $sumSunshineDuration, $zenith)
    {
        $this->stationId = $stationId;
        $this->date = $date;
        $this->sumLongwaveRadiation = $sumLongwaveRadiation;
        $this->sumDiffuseRadiation = $sumDiffuseRadiation;
        $this->sumIncomingRadiation = $sumIncomingRadiation;
        $this->sumSunshineDuration = $sumSunshineDuration;
        $this->zenith = $zenith;
        $this->quality=$quality;
        $this->description=DWDConfiguration::getHourlyConfiguration()->parameters->solar->variables;

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


        return $vars;
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
}