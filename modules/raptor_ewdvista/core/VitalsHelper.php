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
    public static $VSL_TEMPERATURE = "Temp";
    public static $VSL_PULSE = "Pulse";
    public static $VSL_RESPIRATION = "Resp";
    public static $VSL_BLOOD_PRESSURE = "Blood Pressure";
    public static $VSL_HEIGHT = "Height";
    public static $VSL_WEIGHT = "Weight";
    public static $VSL_PAIN = "Pain";
    public static $VSL_PULSE_OXYMETRY = "Pox";
    public static $VSL_CENTRAL_VENOUS_PRESSURE = "CVP";
    public static $VSL_CIRCUMFERENCE_GIRTH = "C/G";
    public static $VSL_BODY_MASS_INDEX = "BMI";

    //Declare the units mashup labels
    public static $VUL_TEMPERATURE = "TEMP";
    public static $VUL_PULSE = "PULSE";
    public static $VUL_RESPIRATION = "RESP";
    public static $VUL_BLOOD_PRESSURE = "BP";
    public static $VUL_HEIGHT = "HT";
    public static $VUL_WEIGHT = "WT";
    public static $VUL_PAIN = "PAIN";
    public static $VUL_PULSE_OXYMETRY = "POx";
    public static $VUL_PULSE_OXYMETRY_ALT = "O2";
    public static $VUL_CENTRAL_VENOUS_PRESSURE = "CVP";
    public static $VUL_CIRCUMFERENCE_GIRTH = "CG";
    public static $VUL_BODY_MASS_INDEX = "BMI";
    
    //Declare the vitals field numbers
    private static $VFLD_FACILITY = 1;
    private static $VFLD_TIMESTAMP = 2;
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
            $onevital['units'] = 'TODO'.print_r($unitparts,TRUE);
        }
        $onevital['rawTime'] = $timestamp;
        return $onevital;
    }

    
    private function XXX_getOneVitalNode($itemdetail,$typename,$unitparts,$unitsname)
    {
        $expl = explode('^',$itemdetail);
        $onevital = $this->XXX_getOneVitalNodeFromParts($typename, $expl[1], $unitparts[$unitsname]);
        return $onevital;
    }

    private function XXX_getOneVitalNodeFromParts($typename,$value,$units)
    {
        $onevital = array();
        $onevital['type'] = array('name'=>$typename);
        $onevital['value1'] = $value;
        if($units != NULL)
        {
            $onevital['units'] = $units;   
        }
        return $onevital;
    }

    private function XXX_getBPVitalNodes($itemdetail,$unitparts)
    {
        $bundle = array();
        $main = $this->XXX_getOneVitalNode($itemdetail, 'Blood Pressure', $unitparts, 'BP');
        $valueparts = explode('/',$main['value1']);
        $myunits = isset($main['units']) ? $main['units'] : NULL;
        $systolic = $this->XXX_getOneVitalNodeFromParts('Systolic Blood Pressure', $valueparts[0], $myunits);
        $diastolic = $this->XXX_getOneVitalNodeFromParts('Diastolic Blood Pressure', $valueparts[1], $myunits);
        $bundle[] = $main;
        return array($main,$systolic,$diastolic);
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
            $displayVitals = array();
            $allVitals = array();
            $aLatestValues = array();
            
            $aLatestValues['Temp'] = NULL;
            $aLatestValues['Height'] = NULL;
            $aLatestValues['Weight'] = NULL;
            $aLatestValues['BMI'] = NULL;
            $aLatestValues['Blood Pressure'] = NULL;
            $aLatestValues['Pulse'] = NULL;
            $aLatestValues['Resp'] = NULL;
            $aLatestValues['Pain'] = NULL;
            $aLatestValues['C/G'] = NULL;
            $aLatestValues['Pox'] = NULL;
            $aLatestValues['CVP'] = NULL;
            $aLatestValues['Blood Glucose'] = NULL;

            //Create a structure where we can track the date of the latest value.
            $aLatestValueDate['Temp'] = NULL;
            $aLatestValueDate['Height'] = NULL;
            $aLatestValueDate['Weight'] = NULL;
            $aLatestValueDate['BMI'] = NULL;
            $aLatestValueDate['Blood Pressure'] = NULL;
            $aLatestValueDate['Pulse'] = NULL;
            $aLatestValueDate['Resp'] = NULL;
            $aLatestValueDate['Pain'] = NULL;
            $aLatestValueDate['C/G'] = NULL;
            $aLatestValueDate['Pox'] = NULL;
            $aLatestValueDate['CVP'] = NULL;
            $aLatestValueDate['Blood Glucose'] = NULL;
        
            $bundle = array();
            $mycollections = array();
            if(!isset($rawresult['result']))
            {
                throw new \Exception("Missing key result in ".print_r($rawresult,TRUE));
            }
            $rawdata = $rawresult['result'];
            $chunkcount = 0;
            foreach ($rawdata as $key=>$onechunk) 
            {
                $chunkcount++;
    error_log("LOOK rawVitals (c=$chunkcount) chunk[$key] = ".print_r($onechunk, TRUE));
                $rowcount = 0;
                $disp_idx=-1;
                $k=0;
                $onecollection = array();
                $onecollection['tag'] = VISTA_SITE;
                $setscontent = array();
                foreach ($onechunk as $timestampkey=>$onerow) 
                {
                    $rowcount++;
                    $disp_idx++;
                    
                    // Initialize vitals to all blanks so we can be sure to return a value for each column
                    $displayVitals[$disp_idx]['Date Taken'] = " ";
                    $displayVitals[$disp_idx]['Temp'] = " ";
                    $displayVitals[$disp_idx]['Height'] = " ";
                    $displayVitals[$disp_idx]['Weight'] = " ";
                    $displayVitals[$disp_idx]['BMI'] = " ";
                    $displayVitals[$disp_idx]['Blood Pressure'] = " ";
                    $displayVitals[$disp_idx]['Pulse'] = " ";
                    $displayVitals[$disp_idx]['Resp'] = " ";
                    $displayVitals[$disp_idx]['Pain'] = " ";
                    $displayVitals[$disp_idx]['C/G'] = " ";
                    $displayVitals[$disp_idx]['Pox'] = " ";
                    $displayVitals[$disp_idx]['CVP'] = " ";
                    $displayVitals[$disp_idx]['Blood Glucose'] = " ";
                    
                    $cleanitem = array();
                    $cleanitem['timestamp'] = $timestampkey;
                    $cleanvitalsigns = array();
    error_log("LOOK rawVitals (c=$chunkcount r=$rowcount) row[$timestampkey]=".print_r($onerow, TRUE));  
                    $facility = NULL;
                    $qualifiers = NULL;
                    $unitsmashup = NULL;
                    $unitparts = array();
                    if(isset($onerow[17]))
                    {
                        $unitsarray = $onerow[17];
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
                    $sDate = isset($timestampkey) ? date("m/d/Y h:i a", strtotime($timestampkey)) : " ";
                    $displayVitals[$disp_idx]['Date Taken'] = $sDate;
                    foreach($onerow as $itemkey=>$itemdetail)
                    {
                        $onecleanvitalsign = NULL;
                        switch($itemkey)
                        {
                            case 1:
                                $expl1 = explode('^',$itemdetail);
                                $expl1parts = explode(';',$expl1[1]);
                                $facility = array('tag' => $expl1parts[1] , 'text' => $expl1parts[0]);
                                break;
                            case self::$VFLD_TEMPERATURE:
                                $typenamestr = self::$VNAME_TEMPERATURE;
                                $thiskey = self::$VSL_TEMPERATURE;
                                $unitsname = self::$VNAME_TEMPERATURE;
                                $onecleanvitalsign = $this->getOneVitalFormattedFromParts($itemdetail,$sDate,$timestampkey,$typenamestr,$unitparts,$unitsname);                    
                                $displayVitals[$disp_idx][$thiskey] = $onecleanvitalsign['value'] . " " . $onecleanvitalsign['units'];
                                if(($aLatestValueDate[$thiskey]==NULL || $timestampkey > $aLatestValueDate[$thiskey]))
                                {
                                    $aLatestValueDate[$thiskey] = $timestampkey;
                                    $aLatestValues[$thiskey] = $onecleanvitalsign['value'];
                                }
                                break;
                            case self::$VFLD_PULSE:
                                $typenamestr = self::$VNAME_PULSE;
                                $thiskey = self::$VSL_PULSE;
                                $unitsname = self::$VNAME_PULSE;
                                $onecleanvitalsign = $this->getOneVitalFormattedFromParts($itemdetail,$sDate,$timestampkey,$typenamestr,$unitparts,$unitsname);                    
                                $displayVitals[$disp_idx][$thiskey] = $onecleanvitalsign['value'] . " " . $onecleanvitalsign['units'];
                                if(($aLatestValueDate[$thiskey]==NULL || $timestampkey > $aLatestValueDate[$thiskey]))
                                {
                                    $aLatestValueDate[$thiskey] = $timestampkey;
                                    $aLatestValues[$thiskey] = $onecleanvitalsign['value'];
                                }
                                break;
                            case self::$VFLD_RESPIRATION:
                                $typenamestr = self::$VNAME_RESPIRATION;
                                $thiskey = self::$VSL_RESPIRATION;
                                $unitsname = self::$VNAME_RESPIRATION;
                                $onecleanvitalsign = $this->getOneVitalFormattedFromParts($itemdetail,$sDate,$timestampkey,$typenamestr,$unitparts,$unitsname);                    
                                $displayVitals[$disp_idx][$thiskey] = $onecleanvitalsign['value'] . " " . $onecleanvitalsign['units'];
                                if(($aLatestValueDate[$thiskey]==NULL || $timestampkey > $aLatestValueDate[$thiskey]))
                                {
                                    $aLatestValueDate[$thiskey] = $timestampkey;
                                    $aLatestValues[$thiskey] = $onecleanvitalsign['value'];
                                }
                                break;
                            case self::$VFLD_BLOOD_PRESSURE;
                                $typenamestr = self::$VNAME_BLOOD_PRESSURE;
                                $thiskey = self::$VSL_BLOOD_PRESSURE;
                                $unitsname = self::$VNAME_BLOOD_PRESSURE;
                                $onecleanvitalsign = $this->getOneVitalFormattedFromParts($itemdetail,$sDate,$timestampkey,$typenamestr,$unitparts,$unitsname);                    
                                $displayVitals[$disp_idx][$thiskey] = $onecleanvitalsign['value'] . " " . $onecleanvitalsign['units'];
                                if(($aLatestValueDate[$thiskey]==NULL || $timestampkey > $aLatestValueDate[$thiskey]))
                                {
                                    $aLatestValueDate[$thiskey] = $timestampkey;
                                    $aLatestValues[$thiskey] = $onecleanvitalsign['value'];
                                }
                                break;
                            case 6:
                                $bpnodes = $this->XXX_getBPVitalNodes($itemdetail,$unitparts);
                                foreach($bpnodes as $onebpnode)
                                {
                                    $cleanvitalsigns[] = $onebpnode;                                
                                }
                                break;
                            case 10:    
                                $cleanvitalsigns[] = $this->XXX_getOneVitalNode($itemdetail,'Pulse Oxymetry',$unitparts,'POX');
                                break;
                            default:
                                if(is_array($itemdetail))
                                {
        error_log("LOOK STRANGE rawVitals (c=$chunkcount r=$rowcount) row[$timestampkey][ik=$itemkey]=".print_r($itemdetail, TRUE));  
                                } else {
                                    $a = explode('^', $itemdetail);
        error_log("LOOK nice rawVitals (c=$chunkcount r=$rowcount) row[$timestampkey][ik=$itemkey]=".print_r($a, TRUE));  
                                }
                        }
if($onecleanvitalsign > NULL)
    error_log("LOOK @$disp_idx onecleanvitalsign = ".print_r($onecleanvitalsign,TRUE));    
                    }
                    $cleanitem['facility'] = $facility;
                    $cleanitem['vitalSigns'] = $cleanvitalsigns;
                    $cleanitem['units'] = $unitsmashup;
                    $cleanitem['qualifiers'] = $qualifiers;
                    $setscontent[] = $cleanitem;
                }
                $onecollection['count'] = count($setscontent);
                $sets = array();
                $sets['VitalSignSetTO'] = $setscontent;
                $onecollection['sets'] = $sets;
                $mycollections[$key] = $onecollection;
            }
            $bundle['count'] = count($mycollections);
            $bundle['arrays'] = $mycollections;
            
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
