<?php
/**
 * User: Fabian Widmann
 * Date: 30.11.17
 * Time: 15:37
 */

namespace FWidm\DWDHourlyCrawler\Transformer;


use FWidm\DWDHourlyCrawler\Model\DWDStation;
use League\Fractal\TransformerAbstract;

class StationTransformer extends TransformerAbstract
{
    /**
     * @param DWDStation $station
     * @return array
     */
    public function transform(DWDStation $station)
    {
        return [
            'id' => $station->getId(),
            'from' => $station->getFrom()->toIso8601String(),
            'until' => $station->getUntil()->toIso8601String(),
            'name' => $station->getName(),
            'state' => $station->getState(),
            'height' => $station->getHeight(),
            'lon' => $station->getLongitude(),
            'lat' => $station->getLatitude(),
            'active' => $station->getActive(),
        ];
    }

}