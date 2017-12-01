<?php

namespace FWidm\DWDHourlyCrawler\Hourly\Services;

use Carbon\Carbon;
use DateTime;
use FWidm\DWDHourlyCrawler\DWDConfiguration;
use FWidm\DWDHourlyCrawler\DWDUtil;
use FWidm\DWDHourlyCrawler\Model\DWDAbstractParameter;
use FWidm\DWDHourlyCrawler\Model\DWDStation;
use Location\Coordinate;
use ParseError;


/**
 * Created by PhpStorm.
 * User: Fabian-Desktop
 * Date: 16.06.2017
 * Time: 09:37
 */
abstract class AbstractHourlyService
{
    private $parameter;

    /**
     * AbstractHourlyService constructor.
     * @param $parameter
     */
    public function __construct(string $parameter)
    {
        $this->parameter = $parameter;
    }

    /**get the parameter
     * @return string
     */
    public function getParameter(): string
    {
        return $this->parameter;
    }


    /**
     * Parse the textual representation of DWD Data, can be filtered by specifying before and after.
     * This means if you specify after - you will get timestamps after the specified team
     * If you also specify before you can pinpoint values.
     * @param String $content - Textual representation of a DWD Hourly/Recent pressure file.
     * @param DateTime|null $start - returns all values after the specific time
     * @param DateTime|null $end - returns all values after $after AND after if set.
     * @return array of parameters
     * @throws ParseError
     */
    public function parseHourlyData(String $content, DWDStation $nearestStation, Coordinate $coordinate, DateTime $start = null, DateTime $end = null): array
    {
        $lines = explode('eor', $content);
        $data = array();

        /**
         * steps to refactor:
         *  1. get latest date from the last line, parse it
         *  2. from this day, calculate the hour difference between requested and $start+$end
         *  3. jump to the specific lines
         *  4. parse
         */
        for ($i = sizeof($lines) - 1; $i > 0; $i--) {
            $lines[$i] = str_replace(' ', '', $lines[$i]);

            $cols = explode(';', $lines[$i]);
            //skip empty lines
            if (sizeof($cols) < 3)
                continue;
            $date = Carbon::createFromFormat($this->getTimeFormat(), $cols[1], 'utc');
            if ($date) {
                //todo: optimize search for values - currently i only parse from new to old values, find the window and add to the return list - something akin to a binary search might work.
                switch (func_num_args()) {
                    //$start is set
                    case 4: {
                        if ($date >= $start) {
                            $temp = $this->createParameter($cols, $date, $nearestStation, $coordinate);

                            $data[] = $temp;
                        } else
                            //break from loop and switch
                            break 2;

                        break;
                    }
                    //$start & $end are set
                    case 5: {
                        if ($date <= $end && $date >= $start) {
                            $temp = $this->createParameter($cols, $date, $nearestStation, $coordinate);

                            $data[] = $temp;
                        } else
                            if ($date <= $start) {
                                //break from loop and switch
                                break 2;
                            }

                        break;
                    }
                    default: {
                        $temp = $this->createParameter($cols, $date, $nearestStation, $coordinate);

                        $data[] = $temp;
                    }
                }

            } else
                throw new ParseError(self::class . " - Error while parsing date: col=" . $cols[1] . " | date=" . $date);
        }

        return $data;
    }

    /**
     * Get the dateformat. default is in dwdHourly->parserSettings->dateFormat. Override as needed (ex. solar).
     * @return string
     */
    public function getTimeFormat(): string
    {
        return DWDConfiguration::getHourlyConfiguration()->parserSettings->dateFormat;
    }

    /**
     * Instantiate one parameter by the columns and date
     * @param array $cols
     * @param DateTime $date
     * @param DWDStation $station
     * @param Coordinate $coordinate
     * @return DWDAbstractParameter - concrete object of the needed type
     */
    public abstract function createParameter(array $cols, DateTime $date, DWDStation $station, Coordinate $coordinate): DWDAbstractParameter;

    /**Get the FTP file path
     * @param string $stationId
     * @return mixed
     */
    public abstract function getFileFTPPath(string $stationId);

    /**Get the filename
     * @param string $stationId
     * @return mixed
     */
    public abstract function getFileName(string $stationId);

    /**Get the path to the downloaded file
     * @param string $fileName
     * @return mixed
     */
    public abstract function getFilePath(string $fileName);

    /**Return the path to the file that contains the stations.
     * @param string $ftpPath
     * @return string
     */
    public function getStationFTPPath(string $ftpPath)
    {
        $fileName = DWDUtil::getFileNameFromPath($ftpPath);
        $filePath = DWDConfiguration::getConfiguration()->baseDirectory . DWDConfiguration::getConfiguration()->dwdHourly->localBaseFolder . '/' . $fileName;
        return $filePath;
    }

}
