<?php

namespace FWidm\DWDHourlyCrawler\Hourly;

use Carbon\Carbon;
use DateTime;
use FWidm\DWDHourlyCrawler\DWDConfiguration;
use FWidm\DWDHourlyCrawler\DWDUtil;
use FWidm\DWDHourlyCrawler\Model\DWDSoilTemp;
use FWidm\DWDHourlyCrawler\Model\DWDSolar;
use ParseError;

/**
 * Created by PhpStorm.
 * User: Fabian-Desktop
 * Date: 12.06.2017
 * Time: 15:21
 */
class DWDHourlySolarController extends DWDAbstractHourlyController
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
        $data = array();

        for ($i = sizeof($lines) - 1; $i > 0; $i--) {
            /*
             * STATIONS_ID;MESS_DATUM;QN_2;V_TE002;V_TE005;V_TE010;V_TE020;V_TE050;V_TE100;eor
             */

            $lines[$i] = str_replace(' ', '', $lines[$i]);

            $cols = explode(';', $lines[$i]);

            //skip empty
            if (sizeof($cols) < 5)
                continue;
//            DWDUtil::log(self::class,$cols[1]);
            $date = Carbon::createFromFormat(DWDConfiguration::getHourlyConfiguration()->parameters->solar->dateFormat, $cols[1], 'utc');
            if ($date) {
                $temp = new DWDSolar($cols[0], $date, $cols[2], $cols[3], $cols[4], $cols[5], $cols[6], $cols[7]);

                switch (func_num_args()) {
                    //After is set
                    case 2: {
                        if ($date > $after) {
                            $data[] = $temp;
                        } else
                            //break from loop and switch
                            break 2;

                        break;
                    }
                    //After & Before are set
                    case 3: {
                        if ($date < $before && $date > $after) {

                            $data[] = $temp;
                        } else
                            if ($date < $after) {
                                //break from loop and switch
                                break 2;
                            }

                        break;
                    }
                    default: {
                        $data[] = $temp;
                    }
                }

            } else
                throw new ParseError("Error while parsing date: col=" . $cols[1] . " | date=" . $date);
        }

        return $data;
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
        $localPath = $_SERVER['DOCUMENT_ROOT'] . $hourlyConfig->localBaseFolder . $hourlyConfig->parameters->solar->localFolder;
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

}