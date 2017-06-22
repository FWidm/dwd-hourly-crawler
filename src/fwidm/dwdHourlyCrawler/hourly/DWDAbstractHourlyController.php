<?php
namespace FWidm\DWDHourlyCrawler\Hourly;

use DateTime;
use FWidm\DWDHourlyCrawler\Model\DWDStation;

/**
 * Created by PhpStorm.
 * User: Fabian-Desktop
 * Date: 16.06.2017
 * Time: 09:37
 */
abstract class DWDAbstractHourlyController
{
    public abstract function retrieveFile(DWDStation $nearestStation, $forceDownloadFile=false);

    public abstract function getStations(bool $activeOnly=false);

    public abstract function parseHourlyData(String $content, DateTime $after = null, DateTime $before = null): array;

}
