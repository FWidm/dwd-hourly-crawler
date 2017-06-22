<?php
namespace FWidm\DWDHourlyCrawler\Model;

use DateTime;


/**
 * Created by PhpStorm.
 * User: fabianwidmann
 * Date: 10.06.17
 * Time: 11:11
 */
class DWDStation implements \JsonSerializable
{
    private $id;
    private $from;
    private $until;
    private $height;
    // ISO 6709 Lat before long
    private $latitude;
    private $longitude;
    private $name;
    private $state;
    private $active;

    /**
     * DWDStation constructor.
     * @param $stationId
     * @param $from
     * @param $until
     * @param $height
     * @param $longitude
     * @param $latitude
     * @param $name
     * @param $state
     */
    public function __construct($id, DateTime $from, DateTime $until, $height, $latitude, $longitude, $name, $state,$activeDayThreshold)
    {
        $this->id = $id;
        $this->from = $from;
        $this->until = $until;
        $this->height = $height;
        $this->longitude = $longitude;
        $this->latitude = $latitude;
        $this->name = $name;
        $this->state = $state;
        $this->setActive($activeDayThreshold);
    }

    public function setActive($activeDayThreshold){
        $now=new DateTime('now');
//        print('<hr>');
//        print(gettype($this->until));
//        print_r($this->until);
//        print('<hr>');
        $difference=$now->diff($this->until);
//        print_r( $difference->y);
        if($difference->y==0&&$difference->m==0 && $difference->d < $activeDayThreshold)
            $this->active=true;
        else $this->active=false;
//        $elapsed = $difference->format('%y years %m months %a days %h hours %i minutes %s seconds');
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
     * @return mixed
     */
    public function isActive()
    {
        return $this->active;
    }

    function __toString()
    {

        return 'DWDStation [id='.$this->id.', name='.$this->name.', until='.$this->until->format('Y-m-d').']';
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
        $vars['until']=$this->until->format(DateTime::ATOM);
        $vars['from']=$this->from->format(DateTime::ATOM);


        return $vars;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

}