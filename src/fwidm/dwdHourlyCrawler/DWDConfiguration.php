<?php
namespace FWidm\DWDHourlyCrawler;

use ParseError;

/**
 * Created by PhpStorm.
 * User: Fabian-Desktop
 * Date: 12.06.2017
 * Time: 14:58
 */
class DWDConfiguration
{
    public static $configFilePath = __DIR__.'/../../config/configuration.json';
    private static $configuration = null;

    /**
     * Returns the complete configuration file to the callee
     * @return mixed
     */
    public static function getConfiguration()
    {
        if (DWDConfiguration::$configuration === null) {
            echo self::$configFilePath;
            $jsonConfig = file_get_contents(DWDConfiguration::$configFilePath);
            DWDConfiguration::$configuration = json_decode($jsonConfig);
            if (DWDConfiguration::$configuration === null)
                throw new ParseError('Error, configuration file could not be found or contains invalid json. Expected path: "config/configuration.json"');
        }
        return DWDConfiguration::$configuration;

    }


    /**
     * Returns the _hourly configuration to the callee
     * @return mixed
     */
    public static function getHourlyConfiguration()
    {
        return self::getConfiguration()->dwdHourly;
    }

    /**
     * Returns the station config to the callee
     * @return mixed
     */
    public static function getStationConfiguration()
    {
        return self::getConfiguration()->dwdStations;
    }

    /**
     * Returns the ftp configuration
     * @return mixed
     */
    public static function getFTPConfiguration()
    {
        return self::getConfiguration()->ftp;
    }

    /*
        function writeConfiguration($filePath)
        {
            $url = "ftp-cdc.dwd.de";
            $localStationFile = 'in/stations.txt';
            $serverStationFile = 'pub/CDC/observations_germany/climate/daily/kl/historical/KL_Tageswerte_Beschreibung_Stationen.txt';
            $userName = 'anonymous';
            $userPassword = '';

            $settings = array(
                'url' => $url,
                'userName' => $userName,
                'userPassword' => $userPassword,
                'ftpFile' => $serverStationFile,
                'localFile' => $localStationFile
            );
            echo json_encode($settings);
            $objectHandle = fopen($filePath, 'w');
            fwrite($objectHandle, json_encode($settings, JSON_PRETTY_PRINT));
        }*/

}