<?php
/**
 * Created by PhpStorm.
 * User: Fabian-Desktop
 * Date: 18.06.2017
 * Time: 09:36
 */

namespace FWidm\DWDHourlyCrawler\Model;


use FWidm\DWDHourlyCrawler\Transformer\ParameterTransformer;
use FWidm\DWDHourlyCrawler\Util\FractalWrapper;

abstract class  DWDAbstractParameter
{
//    protected $id;
    protected $stationId;

    protected $date;

    protected $description;

    protected $quality;

    protected $latitude;

    protected $longitude;

    protected $distance;

    protected $classification;

    /**
     * @return mixed
     */
    public function getStationId()
    {
        return $this->stationId;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
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
    public function getQuality()
    {
        return $this->quality;
    }

    /**
     * @return mixed
     */
    public function getLatitude()
    {
        return $this->latitude;
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
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * @return mixed
     */
    public function getClassification()
    {
        return $this->classification;
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
        $res = FractalWrapper::toResource($this, new ParameterTransformer());
        return FractalWrapper::toArray($res);
    }

    /**
     * Encode the current parameter into a standardized 1 parameter per object type. This method thus encodes all DWD
     * params from their multi param per object variant to an array of single objects per parameter
     * @return array
     */
    public abstract function exportSingleVariables(): array;

}