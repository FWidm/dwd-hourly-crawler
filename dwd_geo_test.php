<?php

use FWidm\DWDHourlyCrawler\DWDLib;
use FWidm\DWDHourlyCrawler\Hourly\Variables\DWDHourlyParameters;
use FWidm\DWDHourlyCrawler\Model\DWDCompactParameter;
use FWidm\DWDHourlyCrawler\Transformer\ParameterTransformer;
use FWidm\DWDHourlyCrawler\Transformer\StationTransformer;
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
prettyPrint("Checking for Coordinates: " . $coordinates->format(new GeoJSON()) . ", @ " . $date->format(DateTime::ATOM));


$dwdLib = new DWDLib("storage");

$vars = new DWDHourlyParameters();
$vars->addAirTemperature()->addCloudiness()->addPrecipitation()->addPressure()->addSoilTemperature()->addSun()->addWind()/*->add...*/
;

$out = $dwdLib->getHourlyByInterval($vars, $date, $coordinates->getLat(), $coordinates->getLng());
//prettyPrint("Got n=".count($out['values']['cloudiness'])." results!");
//var_dump($out);
foreach ($out['values'] as $key => $obj) {
    print "obj=$key<br>";
    foreach ($obj as $value) {
        /* @var $value \FWidm\DWDHourlyCrawler\Model\DWDAbstractParameter */
        prettyPrint($value->toJson($value->toResource(new ParameterTransformer()), JSON_PRETTY_PRINT));

    }
}

foreach ($out['stations'] as $key => $obj) {
    print "obj=$key<br>";
    /* @var $obj \FWidm\DWDHourlyCrawler\Model\DWDStation */
    prettyPrint($obj->toJson($obj->toResource(new StationTransformer()), JSON_PRETTY_PRINT));
}

print "<hr>";
$dwdCompact = new DWDCompactParameter(1, ["a" => "b"], "none", 100.3, 10, 20, Carbon::now(), 2030.45, "x");
var_dump($dwdCompact);
prettyPrint(json_encode($dwdCompact));