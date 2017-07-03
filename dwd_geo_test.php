<?php

use FWidm\DWDHourlyCrawler\DWDLib;
use FWidm\DWDHourlyCrawler\Hourly\Variables\DWDHourlyParameters;
use Location\Coordinate;
use Location\Formatter\Coordinate\GeoJSON;
use Carbon\Carbon;
require 'vendor/autoload.php';


$coordinatesUlm=new Coordinate(48.4391,9.9823);

$date=new DateTime();
$date->modify("-4 days");
prettyPrint("Checking for Coordinates: ".$coordinatesUlm->format(new GeoJSON()). ", @ ".$date->format(DateTime::ATOM));



$dwdLib=new DWDLib();

$vars=new DWDHourlyParameters();
$vars->addPressure()->addAirTemperature()->addCloudiness()->addPrecipitation()->addSoilTemperature()->addSun()->addWind();
//$vars->addAirTemperature()->addCloudiness();
//$vars->addCloudiness();

//$out=$dwdLib->getHourlyFailsafe($vars,$date ,$coordinatesUlm->getLat(),$coordinatesUlm->getLng());
$start=Carbon::createFromFormat("Y-m-d H","2017-06-12 00");
$out=$dwdLib->getHourlyDataByDay($vars,$start,$coordinatesUlm->getLat(),$coordinatesUlm->getLng());
prettyPrint("Got n=".count($out['values']['cloudiness'])." results!");
prettyPrint(json_encode($out,JSON_PRETTY_PRINT));


$coordinatesDellmensingen=new Coordinate(48.301669,9.900532);
echo '<hr>';
$date=DateTime::createFromFormat("Y-m-d H","2017-06-12 12");
prettyPrint("Checking for Coordinates: ".$coordinatesDellmensingen->format(new GeoJSON()).", @ ".$date->format(DateTime::ATOM));
$out=$dwdLib->getHourlyByInterval($vars,$date,$coordinatesDellmensingen->getLat(),$coordinatesDellmensingen->getLng());
prettyPrint(json_encode($out,JSON_PRETTY_PRINT));

function prettyPrint($obj)
{
    echo "<pre>";
    print_r($obj);
    echo "</pre>";
}


echo '<hr>';
$vars=new DWDHourlyParameters();
$vars->addSolar();

$coordinatesSolarTest=new Coordinate(48.6657,9.8646);
$date=DateTime::createFromFormat('YmdH:i','2013073121:00');
prettyPrint("Checking Solar for Coordinates: ".$coordinatesSolarTest->format(new GeoJSON()).", @ ".$date->format(DateTime::ATOM));
$out=$dwdLib->getHourlyByInterval($vars,$date,$coordinatesSolarTest->getLat(),$coordinatesSolarTest->getLng());
prettyPrint(json_encode($out,JSON_PRETTY_PRINT));