<?php
/**
 * Created by PhpStorm.
 * User: fabianwidmann
 * Date: 31.08.17
 * Time: 13:37
 */

namespace FWidm\DWDHourlyCrawler\Model;

use Carbon\Carbon;
use FWidm\DWDHourlyCrawler\Exceptions\DWDLibException;
use FWidm\DWDHourlyCrawler\Transformer\CompactParameterTransformer;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\ArraySerializer;
use League\Fractal\Serializer\DataArraySerializer;

/**
 * Class DWDCompactParameter
 * @package FWidm\DWDHourlyCrawler\fwidm\dwdHourlyCrawler\model
 * @author Fabian Widmann <fabian.widmann@gmail.com>
 * target: json
 * "$variable": [
 * {
 *  "stationId": $id
 *  "descrpiton": {}
 *  "classification: $classification -> name, date, step, unit, shortname, convertedunit
 *  "distance":
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
    public function __construct($stationID, $description, $classification, $distance, $longitude, $latitude, Carbon $date, float $value, string $type)
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
        return $this->toArray();
    }

    /**
     * @return mixed
     */
    public function getStationID()
    {
        return $this->stationID;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return mixed
     */
    public function getClassification()
    {
        return $this->classification;
    }

    /**
     * @return mixed
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * @return mixed
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @return mixed
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @return Carbon
     */
    public function getDate(): Carbon
    {
        return $this->date;
    }

    /**
     * @return float
     */
    public function getValue(): float
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    public function __toString()
    {
        return "DWDCompactParameter: [type=" . $this->type . "; classification=" . $this->getClassification() . "; value=" . $this->value . "]";
    }

    /**
     * Applies the default (or given) transformer to this object, returns a fractal resource containing the values of the object.
     * @param string $customTransformer
     * @return Item
     */
    public function toResource($customTransformer = CompactParameterTransformer::class): Item
    {
        try {
            $resource = new Item($this, new $customTransformer());
            return $resource;

        } catch (\Error $e) {
            throw new DWDLibException("Specified transformer is not a class. Got transformer class=$customTransformer");
        }
    }

    /** Uses the given serializer and transformer to transform $this into an array of the expected format.
     * @param string $serializer
     * @param string $transformer
     * @return array
     */
    function toArray($serializer = ArraySerializer::class, $transformer = CompactParameterTransformer::class)
    {
        $resource = $this->toResource(new $transformer());
        $manager = new Manager();
        $manager->setSerializer(new $serializer());
        return $manager->createData($resource)->toArray();
    }
}