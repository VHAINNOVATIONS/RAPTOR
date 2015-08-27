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
 * Helper for returning medication content
 *
 * @author Frank Font of SAN Business Consultants
 */
class LabsHelper
{
    
    //Declare the field numbers
    private static $FLDNM_MEDNAME = 'name';
    private static $FLDNM_STATUS = 'status';
    private static $FLDNM_STARTDT = 'startDate';
    private static $FLDNM_DOCDT = 'dateDocumented';
    private static $FLDNM_COMMENT = 'comment';

    private $m_oContext;
    private $m_oRuntimeResultFlexCache = NULL;
    
    
    function __construct($oContext, $override_patientId=NULL)
    {
        $this->m_oContext = $oContext;
        $this->m_oRuntimeResultFlexCache = \raptor\RuntimeResultFlexCache::getInstance('ProtocolSupportingData');
    }
    
    /**
     * Display labs array
     */
    public function getDisplayLabs($override_patientId=NULL)
    {
        if($override_patientId == NULL)
        {
            $tid = $this->m_oContext->getSelectedTrackingID();
            $myDao = $this->m_oContext->getEhrDao()->getImplementationInstance();
            $pid = $myDao->getPatientIDFromTrackingID($tid);
        } else {
            $pid = $override_patientId;
        }
        $sThisResultName = $pid . '_getDisplayLabsEWD'; //patient specific
        $aCachedResult = $this->m_oRuntimeResultFlexCache->checkCache($sThisResultName);
        if($aCachedResult !== null)
        {
            //Found it in the cache!
            return $aCachedResult;
        }
        $this->m_oRuntimeResultFlexCache->markCacheBuilding($sThisResultName);

        $displayLabsResult = array();
        
        $today = getDate();
        $toDate = "".($today['year']+1)."0101";
        $fromDate = "".($today['year'] - 20)."0101";

        //$serviceResponse = $this->m_oContext->getEMRService()->getChemHemReports(array('fromDate'=>$fromDate,'toDate'=>$toDate,'nrpts'=>'0'));
        $mdwsDao = $this->m_oContext->getEhrDao()->getImplementationInstance();
        //$serviceResponse = $this->m_oContext->getMdwsClient()->makeQuery("getChemHemReports", array('fromDate'=>$fromDate,'toDate'=>$toDate,'nrpts'=>'0'));
        $serviceResponse = $mdwsDao->makeQuery("getChemHemReports", array('fromDate'=>$fromDate,'toDate'=>$toDate,'nrpts'=>'0'));
        
        $blank = " ";
        if(!isset($serviceResponse->getChemHemReportsResult->arrays->TaggedChemHemRptArray->count))
        {
            $this->m_oRuntimeResultFlexCache->clearCacheBuilding($sThisResultName);
            return $displayLabsResult;
        }
        $numTaggedRpts = $serviceResponse->getChemHemReportsResult->arrays->TaggedChemHemRptArray->count;
        if($numTaggedRpts == 0)
        {
            $this->m_oRuntimeResultFlexCache->clearCacheBuilding($sThisResultName);
            return $displayLabsResult;
        }
        
        for($i=0; $i<$numTaggedRpts; $i++)
        { //ChemHemRpts
            // Check to see if the set of rpts is an object or an array
            if (is_array($serviceResponse->getChemHemReportsResult->arrays->TaggedChemHemRptArray->rpts->ChemHemRpt)){
                $rpt = $serviceResponse->getChemHemReportsResult->arrays->TaggedChemHemRptArray->rpts->ChemHemRpt[$i];
            }
            else {
                $rpt = $serviceResponse->getChemHemReportsResult->arrays->TaggedChemHemRptArray->rpts->ChemHemRpt;
            }

            $specimen = $rpt->specimen;
            $nResults = is_array($rpt->results->LabResultTO) ? count($rpt->results->LabResultTO) : 1;
            for($j = 0; $j< $nResults; $j++)
            {
                $result = is_array($rpt->results->LabResultTO) ? $rpt->results->LabResultTO[$j] : $rpt->results->LabResultTO;
                $test = $result->test;
                $displayLabsResult[] = array(
                    'name' => isset($test->name) ? $test->name : " ",
                    'date' => isset($rpt->timestamp) ? date("m/d/Y h:i a", strtotime($rpt->timestamp)) : " ",
                    'value' => isset($result->value) ? $result->value : " ",
                    'units' =>isset($test->units) ? $test->units : " ",
                    'refRange' => isset($test->refRange) ? $test->refRange : " ",
                    'rawTime' => isset($rpt->timestamp) ? $rpt->timestamp : " ");
            }
        }
            
        $this->m_oRuntimeResultFlexCache->addToCache($sThisResultName, $displayLabsResult, CACHE_AGE_LABS);
        $this->m_oRuntimeResultFlexCache->clearCacheBuilding($sThisResultName);
        return $displayLabsResult;
    }
    
    /**
     * 1. Diagnostic labs detail array
     * 2. Just eGFR array
     * @return array of arrays
     */
    private function getLabsDetailData()
    {
        $sThisResultName = $this->m_oContext->getSelectedTrackingID() . '_getLabsDetailData'; //patient specific
        $aCachedResult = $this->m_oRuntimeResultFlexCache->checkCache($sThisResultName);
        if($aCachedResult !== NULL)
        {
            //Found it in the cache!
            return $aCachedResult;
        }
        $this->m_oRuntimeResultFlexCache->markCacheBuilding($sThisResultName);

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
        
        $isProc = true;     //$oContext->getProcedure()->isProcedure();
        $patientInfo = $this->m_aPatientInfo;

        //$patientInfo = $this->m_oContext->getPatient();
        $ethnicity = $patientInfo['ethnicity'];
        $gender = strtoupper(trim($patientInfo['gender']));
        $age = $patientInfo['age'];
        $isAfricanAmerican = (strpos('BLACK', strtoupper($ethnicity)) !== FALSE) ||
                             (strpos('AFRICAN', strtoupper($ethnicity)) !== FALSE);
        $isMale = $gender > '' && strtoupper(substr($gender,0,1)) == 'M';
        if(!$isMale)
        {
            $isFemale = $gender > '' && strtoupper(substr($gender,0,1)) == 'F';
        } else {
            $isFemale = FALSE;
        }

        $filteredLabs = array();
        $allLabs = $this->getDisplayLabs();
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
            $refRange[$key] = $row['refRange'];
            $rawTime[$key] = $row['rawTime'];
        }
        
        if(isset($name) && is_array($name)) //20140603
        {
            array_multisort($name, SORT_ASC, $rawTime, SORT_DESC, $sortedLabs);
        }

        foreach($sortedLabs as $key => $lab)
        {
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

            $alert = FALSE;
            if(isset($lowerLimit) && isset($upperLimit))
                $alert = ($lab['value'] < $lowerLimit) || ($lab['value'] > $upperLimit);
            elseif(isset($lowerLimit)&& !isset($upperLimit))
                $alert = $lab['value'] < $lowerLimit;
            elseif(!isset($lowerLimit) && isset($upperLimit))
                $alert = $lab['value'] > $upperLimit;
            else
                $alert = FALSE;
            $value = $alert ? "<span class='medical-value-danger'>!! "
                    .$lab['value']." ".$lab['units']
                    ." !!</span>" : $lab['value']." ".$lab['units'];

            $rawValue = $lab['value'];
            $units = $lab['units'];
            $creatinineRefRange = '';
            $eGFRRefRange = '';

            if($foundCreatinine)
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
        
        $aResult = array($aDiagLabs, $aJustEGFR);
        $this->m_oRuntimeResultFlexCache->addToCache($sThisResultName, $aResult, CACHE_AGE_LABS);
        $this->m_oRuntimeResultFlexCache->clearCacheBuilding($sThisResultName);
        
        return $aResult;
    }    
}
