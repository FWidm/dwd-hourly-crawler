<?php

use FWidm\DWDHourlyCrawler\DWDLib;
use FWidm\DWDHourlyCrawler\Hourly\Variables\DWDHourlyParameters;
use Location\Coordinate;
use Location\Formatter\Coordinate\GeoJSON;

require 'vendor/autoload.php';


$coordinatesUlm=new Coordinate(48.4391,9.9823);

$date=new DateTime();
$date->modify("-4 days");
prettyPrint("Checking for Coordinates: ".$coordinatesUlm->format(new GeoJSON()). ", @ ".$date->format(DateTime::ATOM));



$dwdLib=new DWDLib();

$vars=new DWDHourlyParameters();
$vars->addPressure()->addAirTemperature()->addCloudiness()->addPrecipitation();
//$vars->addAirTemperature()->addCloudiness();
//$vars->addPrecipitation();

$out=$dwdLib->getHourlyFailsafe($vars,$date ,$coordinatesUlm->getLat(),$coordinatesUlm->getLng());

$coordinatesDellmensingen=new Coordinate(48.301669,9.900532);
prettyPrint(json_encode($out,JSON_PRETTY_PRINT));
echo '<hr>';
$date=DateTime::createFromFormat("Y-m-d H","2017-06-12 12");
prettyPrint("Checking for Coordinates: ".$coordinatesDellmensingen->format(new GeoJSON()).", @ ".$date->format(DateTime::ATOM));
$out=$dwdLib->getHourlyFailsafe($vars,$date,$coordinatesDellmensingen->getLat(),$coordinatesDellmensingen->getLng());
prettyPrint(json_encode($out,JSON_PRETTY_PRINT));

function prettyPrint($obj)
{
    echo "<pre>";
    print_r($obj);
    echo "</pre>";
}

//echo '<hr>';
//$date1=new DateTime();
//$date1->modify("-4 days");
//$date=new DateTime();
//$date->modify("+4 days");
//$out=$dwdLib->getHourlyDataByDates($vars,$date,$date1,$coordinatesDellmensingen->getLat(),$coordinatesDellmensingen->getLng());
//prettyPrint(json_encode($out,JSON_PRETTY_PRINT));
//prettyPrint(count($out[0])+count($out[1])+count($out[2]));