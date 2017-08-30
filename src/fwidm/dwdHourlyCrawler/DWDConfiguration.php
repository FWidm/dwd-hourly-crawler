<?php

namespace FWidm\DWDHourlyCrawler;

use FWidm\DWDHourlyCrawler\Exceptions\DWDLibException;
use ParseError;

/**
 * Created by PhpStorm.
 * User: Fabian-Desktop
 * Date: 12.06.2017
 * Time: 14:58
 */
class DWDConfiguration
{
    public static $configFilePath = __DIR__ . '/../../config/configuration.php';
    private static $configuration = null;

    /**
     * Returns the complete configuration file to the callee
     * @return mixed
     */
    public static function getConfiguration()
    {
        if (self::$configuration === null && file_exists(self::$configFilePath)) {
//            $jsonConfig = file_get_contents(self::$configFilePath_depr);
//            self::$configuration = json_decode($jsonConfig);

            $settings = include __DIR__ . '/../../config/configuration.php';

            self::$configuration = DWDUtil::array_to_object($settings);

            if (DWDConfiguration::$configuration === null)
                throw new ParseError('Error, configuration file could not be found or contains invalid lines.');
        }
        return DWDConfiguration::$configuration;

    }

    public static function editConfiguration($keyValPairs)
    {
        $settings = include __DIR__ . '/../../config/configuration.php';

        foreach ($keyValPairs as $key => $val) {
            if (isset($settings[$key])) {
                DWDUtil::log(self::class,$settings[$key]);
                if (is_array($settings[$key])) {
                    DWDUtil::log(self::class,"is arr, set val=".var_dump($val));

                }

            } else throw new \Exception("Unkown key (k=" . $key . ").");
        }
    }


    /**
     * Returns the _hourly configuration to the callee
     * @return mixed
     */
    public
    static function getHourlyConfiguration()
    {
        return self::getConfiguration()->dwdHourly;
    }

    /**
     * Returns the station config to the callee
     * @return mixed
     */
    public
    static function getStationConfiguration()
    {
        return self::getConfiguration()->dwdStations;
    }

    /**
     * Returns the ftp configuration
     * @return mixed
     */
    public
    static function getFTPConfiguration()
    {
        return self::getConfiguration()->ftp;
    }

    public static function isDebugEnabled()
    {
        return self::getConfiguration()->debug;
    }

    public static function setBaseDir(string $baseDirectory)
    {
        DWDUtil::log(self::class,"basedir=".$baseDirectory);
        if (isset($baseDirectory) && is_dir($baseDirectory)){
            self::getConfiguration()->baseDirectory=$baseDirectory;
        }
        else throw new DWDLibException("The specified directory (baseDirectory=".$baseDirectory." is not set or is not a directory");
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