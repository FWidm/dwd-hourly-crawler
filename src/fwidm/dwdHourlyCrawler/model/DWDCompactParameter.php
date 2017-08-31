<?php
/**
 * Created by PhpStorm.
 * User: fabianwidmann
 * Date: 31.08.17
 * Time: 13:37
 */

namespace FWidm\DWDHourlyCrawler\Model;

use Carbon\Carbon;

/**
 * Class DWDCompactParameter
 * @package FWidm\DWDHourlyCrawler\fwidm\dwdHourlyCrawler\model
 * @author Fabian Widmann <fabian.widmann@uni-ulm.de>
 * target: json
 * "$variable": [
 * {
 *  "stationId": $id
 *  "descrpiton": {}
 *  "classification: $classification -> name, date, step, unit, shortname, convertedunit
 *  "distance": todo: dist from pt to station
 *  "longitude": float
 *  "latitude": float
 *  "date": ISO
 *  "value": val
 *  "type": "$variable
 * }
 *
 * ]
 */
class DWDCompactParameter implements \JsonSerializable
{
    private $stationID;
    private $description;
    private $classification;
    private $distance;
    private $longitude;
    private $latitude;
    /* @var $date Carbon */
    private $date;
    private $value;
    private $type;

    /**
     * DWDCompactParameter constructor.
     * @param $stationID
     * @param $description
     * @param $classification
     * @param $distance
     * @param $longitude
     * @param $latitude
     * @param Carbon $date
     * @param float $value
     * @param string $type
     */
    public function __construct($stationID, $description, $classification, $distance, $longitude, $latitude, Carbon $date, float $value,string $type)
    {
        $this->stationID = $stationID;
        $this->description = $description;
        $this->classification = $classification;
        $this->distance = $distance;
        $this->longitude = $longitude;
        $this->latitude = $latitude;
        $this->date = $date;
        $this->value = $value;
        $this->type = $type;
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
        $vars['date'] = $this->date->toIso8601String();
        return $vars;
    }
}