<?php

namespace FWidm\DWDHourlyCrawler\Hourly\Services;

use DateTime;
use FWidm\DWDHourlyCrawler\DWDConfiguration;
use FWidm\DWDHourlyCrawler\Model\DWDAbstractParameter;
use FWidm\DWDHourlyCrawler\Model\DWDSolar;

/**
 * Created by PhpStorm.
 * User: Fabian-Desktop
 * Date: 12.06.2017
 * Time: 15:21
 */
class HourlySolarService extends AbstractHourlyService
{
    public function __construct(string $parameter)
    {
        parent::__construct($parameter);
    }

    public function getTimeFormat(): string
    {
        return DWDConfiguration::getHourlyConfiguration()->parameters->solar->dateFormat;
    }

    public function getFileName(string $stationID)
    {
        $config = DWDConfiguration::getConfiguration();
        $parameterConf = $config->dwdHourly->parameters;
        $fileName = $parameterConf->solar->shortCode . '_'
            . $stationID . $parameterConf->solar->fileExtension;
        return $fileName;
    }

    public function getFilePath(string $fileName)
    {
        $config = DWDConfiguration::getConfiguration();
        $hourlyConfig = $config->dwdHourly;
        $localPath = $config->baseDirectory . $hourlyConfig->localBaseFolder . $hourlyConfig->parameters->solar->localFolder;
        $localFilePath = $localPath . '/' . $hourlyConfig->filePrefix . $fileName;

        return $localFilePath;
    }

    public function getFileFTPPath(string $stationID)
    {
        $config = DWDConfiguration::getConfiguration();
        $parameterConf = $config->dwdHourly->parameters;

        $fileName = $parameterConf->solar->shortCode . '_'
            . $stationID . $parameterConf->solar->fileExtension;

        $ftpPath = $config->dwdHourly->baseFTPPath . $parameterConf->solar->name
            . $parameterConf->solar->recentValuePath . $fileName;

        return $ftpPath;
    }

    public function createParameter(array $cols, DateTime $date): DWDAbstractParameter
    {
        return new DWDSolar($cols[0], $date, $cols[2], $cols[3], $cols[4], $cols[5], $cols[6], $cols[7]);
    }
}