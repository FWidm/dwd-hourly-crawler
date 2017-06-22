# DWD-Crawler

This package contains methods to query the DWD FTP by specifying the parameters you want to query, as well as a date and latitude + longitude.

## Data Source
All data is retrieved from the public data set made available by the German Meteorological Service (DWD). 

Root of the public CDC FTP: `ftp://ftp-cdc.dwd.de/pub/CDC/`.

![](doc/img/dwd_logo_258x69.png).

## Features

- Queries recent hourly data on the public DWD FTP for all of Germany
    - [Available params](ftp://ftp-cdc.dwd.de/pub/CDC/observations_germany/climate/hourly/)
- Contains a safe query option that queries several nearest stations to get one result
- Parses the Output into different Objects that contain all of the data the file contains plus the short description for 
all the parameters.

## Example
```php
<?php
$coordinatesUlm=new Coordinate(48.4391,9.9823);
$dwdLib=new DWDLib();

//add variables
$vars=new DWDHourlyParameters();
$vars->addPrecipitation()/*->add...*/;

$out=$dwdLib->getHourlyExperimental($vars,$date ,$coordinatesUlm->getLat(),$coordinatesUlm->getLng());
/*
 * json_encode($out)={
           "precipitation": [
               {
                   "quality": 1,
                   "precipitationHeight_mm": "0.0",
                   "precipitationIndex": "0",
                   "preciptionWRType": "-999",
                   "paramDescription": {
                       "qualityBit": "QN_8: Quality bit, see @ ftp:\/\/ftp-cdc.dwd.de\/pub\/CDC\/observations_germany\/climate\/hourly\/cloudiness\/historical\/BESCHREIBUNG_test_obsgermany_climate_hourly_cloudiness_historical_de.pdf",
                       "hourlyPrecipitation": "R1: Hourly precipitation in mm.",
                       "precipitationIndex": "RS_IND: Index - 0 no precipitation, 1 precipitation.",
                       "precipitationWRType": "WRTR: WR precipitation coding."
                   },
                   "stationId": 15444,
                   "date": "2017-06-15T17:00:00+02:00"
               }
           ]
       }
 */
```
