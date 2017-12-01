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
     * @deprecated
     * @param String $content - Textual representation of a DWD Hourly/Recent pressure file.
     * @param Carbon|null $startDate - returns all values after the specific time
     * @param Carbon|null $endDate - returns all values after $after AND after if set.
     * @return array of parameters
     * @throws ParseError
     */
    public function parseHourlyDataOld(String $content, DWDStation $nearestStation, Coordinate $coordinate, Carbon $startDate = null, Carbon $endDate = null): array
    {
        $time = microtime(true);
        $lines = explode('eor', $content);
        $data = array();

        /**
         * steps to refactor:
         *  1. get latest date from the last line, parse it
         *  2. from this day, calculate the hour difference between requested and $start+$end
         *  3. jump to the specific lines
         *  4. parse
         *  DOES NOT PROVIDE CORRECT DATA - the dwd files do skip missing data instead of providing "-999" as value, as such the optimization will not work.
         */

//        $newestDate = null;
//        //retrieve the latest line that contains a valid date
//        for ($i = count($lines) - 1; !isset($newestDate); $i++) {
//            $newestData = str_replace(' ', '', $lines[count($lines) - $i]);
//            $cols = explode(';', $newestData);
//            if (sizeof($cols) > 3){
//                $newestDate = Carbon::createFromFormat($this->getTimeFormat(), $cols[1], 'utc');
//                break;
//            }
//        }
//        /* @var $newestDate Carbon */
//        $start = min($newestDate->diffInHours($startDate), $newestDate->diffInHours($endDate));
//        $end = max($newestDate->diffInHours($startDate), $newestDate->diffInHours($endDate));
//        DWDUtil::log("MAXMIN", "start=" .$start."; end=".$end. "available Lines=".count($lines));
//        //Retrieve the rest of the data that is found between start and end.
//        for ($i = $start; $i<count($lines) && $i < $end; $i++) {
//            $lines[$i] = str_replace(' ', '', $lines[$i]);
//            $cols = explode(';', $lines[$i]);
//            $date = Carbon::createFromFormat($this->getTimeFormat(), $cols[1], 'utc');
//            if (isset($date)) {
//                $data[] = $this->createParameter($cols, $date, $nearestStation, $coordinate);
//            } else
//                throw new ParseError(self::class . " - Error while parsing date: col=" . $cols[1] . " | date=" . $date);
//        }

        DWDUtil::log("PARSER", "DATE=[" . $endDate->toIso8601String() . "," . $startDate->toIso8601String() . "]");
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
                        if ($date >= $startDate) {
                            $temp = $this->createParameter($cols, $date, $nearestStation, $coordinate);

                            $data[] = $temp;
                        } else
                            //break from loop and switch
                            break 2;

                        break;
                    }
                    //$start & $end are set
                    case 5: {
                        if ($date <= $endDate && $date >= $startDate) {
                            $temp = $this->createParameter($cols, $date, $nearestStation, $coordinate);

                            $data[] = $temp;
                        } else
                            if ($date <= $startDate) {
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
        DWDUtil::log("PARSER", "RetCount=" . count($data));

        DWDUtil::log("TIMER", "Duration=" . (microtime(true) - $time));

        return $data;
    }


    public function parseHourlyData(String $content, DWDStation $nearestStation, Coordinate $coordinate, Carbon $startDate, Carbon $endDate): array
    {
        $start = $startDate->format($this->getTimeFormat());
        $end = $endDate->format($this->getTimeFormat());

        $content = str_replace([" ", PHP_EOL], "", $content);
        $lines = explode(";eor", $content);
        $data = [];
        $startIndex = $this->binarySearch($start, $lines);
        print "go station=$nearestStation<br>";
        for ($i = (int)$startIndex; $i < count($lines); $i++) {
            $cols = explode(';', $lines[$i]);
            $date = Carbon::createFromFormat($this->getTimeFormat(), $cols[1], 'utc');

            print $i . ": " . $date->toIso8601String() . ">>> DIFF=" . $endDate->diff($date)->h . "<br>";

            if ($date <= $endDate && $date >= $startDate) {
                print "1.=".(int)($date <= $endDate)."    2.=".(int)($date >= $startDate)."<br>";
                $temp = $this->createParameter($cols, $date, $nearestStation, $coordinate);
                $data[] = $temp;
            } else
                break;            //break if we exceed our end point

        }
        return $data;
    }

    /** Finds the position of $item in $array
     * @param $item
     * @param $array
     * @return int
     */
    private function binarySearch($item, $array)
    {
        $low = 0;
        $high = count($array);
        while ($high - $low > 1) {
            $center = ($high + $low) / 2;
//            print("high=$high, low=$low, center=$center -- val=".(int)explode(';', $array[$center])[1]."<br>");
            if ((int)explode(';', $array[$center])[1] < (int)$item) {
                $low = $center;
            } else
                $high = $center;
        }
        if ($high == count($array))
            throw new ParseError(self::class . " - Error while searching for position of item=" . $item . "high=" . $high . "; val=" . $array[$high]);
        else
            return $high + 1;
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
