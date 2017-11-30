<?php

use FWidm\DWDHourlyCrawler\DWDLib;
use FWidm\DWDHourlyCrawler\Hourly\Variables\DWDHourlyParameters;
use FWidm\DWDHourlyCrawler\Model\DWDCompactParameter;
use FWidm\DWDHourlyCrawler\Transformer\CompactParameterTransformer;
use League\Fractal\Manager;
use League\Fractal\Serializer\ArraySerializer;
use League\Fractal\Serializer\DataArraySerializer;
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

$coordinates=new Coordinate(48.398400,9.091550);

$date=Carbon::parse('2017-09-17 00:01:00');
prettyPrint("Checking for Coordinates: ".$coordinates->format(new GeoJSON()). ", @ ".$date->format(DateTime::ATOM));



$dwdLib=new DWDLib("storage");

$vars=new DWDHourlyParameters();
$vars->addAirTemperature()->addCloudiness()->addPrecipitation()->addPressure()->addSoilTemperature()->addSun()->addWind()/*->add...*/;
//$vars->addCloudiness();

//$out=$dwdLib->getHourlyFailsafe($vars,$date ,$coordinatesUlm->getLat(),$coordinatesUlm->getLng());
$out=$dwdLib->getHourlyByInterval($vars,$date,$coordinates->getLat(),$coordinates->getLng());
//prettyPrint("Got n=".count($out['values']['cloudiness'])." results!");
var_dump($out);
foreach($out['values'] as $key =>  $obj) {
    print "obj=$key<br>";
    foreach ($obj as $value){
        /* @var $value \FWidm\DWDHourlyCrawler\Model\DWDAbstractParameter */
        prettyPrint((string)$value->exportSingleVariables()[0]);

//        prettyPrint(json_encode($value->exportSingleVariables(),JSON_PRETTY_PRINT));
    }

}
//echo '<hr>';
//$vars=new DWDHourlyParameters();
//$vars->addSolar();
//
//$coordinatesSolarTest=new Coordinate(48.6657,9.8646);
//$date=DateTime::createFromFormat('YmdH:i','2013073111:00');
//prettyPrint("Checking Solar for Coordinates: ".$coordinatesSolarTest->format(new GeoJSON()).", @ ".$date->format(DateTime::ATOM));
//$out=$dwdLib->getHourlyByInterval($vars,$date,$coordinatesSolarTest->getLat(),$coordinatesSolarTest->getLng());
//prettyPrint(json_encode($out,JSON_PRETTY_PRINT));
//if($out) {
//    foreach ($out['values'] as $key => $obj) {
//        print "obj=$key<br>";
//        foreach ($obj as $value) {
//            /* @var $value \FWidm\DWDHourlyCrawler\Model\DWDAbstractParameter */
//
//            prettyPrint(json_encode($value->exportSingleVariables(), JSON_PRETTY_PRINT));
//        }
//
//    }
//}
print "<hr>";
$dwdCompact = new DWDCompactParameter(1,["a"=>"b"],"none",100.3,10,20,Carbon::now(),2030.45,"x");
var_dump($dwdCompact);

prettyPrint(json_encode($dwdCompact));
$resource=$dwdCompact->toResource();

$manager = new Manager();
$manager->setSerializer(new ArraySerializer());

// Run all transformers
prettyPrint($manager->createData($resource)->toJson(JSON_PRETTY_PRINT));