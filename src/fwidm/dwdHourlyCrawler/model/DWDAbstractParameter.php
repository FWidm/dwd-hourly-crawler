<?php
/**
 * Created by PhpStorm.
 * User: Fabian-Desktop
 * Date: 18.06.2017
 * Time: 09:36
 */

namespace FWidm\DWDHourlyCrawler\Model;


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
     * Encode the current parameter into a standardized 1 parameter per object type. This method thus encodes all DWD
     * params from their multi param per object variant to an array of single objects per parameter
     * @return array
     */
    public abstract function exportSingleVariables():array;

}