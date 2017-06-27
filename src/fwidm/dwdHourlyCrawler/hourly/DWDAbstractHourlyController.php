<?php

namespace FWidm\DWDHourlyCrawler\Hourly;

use DateTime;
use FWidm\DWDHourlyCrawler\DWDConfiguration;
use FWidm\DWDHourlyCrawler\DWDUtil;


/**
 * Created by PhpStorm.
 * User: Fabian-Desktop
 * Date: 16.06.2017
 * Time: 09:37
 */
abstract class DWDAbstractHourlyController
{
    private $parameter;

    /**
     * DWDAbstractHourlyController constructor.
     * @param $parameter
     */
    public function __construct(string $parameter)
    {
        $this->parameter = $parameter;
    }


    public function getParameter():string
    {
        return $this->parameter;
    }

    public abstract function parseHourlyData(String $content, DateTime $after = null, DateTime $before = null): array;

    public abstract function getFileFTPPath(string $stationID);

    public abstract function getFileName(string $stationID);

    public abstract function getFilePath(string $fileName);

    public function getStationFTPPath(string $ftpPath)
    {
        $fileName = DWDUtil::getFileNameFromPath($ftpPath);
        $filePath = $_SERVER['DOCUMENT_ROOT'] . DWDConfiguration::getConfiguration()->dwdHourly->localBaseFolder . '/' . $fileName;
        return $filePath;
    }

}
