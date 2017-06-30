<?php

namespace FWidm\DWDHourlyCrawler\Model\Transformers;

use FWidm\DWDHourlyCrawler\Model\DWDStation;
use League\Fractal\TransformerAbstract;

/**
 * Created by PhpStorm.
 * User: fabianwidmann
 * Date: 30.06.17
 * Time: 11:13
 */
class DWDStationTransformer extends TransformerAbstract
{
    /**
     * @param DWDStation $station
     * @return array
     */
    public function transform(DWDStation $station)
    {
        return [
            'id' => (int)$station->getId(),
            'name' => $station->getName(),
            'state' => $station->getState(),
            'from' => $station->getFrom(),
            'until' => $station->getUntil(),
            'height' => $station->getHeight(),
            'location' => [
                'latitude' => $station->getLatitude(),
                'longitude' => $station->getLongitude(),
            ],
            'active' => $station->isActive()
        ];
    }
}