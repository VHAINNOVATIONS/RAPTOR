<?php
/**
 * @file
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase 2
 * Open Source VA Innovation Project 2011-2015
 * VA Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, Alex Podlesny, et al
 * Contacts: acasertano@sanbusinessconsultants.com, ffont@sanbusinessconsultants.com
 * ------------------------------------------------------------------------------------
 * 
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

    
    /**
     * Look at ProtocolSupportingData.php>>>getVitalsData()
     * 
     *      $displayVitals = array();
            $allVitals = array();
            $aLatestValues = array();
     * 
     * TODO --- Make the output look like these 3 parts.......
     * 
     *     [0] => Array
        (
            [0] => Array
                (
                    [Date Taken] => 08/17/2010 04:03 pm
                    [Temp] => 99.5 F
                    [Height] =>  
                    [Weight] =>  
                    [BMI] =>  
                    [Blood Pressure] => 190/85 mmHg
                    [Pulse] => 61 /min
                    [Resp] => 22 /min
                    [Pain] =>  
                    [C/G] =>  
                    [Pox] => 96 %SpO2
                    [CVP] =>  
                    [Blood Glucose] =>  
                )

            [1] => Array
                (
                    [Date Taken] => 08/16/2010 09:29 pm
                    [Temp] => 99.5 F
                    [Height] =>  
                    [Weight] =>  
                    [BMI] =>  
                    [Blood Pressure] => 190/85 mmHg
                    [Pulse] => 61 /min
                    [Resp] => 22 /min
                    [Pain] =>  
                    [C/G] =>  
                    [Pox] => 96 %SpO2
                    [CVP] =>  
                    [Blood Glucose] =>  
                )
     * ....
     *     [1] => Array
        (
            [0] => Array
                (
                    [date] => 08/17/2010 04:03 pm
                    [name] => Temperature
                    [value] => 99.5
                    [units] => F
                    [rawTime] => 20100817.160300
                )

            [1] => Array
                (
                    [date] => 08/17/2010 04:03 pm
                    [name] => Pulse
                    [value] => 61
                    [units] => /min
                    [rawTime] => 20100817.160300
                )

            [2] => Array
                (
                    [date] => 08/17/2010 04:03 pm
                    [name] => Respiration
                    [value] => 22
                    [units] => /min
                    [rawTime] => 20100817.160300
                )

            [3] => Array
                (
                    [date] => 08/17/2010 04:03 pm
                    [name] => Blood Pressure
                    [value] => 190/85
                    [units] => mmHg
                    [rawTime] => 20100817.160300
                )
     *....
     *     [2] => Array
        (
            [Temp] => 99.5
            [Height] => 71
            [Weight] => 79.4
            [BMI] => 24
            [Blood Pressure] => 190/85
            [Pulse] => 61
            [Resp] => 22
            [Pain] => 2
            [C/G] => 
            [Pox] => 96
            [CVP] => 
            [Blood Glucose] => 
        )


     * 
     */
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
            $chunkcount = 0;
            foreach ($rawdata as $onechunk) 
            {
                $chunkcount++;
                $rowcount = 0;
                $disp_idx=-1;
                foreach($onechunk as $timestampkey=>$onerow) 
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
                                $facility = array('tag' => $expl1parts[1] , 'text' => $expl1parts[0]);
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
                            case self::$VFLD_UKNOWN13;
                                //Log it on the off chance we can determine what this is later
                                error_log("WARNING Found instance of VFLD_UKNOWN13 rawVitals"
                                        . " (c=$chunkcount r=$rowcount) row[$timestampkey][ik=$itemkey]=" 
                                        . print_r($itemdetail, TRUE));  
                                break;
                            case self::$VFLD_UKNOWN14;
                                //Log it on the off chance we can determine what this is later
                                error_log("WARNING Found instance of VFLD_UKNOWN14 rawVitals"
                                        . " (c=$chunkcount r=$rowcount) row[$timestampkey][ik=$itemkey]=" 
                                        . print_r($itemdetail, TRUE));  
                                break;
                            case self::$VFLD_QUALIFIERS;
                                //Log it on the off chance we can determine what this has in it later
                                error_log("WARNING Found instance of VFLD_QUALIFIERS rawVitals"
                                        . " (c=$chunkcount r=$rowcount) row[$timestampkey][ik=$itemkey]=" 
                                        . print_r($itemdetail, TRUE));  
                                break;
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

            //Bundle it all up for return
            $bundle = array(
                $displayVitals,
                $allVitals,
                $aLatestValues);
            
            return $bundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
