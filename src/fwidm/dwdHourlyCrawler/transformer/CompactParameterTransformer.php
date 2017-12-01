<?php
/**
 * User: Fabian Widmann
 * Date: 30.11.17
 * Time: 15:37
 */

namespace FWidm\DWDHourlyCrawler\Transformer;


use FWidm\DWDHourlyCrawler\Model\DWDCompactParameter;
use League\Fractal\TransformerAbstract;

class CompactParameterTransformer extends TransformerAbstract
{
    public function transform(DWDCompactParameter $parameter)
    {
        return [
            'station_id' => $parameter->getStationID(),
            'description' => $parameter->getDescription(),
            'classification' => $parameter->getClassification(),
            'distance' => $parameter->getDistance(),
            'lon' => $parameter->getLongitude(),
            'lat' => $parameter->getLatitude(),
            'date' => $parameter->getDate()->toIso8601String(),
            'value' => $parameter->getValue(),
            'type' => $parameter->getType(),
        ];
    }

}