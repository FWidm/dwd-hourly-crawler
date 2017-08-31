<?php
/**
 * Created by PhpStorm.
 * User: fabianwidmann
 * Date: 26.06.17
 * Time: 11:56
 */
return [
    'debug' => false,
    //Base directory of all downloaded files - per default it's the doc root - mostly you want to point it to 'storage' or other dirs.
    'baseDirectory' => $_SERVER['DOCUMENT_ROOT'],

    //FTP Settings for the DWD FTP
    'ftp' => [
        'url' => 'ftp-cdc.dwd.de',
        'userName' => 'anonymous',
        'userPassword' => ''
    ],
    //Configuration for the station controller.
    'dwdStations' => [
        //skip the first few lines of input. In this case the header col + table formatting '----'
        'skipLines' => 2,
        //Set the threshhold for active stations - integer in days.
        'activeRequirementDays' => 4,
        'dateFormat' => 'Ymd',
    ],

    //Configuration for crawling hourly files
    'dwdHourly' => [
        'baseFTPPath' => '/pub/CDC/observations_germany/climate/hourly/',
        'localBaseFolder' => DIRECTORY_SEPARATOR . 'output' . DIRECTORY_SEPARATOR . 'hourly',
        //The prefix of the searched file that contains all the weather data e.g. 'produkt_sd_stunde_20160218_20170820_15444'
        'zipExtractionPrefix' => 'produkt',

        'recentValuePath' => '/recent/stundenwerte_',
        'filePrefix' => 'stundenwerte_',
        'fileExtension' => '_akt.zip',


        'parserSettings' => [
            'dateFormat' => 'YmdH',
            'lineDelimiter' => 'eor',
            'colDelimiter' => ';'
        ],


        'parameters' => [
            'airTemperature' => [
                'name' => 'air_temperature',
                'shortCode' => 'TU',
                'classification' => 'Temperature',
                'stations' => 'pub/CDC/observations_germany/climate/hourly/air_temperature/recent/TU_Stundenwerte_Beschreibung_Stationen.txt',
                'localFolder' => DIRECTORY_SEPARATOR . 'air_temperature' . DIRECTORY_SEPARATOR . 'recent',
                'variables' => [
                    'qualityBit' => 'QN_9: Quality bit refer to ftp://ftp-cdc.dwd.de/pub/CDC/observations_germany/climate/hourly/air_temperature/historical/BESCHREIBUNG_test_obsgermany_climate_hourly_tu_historical_de.pdf',
                    'temperature2m' => 'TT_TU: temperature in 2m height - in degrees Celsius.',
                    'relativeHumidity' => 'RF_TU: relative humidity in percent.'
                ]
            ],

            'cloudiness' => [
                'name' => 'cloudiness',
                'shortCode' => 'N',
                'classification' => 'Cloudiness',
                'stations' => 'pub/CDC/observations_germany/climate/hourly/cloudiness/recent/N_Stundenwerte_Beschreibung_Stationen.txt',
                'localFolder' => DIRECTORY_SEPARATOR . 'cloudiness' . DIRECTORY_SEPARATOR . 'recent',
                'variables' => [
                    'qualityBit' => 'QN_8: Quality bit, see @ ftp://ftp-cdc.dwd.de/pub/CDC/observations_germany/climate/hourly/cloudiness/historical/BESCHREIBUNG_test_obsgermany_climate_hourly_cloudiness_historical_de.pdf',
                    'indexObservationType' => 'V_N_I: index to differentiate measurements done by observation or a device.',
                    'cloudiness_eights' => 'V_N: Cloudiness differentiation in eights - n/8. -1 is the error value.',
                ]

            ],

            'precipitation' => [
                'name' => 'precipitation',
                'shortCode' => 'RR',
                'classification' => 'Precipitation',
                'stations' => 'pub/CDC/observations_germany/climate/hourly/precipitation/recent/RR_Stundenwerte_Beschreibung_Stationen.txt',
                'localFolder' => DIRECTORY_SEPARATOR . 'precipitation' . DIRECTORY_SEPARATOR . 'recent',
                'variables' => [
                    'qualityBit' => 'QN_8: Quality bit, see @ ftp://ftp-cdc.dwd.de/pub/CDC/observations_germany/climate/hourly/cloudiness/historical/BESCHREIBUNG_test_obsgermany_climate_hourly_cloudiness_historical_de.pdf',
                    'hourlyPrecipitation' => 'R1: Hourly precipitation in mm.',
                    'precipitationIndex' => 'RS_IND: Index - 0 no precipitation, 1 precipitation.',
                    'precipitationWRType' => 'WRTR: WR precipitation coding.',
                ],

            ],

            'pressure' => [
                'name' => 'pressure',
                'shortCode' => 'P0',
                'classification' => 'Atmosphere',
                'stations' => 'pub/CDC/observations_germany/climate/hourly/pressure/recent/P0_Stundenwerte_Beschreibung_Stationen.txt',
                'localFolder' => DIRECTORY_SEPARATOR . 'pressure' . DIRECTORY_SEPARATOR . 'recent',
                'variables' => [
                    'qualityBit' => 'QN_8: Quality bit, see @ ftp://ftp-cdc.dwd.de/pub/CDC/observations_germany/climate/hourly/pressure/historical/BESCHREIBUNG_test_obsgermany_climate_hourly_cloudiness_historical_de.pdf,',
                    'pressureSeaLevel' => 'P: Air pressure at sea level NN in hPA.',
                    'pressureStationLevel' => 'p0: Air pressure at station level in hPA.',
                ],

            ],

            'soilTemperature' => [
                'name' => 'soil_temperature',
                'shortCode' => 'EB',
                'classification' => 'Temperature',
                'stations' => 'pub/CDC/observations_germany/climate/hourly/soil_temperature/recent/EB_Stundenwerte_Beschreibung_Stationen.txt',
                'localFolder' => DIRECTORY_SEPARATOR . 'soil_temperature' . DIRECTORY_SEPARATOR . 'recent',
                'variables' => [
                    'qualityBit' => 'QN_2: Quality bit, see @ ftp://ftp-cdc.dwd.de/pub/CDC/observations_germany/climate/hourly/soil_temperature/recent/DESCRIPTION_obsgermany_climate_hourly_soil_temperature_recent_en.pdf,',
                    '$soilTemp_2cm_deg' => 'V_TE002: Hurly soil temperature in 2cm depth measured in degrees Celsius.',
                    '$soilTemp_5cm_deg' => 'V_TE005: Hourly soil temperature in 5cm depth measured in degrees Celsius.',
                    '$soilTemp_10cm_deg' => 'V_TE010: Hourly soil temperature in 10cm depth measured in degrees Celsius.',
                    '$soilTemp_20cm_deg' => 'V_TE020: Hourly soil temperature in 20cm depth measured in degrees Celsius.',
                    '$soilTemp_50cm_deg' => 'V_TE050: Hourly soil temperature in 50cm depth measured in degrees Celsius.',
                    '$soilTemp_100cm_deg' => 'V_TE100: Hourly soil temperature in 50cm depth measured in degrees Celsius.'
                ],
            ],

            'solar' => [
                'name' => 'solar',
                'shortCode' => 'ST',
                'classification' => 'Atmosphere',
                'stations' => 'pub/CDC/observations_germany/climate/hourly/solar/ST_Stundenwerte_Beschreibung_Stationen.txt',
                'localFolder' => DIRECTORY_SEPARATOR . 'solar' . DIRECTORY_SEPARATOR . 'recent',
                'variables' => [
                    'qualityBit' => 'QN_2: Quality bit, see @ ftp://ftp-cdc.dwd.de/pub/CDC/observations_germany/climate/hourly/solar/recent/DESCRIPTION_obsgermany_climate_hourly_solar_recent_en.pdf,',
                    '$sumLongwaveRadiation' => 'ATMO_STRAHL: Hourly sum of longwave downward radiation in J/cm^2.',
                    '$sumDiffuseRadiation' => 'FD_STRAHL: Hourly sum of diffuse solar radiation in J/cm^2.',
                    '$sumIncomingRadiation' => 'FG_STRAHL: Hourly sum of solar incoming radiation in J/cm^2.',
                    '$sumSunshineDuration' => 'SD_STRAHL: Hourly sum of sunshine duration in minutes.',
                    '$zenith' => 'ZENITH: Solar zenith angle at mid interval in degrees.'
                ],
                //File extension for solar file was changed, no idea why
                'fileExtension' => '_row.zip',
                //Other folder structure - no /recent/stundenwerte_...
                'recentValuePath' => '/stundenwerte_',
                //other dateformat including the minutes after a colon.
                'dateFormat' => 'YmdH:i'
            ],

            'sun' => [
                'name' => 'sun',
                'shortCode' => 'SD',
                'classification' => 'Atmosphere',
                'stations' => 'pub/CDC/observations_germany/climate/hourly/sun/recent/SD_Stundenwerte_Beschreibung_Stationen.txt',
                'localFolder' => DIRECTORY_SEPARATOR . 'sun' . DIRECTORY_SEPARATOR . 'recent',
                'variables' => [
                    'qualityBit' => 'QN_7: Quality bit, see @ ftp://ftp-cdc.dwd.de/pub/CDC/observations_germany/climate/hourly/sun/recent/DESCRIPTION_obsgermany_climate_hourly_sun_recent_en.pdf,',
                    '$sunshineDuration' => 'SD_SO: hourly sunshine duration in minutes..',
                ],
            ],

            'wind' => [
                'name' => 'wind',
                'shortCode' => 'FF',
                'classification' => 'Wind',
                'stations' => 'pub/CDC/observations_germany/climate/hourly/wind/recent/FF_Stundenwerte_Beschreibung_Stationen.txt',
                'localFolder' => DIRECTORY_SEPARATOR . 'wind' . DIRECTORY_SEPARATOR . 'recent',
                'variables' => [
                    'qualityBit' => 'QN_3: Quality bit, see @ ftp://ftp-cdc.dwd.de/pub/CDC/observations_germany/climate/wind/sun/recent/DESCRIPTION_obsgermany_climate_hourly_wind_recent_en.pdf,',
                    '$meanWindSpeed' => 'F: mean wind speed in m/s.',
                    '$meanWindDirection' => 'D: mean wind direction in degrees.',
                    '$u' => 'Calculated U wind vector component from the given speed and direction.',
                    '$v' => 'Calculated V wind vector component from the given speed and direction.',
                ],
            ],

        ],
    ]];