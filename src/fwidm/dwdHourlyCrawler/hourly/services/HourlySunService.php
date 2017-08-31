<?php

namespace FWidm\DWDHourlyCrawler\Hourly\Services;

use DateTime;
use FWidm\DWDHourlyCrawler\DWDConfiguration;
use FWidm\DWDHourlyCrawler\Model\DWDAbstractParameter;
use FWidm\DWDHourlyCrawler\Model\DWDsun;

/**
 * Created by PhpStorm.
 * User: Fabian-Desktop
 * Date: 12.06.2017
 * Time: 15:21
 */
class HourlySunService extends AbstractHourlyService
{
    public function __construct(string $parameter)
    {
        parent::__construct($parameter);
    }

    public function getFileName(string $stationID)
    {
        $config = DWDConfiguration::getConfiguration();
        $parameterConf = $config->dwdHourly->parameters;
        $fileName = $parameterConf->sun->shortCode . '_'
            . $stationID . $config->dwdHourly->fileExtension;
        return $fileName;
    }

    public function getFilePath(string $fileName)
    {
        $config = DWDConfiguration::getConfiguration();
        $hourlyConfig = $config->dwdHourly;
        $localPath = $config->baseDirectory  . $hourlyConfig->localBaseFolder . $hourlyConfig->parameters->sun->localFolder;
        $localFilePath = $localPath . '/' . $hourlyConfig->filePrefix . $fileName;

        return $localFilePath;
    }

    public function getFileFTPPath(string $stationID)
    {
        $config = DWDConfiguration::getConfiguration();
        $parameterConf = $config->dwdHourly->parameters;

        $fileName = $parameterConf->sun->shortCode . '_'
            . $stationID . $config->dwdHourly->fileExtension;

        $ftpPath = $config->dwdHourly->baseFTPPath . $parameterConf->sun->name
            . $config->dwdHourly->recentValuePath . $fileName;

        return $ftpPath;
    }

    public function createParameter(array $cols, DateTime $date): DWDAbstractParameter
    {
        return new DWDSun($cols[0], $date, $cols[2], $cols[3]);
    }
}