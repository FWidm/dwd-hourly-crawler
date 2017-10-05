<?php
/**
 * Created by PhpStorm.
 * User: fabianwidmann
 * Date: 26.06.17
 * Time: 11:56
 */
return [
    'debug' => true,
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
        'loggingFileName' => DIRECTORY_SEPARATOR.'dwd_hourly.log',
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
                    'qualityLevel' => 'QN_9: quality level - refer to ftp://ftp-cdc.dwd.de/pub/CDC/observations_germany/climate/hourly/air_temperature/recent/DESCRIPTION_obsgermany_climate_hourly_tu_recent_en.pdf',
                    'temperature2m' => 'TT_TU: temperature in 2m height - in degrees Celsius.',
                    'relativeHumidity' => 'RF_TU: relative humidity in percent.',
                    'temperature2mUnit' => 'C',
                    'relativeHumidityUnit' => '%',
                ]
            ],

            'cloudiness' => [
                'name' => 'cloudiness',
                'shortCode' => 'N',
                'classification' => 'Cloudiness',
                'stations' => 'pub/CDC/observations_germany/climate/hourly/cloudiness/recent/N_Stundenwerte_Beschreibung_Stationen.txt',
                'localFolder' => DIRECTORY_SEPARATOR . 'cloudiness' . DIRECTORY_SEPARATOR . 'recent',
                'variables' => [
                    'qualityLevel' => 'QN_8: quality level, see @ ftp://ftp-cdc.dwd.de/pub/CDC/observations_germany/climate/hourly/cloudiness/recent/DESCRIPTION_obsgermany_climate_hourly_cloudiness_recent_en.pdf',
                    'indexObservationType' => 'V_N_I: index to differentiate measurements done by human observation (H) or a device (I).',
                    'cloudinessEights' => 'V_N: Cloudiness differentiation in eights - n/8. -1 is the error value.',
                    'cloudinessEightsUnit' => '% (n/8 where -1 means error)'
                ]

            ],

            'precipitation' => [
                'name' => 'precipitation',
                'shortCode' => 'RR',
                'classification' => 'Precipitation',
                'stations' => 'pub/CDC/observations_germany/climate/hourly/precipitation/recent/RR_Stundenwerte_Beschreibung_Stationen.txt',
                'localFolder' => DIRECTORY_SEPARATOR . 'precipitation' . DIRECTORY_SEPARATOR . 'recent',
                'variables' => [
                    'qualityLevel' => 'QN_8: quality level, see @ ftp://ftp-cdc.dwd.de/pub/CDC/observations_germany/climate/hourly/precipitation/recent/DESCRIPTION_obsgermany_climate_hourly_precipitation_recent_en.pdf',
                    'hourlyPrecipitation' => 'R1: Hourly precipitation in mm.',
                    'hourlyPrecipitationUnit' => 'mm',
                    'precipitationIndex' => 'RS_IND: Index - 0 no precipitation, 1 precipitation.',
                    'precipitationIndexUnit' => 'bool (0 no precipitation, 1 precipitation)',
                    'precipitationWRType' => 'WRTR: WR precipitation coding. W_R definition (see Table 55, VUB 2 Band D, 2013) is used: 0- no fallen precipitation or too little deposition (e.g., dew or frost) to form a precipitation height larger than 0.0; 1 - precipitation height only due to deposition (dew or frost) or if it cannot decided how large the part from deposition is; 2 - precipitation height only due to liquid deposition; 3 -precipitation height only due to solid precipitation; 6 - precipitation height due to fallen liquid precipitation, may also include deposition of any kind; 7 - precipitation height due to fallen solid precipitation, may also include deposition of any kind; 8 - fallen precipitation in liquid and solid form; 9 - no precipitation measurement, form of precipitation cannot be determined.',
                    'precipitationWRTypeUnit' => 'integer (0-9)',
                ],

            ],

            'pressure' => [
                'name' => 'pressure',
                'shortCode' => 'P0',
                'classification' => 'Atmosphere',
                'stations' => 'pub/CDC/observations_germany/climate/hourly/pressure/recent/P0_Stundenwerte_Beschreibung_Stationen.txt',
                'localFolder' => DIRECTORY_SEPARATOR . 'pressure' . DIRECTORY_SEPARATOR . 'recent',
                'variables' => [
                    'qualityLevel' => 'QN_8: quality level, see @ ftp://ftp-cdc.dwd.de/pub/CDC/observations_germany/climate/hourly/pressure/recent/DESCRIPTION_obsgermany_climate_hourly_pressure_recent_en.pdf',
                    'pressureSeaLevel' => 'P: Air pressure at sea level NN in hPA.',
                    'pressureSeaLevelUnit' => 'hPA',
                    'pressureStationLevel' => 'p0: Air pressure at station level in hPA.',
                    'pressureStationLevelUnit' => 'hPA',
                ],

            ],

            'soilTemperature' => [
                'name' => 'soil_temperature',
                'shortCode' => 'EB',
                'classification' => 'Temperature',
                'stations' => 'pub/CDC/observations_germany/climate/hourly/soil_temperature/recent/EB_Stundenwerte_Beschreibung_Stationen.txt',
                'localFolder' => DIRECTORY_SEPARATOR . 'soil_temperature' . DIRECTORY_SEPARATOR . 'recent',
                'variables' => [
                    'soilTemp_unit' => 'C',
                    'qualityLevel' => 'QN_2: quality level, see @ ftp://ftp-cdc.dwd.de/pub/CDC/observations_germany/climate/hourly/soil_temperature/recent/DESCRIPTION_obsgermany_climate_hourly_soil_temperature_recent_en.pdf',
                    'soilTemp_2cm_deg' => 'V_TE002: Hurly soil temperature in 2cm depth measured in degrees Celsius.',
                    'soilTemp_5cm_deg' => 'V_TE005: Hourly soil temperature in 5cm depth measured in degrees Celsius.',
                    'soilTemp_10cm_deg' => 'V_TE010: Hourly soil temperature in 10cm depth measured in degrees Celsius.',
                    'soilTemp_20cm_deg' => 'V_TE020: Hourly soil temperature in 20cm depth measured in degrees Celsius.',
                    'soilTemp_50cm_deg' => 'V_TE050: Hourly soil temperature in 50cm depth measured in degrees Celsius.',
                    'soilTemp_100cm_deg' => 'V_TE100: Hourly soil temperature in 50cm depth measured in degrees Celsius.'
                ],
            ],

            'solar' => [
                'name' => 'solar',
                'shortCode' => 'ST',
                'classification' => 'Atmosphere',
                'stations' => 'pub/CDC/observations_germany/climate/hourly/solar/ST_Stundenwerte_Beschreibung_Stationen.txt',
                'localFolder' => DIRECTORY_SEPARATOR . 'solar' . DIRECTORY_SEPARATOR . 'recent',
                'variables' => [
                    'qualityLevel' => 'QN_2: quality level, see @ ftp://ftp-cdc.dwd.de/pub/CDC/observations_germany/climate/hourly/solar/recent/DESCRIPTION_obsgermany_climate_hourly_solar_recent_en.pdf',
                    'radiationUnit' => 'J cm**-2.',
                    'sumLongWaveRadiation' => 'ATMO_STRAHL: Hourly sum of longwave downward radiation in J/cm^2.',
                    'sumDiffuseRadiation' => 'FD_STRAHL: Hourly sum of diffuse solar radiation in J/cm^2.',
                    'sumIncomingRadiation' => 'FG_STRAHL: Hourly sum of solar incoming radiation in J/cm^2.',
                    'sumSunshineDuration' => 'SD_STRAHL: Hourly sum of sunshine duration in minutes.',
                    'sumSunshineDurationUnit' => 'min',
                    'zenith' => 'ZENITH: Solar zenith angle at mid interval in degrees.',
                    'zenithUnit' => 'deg',
                ],
                //File ending for solar file was changed, no idea why
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
                    'qualityLevel' => 'QN_7: quality level, see @ ftp://ftp-cdc.dwd.de/pub/CDC/observations_germany/climate/hourly/sun/recent/DESCRIPTION_obsgermany_climate_hourly_sun_recent_en.pdf,',
                    'sunshineDuration' => 'SD_SO: hourly sunshine duration in minutes.',
                    'sunshineDurationUnit' => 'min',
                ],
            ],

            'wind' => [
                'name' => 'wind',
                'shortCode' => 'FF',
                'classification' => 'Wind',
                'stations' => 'pub/CDC/observations_germany/climate/hourly/wind/recent/FF_Stundenwerte_Beschreibung_Stationen.txt',
                'localFolder' => DIRECTORY_SEPARATOR . 'wind' . DIRECTORY_SEPARATOR . 'recent',
                'variables' => [
                    'qualityLevel' => 'QN_3: quality level, see @ ftp://ftp-cdc.dwd.de/pub/CDC/observations_germany/climate/wind/sun/recent/DESCRIPTION_obsgermany_climate_hourly_wind_recent_en.pdf,',
                    'meanWindSpeed' => 'F: mean wind speed in m/s.',
                    'meanWindSpeedUnit' => 'm s**-1',
                    'meanWindDirection' => 'D: mean wind direction in degrees.',
                    'meanWindDirectionUnit' => 'deg',
                    'uvUnit' => 'm s**-1',
                    'u' => 'Calculated U wind vector component from the given speed and direction. U component is positive for a west to east flow.',
                    'v' => 'Calculated V wind vector component from the given speed and direction. V component is positive for south to north flow (northward wind).',
                ],
            ],

        ],
    ]];