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


//    /**
//     * @return mixed
//     */
//    public function getId()
//    {
//        return $this->id;
//    }

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


}