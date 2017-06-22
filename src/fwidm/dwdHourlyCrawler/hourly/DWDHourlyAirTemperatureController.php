<?php

namespace FWidm\DWDHourlyCrawler\Hourly;

use DateTime;
use FWidm\DWDHourlyCrawler\DWDConfiguration;
use FWidm\DWDHourlyCrawler\DWDUtil;
use FWidm\DWDHourlyCrawler\Model\DWDAirTemperature;
use FWidm\DWDHourlyCrawler\Model\DWDStation;
use Error;
use ParseError;

/**
 * Created by PhpStorm.
 * User: Fabian-Desktop
 * Date: 12.06.2017
 * Time: 15:21
 */
class DWDHourlyAirTemperatureController extends DWDAbstractHourlyController
{
    /**
     * Try to get the newest _hourly file. Hourly files are generally 1 day behind, thus we all
     * @param DWDStation $nearestStation only download one specific file containing data for the station.
     * @return string - path to the file
     * @throws Error - Error while logging into the ftp or retrieving the file.
     */
    public function retrieveFile(DWDStation $nearestStation, $forceDownloadFile = false): string
    {
        $config = DWDConfiguration::getConfiguration();
        $ftpConfig = $config->ftp;
        $hourlyConfig = $config->dwdHourly;
        $parameterConf = $config->dwdHourly->parameters;

        $stationID = $nearestStation->getId();

        //example: setting up the url via configuration files.
        //   |- basePath -|- var -|- recentVauluePath - |- shortcode + '_' -|- stationId -|- fileExtension -|
        //         |          |             |                  |           |            |->'_akt.zip'
        //         |          |             |                  |           |->'15444'
        //         |          |             |                  |-> 'P0_'
        //         |          |             |-> '/recent/stundenwerte_'
        //         |          |-> 'air_temperature'
        //         |-> '/pub/CDC/observations_germany/climate/_hourly/'
        //complete: /pub/CDC/observations_germany/climate/_hourly/air_temperature/recent/stundenwerte_P0_15444

        //todo: simplify
        $fileName = $parameterConf->airTemperature->shortCode . '_'
            . $stationID . $config->dwdHourly->fileExtension;

        $ftpPath = $config->dwdHourly->baseFTPPath . $parameterConf->airTemperature->name
            . $config->dwdHourly->recentValuePath . $fileName;
        //echo $ftpPath . "<br>";


        //get file.
        $ftp_connection = ftp_connect($ftpConfig->url);
        set_time_limit(9000);
        //ftp_set_option($ftp_connection, FTP_TIMEOUT_SEC, 9000);
        $files = array();
        $localPath = $_SERVER['DOCUMENT_ROOT'] . $hourlyConfig->localBaseFolder . $parameterConf->precipitation->localFolder;
        $localFilePath = $localPath . '/' . $hourlyConfig->filePrefix . $fileName;
        if (file_exists($localFilePath)) {
            $lastModifiedStationFile = DateTime::createFromFormat('U', (filemtime($localFilePath)));
        }
        //check if the date on the old file is older than 1 day, else return the old path.
        // download can be forced with the optional parameter.
        if ($forceDownloadFile || !file_exists($localFilePath)
            || (isset($lastModifiedStationFile) && $lastModifiedStationFile->diff(new DateTime())->d >= 1)
        ) {
            //echo "<p>Controller::retrieveFile >> load new zip!</p>";

            if (ftp_login($ftp_connection, $ftpConfig->userName, $ftpConfig->userPassword)) {
                if (!is_dir($localPath)) {
                    mkdir($localPath, 0755, true);
                }

                //echo $localFilePath;

                if (ftp_get($ftp_connection, $localFilePath, $ftpPath, FTP_BINARY)) {
                    $files[] = $localFilePath;
                } else
                    return null;

                ftp_close($ftp_connection);
                return $localFilePath;
            }
        }

        return $localFilePath;
    }


    /**
     * Retrieves the correct stations file, can be filtered to only show stations that are flagges as active. Conditions
     * for this can be
     * @param bool $activeOnly
     * @return array
     */
    public function getStations(bool $activeOnly = false, bool $forceDownloadFile = false)
    {
        $stationsFTPPath = DWDConfiguration::getHourlyConfiguration()->parameters->airTemperature->stations;
        $fileName = DWDUtil::getFileNameFromPath($stationsFTPPath);
        $filePath = $_SERVER['DOCUMENT_ROOT'] . '/in/' . $fileName;
        //Retrieve Stations
        if (file_exists($filePath)) {
            $lastModifiedStationFile = DateTime::createFromFormat('U', (filemtime($filePath)));
        }

        if ($forceDownloadFile || !file_exists($filePath)
            || (isset($lastModifiedStationFile) && $lastModifiedStationFile->diff(new DateTime())->d >= 1)
        ) {
            DWDStationsController::getStationFile($stationsFTPPath, $filePath);
        }

        $stations = DWDStationsController::parseStations($filePath);
        if ($activeOnly) {
            return array_filter($stations,
                function (DWDStation $station) {
                    return $station->isActive();
                });
        }
        return $stations;
    }

    /**
     * Parse the textual representation of DWD Data, can be filtered by specifying before and after.
     * This means if you specify after - you will get timestamps after the specified team
     * If you also specify before you can pinpoint values.
     * @param String $content - Textual representation of a DWD Hourly/Recent variable file.
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
             * [0] => STATIONS_ID
             * [1] => MESS_DATUM
             * [2] => QN_9
             * [3] => TT_TU
             * [4] => RF_TU
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
                            $lineData = new DWDAirTemperature($cols[0], $date, $cols[2], $cols[3], $cols[4]);
                            $data[] = $lineData;
                        } else
                            //break from loop and switch
                            break 2;

                        break;
                    }
                    //After & Before are set
                    case 3: {
                        if ($date < $before && $date > $after) {
                            $lineData = new DWDAirTemperature($cols[0], $date, $cols[2], $cols[3], $cols[4]);
                            $data[] = $lineData;
                        } else
                            if ($date < $after) {
                                //break from loop and switch
                                break 2;
                            }

                        break;
                    }
                    default: {
                        $lineData = new DWDAirTemperature($cols[0], $date, $cols[2], $cols[3], $cols[4]);
                        $data[] = $lineData;
                    }
                }

            } else
                throw new ParseError("Error while parsing date: col=" . $cols[1] . " | date=" . $date);
        }

        return $data;
    }


}