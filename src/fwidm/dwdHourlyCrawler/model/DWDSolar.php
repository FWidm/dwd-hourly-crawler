<?php
/**
 * Created by PhpStorm.
 * User: Fabian-Desktop
 * Date: 01.07.2017
 * Time: 09:45
 */

namespace FWidm\DWDHourlyCrawler\Model;


use DateTime;
use FWidm\DWDHourlyCrawler\DWDConfiguration;

class DWDSolar extends DWDAbstractParameter
{

    private $sumLongwaveRadiation;
    private $sumDiffuseRadiation;
    private $sumIncomingRadiation;
    private $sumSunshineDuration;
    private $zenith;


    /**
     * DWDSolar constructor.
     * @param $quality
     * @param $sumLongwaveRadiation
     * @param $sumDiffuseRadiation
     * @param $sumIncomingRadiation
     * @param $sumSunshineDuration
     * @param $zenith
     */
    public function __construct(int $stationId, DateTime $date, $quality, $sumLongwaveRadiation, $sumDiffuseRadiation, $sumIncomingRadiation, $sumSunshineDuration, $zenith)
    {
        $this->stationId = $stationId;
        $this->date = $date;
        $this->sumLongwaveRadiation = $sumLongwaveRadiation;
        $this->sumDiffuseRadiation = $sumDiffuseRadiation;
        $this->sumIncomingRadiation = $sumIncomingRadiation;
        $this->sumSunshineDuration = $sumSunshineDuration;
        $this->zenith = $zenith;
        $this->quality=$quality;
        $this->description=DWDConfiguration::getHourlyConfiguration()->parameters->precipitation->variables;

    }


}