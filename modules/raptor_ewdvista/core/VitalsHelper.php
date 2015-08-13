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
    
    private function getOneVitalNode($itemdetail,$typename,$unitparts,$unitsname)
    {
        $expl = explode('^',$itemdetail);
        $onevital = $this->getOneVitalNodeFromParts($typename, $expl[1], $unitparts[$unitsname]);
        return $onevital;
    }

    private function getOneVitalNodeFromParts($typename,$value,$units)
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

    private function getBPVitalNodes($itemdetail,$unitparts)
    {
        $bundle = array();
        $main = $this->getOneVitalNode($itemdetail, 'Blood Pressure', $unitparts, 'BP');
        $valueparts = explode('/',$main['value1']);
        $myunits = isset($main['units']) ? $main['units'] : NULL;
        $systolic = $this->getOneVitalNodeFromParts('Systolic Blood Pressure', $valueparts[0], $myunits);
        $diastolic = $this->getOneVitalNodeFromParts('Diastolic Blood Pressure', $valueparts[1], $myunits);
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
                $onecollection = array();
                $onecollection['tag'] = VISTA_SITE;
                $setscontent = array();
                foreach ($onechunk as $timestampkey=>$onerow) 
                {
                    $rowcount++;
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
                    foreach($onerow as $itemkey=>$itemdetail)
                    {
                        switch($itemkey)
                        {
                            case 1:
                                $expl1 = explode('^',$itemdetail);
                                $expl1parts = explode(';',$expl1[1]);
                                $facility = array('tag' => $expl1parts[1] , 'text' => $expl1parts[0]);
                                break;
                            case 3:
                                $cleanvitalsigns[] = $this->getOneVitalNode($itemdetail,'Temperature',$unitparts,'TEMP');
                                break;
                            case 4:
                                $cleanvitalsigns[] = $this->getOneVitalNode($itemdetail,'Pulse',$unitparts,'PULSE');
                                break;
                            case 5:    
                                $cleanvitalsigns[] = $this->getOneVitalNode($itemdetail,'Respiration',$unitparts,'RESP');
                                break;
                            case 6:
                                $bpnodes = $this->getBPVitalNodes($itemdetail,$unitparts);
                                foreach($bpnodes as $onebpnode)
                                {
                                    $cleanvitalsigns[] = $onebpnode;                                
                                }
                                break;
                            case 10:    
                                $cleanvitalsigns[] = $this->getOneVitalNode($itemdetail,'Pulse Oxymetry',$unitparts,'POX');
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
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
