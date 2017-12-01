<?php

namespace FWidm\DWDHourlyCrawler\Model;

use Carbon\Carbon;
use FWidm\DWDHourlyCrawler\Transformer\StationTransformer;


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
    public function __construct($id, Carbon $from, Carbon $until, $height, $latitude, $longitude, $name, $state, $activeDayThreshold)
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
        return 'DWDStation [id=' . $this->id . ', name=' . $this->name . ', until=' . $this->until->format('Y-m-d') . ', active? ' . $this->active . ']';
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
        return $this->toArray($this->toItem(new StationTransformer()));
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Carbon
     */
    public function getFrom(): Carbon
    {
        return $this->from;
    }

    /**
     * @return Carbon
     */
    public function getUntil(): Carbon
    {
        return $this->until;
    }

    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return mixed
     */
    public function getActive()
    {
        return $this->active;
    }

    public function setActive($activeDayThreshold)
    {
        $now = new Carbon('now', 'utc');
        $until = new Carbon($this->until);

        $diffDays = $now->diffInDays($until);

        if ($diffDays < $activeDayThreshold) {
            $this->active = true;
        } else {
            $this->active = false;
        }

    }


}