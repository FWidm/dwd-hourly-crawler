<?php
/**
 * User: Fabian Widmann
 * Date: 30.11.17
 * Time: 15:37
 */

namespace FWidm\DWDHourlyCrawler\Transformer;


use FWidm\DWDHourlyCrawler\Model\DWDAbstractParameter;
use FWidm\DWDHourlyCrawler\Model\DWDAirTemperature;
use FWidm\DWDHourlyCrawler\Model\DWDCloudiness;
use FWidm\DWDHourlyCrawler\Model\DWDPrecipitation;
use FWidm\DWDHourlyCrawler\Model\DWDPressure;
use FWidm\DWDHourlyCrawler\Model\DWDSoilTemp;
use FWidm\DWDHourlyCrawler\Model\DWDSolar;
use FWidm\DWDHourlyCrawler\Model\DWDSun;
use FWidm\DWDHourlyCrawler\Model\DWDWind;
use League\Fractal\TransformerAbstract;

class ParameterTransformer extends TransformerAbstract
{
    public function transform(DWDAbstractParameter $parameter)
    {
        $values = [
            'station_id' => $parameter->getStationID(),
            'description' => (array)$parameter->getDescription(),
            'classification' => $parameter->getClassification(),
            'distance' => $parameter->getDistance(),
            'lon' => $parameter->getLongitude(),
            'lat' => $parameter->getLatitude(),
            'date' => $parameter->getDate()->toIso8601String(),
        ];

        switch (get_class($parameter)) {
            case DWDAirTemperature::class:
                /* @var $parameter DWDAirTemperature */
                $values['2m_temperature'] = $parameter->getTemperature2mDegC();
                $values['2m_temperature_unit'] = 'C';
                $values['relative_humidity'] = $parameter->getRelativeHumidityPercent();
                $values['relative_humidity_unit'] = '%';
                break;
            case DWDCloudiness::class:
                /* @var $parameter DWDCloudiness */
                $values['index_observation_type'] = $parameter->getIndexObservationType();
                $values['index_observation_type_unit'] = 'H(uman)/I(nstrument)';
                $values['cloudiness'] = $parameter->getCloudinessEights();
                $values['cloudiness_unit'] = 'n/8';
                break;
            case DWDPrecipitation::class:
                /* @var $parameter DWDPrecipitation */
                $values['index_observation_type'] = $parameter->getPrecipitationIndex();
                $values['index_observation_type_unit'] = 'bool';
                $values['precipitation_height'] = $parameter->getPrecipitationHeightMm();
                $values['precipitation_height_unit'] = 'mm';
                $values['wr_type'] = $parameter->getPrecipitationWRType();
                $values['wr_type_unit'] = 'int(0,9)';
                break;
            case DWDPressure::class:
                /* @var $parameter DWDPressure */
                $values['pressure_sea_level'] = $parameter->getPressureSeaLevelHPA();
                $values['pressure_sea_level_unit'] = 'hPA';
                $values['pressure_station_level'] = $parameter->getPressureStationLevelHPA();
                $values['pressure_station_level_unit'] = 'hPA';
                break;
            case DWDSoilTemp::class:
                /* @var $parameter DWDSoilTemp */
                $values['2cm_soil_temperature'] = $parameter->getSoilTemp2cmDeg();
                $values['2cm_soil_temperature_unit'] = 'C';
                $values['10cm_soil_temperature'] = $parameter->getSoilTemp10cmDeg();
                $values['10cm_soil_temperature_unit'] = 'C';
                $values['20cm_soil_temperature'] = $parameter->getSoilTemp20cmDeg();
                $values['20cm_soil_temperature_unit'] = 'C';
                $values['50cm_soil_temperature'] = $parameter->getSoilTemp50cmDeg();
                $values['50cm_soil_temperature_unit'] = 'C';
                $values['100cm_soil_temperature'] = $parameter->getSoilTemp100cmDeg();
                $values['100cm_soil_temperature_unit'] = 'C';
                break;
            case DWDSolar::class:
                /* @var $parameter DWDSolar */
                $values['diffuse_radiation_sum'] = $parameter->getSumDiffuseRadiation();
                $values['diffuse_radiation_sum_unit'] = 'J cm**-2';
                $values['incoming_radiation_sum'] = $parameter->getSumIncomingRadiation();
                $values['incoming_radiation_sum_unit'] = 'J cm**-2';
                $values['longwave_radiation_sum'] = $parameter->getSumLongwaveRadiation();
                $values['longwave_radiation_sum_unit'] = 'J cm**-2';
                $values['sunshine_duration_sum'] = $parameter->getSumSunshineDuration();
                $values['sunshine_duration_sum_unit'] = 'min';
                break;
            case DWDSun::class:
                /* @var $parameter DWDSun */
                $values['sunshine_duration'] = $parameter->getSunshineDuration();
                $values['sunshine_duration_unit'] = 'min';
                break;
            case DWDWind::class:
                /* @var $parameter DWDWind */
                $values['mean_wind_direction'] = $parameter->getMeanWindDirection();
                $values['mean_wind_direction_unit'] = 'deg';
                $values['mean_wind_speed'] = $parameter->getMeanWindSpeed();
                $values['mean_wind_speed_unit'] = 'm s**-1';
                $values['u_wind_component'] = $parameter->calculateU();
                $values['u_wind_component_unit'] = 'm s**-1';
                $values['v_wind_component'] = $parameter->calculateV();
                $values['v_wind_component_unit'] = 'm s**-1';
                break;
        }
        return $values;
    }

}