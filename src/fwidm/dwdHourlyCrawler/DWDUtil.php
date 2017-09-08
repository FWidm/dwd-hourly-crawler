<?php

namespace FWidm\DWDHourlyCrawler;

use Carbon\Carbon;
use FWidm\DWDHourlyCrawler\Exceptions\DWDLibException;
use FWidm\DWDHourlyCrawler\Model\DWDStation;
use Location\Coordinate;
use Location\Distance\Vincenty;
use stdClass;
use ZipArchive;

/**
 * Created by PhpStorm.
 * User: Fabian-Desktop
 * Date: 13.06.2017
 * Time: 16:40
 */
class DWDUtil
{

    /**
     * @param string $path path to the output dir.
     */
    public static function initializeOutputFolder(string $path)
    {
        $path = DWDConfiguration::getConfiguration()->baseDirectory . $path;
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    /** Splits (FTP) Paths with forward slashes to get the file name.
     * @param string $path
     * @return string
     */
    public static function getFileNameFromPath(string $path)
    {
        $split = explode('/', $path);
        $name = (string)end($split);

        return $name;
    }

    /**
     * Extract the single file we need to use to get the data.
     *
     * @param $zipFile - zip file we want to exctract from
     * @param $extractionPrefix - part of the file's prefix we want to match
     * @return null|string
     */
    static function getDataFromZip($zipFile, $extractionPrefix)
    {
        $zip = new ZipArchive;
        self::log(self::class, "zip=" . $zipFile);
        if ($zip->open($zipFile)) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
//            print_r(basename($stat['name']) . '<br>');
                //check if the file starts with the prefix
                if (substr($stat['name'], 0, strlen($extractionPrefix)) === $extractionPrefix) {
                    //echo $zip->getFromName($stat['name']);
                    return $zip->getFromName($stat['name']);
                }
            }
            $zip->close();

        } else {
            throw new DWDLibException("zip content is empty! zip file count=" . $zip->numFiles);

        }
        return null;
    }

    /** Converts an array recursively to obj - taken from Jacob Relkin @ https://stackoverflow.com/a/4790485
     * @param $array
     * @return stdClass
     */
    static function array_to_object($array)
    {
        $obj = new stdClass;
        foreach ($array as $k => $v) {
            if (strlen($k)) {
                if (is_array($v)) {
                    $obj->{$k} = self::array_to_object($v); //RECURSION
                } else {
                    $obj->{$k} = $v;
                }
            }
        }
        return $obj;
    }

    static function log($objectType, $content, $htmlOutput = false)
    {
        //todo: replace with monolog or another framework.
        if (DWDConfiguration::isDebugEnabled()) {
            $date = new Carbon();
            if ($htmlOutput) echo "<div style=\"white-space: pre-wrap;\">";

            print($date->format(Carbon::ISO8601) . '@' . $objectType . ' msg=');
            print_r($content);
            if ($htmlOutput) echo "</div>";
            else
                echo "/n";
        }
    }

    /**
     * Calculates the distance from coordinate to station in either meters (default) or km
     * @param Coordinate $coordinate
     * @param DWDStation $station
     * @param string $unit - default is "m" for meters, can be set to "km" for km.
     * @return float - distance in meters or km
     */
    public static function calculateDistanceToStation(Coordinate $coordinate,DWDStation $station, $unit="m")
    {
        $coordinateStation = new Coordinate($station->getLatitude(), $station->getLongitude()); // Mauna Kea Summit

        $calculator = new Vincenty();
        $distance_meters = $calculator->getDistance($coordinate, $coordinateStation); // in meters
        if ($unit=="km"){
            return $distance_meters/1000.0;
        }
        else
            return $distance_meters;
    }

}