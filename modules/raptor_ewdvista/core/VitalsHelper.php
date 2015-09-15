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
 * Helper for returning dashboard content
 *
 * @author Frank Font of SAN Business Consultants
 */
class VitalsHelper
{
    //Declare the vitals name strings
    public static $VNAME_TEMPERATURE = "Temperature";
    public static $VNAME_PULSE = "Pulse";
    public static $VNAME_RESPIRATION = "Respiration";
    public static $VNAME_BLOOD_PRESSURE = "Blood Pressure";
    public static $VNAME_HEIGHT = "Height";
    public static $VNAME_WEIGHT = "Weight";
    public static $VNAME_PAIN = "Pain";
    public static $VNAME_PULSE_OXYMETRY = "Pulse Oxymetry";
    public static $VNAME_CENTRAL_VENOUS_PRESSURE = "Central Venous Pressure";
    public static $VNAME_CIRCUMFERENCE_GIRTH = "Circumference/Girth";
    public static $VNAME_BODY_MASS_INDEX = "Body Mass Index";

    //Declare the short labels
    public static $VSL_DATE_TAKEN = "Date Taken";
    public static $VSL_TEMPERATURE = "Temp";
    public static $VSL_PULSE = "Pulse";
    public static $VSL_RESPIRATION = "Resp";
    public static $VSL_BLOOD_PRESSURE = "Blood Pressure";
    public static $VSL_HEIGHT = "Height";
    public static $VSL_WEIGHT = "Weight";
    public static $VSL_PAIN = "Pain";
    public static $VSL_PULSE_OXYMETRY = "POx";
    public static $VSL_CENTRAL_VENOUS_PRESSURE = "CVP";
    public static $VSL_CIRCUMFERENCE_GIRTH = "C/G";
    public static $VSL_BODY_MASS_INDEX = "BMI";
    public static $VSL_BLOOD_GLUCOSE = "Blood Glucose"; //What VistA field does this come from?

    //Declare the units mashup labels
    public static $VUL_TEMPERATURE = "TEMP";
    public static $VUL_PULSE = "PULSE";
    public static $VUL_RESPIRATION = "RESP";
    public static $VUL_BLOOD_PRESSURE = "BP";
    public static $VUL_HEIGHT = "HT";
    public static $VUL_WEIGHT = "WT";
    public static $VUL_PAIN = "PAIN";
    public static $VUL_PULSE_OXYMETRY = "POX";
    public static $VUL_PULSE_OXYMETRY_ALT = "O2";
    public static $VUL_CENTRAL_VENOUS_PRESSURE = "CVP";
    public static $VUL_CIRCUMFERENCE_GIRTH = "CG";
    public static $VUL_BODY_MASS_INDEX = "BMI";
    
    //Declare the vitals field numbers
    private static $VFLD_FACILITY = 1;
    private static $VFLD_DATE_TAKEN = 2;
    private static $VFLD_TEMPERATURE = 3;
    private static $VFLD_PULSE = 4;
    private static $VFLD_RESPIRATION = 5;
    private static $VFLD_BLOOD_PRESSURE = 6;
    private static $VFLD_HEIGHT = 7;
    private static $VFLD_WEIGHT = 8;
    private static $VFLD_PAIN = 9;
    private static $VFLD_PULSE_OXYMETRY = 10;
    private static $VFLD_CENTRAL_VENOUS_PRESSURE = 11;
    private static $VFLD_CIRCUMFERENCE_GIRTH = 12;
    private static $VFLD_UKNOWN13 = 13;
    private static $VFLD_UKNOWN14 = 14;
    private static $VFLD_QUALIFIERS = 15;
    private static $VFLD_BODY_MASS_INDEX = 16;
    private static $VFLD_UNITSMASHUP = 17;
    
    private function getOneVitalFormattedFromParts($itemdetail,$datestr,$timestamp,$typenamestr,$unitparts,$unitsname)
    {
        $onevital = array();
        $onevital['date'] = $datestr;
        $onevital['name'] = $typenamestr;
        $expl = explode('^',$itemdetail);
        $onevital['value'] = $expl[1];
        if(isset($unitparts[$unitsname]))
        {
            $onevital['units'] = $unitparts[$unitsname];
        } else {
            $onevital['units'] = NULL;  //NULL means no units found for this value
        }
        $onevital['rawTime'] = $timestamp;
        return $onevital;
    }

    public function getFormattedSuperset($rawresult)
    {
        try
        {
            //Initialize the component arrays.
            $displayVitals = array();   //Each node is a row of vitals
            $allVitals = array();       //Each node is ONE vital
            $aLatestValues = array();   //Only the latest vitals
            
            $aLatestValues[self::$VSL_TEMPERATURE] = NULL;
            $aLatestValues[self::$VSL_HEIGHT] = NULL;
            $aLatestValues[self::$VSL_WEIGHT] = NULL;
            $aLatestValues[self::$VSL_BODY_MASS_INDEX] = NULL;
            $aLatestValues[self::$VSL_BLOOD_PRESSURE] = NULL;
            $aLatestValues[self::$VSL_PULSE] = NULL;
            $aLatestValues[self::$VSL_RESPIRATION] = NULL;
            $aLatestValues[self::$VSL_PAIN] = NULL;
            $aLatestValues[self::$VSL_CIRCUMFERENCE_GIRTH] = NULL;
            $aLatestValues[self::$VSL_PULSE_OXYMETRY] = NULL;
            $aLatestValues[self::$VSL_CENTRAL_VENOUS_PRESSURE] = NULL;
            $aLatestValues[self::$VSL_BLOOD_GLUCOSE] = NULL;

            //Create a structure where we can track the date of the latest value for a date.
            $aLatestValueDate[self::$VSL_TEMPERATURE] = NULL;
            $aLatestValueDate[self::$VSL_HEIGHT] = NULL;
            $aLatestValueDate[self::$VSL_WEIGHT] = NULL;
            $aLatestValueDate[self::$VSL_BODY_MASS_INDEX] = NULL;
            $aLatestValueDate[self::$VSL_BLOOD_PRESSURE] = NULL;
            $aLatestValueDate[self::$VSL_PULSE] = NULL;
            $aLatestValueDate[self::$VSL_RESPIRATION] = NULL;
            $aLatestValueDate[self::$VSL_PAIN] = NULL;
            $aLatestValueDate[self::$VSL_CIRCUMFERENCE_GIRTH] = NULL;
            $aLatestValueDate[self::$VSL_PULSE_OXYMETRY] = NULL;
            $aLatestValueDate[self::$VSL_CENTRAL_VENOUS_PRESSURE] = NULL;
            $aLatestValueDate[self::$VSL_BLOOD_GLUCOSE] = NULL;
            
            if(!isset($rawresult['result']))
            {
                throw new \Exception("Missing key result in ".print_r($rawresult,TRUE));
            }
            $rawdata = $rawresult['result'];
//error_log("LOOK vitalshelper thing rawdata>>>".print_r($rawdata,TRUE));                
            $chunkcount = 0;
            foreach ($rawdata as $onechunk) 
            {
                $chunkcount++;
                $rowcount = 0;
                $disp_idx=-1;
//error_log("LOOK vitalshelper thing onecheck>>>".print_r($onechunk,TRUE)); 
                foreach($onechunk as $timestampkey=>$onerow)
                {
//error_log("LOOK vitalshelper thing blocks>>>".print_r($blocks,TRUE)); 
                    //foreach($blocks as $timestampkey=>$onerow) 
                    {
                        $rowcount++;
                        $disp_idx++;

                        // Initialize vitals to all blanks so we can be sure to return a value for each column
                        $displayVitals[$disp_idx][self::$VSL_DATE_TAKEN] = " ";
                        $displayVitals[$disp_idx][self::$VSL_TEMPERATURE] = " ";
                        $displayVitals[$disp_idx][self::$VSL_HEIGHT] = " ";
                        $displayVitals[$disp_idx][self::$VSL_WEIGHT] = " ";
                        $displayVitals[$disp_idx][self::$VSL_BODY_MASS_INDEX] = " ";
                        $displayVitals[$disp_idx][self::$VSL_BLOOD_PRESSURE] = " ";
                        $displayVitals[$disp_idx][self::$VSL_PULSE] = " ";
                        $displayVitals[$disp_idx][self::$VSL_RESPIRATION] = " ";
                        $displayVitals[$disp_idx][self::$VSL_PAIN] = " ";
                        $displayVitals[$disp_idx][self::$VSL_CIRCUMFERENCE_GIRTH] = " ";
                        $displayVitals[$disp_idx][self::$VSL_PULSE_OXYMETRY] = " ";
                        $displayVitals[$disp_idx][self::$VSL_CENTRAL_VENOUS_PRESSURE] = " ";
                        $displayVitals[$disp_idx][self::$VSL_BLOOD_GLUCOSE] = " ";

                        $cleanvitalsigns = array();
                        $facility = NULL;
                        $qualifiers = NULL;
                        $unitsmashup = NULL;
                        $unitparts = array();
                        if(isset($onerow[self::$VFLD_UNITSMASHUP]))
                        {
                            $unitsarray = $onerow[self::$VFLD_UNITSMASHUP];
                            $expl = explode('^',$unitsarray[1]);
                            $unitsmashup = $expl[1];
                            $explparts = explode(',',$unitsmashup);
                            foreach($explparts as $onepart)
                            {
                                $pair = explode(':',$onepart);
                                $name = strtoupper(trim($pair[0]));
                                $unitparts[$name] = trim($pair[1]);
                            }
                        }
                        if(isset($onerow[self::$VFLD_DATE_TAKEN]))
                        {
                            $rawstr = $onerow[self::$VFLD_DATE_TAKEN];
                            $expl = explode('^',$rawstr);
                            if(count($expl) != 2)
                            {
                                throw new \Exception("Expected 2 parts in rawstr '$rawstr' for field ".self::$VFLD_DATE_TAKEN." of ".print_r($onerow,TRUE));
                            }
                            $sDate = $expl[1];  // . " (rawstr=$rawstr)";
                        } else {
                            $sDate = isset($timestampkey) ? date("m/d/Y h:i a", strtotime($timestampkey)) : " ";
                        }
                        $displayVitals[$disp_idx]['Date Taken'] = $sDate;
                        foreach($onerow as $itemkey=>$itemdetail)
                        {
                            $onecleanvitalsign = NULL;
                            switch($itemkey)
                            {
                                case self::$VFLD_FACILITY:
                                    $expl1 = explode('^',$itemdetail);
                                    $expl1parts = explode(';',$expl1[1]);
                                    if(count($expl1parts) !== 2)
                                    {
                                        throw new \Exception("Expected 2 parts in second part of '$itemdetail'(parts=".print_r($expl1,TRUE).") for field ".self::$VFLD_FACILITY." of ".print_r($onerow,TRUE));
                                    }
                                    $facility = ['tag' => $expl1parts[1] , 'text' => $expl1parts[0]];
                                    break;
                                case self::$VFLD_TEMPERATURE:
                                    $typenamestr = self::$VNAME_TEMPERATURE;
                                    $thiskey = self::$VSL_TEMPERATURE;
                                    $unitsname = self::$VUL_TEMPERATURE;
                                    $onecleanvitalsign = $this->getOneVitalFormattedFromParts($itemdetail,$sDate,$timestampkey,$typenamestr,$unitparts,$unitsname);                    
                                    $displayVitals[$disp_idx][$thiskey] = $onecleanvitalsign['value'] 
                                            . " " 
                                            . $onecleanvitalsign['units'];
                                    if(($aLatestValueDate[$thiskey]==NULL || $timestampkey > $aLatestValueDate[$thiskey]))
                                    {
                                        $aLatestValueDate[$thiskey] = $timestampkey;
                                        $aLatestValues[$thiskey] = $onecleanvitalsign['value'];
                                    }
                                    break;
                                case self::$VFLD_PULSE:
                                    $typenamestr = self::$VNAME_PULSE;
                                    $thiskey = self::$VSL_PULSE;
                                    $unitsname = self::$VUL_PULSE;
                                    $onecleanvitalsign = $this->getOneVitalFormattedFromParts($itemdetail,$sDate,$timestampkey,$typenamestr,$unitparts,$unitsname);                    
                                    $displayVitals[$disp_idx][$thiskey] = $onecleanvitalsign['value'] 
                                            . " " 
                                            . $onecleanvitalsign['units'];
                                    if(($aLatestValueDate[$thiskey]==NULL || $timestampkey > $aLatestValueDate[$thiskey]))
                                    {
                                        $aLatestValueDate[$thiskey] = $timestampkey;
                                        $aLatestValues[$thiskey] = $onecleanvitalsign['value'];
                                    }
                                    break;
                                case self::$VFLD_RESPIRATION:
                                    $typenamestr = self::$VNAME_RESPIRATION;
                                    $thiskey = self::$VSL_RESPIRATION;
                                    $unitsname = self::$VUL_RESPIRATION;
                                    $onecleanvitalsign = $this->getOneVitalFormattedFromParts($itemdetail,$sDate,$timestampkey,$typenamestr,$unitparts,$unitsname);                    
                                    $displayVitals[$disp_idx][$thiskey] = $onecleanvitalsign['value'] 
                                            . " " 
                                            . $onecleanvitalsign['units'];
                                    if(($aLatestValueDate[$thiskey]==NULL || $timestampkey > $aLatestValueDate[$thiskey]))
                                    {
                                        $aLatestValueDate[$thiskey] = $timestampkey;
                                        $aLatestValues[$thiskey] = $onecleanvitalsign['value'];
                                    }
                                    break;
                                case self::$VFLD_BLOOD_PRESSURE;
                                    $typenamestr = self::$VNAME_BLOOD_PRESSURE;
                                    $thiskey = self::$VSL_BLOOD_PRESSURE;
                                    $unitsname = self::$VUL_BLOOD_PRESSURE;
                                    $onecleanvitalsign = $this->getOneVitalFormattedFromParts($itemdetail,$sDate,$timestampkey,$typenamestr,$unitparts,$unitsname);                    
                                    $displayVitals[$disp_idx][$thiskey] = $onecleanvitalsign['value'] 
                                            . " " 
                                            . $onecleanvitalsign['units'];
                                    if(($aLatestValueDate[$thiskey]==NULL || $timestampkey > $aLatestValueDate[$thiskey]))
                                    {
                                        $aLatestValueDate[$thiskey] = $timestampkey;
                                        $aLatestValues[$thiskey] = $onecleanvitalsign['value'];
                                    }
                                    break;
                                case self::$VFLD_HEIGHT;
                                    $typenamestr = self::$VNAME_HEIGHT;
                                    $thiskey = self::$VSL_HEIGHT;
                                    $unitsname = self::$VUL_HEIGHT;
                                    $onecleanvitalsign = $this->getOneVitalFormattedFromParts($itemdetail,$sDate,$timestampkey,$typenamestr,$unitparts,$unitsname);                    
                                    $displayVitals[$disp_idx][$thiskey] = $onecleanvitalsign['value'] 
                                            . " " 
                                            . $onecleanvitalsign['units'];
                                    if(($aLatestValueDate[$thiskey]==NULL || $timestampkey > $aLatestValueDate[$thiskey]))
                                    {
                                        $aLatestValueDate[$thiskey] = $timestampkey;
                                        $aLatestValues[$thiskey] = $onecleanvitalsign['value'];
                                    }
                                    break;
                                case self::$VFLD_WEIGHT;
                                    $typenamestr = self::$VNAME_WEIGHT;
                                    $thiskey = self::$VSL_WEIGHT;
                                    $unitsname = self::$VUL_WEIGHT;
                                    $onecleanvitalsign = $this->getOneVitalFormattedFromParts($itemdetail,$sDate,$timestampkey,$typenamestr,$unitparts,$unitsname);                    
                                    $displayVitals[$disp_idx][$thiskey] = $onecleanvitalsign['value'] 
                                            . " " 
                                            . $onecleanvitalsign['units'];
                                    if(($aLatestValueDate[$thiskey]==NULL || $timestampkey > $aLatestValueDate[$thiskey]))
                                    {
                                        $aLatestValueDate[$thiskey] = $timestampkey;
                                        $aLatestValues[$thiskey] = $onecleanvitalsign['value'];
                                    }
                                    break;
                                case self::$VFLD_PAIN;
                                    $typenamestr = self::$VNAME_PAIN;
                                    $thiskey = self::$VSL_PAIN;
                                    $unitsname = self::$VUL_PAIN;
                                    $onecleanvitalsign = $this->getOneVitalFormattedFromParts($itemdetail,$sDate,$timestampkey,$typenamestr,$unitparts,$unitsname);                    
                                    $displayVitals[$disp_idx][$thiskey] = $onecleanvitalsign['value'] 
                                            . " " 
                                            . $onecleanvitalsign['units'];
                                    if(($aLatestValueDate[$thiskey]==NULL || $timestampkey > $aLatestValueDate[$thiskey]))
                                    {
                                        $aLatestValueDate[$thiskey] = $timestampkey;
                                        $aLatestValues[$thiskey] = $onecleanvitalsign['value'];
                                    }
                                    break;
                                case self::$VFLD_PULSE_OXYMETRY;
                                    $typenamestr = self::$VNAME_PULSE_OXYMETRY;
                                    $thiskey = self::$VSL_PULSE_OXYMETRY;
                                    $unitsname = self::$VUL_PULSE_OXYMETRY;
                                    $onecleanvitalsign = $this->getOneVitalFormattedFromParts($itemdetail,$sDate,$timestampkey,$typenamestr,$unitparts,$unitsname);                    
                                    $displayVitals[$disp_idx][$thiskey] = $onecleanvitalsign['value'] 
                                            . " " 
                                            . $onecleanvitalsign['units'];
                                    if(($aLatestValueDate[$thiskey]==NULL || $timestampkey > $aLatestValueDate[$thiskey]))
                                    {
                                        $aLatestValueDate[$thiskey] = $timestampkey;
                                        $aLatestValues[$thiskey] = $onecleanvitalsign['value'];
                                    }
                                    break;
                                case self::$VFLD_CENTRAL_VENOUS_PRESSURE;
                                    $typenamestr = self::$VNAME_CENTRAL_VENOUS_PRESSURE;
                                    $thiskey = self::$VSL_CENTRAL_VENOUS_PRESSURE;
                                    $unitsname = self::$VUL_CENTRAL_VENOUS_PRESSURE;
                                    $onecleanvitalsign = $this->getOneVitalFormattedFromParts($itemdetail,$sDate,$timestampkey,$typenamestr,$unitparts,$unitsname);                    
                                    $displayVitals[$disp_idx][$thiskey] = $onecleanvitalsign['value'] 
                                            . " " 
                                            . $onecleanvitalsign['units'];
                                    if(($aLatestValueDate[$thiskey]==NULL || $timestampkey > $aLatestValueDate[$thiskey]))
                                    {
                                        $aLatestValueDate[$thiskey] = $timestampkey;
                                        $aLatestValues[$thiskey] = $onecleanvitalsign['value'];
                                    }
                                    break;
                                case self::$VFLD_CIRCUMFERENCE_GIRTH;
                                    $typenamestr = self::$VNAME_CIRCUMFERENCE_GIRTH;
                                    $thiskey = self::$VSL_CIRCUMFERENCE_GIRTH;
                                    $unitsname = self::$VUL_CIRCUMFERENCE_GIRTH;
                                    $onecleanvitalsign = $this->getOneVitalFormattedFromParts($itemdetail,$sDate,$timestampkey,$typenamestr,$unitparts,$unitsname);                    
                                    $displayVitals[$disp_idx][$thiskey] = $onecleanvitalsign['value'] 
                                            . " " 
                                            . $onecleanvitalsign['units'];
                                    if(($aLatestValueDate[$thiskey]==NULL || $timestampkey > $aLatestValueDate[$thiskey]))
                                    {
                                        $aLatestValueDate[$thiskey] = $timestampkey;
                                        $aLatestValues[$thiskey] = $onecleanvitalsign['value'];
                                    }
                                    break;
                                case self::$VFLD_BODY_MASS_INDEX;
                                    $typenamestr = self::$VNAME_BODY_MASS_INDEX;
                                    $thiskey = self::$VSL_BODY_MASS_INDEX;
                                    $unitsname = self::$VUL_BODY_MASS_INDEX;
                                    $onecleanvitalsign = $this->getOneVitalFormattedFromParts($itemdetail,$sDate,$timestampkey,$typenamestr,$unitparts,$unitsname);                    
                                    $displayVitals[$disp_idx][$thiskey] = $onecleanvitalsign['value'] 
                                            . " " 
                                            . $onecleanvitalsign['units'];
                                    if(($aLatestValueDate[$thiskey]==NULL || $timestampkey > $aLatestValueDate[$thiskey]))
                                    {
                                        $aLatestValueDate[$thiskey] = $timestampkey;
                                        $aLatestValues[$thiskey] = $onecleanvitalsign['value'];
                                    }
                                    break;
                                    /*
                                case self::$VFLD_UKNOWN13;
                                    //Empty?
                                    if(count($itemdetail) > 0)
                                    {
                                        //Log it on the off chance we can determine what this is later
                                        error_log("WARNING Found instance of VFLD_UKNOWN13 rawVitals"
                                                . " (c=$chunkcount r=$rowcount) row[$timestampkey][ik=$itemkey]=" 
                                                . print_r($itemdetail, TRUE));  
                                    }
                                    break;
                                case self::$VFLD_UKNOWN14;
                                    //Empty?
                                    if(count($itemdetail) > 0)
                                    {
                                        //Log it on the off chance we can determine what this is later
                                        error_log("WARNING Found instance of VFLD_UKNOWN14 rawVitals"
                                                . " (c=$chunkcount r=$rowcount) row[$timestampkey][ik=$itemkey]=" 
                                                . print_r($itemdetail, TRUE));  
                                    }
                                    break;
                                case self::$VFLD_QUALIFIERS;
                                    foreach($itemdetail as $k=>$v)
                                    {
                                        //Empty value?
                                        if(strlen($v>3))
                                        {
                                            //Log it on the off chance we can determine what this has in it later
                                            error_log("WARNING Found instance of VFLD_QUALIFIERS rawVitals"
                                                    . " (c=$chunkcount r=$rowcount) row[$timestampkey][ik=$itemkey]=" 
                                                    . print_r($itemdetail, TRUE));  
                                        }
                                    }
                                    break;
                                     * 
                                     */
                                default:
                                    //Unmapped item to simply ignore
                                    $onecleanvitalsign = NULL;
                            }
                            if($onecleanvitalsign > NULL)
                            {
                                $allVitals[] = $onecleanvitalsign;
                                //error_log("LOOK @$disp_idx onecleanvitalsign = ".print_r($onecleanvitalsign,TRUE));    
                            }
                        }
                    }
                }
            }

            //Bundle it all up for return
            $bundle = array(
                $displayVitals,
                $allVitals,
                $aLatestValues);
            
            return $bundle;
            
        } catch (\Exception $ex) {
            $fatalmsg = "Failed to process INPUT in getFormattedSuperset because $ex";
            error_log($fatalmsg . "\n...FAILED INPUT=".print_r($rawresult,TRUE));
            throw new \Exception($fatalmsg,99876,$ex);
        }
    }
    
    
    /**
     * The labels are meant for display to to the user.  The values include units where units are appropriate.
     * @return type array of labels and their values
     */
    function getVitalsSummary($vitalsbundle)
    {
        $result = array();
        $displayVitals  = $vitalsbundle[0];
        $allVitals      = $vitalsbundle[1];

        if(empty($displayVitals))
        {
            return $result;
        }
        
        $tempLabels = array("Temperature", "TEMP", "TEMPERATURE");
        $hrLabels = array("Heart Rate", "HR", "HEART RATE");
        $bpLabels = array("Blood Pressure", "DIASTOLIC BLOOD PRESSURE", "SYSTOLIC BLOOD PRESSURE", "BP", "BLOOD PRESSURE");
        $htLabels = array("Height", "HT", "HEIGHT");
        $wtLabels = array("Weight", "WT", "WEIGHT");
        $bmiLabels = array("Body Mass Index", "BMI", "BODY MASS INDEX");
        
        $nTemp = 0;
        $nHR = 0;
        $nBP = 0;
        $nHT = 0;
        $nWT = 0;
        $nBMI = 0;
        $nToFind = 1;
        
        //Sort the vitals by type, then date (desc)
        // Iterate through, looking for the 1st occurance of Temp, HR, BP, Ht, Wt, BMI
        // Obtain a list of columns
        $sortedVitals = $allVitals;
        foreach ($sortedVitals as $key => $row) 
        {
            $date[$key] = $row['date'];
            $name[$key]  = $row['name'];
            $value[$key] = $row['value'];
            $units[$key] = $row['units'];
            $rawTime[$key] = $row['rawTime'];
        }
        if(empty($name))
        {
            return $result;
        }

        array_multisort($name, SORT_ASC, $rawTime, SORT_DESC, $sortedVitals);
        
        $blank = array('date' => "", 'name' => "", 'value' => "None Found", 'units' => "");
        
        foreach($sortedVitals as $vital)
        {
            if($nTemp >= $nToFind && $nHR >= $nToFind 
                    && $nBP >= $nToFind && $nHT >= $nToFind 
                    && $nWT >= $nToFind && $nBMI >= $nToFind)
            {
                break;
            }
            if(in_array(strtoupper($vital['name']), $tempLabels)){ // Temp
                if ($nTemp++ < $nToFind)
                {
                   $vital['value'] .= isset($vital['units']) ? " ".$vital['units'] : "";
                   $vital['score'] = "1";
                   $result[] = $vital;
                }
            }
            elseif(in_array(strtoupper($vital['name']), $hrLabels)){ //HR)
                if ($nHR++ < $nToFind)
                {
                   $vital['value'] .= isset($vital['units']) ? " ".$vital['units'] : "";
                   $vital['score'] = "2";
                   $result[] = $vital;
                }
            }
            elseif (in_array(strtoupper($vital['name']), $bpLabels)) { //BP
                if ($nBP++ < $nToFind)
                {
                   $vital['value'] .= isset($vital['units']) ? " ".$vital['units'] : "";
                   $vital['score'] = "3";
                   $result[] = $vital;
                }
            }
            elseif (in_array(strtoupper($vital['name']), $htLabels)) { //HT
                if ($nHT++ < $nToFind)
                {
                   $cms = round($vital['value']*2.54, 1);
                   $vital['value'] .= isset($vital['units']) ? " ".$vital['units']." (".$cms." cms)" : "";
                   $vital['score'] = "4";
                   $result[] = $vital;
                }
            }
            elseif (in_array(strtoupper($vital['name']), $wtLabels)) { //WT
                if ($nWT++ < $nToFind)
                {
                   $kgs = round($vital['value']*0.45359237, 1);
                   $vital['value'] .= isset($vital['units']) ? " ".$vital['units']." (".$kgs." kgs)" : "";
                   $vital['score'] = "5";
                   $result[] = $vital;
                }
            }
            elseif (in_array(strtoupper($vital['name']), $bmiLabels)){ //BMI
                if ($nBMI++ < $nToFind)
                {
                   $vital['value'] .= isset($vital['units']) ? " ".$vital['units'] : "";
                   $vital['score'] = "6";
                   $result[] = $vital;
                }
            }
        }
        
        //Re-Sort the vitals for display by date (desc) then by type
        // Iterate through, looking for the 1st occurance of Temp, HR, BP, Ht, Wt, BMI
        // Obtain a list of columns
        
        unset($sortedVitals);
        unset($date);
        unset($name);
        unset($value);
        unset($units);
        unset($rawTime);
        
        $score=array(); //FJF 20120319
        $sortedVitals = $result;
        foreach ($sortedVitals as $key => $row) 
        {
            $date[$key] = $row['date'];
            $name[$key]  = $row['name'];
            $value[$key] = $row['value'];
            $units[$key] = $row['units'];
            $rawTime[$key] = $row['rawTime'];
            $score[$key] = $row['score'];
        }
        array_multisort($score, SORT_ASC, $sortedVitals);
        $result = array('Temperature' => ''
            , 'Heart Rate' => ''
            , 'Blood Pressure' => ''
            , 'Height' => ''
            , 'Weight' => ''
            , 'Body Mass Index' => '');
        foreach ($sortedVitals as $vital) 
        {
            $result[$vital['name']] = array('Date of Measurement' => $vital['date']
                    , 'Measurement Value' => $vital['value']);
        }
        
        // Add message for Vitals not found
        if($nTemp == 0){
            $result[$tempLabels[0]] = array("Date of Measurement" => "", "Measurement Value" => $blank['value']);        
        }
        if($nHR == 0){
            $result[$hrLabels[0]] = array("Date of Measurement" => "", "Measurement Value" => $blank['value']);
        }
        if($nBP == 0){
            $result[$bpLabels[0]] = array("Date of Measurement" => "", "Measurement Value" => $blank['value']);
        }
        if($nHT == 0){
            $result[$htLabels[0]] = array("Date of Measurement" => "", "Measurement Value" => $blank['value']);
        }
        if($nWT == 0){
            $result[$wtLabels[0]] = array("Date of Measurement" => "", "Measurement Value" => $blank['value']);
        }
        if($nBMI == 0){
            $result[$bmiLabels[0]] = array("Date of Measurement" => "", "Measurement Value" => $blank['value']);
        }
        
        return $result;
    }
}
