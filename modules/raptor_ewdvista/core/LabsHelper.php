<?php
/**
 * @file
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase 2
 * Open Source VA Innovation Project 2011-2015
 * VA Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, Alex Podlesny, et al
 * EWD Integration and VISTA collaboration: Joel Mewton, Rob Tweed
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

namespace raptor_ewdvista;

require_once 'EwdUtils.php';

/**
 * Helper for returning labs content
 *
 * @author Frank Font of SAN Business Consultants
 */
class LabsHelper
{
    private $m_oContext;
    private $m_oRuntimeResultFlexCache = NULL;
    
    function __construct($oContext, $override_patientId=NULL)
    {
        $this->m_oContext = $oContext;
        $this->m_oRuntimeResultFlexCache = \raptor\RuntimeResultFlexCache::getInstance('ProtocolSupportingData');
    }
    
    public function getFormattedChemHemLabsDetail($rawresult_ar)
    {
        if(!is_array($rawresult_ar))
        {
            throw new \Exception("Cannot format non-array of data in getFormattedChemHemLabsDetail!");
        }
        try
        {
            $labsResults = array();
            foreach ($rawresult_ar as $onebundle)
            {
                $onebundle_rawTime = $onebundle['timestamp'];
                $onebundle_specimen_ar = $onebundle['specimen'];
                $onebundle_date = EwdUtils::convertVistaDateTimeToDate($onebundle_rawTime);
                $onebundle_time = EwdUtils::convertVistaDateTimeToDatetime($onebundle_rawTime);
                foreach($onebundle['labResults'] as $labResult)
                {
                    $labResult_value = $labResult['value'];
                    $labTest = $labResult['labTest'];
                    $labTest_name = $labTest['name'];
                    $labTest_units = $labTest['units'];
                    $labTest_refRange = $labTest['refRange'];
                    $oneresult = array(
                        'name'      => $labTest_name,
                        'date'      => $onebundle_date,
                        'datetime'  => $onebundle_time,
                        'value'     => $labResult_value,
                        'units'     => $labTest_units,
                        'refRange'  => $labTest_refRange,
                        'rawTime'   => EwdUtils::convertVistaDateToYYYYMMDDtttt($onebundle_rawTime),
                        'specimen_ar'   => $onebundle_specimen_ar,
                        );
                    $labsResults[] = $oneresult;
                }
            }
            return $labsResults;
        } catch (\Exception $ex) {
            throw $ex;
        }     
    }
    
    /**
     * This returns an array of arrays with following offset content:
     *  0. The ChemHem labs array
     *  1. Just the eGFR array
     */
    public function getLabsDetailData($override_patientId=NULL)
    {
        try
        {
            module_load_include('php', 'raptor_formulas', 'core/Labs');
            $myDao = $this->m_oContext->getEhrDao()->getImplementationInstance();
            if($override_patientId == NULL)
            {
                $tid = $this->m_oContext->getSelectedTrackingID();
                if(trim($tid) == '')
                {
                    throw new \Exception("No patient id was provided and no order is currently active!");
                }
                $pid = $myDao->getPatientIDFromTrackingID($tid);
            } else {
                $pid = $override_patientId;
            }

            $sThisResultName = $pid . '_getLabsDetailDataEWD'; //patient specific
            $aCachedResult = $this->m_oRuntimeResultFlexCache->checkCache($sThisResultName);
            if($aCachedResult !== NULL)
            {
                //Found it in the cache!
                return $aCachedResult;
            }

            //Did NOT find it in the cache.  Build it now.
            $allLabs = $myDao->getChemHemLabs($pid);
            $this->m_oRuntimeResultFlexCache->markCacheBuilding($sThisResultName);
            $patient_data_ar = $myDao->getPatientMap($pid);
            $labs_formulas = new \raptor_formulas\Labs();
            $aDiagLabs = array();
            $aJustEGFR = array();

            //Create placeholders for the values we will return.
            $aJustEGFR['LATEST_EGFR'] = NULL;
            $aJustEGFR['MIN_EGFR_10DAYS'] = NULL;
            $aJustEGFR['MIN_EGFR_15DAYS'] = NULL;
            $aJustEGFR['MIN_EGFR_30DAYS'] = NULL;
            $aJustEGFR['MIN_EGFR_45DAYS'] = NULL;
            $aJustEGFR['MIN_EGFR_60DAYS'] = NULL;
            $aJustEGFR['MIN_EGFR_90DAYS'] = NULL;

            //Create a structure where we can track the dates.
            $aJustEGFRDate['LATEST_EGFR'] = NULL;
            $aJustEGFRDate['MIN_EGFR_10DAYS'] = NULL;
            $aJustEGFRDate['MIN_EGFR_15DAYS'] = NULL;
            $aJustEGFRDate['MIN_EGFR_30DAYS'] = NULL;
            $aJustEGFRDate['MIN_EGFR_45DAYS'] = NULL;
            $aJustEGFRDate['MIN_EGFR_60DAYS'] = NULL;
            $aJustEGFRDate['MIN_EGFR_90DAYS'] = NULL;

            $ethnicity = $patient_data_ar['ethnicity'];
            $gender = strtoupper(trim($patient_data_ar['gender']));
            $age = $patient_data_ar['age'];
            $isAfricanAmerican = (strpos('BLACK', strtoupper($ethnicity)) !== FALSE) ||
                                 (strpos('AFRICAN', strtoupper($ethnicity)) !== FALSE);
            $isMale = $gender > '' && strtoupper(substr($gender,0,1)) == 'M';
            if(!$isMale)
            {
                $isFemale = $gender > '' && strtoupper(substr($gender,0,1)) == 'F';
            } else {
                $isFemale = FALSE;
            }

            $foundSerumCreatinine = FALSE;
            $foundNonSerumCreatinine = FALSE;
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
                //$refRange[$key] = $row['refRange'];
                $rawTime[$key] = $row['rawTime'];
            }
            if(isset($name) && is_array($name)) //20140603
            {
                array_multisort($name, SORT_ASC, $rawTime, SORT_DESC, $sortedLabs);
            }

            foreach($sortedLabs as $key => $lab)
            {
                $name = $lab['name'];
                if(isset($lab['specimen_ar']))
                {
                    $lab_specimen_ar = $lab['specimen_ar'];
                    $lab_specimen_name = strtoupper($lab_specimen_ar['name']);
                } else {
                    $lab_specimen_ar = array();
                    $lab_specimen_name = NULL;
                }
                if($lab_specimen_name == 'SERUM')
                {
                    $foundSerumCreatinine = strpos('CREATININE', strtoupper($name)) !== FALSE;
                    $foundNonSerumCreatinine = FALSE;
                } else {
                    //There are other types of CREATININE, such as in urine.
                    $foundSerumCreatinine = FALSE;
                    $foundNonSerumCreatinine = strpos('CREATININE', strtoupper($name)) !== FALSE;
                }
                $foundHCT = strpos('HCT', strtoupper($lab['name'])) !== FALSE;
                $foundINR = strpos('INR', strtoupper($lab['name'])) !== FALSE;
                $foundPT = strpos('PT', strtoupper($lab['name'])) !== FALSE;
                $foundPLT = strpos('PLT', strtoupper($lab['name'])) !== FALSE;
                $foundPTT = strpos('PTT', strtoupper($lab['name'])) !== FALSE;

                $limits = explode(" - ", $lab['refRange']);
                $lowerLimit = isset($limits[0]) ? $limits[0] : NULL;
                $upperLimit = isset($limits[1]) ? $limits[1] : NULL;

                $alert = FALSE;
                if(isset($lowerLimit) && isset($upperLimit))
                {
                    $alert = ($lab['value'] < $lowerLimit) || ($lab['value'] > $upperLimit);
                } elseif(isset($lowerLimit)&& !isset($upperLimit)) {
                    $alert = $lab['value'] < $lowerLimit;
                } elseif(!isset($lowerLimit) && isset($upperLimit)) {
                    $alert = $lab['value'] > $upperLimit;
                } else {
                    $alert = FALSE;
                }
                $value = $alert ? "<span class='medical-value-danger'>!! "
                        .$lab['value']." ".$lab['units']
                        ." !!</span>" : $lab['value']." ".$lab['units'];

                $rawValue = $lab['value'];
                $units = $lab['units'];
                $creatinineRefRange = '';
                $eGFRRefRange = '';

                if($foundSerumCreatinine)
                {
                    $creatinineRefRange = $lab['refRange'];
                    $foundEGFR = FALSE;
                    $checkDate = $lab['date'];
                    $dDate = strtotime($checkDate);
                    foreach($sortedLabs as $checkLab)
                    {
                        if(strpos('EGFR', strtoupper($checkLab['name'])) !== FALSE)
                        {
                            if($checkDate == $checkLab['date'])
                            {
                                $foundEGFR = TRUE;
                                $eGFR = $checkLab['value'];
                                $eGFRRefRange = $checkLab['refRange'];
                                $eGFRSource = " (eGFR from VistA)";
                                break;
                            }
                        }
                    }
                    if(!$foundEGFR)
                    {
                        if(is_numeric($rawValue))
                        {
                            $eGFRSource = " (eGFR calculated)";
                            $eGFR = $labs_formulas->calc_eGFR($rawValue, $age, $isFemale, $isAfricanAmerican); //20150604
                        } else {
                            $eGFRSource = '';
                            $eGFR = '';
                        }
                    }
                    if($eGFR > '')
                    {
                        $eGFRUnits = " mL/min/1.73 m^2";
                        $eGFR_Health = $labs_formulas->get_eGFR_Health($eGFR);
                    } else {
                        $eGFRUnits = '';
                        $eGFR_Health = '';
                    }

                   //$renalLabs[] = array('date'=>$lab['date'], 'creatinineLabel'=>$creatinineLabel, 'creatinineValue'=>$value, 'eGFRDisplayValue'=>$eGFR." ".$eGFRUnits, 'eGFRValue'=>$eGFR, 'eGRRSource'=>$eGFRSource);
                   $aDiagLabs[] = array('DiagDate'=>$lab['date']
                           , 'Creatinine'=>$value
                           , 'eGFR'=>"$eGFR $eGFRUnits"
                           , 'eGFR_Health'=>$eGFR_Health
                           , 'Ref'=>trim("$eGFRSource $creatinineRefRange $eGFRRefRange")
                       );

                   //Assign to the EGFR array.
                   if($eGFR > '')
                   {
                        //First make sure we are set with the latest.
                        if($aJustEGFR['LATEST_EGFR'] == NULL || $aJustEGFRDate['LATEST_EGFR'] < $dDate)
                        {
                            $aJustEGFR['LATEST_EGFR'] = $eGFR;
                            $aJustEGFRDate['LATEST_EGFR'] = $dDate;
                        }
                        //Now process the day cubbies
                        $dToday = strtotime(date('Y-m-d'));
                        $nSeconds = $dToday - $dDate;
                        $nDays = $nSeconds / 86400;
                        if($nDays <= 10)
                        {
                            $thiskey = 'MIN_EGFR_10DAYS';
                            if($aJustEGFR[$thiskey] == NULL || $aJustEGFRDate[$thiskey] < $dDate)
                            {
                                 $aJustEGFR[$thiskey] = $eGFR;
                                 $aJustEGFRDate[$thiskey] = $dDate;
                            }
                        } 
                        if($nDays <= 15)
                        {
                            $thiskey = 'MIN_EGFR_15DAYS';
                            if($aJustEGFR[$thiskey] == NULL || $aJustEGFRDate[$thiskey] < $dDate)
                            {
                                 $aJustEGFR[$thiskey] = $eGFR;
                                 $aJustEGFRDate[$thiskey] = $dDate;
                            }
                        } 
                        if($nDays <= 30)
                        {
                            $thiskey = 'MIN_EGFR_30DAYS';
                            if($aJustEGFR[$thiskey] == NULL || $aJustEGFRDate[$thiskey] < $dDate)
                            {
                                 $aJustEGFR[$thiskey] = $eGFR;
                                 $aJustEGFRDate[$thiskey] = $dDate;
                            }
                        } 
                        if($nDays <= 45)
                        {
                            $thiskey = 'MIN_EGFR_45DAYS';
                            if($aJustEGFR[$thiskey] == NULL || $aJustEGFRDate[$thiskey] < $dDate)
                            {
                                 $aJustEGFR[$thiskey] = $eGFR;
                                 $aJustEGFRDate[$thiskey] = $dDate;
                            }
                        } 
                        if($nDays <= 60)
                        {
                            $thiskey = 'MIN_EGFR_60DAYS';
                            if($aJustEGFR[$thiskey] == NULL || $aJustEGFRDate[$thiskey] < $dDate)
                            {
                                 $aJustEGFR[$thiskey] = $eGFR;
                                 $aJustEGFRDate[$thiskey] = $dDate;
                            }
                        } 
                        if($nDays <= 90)
                        {
                            $thiskey = 'MIN_EGFR_90DAYS';
                            if($aJustEGFR[$thiskey] == NULL || $aJustEGFRDate[$thiskey] < $dDate)
                            {
                                 $aJustEGFR[$thiskey] = $eGFR;
                                 $aJustEGFRDate[$thiskey] = $dDate;
                            }
                        }
                   }
                }
            }

            //Build the bundle and store it in the cache.
            $bundle = array($aDiagLabs, $aJustEGFR);
            $this->m_oRuntimeResultFlexCache->addToCache($sThisResultName, $bundle, CACHE_AGE_LABS);
            $this->m_oRuntimeResultFlexCache->clearCacheBuilding($sThisResultName);

            //Share this with the caller.
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }    
}
