<?php

namespace FWidm\DWDHourlyCrawler\Hourly;

use DateTime;
use FWidm\DWDHourlyCrawler\DWDConfiguration;
use FWidm\DWDHourlyCrawler\DWDUtil;
use FWidm\DWDHourlyCrawler\Model\DWDPressure;
use ParseError;

/**
 * Created by PhpStorm.
 * User: Fabian-Desktop
 * Date: 12.06.2017
 * Time: 15:21
 */
class DWDHourlyPressureController extends DWDAbstractHourlyController
{
    public function __construct(string $parameter)
    {
        parent::__construct($parameter);
    }
    /**
     * Parse the textual representation of DWD Data, can be filtered by specifying before and after.
     * This means if you specify after - you will get timestamps after the specified team
     * If you also specify before you can pinpoint values.
     * @param String $content - Textual representation of a DWD Hourly/Recent pressure file.
     * @param DateTime|null $after - returns all values after the specific time
     * @param DateTime|null $before - returns all values after $after AND after if set.
     * @return array
     * @throws ParseError
     */
    public function parseHourlyData(String $content, DateTime $after = null, DateTime $before = null): array
    {
        // eliminate multiple spaces, replace by nothing
        //$content = preg_replace('!\s+!', '', $content);
        $lines = explode('eor', $content);
        $pressureArray = array();

        for ($i = sizeof($lines) - 1; $i > 0; $i--) {
            /*
             * [0] => STATIONS_ID
             * [1] => MESS_DATUM
             * [2] => QN_8
             * [3] => P
             * [4] => P0
             */

            $cols = explode(';', $lines[$i]);

            //skip last line
            if (sizeof($cols) < 5)
                continue;

            $date = DateTime::createFromFormat("YmdH", $cols[1]);
            if ($date) {
                //todo: Schöner...
                switch (func_num_args()) {
                    //After is set
                    case 2: {
                        if ($date > $after) {
                            $pressureData = new DWDPressure($cols[0], $date, $cols[2], $cols[3], $cols[4]);
                            $pressureArray[] = $pressureData;
                        } else
                            //break from loop and switch
                            break 2;

                        break;
                    }
                    //After & Before are set
                    case 3: {
                        if ($date < $before && $date > $after) {
                            $pressureData = new DWDPressure($cols[0], $date, $cols[2], $cols[3], $cols[4]);
                            $pressureArray[] = $pressureData;
                        } else
                            if ($date < $after) {
                                //break from loop and switch
                                break 2;
                            }

                        break;
                    }
                    default: {
                        $pressureData = new DWDPressure($cols[0], $date, $cols[2], $cols[3], $cols[4]);
                        $pressureArray[] = $pressureData;
                    }
                }

            } else
                throw new ParseError("Error while parsing date: col=" . $cols[1] . " | date=" . $date);
        }

        return $pressureArray;
    }

    public function getFileName(string $stationID)
    {
        $config = DWDConfiguration::getConfiguration();
        $parameterConf = $config->dwdHourly->parameters;
        $fileName = $parameterConf->pressure->shortCode . '_'
            . $stationID . $config->dwdHourly->fileExtension;
        return $fileName;
    }

    public function getFilePath(string $fileName)
    {
        $config = DWDConfiguration::getConfiguration();
        $hourlyConfig = $config->dwdHourly;
        $localPath = $_SERVER['DOCUMENT_ROOT'] . $hourlyConfig->localBaseFolder . $hourlyConfig->parameters->precipitation->localFolder;
        $localFilePath = $localPath . '/' . $hourlyConfig->filePrefix . $fileName;

        return $localFilePath;
    }

    public function getFileFTPPath(string $stationID)
    {
        $config = DWDConfiguration::getConfiguration();
        $parameterConf = $config->dwdHourly->parameters;

        $fileName = $parameterConf->pressure->shortCode . '_'
            . $stationID . $config->dwdHourly->fileExtension;

        $ftpPath = $config->dwdHourly->baseFTPPath . $parameterConf->pressure->name
            . $config->dwdHourly->recentValuePath . $fileName;

        return $ftpPath;
    }

}