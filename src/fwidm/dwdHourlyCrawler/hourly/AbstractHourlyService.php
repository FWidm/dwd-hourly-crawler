<?php

namespace FWidm\DWDHourlyCrawler\Hourly;

use Carbon\Carbon;
use DateTime;
use FWidm\DWDHourlyCrawler\DWDConfiguration;
use FWidm\DWDHourlyCrawler\DWDUtil;
use FWidm\DWDHourlyCrawler\Model\DWDAbstractParameter;
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
    public function getParameter():string
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
    public function parseHourlyData(String $content, DateTime $start = null, DateTime $end = null): array
    {
        $lines = explode('eor', $content);
        $data = array();

        for ($i = sizeof($lines) - 1; $i > 0; $i--) {
            $lines[$i] = str_replace(' ', '', $lines[$i]);

            $cols = explode(';', $lines[$i]);
            //skip empty lines
            if (sizeof($cols) < 3)
                continue;
            $date = Carbon::createFromFormat($this->getTimeFormat(), $cols[1],'utc');
            if ($date) {
                $temp = $this->createParameter($cols,$date);

                switch (func_num_args()) {
                    //After is set
                    case 2: {
                        if ($date >= $start) {
                            $data[] = $temp;
                        } else
                            //break from loop and switch
                            break 2;

                        break;
                    }
                    //After & Before are set
                    case 3: {
                        if ($date <= $end && $date >= $start) {

                            $data[] = $temp;
                        } else
                            if ($date <= $start) {
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
                throw new ParseError(self::class." - Error while parsing date: col=" . $cols[1] . " | date=" . $date);
        }

        return $data;
    }

    /**
     * Instantiate one parameter by the columns and date
     * @param array $cols
     * @param DateTime $date
     * @return DWDAbstractParameter - concrete object of the needed type
     */
    public abstract function  createParameter(array $cols, DateTime $date) : DWDAbstractParameter;

    /**
     * Get the dateformat. default is in dwdHourly->parserSettings->dateFormat. Override as needed (ex. solar).
     * @return string
     */
    public function  getTimeFormat() : string {
        return DWDConfiguration::getHourlyConfiguration()->parserSettings->dateFormat;
    }

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
        $filePath = $_SERVER['DOCUMENT_ROOT'] . DWDConfiguration::getConfiguration()->dwdHourly->localBaseFolder . '/' . $fileName;
        return $filePath;
    }

}
