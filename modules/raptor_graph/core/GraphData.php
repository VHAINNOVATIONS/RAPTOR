<?php
/**
 * @file
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase 2
 * Open Source VA Innovation Project 2011-2015
 * VA Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, et al
 * Contacts: acasertano@sanbusinessconsultants.com, ffont@sanbusinessconsultants.com
 * ------------------------------------------------------------------------------------
 * Copyright 2015 SAN Business Consultants, a Maryland USA company (sanbusinessconsultants.com)
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ------------------------------------------------------------------------------------
 */ 

namespace raptor;

/**
 * This returns data for graphs (aka charts).
 * 
 * @author Frank Font of SAN Business Consultants
 */
class GraphData
{
    private $m_oContext = NULL;
    
    function __construct($oContext)
    {
        module_load_include('php', 'raptor_datalayer', 'core/Context');
        module_load_include('php', 'raptor_datalayer', 'core/VistaDao');
        module_load_include('php', 'raptor_formulas', 'core/Labs');
        $this->m_oContext = $oContext;
    }    
    
    function getThumbnailGraphValues()
    {
        try 
        {
            $ehrDao = $this->m_oContext->getEhrDao();
            $rawVitalsBundle = $ehrDao->getRawVitalSignsMap();
            $max_dates = GRAPH_THUMBNAIL_DEFAULT_POINT_COUNT;
            $result = $this->convertVitalsToGraphFormat(array('Temperature'), $rawVitalsBundle, $max_dates);
            if(!is_array($result))
            {
                error_log("WARNING getThumbnailGraphValues unexpected result format>>>" . print_r($result,TRUE));
                $result = array();
            }
            return $result;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    function getVitalsGraphValues()
    {
        try
        {
            $ehrDao = $this->m_oContext->getEhrDao();
            $rawResult = $ehrDao->getRawVitalSignsMap();
            $max_dates = GRAPH_NORMAL_DEFAULT_POINT_COUNT;
            $result = $this->convertVitalsToGraphFormat(array('Temperature', 'Pulse'), $rawResult, $max_dates);
            if(!is_array($result))
            {
                error_log("WARNING unexpected format received by getVitalsGraphValues>>>".print_r($result,TRUE));
                $result = array();
            }
            return $result;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    function getLabsGraphValues()
    {
        try
        {
            $ehrDao = $this->m_oContext->getEhrDao();
            $aDD = $ehrDao->getDashboardDetailsMap();
            $selectedPatient = array(
                      'ethnicity'=>$aDD['PatientEthnicity']
                    , 'gender'=>$aDD['PatientGender']
                    , 'age'=>$aDD['PatientAge']);
            $labsResult = $ehrDao->getChemHemLabs();
            $result = $this->convertLabsToGraphFormat($selectedPatient,$labsResult);
            if(!is_array($result))
            {
                $result = array();
            }
            return $result;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Parses the legacy MDWS soap result format
     */
    private function parseVitalsFromLegacyFormat($typeArray, $vitals, $max_dates)
    {
        try
        {
            if (isset($vitals->getVitalSignsResult->fault)) {
                $errmsg = $vitals->getVitalSignsResult->fault->message;
                error_log("ERROR detected in convertVitalsToGraphFormat fault=$errmsg");
                throw new \Exception($errmsg);
            }

            $result = array();
            if (!isset($vitals->getVitalSignsResult->arrays->TaggedVitalSignSetArray) ||
                    !isset($vitals->getVitalSignsResult->arrays->TaggedVitalSignSetArray->sets)) 
            {
                //Just return the empty array.
                return $result;
            }
            $vitalsAryTO = $vitals->getVitalSignsResult->arrays->TaggedVitalSignSetArray;
            $vitalsCount = count($vitalsAryTO->sets->VitalSignSetTO);
            $dates_with_data = 0;
            $prev_timestamp = '';
            for ($i = 0; $i < $vitalsCount; $i++) 
            {
                $currentVitalsSet = NULL;
                if (is_array($vitalsAryTO->sets->VitalSignSetTO)) {
                    $currentVitalsSet = $vitalsAryTO->sets->VitalSignSetTO[$i];
                }
                else 
                {
                    $currentVitalsSet = $vitalsAryTO->sets->VitalSignSetTO;
                }

                $just_date = self::convertYYYYMMDDToDate($currentVitalsSet->timestamp);
                $datetime = self::convertYYYYMMDDToDatetime($currentVitalsSet->timestamp);
                $signsCount = count($currentVitalsSet->vitalSigns->VitalSignTO);
                $aryForTimestamp = array();
                $aryForTimestamp['date'] = $just_date;      //Only the date
                $aryForTimestamp['datetime'] = $datetime;   //The date and the time
                $count_data_items_thisrecord = 0;   //Reset everytime.
                for ($j = 0; $j < $signsCount; $j++) 
                {
                    // it appears PHP is making arrays with one object stdclass and not array...
                    $currentSign = NULL;
                    if (is_array($currentVitalsSet->vitalSigns->VitalSignTO)) {
                        $currentSign = $currentVitalsSet->vitalSigns->VitalSignTO[$j];
                    }
                    else 
                    {
                        $currentSign = $currentVitalsSet->vitalSigns->VitalSignTO;
                    }

                    $currentType = $currentSign->type->name;

                    if (in_array($currentType, $typeArray)) 
                    {
                        if(is_numeric( $currentSign->value1))   //20150528
                        {
                            if ($currentType === 'Temperature') 
                            {
                                $aryForTimestamp['temperature'] = $currentSign->value1;
                                $count_data_items_thisrecord++;
                            }
                            else if ($currentType === 'Pulse') 
                            {
                                $aryForTimestamp['pulse'] = $currentSign->value1;
                                $count_data_items_thisrecord++;
                            }
                        }
                    }
                }

                if (count($aryForTimestamp) > 1) 
                { 
                    //We have a data point, do we have data from the vitals set?
                    if($count_data_items_thisrecord > 0)
                    {
                        //We added data from the vitals set.
                        if($prev_timestamp !== $just_date)
                        {
                            $dates_with_data++;
                        }
                        if ($dates_with_data <= $max_dates) 
                        {
                            $result[] = $aryForTimestamp;
                        }
                        else 
                        {
                            //Done!
                            break;
                        }
                    }
                }

                //Setup for next loop.
                $prev_timestamp = $just_date;
            }
            return $result;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Parses the preferred raw format.
     *     [0] => Temperature
     *     [1] => Pulse
     */
    private function parseVitalsFromFullFormat($typeArray, $vitals_bundle, $max_data_points)
    {
        try
        {
            $gettemp = in_array('Temperature', $typeArray);
            $getpulse = in_array('Pulse', $typeArray);
            $rows = $vitals_bundle[0];
            $datecount = 0;
            $prevdate = NULL;
            $prevdate_temp = NULL;
            $prevdate_pulse = NULL;
            $result = array();
            foreach($rows as $onerow)
            {
                $rowdatetime_tx = $onerow['Date Taken'];
                $timestamp = strtotime($rowdatetime_tx);
                $dtparts = $this->getGraphFriendlyDateTimeParts($timestamp);
                $formatted_datetime_tx = $dtparts['datetime_text'];
                $formatted_just_date_tx = $dtparts['just_date_text'];
                $just_date_ts = $dtparts['datetime_text'];
                if($prevdate != $just_date_ts)
                {
                    $datecount++;
                    $prevdate = $just_date_ts;
                    /*
                    if($datecount > $max_data_points)
                    {
                        //No more.
                        break;
                    }
                     */
                    //We only grab ONE value per date.
                    $oneitem = NULL;
                    if($gettemp && $prevdate_temp != $just_date_ts)
                    {
                        if($oneitem == NULL)
                        {
                            $oneitem = array();
                        }
                        $temp_tx = trim($onerow['Temp']);
                        if($temp_tx > '')
                        {
                            $founddata = TRUE;
                            $prevdate_temp = $just_date_ts;
                            $parts = explode(' ', $temp_tx);
                            $oneitem['temperature'] = $parts[0];
                        }
                    }
                    if($getpulse && $prevdate_pulse != $just_date_ts)
                    {
                        if($oneitem == NULL)
                        {
                            $oneitem = array();
                        }
                        $pulse_tx = trim($onerow['Pulse']);
                        if($pulse_tx > '')
                        {
                            $founddata = TRUE;
                            $prevdate_pulse = $just_date_ts;
                            $parts = explode(' ', $pulse_tx);
                            $oneitem['pulse'] = $parts[0];
                        }
                    }
                    if($oneitem != NULL)
                    {
                        $oneitem['date'] = $formatted_just_date_tx;
                        $oneitem['datetime'] = $formatted_datetime_tx;
                        $result[] = $oneitem;
                    }
                    if(count($result) >= $max_data_points)
                    {
                        //No more.
                        break;
                    }
                }
            }
            return $result;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    private function getGraphFriendlyDateTimeParts($php_timestamp)
    {
        try
        {
            $formatted_datetime_text = date('m-d-Y His', $php_timestamp);
            $too_short_count = 17 - strlen($formatted_datetime_text); //04-30-2010 135233 --- len = 17
            if($too_short_count > 0)
            {
                $tailpad = str_repeat('0', $too_short_count);
                $formatted_datetime_text .= $tailpad;
            }
            $formatted_just_date_text = date('m-d-Y', $php_timestamp);
            $just_date_ts = strtotime($formatted_just_date_text);
            return array(
                'timestamp'=>$php_timestamp,
                'just_date_ts'=>$just_date_ts,
                'just_date_text'=>$formatted_just_date_text,
                'datetime_text'=>$formatted_datetime_text,
                );
        } catch (\Exception $ex) {
            $errmsg = ("Failed to get friendly date time parts from $php_timestamp");
            throw new Exception($errmsg, 99787, $ex);
        }
    }
    
    private function convertVitalsToGraphFormat($typeArray, $vitals, $max_dates=GRAPH_THUMBNAIL_DEFAULT_POINT_COUNT)
    {
        try
        {
            global $user;
            if (!isset($typeArray) || count($typeArray) === 0) 
            {
                $errmsg = 'Invalid vital types argument:'.print_r($typeArray,TRUE);
                error_log("ERROR: $errmsg");
                throw new \Exception($errmsg);
            }
            if(is_array($vitals))
            {
                $result = $this->parseVitalsFromFullFormat($typeArray, $vitals, $max_dates);
            } else {
                $result = $this->parseVitalsFromLegacyFormat($typeArray, $vitals, $max_dates);
            }
            return $result;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    private function convertLabsToGraphFormat($patientInfo, $allLabs, $limitMaxLabs=1000)
    {
        try
        {
            $labs_formulas = new \raptor_formulas\Labs();

            //Removed default of white male and default age 20150530
            $ethnicity = is_null($patientInfo) ? ' ' : $patientInfo['ethnicity'];
            $gender = is_null($patientInfo) ? ' ' : trim(strtoupper($patientInfo['gender']));
            $age = is_null($patientInfo) ? 0 : $patientInfo['age']; //Changed default to 0 instead of 18
            $isAfricanAmerican = (strpos('BLACK', strtoupper($ethnicity)) !== FALSE) ||
                                 (strpos('AFRICAN', strtoupper($ethnicity)) !== FALSE);
            $isMale = $gender > '' && strtoupper(substr($gender,0,1)) == 'M';
            if(!$isMale)
            {
                $isFemale = $gender > '' && strtoupper(substr($gender,0,1)) == 'F';
            } else {
                $isFemale = FALSE;
            }
            $foundCreatinine = FALSE;
            $foundEGFR = FALSE;
            $foundPLT = FALSE;
            $foundPT = FALSE;
            $foundINR = FALSE;
            $foundPTT = FALSE;
            $foundHCT = FALSE;

            $sortedLabs = $allLabs;
            // Obtain a list of columns
            foreach ($sortedLabs as $key => $row) 
            {
                $name[$key]  = $row['name'];
                //$date[$key] = $row['date'];
                $value[$key] = $row['value'];
                $units[$key] = $row['units'];
                $rawTime[$key] = $row['rawTime'];
            }

            if(isset($name) && is_array($name)) //20140603
            {
                array_multisort($name, SORT_ASC, $rawTime, SORT_DESC, $sortedLabs);
            }    

            $result = array();

            foreach($sortedLabs as $lab)
            {
                if (count($result) >= $limitMaxLabs) 
                {
                    break; // per specs - show only last X creatinine/egfr results
                }
                $name = $lab['name'];
                $foundCreatinine = strpos('CREATININE', strtoupper($name)) !== FALSE;
                $foundHCT = strpos('HCT', strtoupper($lab['name'])) !== FALSE;
                $foundINR = strpos('INR', strtoupper($lab['name'])) !== FALSE;
                $foundPT = strpos('PT', strtoupper($lab['name'])) !== FALSE;
                $foundPLT = strpos('PLT', strtoupper($lab['name'])) !== FALSE;
                $foundPTT = strpos('PTT', strtoupper($lab['name'])) !== FALSE;

                //$limits = explode(" - ", $lab['refRange']);
                //$lowerLimit = isset($limits[0]) ? $limits[0] : NULL;
                //$upperLimit = isset($limits[1]) ? $limits[1] : NULL;

                $value = $lab['value'];

                $rawValue = $lab['value'];
                $units = $lab['units'];

                if($foundCreatinine)
                {
                    $foundEGFR = FALSE;
                    $checkDate = $lab['date'];
                    foreach($sortedLabs as $checkLab)
                    {
                        if(strpos('EGFR', strtoupper($checkLab['name'])) !== FALSE)
                        {
                            if($checkDate == $checkLab['date'])
                            {
                                $foundEGFR = TRUE;
                                $eGFR = $checkLab['value'];
                                $eGFRSource = " (eGFR from VistA)";
                                break;
                            }
                        }
                    }
                    if(!$foundEGFR)
                    {
                        if(is_numeric($rawValue))
                        {
                            $eGFRSource = ' (calculated)';
                            $eGFR = $labs_formulas->calc_eGFR($rawValue, $age, $isFemale, $isAfricanAmerican);
                        } else {
                            $eGFRSource = '';
                            $eGFR = '';
                        }                    
                    }
                    //$formattedDate = self::convertYYYYMMDDToDate($lab['rawTime']);
                    $timestamp = self::convertVistaDateTimeToPhpTimestamp($lab['rawTime']);  //added 20141104 
                    $datetimeparts = $this->getGraphFriendlyDateTimeParts($timestamp);
                    $formattedDate = $datetimeparts['just_date_text'];
                    $datetime = $datetimeparts['datetime_text'];
                    $oneresult = array(
                        'date'=>$formattedDate, 
                        'egfr'=>$eGFR,
                        'datetime'=>$datetime);
                    $result[] = $oneresult;
                }
            }
            return $result;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Convert VistA format: 3101231 -> 20101231
     */
    private static function convertVistaDateToYYYYMMDD($vistaDateTime) 
    {
        try 
        {
            $datePart = self::getVistaDateTimePart($vistaDateTime, "date");
            $year = 1700 + substr($datePart, 0, 3);
            $month = substr($datePart, 3, 2);
            $day = substr($datePart, 5, 2);

            return $year.$month.$day;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Convert 20100101 format -> 2010-01-01
     */
    private static function convertYYYYMMDDToDate($vistaDateTime) 
    {
        try 
        {
            $datePart = self::getVistaDateTimePart($vistaDateTime, "date");
            $year = substr($datePart, 0, 4);
            $month = substr($datePart, 4, 2);
            $day = substr($datePart, 6, 2);

            return $month.'-'.$day.'-'.$year;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    private static function convertVistaDateTimeToPhpTimestamp($fullYearVistaDateTime)
    {
        try
        {
            //Make sure we have ALL the time in the string (sometimes trailing zeros are lost)
            $too_short_count = 15 - strlen($fullYearVistaDateTime); //20080131.141010 --- len = 15
            if($too_short_count > 0)
            {
                $tailpad = str_repeat('0', $too_short_count);
                $fullYearVistaDateTime .= $tailpad;
            }
            
            //Get the large parts
            $datePart = self::getVistaDateTimePart($fullYearVistaDateTime, "date");
            $timePart = self::getVistaDateTimePart($fullYearVistaDateTime, "time");
            
            //Get each small part
            $year = substr($datePart, 0, 4);
            $month = substr($datePart, 4, 2);
            $day = substr($datePart, 6, 2);

            $hours = substr($timePart, 0, 2);
            $minutes = substr($timePart, 2, 2);
            $seconds = substr($timePart, 4, 2);
            
            //Rebuild how we want to see it.
            $datetime_text = $year.'-'.$month.'-'.$day.' '
                    . "$hours:$minutes:$seconds";
            $timestamp = strtotime($datetime_text);
            return $timestamp;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Convert 20100101.083400 format -> 2010-01-01 083400
     */
    private static function convertYYYYMMDDToDatetime($vistaDateTime) 
    {
        try
        {
            $datePart = self::getVistaDateTimePart($vistaDateTime, "date");
            $timePart = self::getVistaDateTimePart($vistaDateTime, "time");
            $year = substr($datePart, 0, 4);
            $month = substr($datePart, 4, 2);
            $day = substr($datePart, 6, 2);
            return $month.'-'.$day.'-'.$year.' '.$timePart;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /*
     * Fetch either the date or time part of a VistA date. 
     * Ex 1) ::getVistaDateTimePart('3101231.0930', 'date') -> '3101231'
     * Ex 2) ::getVistaDateTimePart('3101231.0930', 'time') -> '0930'
     * Ex 3) ::getVistaDateTimePart('3101231', 'time') -> '000000' (defaults to midnight if not time part)
     */
    private static function getVistaDateTimePart($vistaDateTime, $dateOrTime) 
    {
        try
        {
            if ($vistaDateTime === NULL) 
            {
                throw new \Exception('Vista date/time cannot be null');
            }
            $pieces = explode('.', $vistaDateTime);
            if ($dateOrTime == 'date' || $dateOrTime == 'Date' || $dateOrTime == 'DATE') 
            {
                return $pieces[0];
            } else {
                if (count($pieces) == 1 || trim($pieces[1]) == '') 
                {
                    return '000000'; // default to midnight if no time part 
                }
                return $pieces[1];
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
