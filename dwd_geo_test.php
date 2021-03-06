<?php

use FWidm\DWDHourlyCrawler\DWDLib;
use FWidm\DWDHourlyCrawler\Hourly\Variables\DWDHourlyParameters;
use FWidm\DWDHourlyCrawler\Model\DWDCompactParameter;
use FWidm\DWDHourlyCrawler\Transformer\CompactParameterTransformer;
use FWidm\DWDHourlyCrawler\Transformer\ParameterTransformer;
use FWidm\DWDHourlyCrawler\Transformer\StationTransformer;
use FWidm\DWDHourlyCrawler\Util\FractalWrapper;
use League\Fractal\Manager;
use League\Fractal\Serializer\ArraySerializer;
use Location\Coordinate;
use Location\Formatter\Coordinate\GeoJSON;
use Carbon\Carbon;

require 'vendor/autoload.php';

function prettyPrint($obj)
{
    echo "<pre>";
    print_r($obj);
    echo "</pre>";
}

$coordinates = new Coordinate(48.398400, 9.091550);

$date = Carbon::parse('2017-09-17 00:01:00');
$date = Carbon::instance($date)->setTimezone('utc');;
prettyPrint("Checking for Coordinates: " . $coordinates->format(new GeoJSON()) . ", @ " . $date->format(DateTime::ATOM));


$dwdLib = new DWDLib("storage");

$requestParams = new DWDHourlyParameters();
$requestParams->addAirTemperature()->addCloudiness()->addPrecipitation()->addPressure()->addSoilTemperature()->addSun()->addWind()/*->add...*/;

[$param,$stations] = $dwdLib->getHourlyInInterval($requestParams, $date, $coordinates->getLat(), $coordinates->getLng());

/*
 * Print all retrieved items in the 'values' part => weather parameters as json
 */
foreach ($param as $key => $obj) {
    print "obj=$key - ".count($obj)."<br>";
    $collection=FractalWrapper::toResource($obj,new ParameterTransformer());
    prettyPrint(FractalWrapper::toJson($collection,JSON_PRETTY_PRINT));
}
/*
 * Print all stations as json
 */
foreach ($stations as $key => $obj) {
    print "obj=$key<br>";
    /* @var $obj \FWidm\DWDHourlyCrawler\Model\DWDStation */
    prettyPrint(FractalWrapper::toJson(FractalWrapper::toResource($obj,new StationTransformer()),JSON_PRETTY_PRINT));
}

print "<hr>";
$dwdCompact = new DWDCompactParameter(1, ["a" => "b"], "none", 100.3, 10, 20, Carbon::now(), 2030.45, "x");
var_dump($dwdCompact);
prettyPrint(json_encode($dwdCompact));