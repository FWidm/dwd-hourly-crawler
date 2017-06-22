<?php

namespace FWidm\DWDHourlyCrawler;
use ZipArchive;

/**
 * Created by PhpStorm.
 * User: Fabian-Desktop
 * Date: 13.06.2017
 * Time: 16:40
 */
class DWDUtil
{
    public static function getFileNameFromPath($path)
    {
        $split = explode("/", $path);
        $name = end($split);
        return $name;
    }

    /**
     * Extract the single file we need to use to get the data.
     *
     * @param $zipFile - zip file we want to exctract from
     * @param $extractionPrefix - part of the file's prefix we want to match
     * @return null|string
     */
    static function getDataFileFromZip($zipFile, $extractionPrefix)
    {
        $zip = new ZipArchive;
        $res = $zip->open($zipFile);
        if ($res === TRUE) {
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

        }
        return null;
    }
}