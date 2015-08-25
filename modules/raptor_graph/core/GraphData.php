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
        $ehrDao = $this->m_oContext->getEhrDao();
        $rawResult = $ehrDao->getRawVitalSignsMap();
        $max_dates = 5;
        //$result = $ehrDao->convertSoapVitalsToGraph(array('Temperature'), $soapResult, $max_dates);
        $result = $this->convertVitalsToGraphFormat(array('Temperature'), $rawResult, $max_dates);
        //error_log("LOOK thumb soap data>>>".print_r($soapResult,TRUE));
        if(!is_array($result))
        {
            $result = array();
        }
        return $result;
    }
    
    function getVitalsGraphValues()
    {
        $ehrDao = $this->m_oContext->getEhrDao();
        $rawResult = $ehrDao->getRawVitalSignsMap();
        $max_dates = 20;
        //$result = $ehrDao->convertSoapVitalsToGraph(array('Temperature', 'Pulse'), $soapResult, $max_dates);
        $result = $this->convertVitalsToGraphFormat(array('Temperature', 'Pulse'), $rawResult, $max_dates);
        //error_log("LOOK vitals raw data>>>".print_r($soapResult,TRUE));
        //error_log("LOOK vitals filtered data>>>".print_r($result,TRUE));
        if(!is_array($result))
        {
            $result = array();
        }
        return $result;
    }
    
    function getLabsGraphValues()
    {
       
        //$oDD = new \raptor\DashboardData($this->m_oContext);
        //$aDD = $oDD->getDashboardDetails();
        $ehrDao = $this->m_oContext->getEhrDao();
        $aDD = $ehrDao->getDashboardDetailsMap();
        $selectedPatient = array(
                  'ethnicity'=>$aDD['PatientEthnicity']
                , 'gender'=>$aDD['PatientGender']
                , 'age'=>$aDD['PatientAge']);
        $labsResult = $ehrDao->getChemHemLabs();
            
        //Pass in selected patient and egfr formula if one is defined 
        //$result = $ehrDao->convertSoapLabsToGraph($selectedPatient, NULL, $labsResult);
        $result = $this->convertLabsToGraphFormat($selectedPatient,$labsResult);
        //error_log('getLabsGraphValues patient>>>'.print_r($selectedPatient,TRUE));
        //error_log('getLabsGraphValues labs>>>'.print_r($labsResult,TRUE));
        //error_log('getLabsGraphValues filtered>>>'.print_r($result,TRUE));
        if(!is_array($result))
        {
            $result = array();
        }
        return $result;
    }

    private function convertVitalsToGraphFormat($typeArray, $vitals, $max_dates=5)
    {
        global $user;
        //error_log('Starting convertSoapVitalsToGraph as user '.$user->name.' maxdates='.$max_dates);
        
        if (!isset($typeArray) || count($typeArray) === 0) {
            $errmsg = 'Invalid vital types argument:'.print_r($typeArray,TRUE);
            error_log("ERROR: $errmsg");
            throw new \Exception($errmsg);
        }
        if (isset($vitals->getVitalSignsResult->fault)) {
            $errmsg = $vitals->getVitalSignsResult->fault->message;
            error_log("ERROR detected in convertVitalsToGraphFormat fault=$errmsg");
            throw new \Exception($errmsg);
        }
        
        $result = array();
        if (!isset($vitals->getVitalSignsResult->arrays->TaggedVitalSignSetArray) ||
                !isset($vitals->getVitalSignsResult->arrays->TaggedVitalSignSetArray->sets)) {
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
    }

    private function convertLabsToGraphFormat($patientInfo, $allLabs, $limitMaxLabs=1000)
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
            $date[$key] = $row['date'];
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

            $limits = explode(" - ", $lab['refRange']);
            $lowerLimit = isset($limits[0]) ? $limits[0] : NULL;
            $upperLimit = isset($limits[1]) ? $limits[1] : NULL;

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
                $formattedDate = self::convertYYYYMMDDToDate($lab['rawTime']);
                $datetime = self::convertYYYYMMDDToDatetime($lab['rawTime']);  //added 20141104 
                $result[] = array('date'=>$formattedDate, 'egfr'=>$eGFR, 'datetime'=>$datetime);
            }
        }
        return $result;
    }
    
    /**
     * Convert VistA format: 3101231 -> 20101231
     */
    private static function convertVistaDateToYYYYMMDD($vistaDateTime) {
        $datePart = self::getVistaDateTimePart($vistaDateTime, "date");
        $year = 1700 + substr($datePart, 0, 3);
        $month = substr($datePart, 3, 2);
        $day = substr($datePart, 5, 2);
        
        return $year.$month.$day;
    }

    /**
     * Convert 20100101 format -> 2010-01-01
     */
    private static function convertYYYYMMDDToDate($vistaDateTime) {
        $datePart = self::getVistaDateTimePart($vistaDateTime, "date");
        $year = substr($datePart, 0, 4);
        $month = substr($datePart, 4, 2);
        $day = substr($datePart, 6, 2);
        
        return $month."-".$day."-".$year;
    }
    
    /**
     * Convert 20100101.083400 format -> 2010-01-01 083400
     */
    private static function convertYYYYMMDDToDatetime($vistaDateTime) {
        $datePart = self::getVistaDateTimePart($vistaDateTime, "date");
        $timePart = self::getVistaDateTimePart($vistaDateTime, "time");
        $year = substr($datePart, 0, 4);
        $month = substr($datePart, 4, 2);
        $day = substr($datePart, 6, 2);
        
        return $month."-".$day."-".$year." ".$timePart;
    }
    
    /*
     * Fetch either the date or time part of a VistA date. 
     * Ex 1) ::getVistaDateTimePart('3101231.0930', 'date') -> '3101231'
     * Ex 2) ::getVistaDateTimePart('3101231.0930', 'time') -> '0930'
     * Ex 3) ::getVistaDateTimePart('3101231', 'time') -> '000000' (defaults to midnight if not time part)
     */
    private static function getVistaDateTimePart($vistaDateTime, $dateOrTime) {
        if ($vistaDateTime === NULL) {
            throw new \Exception('Vista date/time cannot be null');
        }
        $pieces = explode('.', $vistaDateTime);
        if ($dateOrTime == 'date' || $dateOrTime == 'Date' || $dateOrTime == 'DATE') {
            return $pieces[0];
        }
        else {
            if (count($pieces) == 1 || trim($pieces[1]) == '') {
                return '000000'; // default to midnight if no time part 
            }
            return $pieces[1];
        }
    }
}
