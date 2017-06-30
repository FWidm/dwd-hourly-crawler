<?php
/**
 * Created by PhpStorm.
 * User: fabianwidmann
 * Date: 26.06.17
 * Time: 11:56
 */
return [
    'debug' => true,

    'ftp' => [

        'url' => 'ftp-cdc.dwd.de',
        'userName' => 'anonymous',
        'userPassword' => ''
    ],
    //Configuration for the station controller.
    'dwdStations' => [
        'ftpFile' => 'pub/CDC/observations_germany/climate/hourly/pressure/recent/P0_Stundenwerte_Beschreibung_Stationen.txt',
        'localFile' => DIRECTORY_SEPARATOR.'in'.DIRECTORY_SEPARATOR.'stations.txt',
        'skipLines' => 2,
        'activeRequirementDays' => 4,
        'dateFormat' => 'Ymd',
    ],

    //Configuration for crawling hourly files
    'dwdHourly' => [

        'baseFTPPath' => '/pub/CDC/observations_germany/climate/hourly'.DIRECTORY_SEPARATOR,
        'localBaseFolder' => DIRECTORY_SEPARATOR.'output'.DIRECTORY_SEPARATOR.'hourly',
        'zipExtractionPrefix' => 'produkt',
        'parameters' => [

            'airTemperature' => [
                'name' => 'air_temperature',
                'shortCode' => 'TU',
                'stations' => 'pub/CDC/observations_germany/climate/hourly/air_temperature/recent/TU_Stundenwerte_Beschreibung_Stationen.txt',
                'localFolder' => DIRECTORY_SEPARATOR.'air_temperature'.DIRECTORY_SEPARATOR.'recent',
                'variables' => [
                    'qualityBit' => 'QN_9: Quality bit refer to ftp://ftp-cdc.dwd.de/pub/CDC/observations_germany/climate/hourly/air_temperature/historical/BESCHREIBUNG_test_obsgermany_climate_hourly_tu_historical_de.pdf',
                    'temperature2m' => 'TT_TU: temperature in 2m height - in degrees Celsius.',
                    'relativeHumidity' => 'RF_TU: relative humidity in percent.'
                ]
            ],

            'cloudiness' => [

                'name' => 'cloudiness',
                'shortCode' => 'N',
                'stations' => 'pub/CDC/observations_germany/climate/hourly/cloudiness/recent/N_Stundenwerte_Beschreibung_Stationen.txt',
                'localFolder' => DIRECTORY_SEPARATOR.'cloudiness'.DIRECTORY_SEPARATOR.'recent',
                'variables' => [
                    'qualityBit' => 'QN_8: Quality bit, see @ ftp://ftp-cdc.dwd.de/pub/CDC/observations_germany/climate/hourly/cloudiness/historical/BESCHREIBUNG_test_obsgermany_climate_hourly_cloudiness_historical_de.pdf',
                    'indexObservationType' => 'V_N_I: index to differentiate measurements done by observation or a device.',
                    'cloudiness_eights' => 'V_N: Cloudiness differentiation in eights - n/8. -1 is the error value.',
                ]

            ],

            'precipitation' => [

                'name' => 'precipitation',
                'shortCode' => 'RR',
                'stations' => 'pub/CDC/observations_germany/climate/hourly/precipitation/recent/RR_Stundenwerte_Beschreibung_Stationen.txt',
                'localFolder' => DIRECTORY_SEPARATOR.'precipitation'.DIRECTORY_SEPARATOR.'recent',
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
                'stations' => 'pub/CDC/observations_germany/climate/hourly/pressure/recent/P0_Stundenwerte_Beschreibung_Stationen.txt',
                'localFolder' => DIRECTORY_SEPARATOR.'pressure'.DIRECTORY_SEPARATOR.'recent',
                'variables' => [

                    'qualityBit' => 'QN_8: Quality bit, see @ ftp://ftp-cdc.dwd.de/pub/CDC/observations_germany/climate/hourly/pressure/historical/BESCHREIBUNG_test_obsgermany_climate_hourly_cloudiness_historical_de.pdf,',
                    'pressureSeaLevel' => 'P: Air pressure at sea level NN in hPA.',
                    'pressureStationLevel' => 'p0: Air pressure at station level in hPA.',
                ],

            ],

            'soilTemperature' => [

                'name' => 'soil_temperature'
            ],

            'solar' => [

                'name' => 'solar'
            ],

            'sun' => [

                'name' => 'sun'
            ],

            'wind' => [

                'name' => 'wind'
            ],

        ],

        'parserSettings' => [

            'dateFormat' => 'YmdH',
            'lineDelimiter' => 'eor',
            'colDelimiter' => ';'
        ],

        'recentValuePath' => '/recent/stundenwerte_',
        'filePrefix' => 'stundenwerte_',
        'fileExtension' => '_akt.zip'
    ]];