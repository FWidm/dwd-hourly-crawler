<?php

namespace FWidm\DWDHourlyCrawler\Hourly\Services;

use DateTime;
use FWidm\DWDHourlyCrawler\DWDConfiguration;
use FWidm\DWDHourlyCrawler\Model\DWDAbstractParameter;
use FWidm\DWDHourlyCrawler\Model\DWDSoilTemp;
use FWidm\DWDHourlyCrawler\Model\DWDStation;
use Location\Coordinate;

/**
 * Created by PhpStorm.
 * User: Fabian-Desktop
 * Date: 12.06.2017
 * Time: 15:21
 */
class HourlySoilTempService extends AbstractHourlyService
{
    public function __construct(string $parameter)
    {
        parent::__construct($parameter);
    }

    public function getFileName(string $stationID)
    {
        $config = DWDConfiguration::getConfiguration();
        $parameterConf = $config->dwdHourly->parameters;
        $fileName = $parameterConf->soilTemperature->shortCode . '_'
            . $stationID . $config->dwdHourly->fileExtension;
        return $fileName;
    }

    public function getFilePath(string $fileName)
    {
        $config = DWDConfiguration::getConfiguration();
        $hourlyConfig = $config->dwdHourly;
        $localPath = $config->baseDirectory  . $hourlyConfig->localBaseFolder . $hourlyConfig->parameters->soilTemperature->localFolder;
        $localFilePath = $localPath . '/' . $hourlyConfig->filePrefix . $fileName;

        return $localFilePath;
    }

    public function getFileFTPPath(string $stationID)
    {
        $config = DWDConfiguration::getConfiguration();
        $parameterConf = $config->dwdHourly->parameters;

        $fileName = $parameterConf->soilTemperature->shortCode . '_'
            . $stationID . $config->dwdHourly->fileExtension;

        $ftpPath = $config->dwdHourly->baseFTPPath . $parameterConf->soilTemperature->name
            . $config->dwdHourly->recentValuePath . $fileName;

        return $ftpPath;
    }

    public function createParameter(array $cols, DateTime $date, DWDStation $nearestStation, Coordinate $coordinate): DWDAbstractParameter
    {
        return new DWDSoilTemp($nearestStation, $coordinate, $cols[0], $date, $cols[2], $cols[3], $cols[4], $cols[5], $cols[6], $cols[7], $cols[8]);
    }
}