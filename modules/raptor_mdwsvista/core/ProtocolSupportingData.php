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
 * 
 */ 

namespace raptor_mdwsvista;

module_load_include('php', 'raptor_glue', 'core/config');
module_load_include('php', 'raptor_formulas', 'core/Labs');
module_load_include('php', 'raptor_datalayer', 'core/Context');
module_load_include('php', 'raptor_datalayer', 'core/RuntimeResultCache');
module_load_include('php', 'raptor_datalayer', 'core/RuntimeResultFlexCache');

/**
 * This class contains the functions that return supplemental information for the 
 * protocoling effort.
 *
 * @author SAN
 */
class ProtocolSupportingData
{
    private $m_oContext;
    private $m_aDashboardMap;
    private $m_aPatientInfo;
    private $m_oRuntimeResultCache;    //Cache results.
    private $m_oRuntimeResultFlexCache;    //Cache results.
   
    function __construct($oContext)
    {
        $this->m_oContext = $oContext;
        $this->m_oRuntimeResultCache = \raptor\RuntimeResultCache::getInstance($this->m_oContext,'ProtocolSupportingData');
        $this->m_oRuntimeResultFlexCache = \raptor\RuntimeResultFlexCache::getInstance('ProtocolSupportingData');
        $wl = new \raptor_mdwsvista\WorklistData($this->m_oContext);
        $this->m_aDashboardMap = $wl->getDashboardMap();   // getWorklistForProtocolSupport();
        $this->m_aPatientInfo = $wl->getPatient($this->m_aDashboardMap["PatientID"]);
        if($this->m_aPatientInfo == NULL)
        {
            $msg = ('Did NOT get patient data in constructor of ProtocolSupport for context='.$oContext 
                    . '<br>orders='. print_r($this->m_aDashboardMap, TRUE) 
                    . '<br>Stack trace...<br>'.Context::debugGetCallerInfo(5));
            error_log($msg);
            throw new \Exception($msg);
        }
    }
    
    /**
     * Return the entire raw dashboard.
     */
    function getPatientDashboard()
    {
        return $this->m_aDashboardMap;
    }
    
    
    function getPendingOrdersMap()
    {
        return $this->m_aDashboardMap['MapPendingOrders'];
    }
         
    /**
     * The order overview
     * @return type array of arrays
     */
    function getOrderOverview()
    {
        return array('RqstBy'=>$this->m_aDashboardMap['RequestedBy'],
                     'PCP'=>$this->m_aPatientInfo['teamPcpName'],
                     'AtP'=>$this->m_aPatientInfo['teamAttendingName'],
                     'RqstStdy'=>$this->m_aDashboardMap['Procedure'],
                     'RsnStdy'=>$this->m_aDashboardMap['ReasonForStudy'],
                    );
    }
    
    /**
     * Return array of medications for a patient. Patient must be set in MDWS context. 
     */
    function getMedicationsDetail($atriskmeds=NULL)
    {
        try
        {
            //$serviceResponse = $this->m_oContext->getEMRService()->getAllMeds();
            //$serviceResponse = $this->m_oContext->getMdwsClient()->makeQuery('getAllMeds', NULL);
            $mdwsDao = $this->m_oContext->getEhrDao()->getImplementationInstance();
            $serviceResponse = $mdwsDao->makeQuery('getAllMeds', NULL);
            $displayMeds = array();
            $atriskhits = array();
            $bundle = array('details' => $displayMeds, 'atrisk_hits'=>$atriskhits);

            if(!isset($serviceResponse->getAllMedsResult->count))
            {
                    return $bundle;
            }
            $numMeds = $serviceResponse->getAllMedsResult->count;

            if($numMeds == 0)
            {
                return $bundle;
            }

            if($atriskmeds==NULL)
            {
                $atriskmeds = array();
                $checkatrisk = FALSE;
            } else {
                $checkatrisk = TRUE;
            }

            $displayMedsLast = array();
            for ($i=0; $i<$numMeds; $i++){
                // Check to see if 'arrays' is actually an array or just an object
                $objType = gettype($serviceResponse->getAllMedsResult->arrays);
                if ($objType == 'array')
                    $medications = $serviceResponse->getAllMedsResult->arrays->TaggedMedicationArray[$i];
                elseif ($objType == 'object')
                    $medications = $serviceResponse->getAllMedsResult->arrays->TaggedMedicationArray;
                else {
                    return $bundle;
                }

                $n = $medications->count;
                if($n == 0)
                {
                    return $bundle;
                }

                for ($j=0; $j<$n; $j++)
                {
                    // Check to see if is actually an array or just an object
                    $objType = gettype($medications->meds->MedicationTO);
                    if ($objType == 'array')
                        $med = $medications->meds->MedicationTO[$j];
                    elseif ($objType == 'object')
                        $med = $medications->meds->MedicationTO;
                    else {
                        $med = NULL;
                    }
                    if($med !== NULL)
                    {
                        $tempMeds = array();
                        if(isset($med->name))
                        {
                            $medname = trim($med->name);
                            $tempMeds['Med'] = $medname;
                            $status = isset($med->status) ? $med->status : " ";
                            $tempMeds['Status'] = $status;
                            $cleanstatus = strtoupper(trim($status));
                            if($checkatrisk)
                            {
                                $atriskmatchtext = self::findSubstringMatchInArray($medname, $atriskmeds);
                                $atrisk = $atriskmatchtext !== FALSE;
                                $tempMeds['AtRisk'] = ($atrisk ? 'YES' : 'no');
                                $tempMeds['warn'] = ($atrisk && ($cleanstatus == '' 
                                        || $cleanstatus == 'ACTIVE' 
                                        || $cleanstatus == 'PENDING')); 
                                if($atrisk)
                                {
                                    $atriskhits[$atriskmatchtext] = $atriskmatchtext;   //Set the key and value the same!
                                    $displayMeds[] = $tempMeds;
                                } else {
                                    $displayMedsLast[] = $tempMeds;
                                }
                            } else {
                                $tempMeds['AtRisk'] = '';
                                $tempMeds['warn'] = FALSE;
                                $displayMeds[] = $tempMeds;
                            }
                        }
                    }
                }
            }
            $bottom = array();
            foreach($displayMedsLast as $medsinfo)
            {
                if($medsinfo['warn'])
                {
                    $displayMeds[] = $medsinfo;
                } else {
                    $bottom[] = $medsinfo;
                }
            }
            $displayMeds = array_merge($displayMeds, $bottom);
            $bundle = array('details' => $displayMeds, 'atrisk_hits'=>$atriskhits);
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    private static function findSubstringMatchInArray($needle, $haystackarray)
    {
        try
        {
            $cleanneedle = strtoupper(trim($needle));
            foreach($haystackarray as $check)
            {
                $cleancheck = strtoupper(trim($check));
                if(FALSE !== strpos($cleancheck,$cleanneedle))
                {
                    return $check;
                }
                if(FALSE !== strpos($cleanneedle,$cleancheck))
                {
                    return $check;
                }
            }
            return FALSE;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getAllHospitalLocations($mdwsDao,$maxqueries=120,$startingitem='',$prependlist=NULL)
    {
        $sThisResultName = 'getAllHospitalLocations';   //Not patient specific
        if($prependlist == NULL)
        {
            $aCachedResult = $this->m_oRuntimeResultFlexCache->checkCache($sThisResultName);
            if($aCachedResult !== NULL)
            {
                //Found it in the cache!
                return $aCachedResult;
            }
        }
        $this->m_oRuntimeResultFlexCache->markCacheBuilding($sThisResultName);
        $queries = 1;
        //$locations = \raptor_mdwsvista\MdwsUtils::getHospitalLocations($mdwsDao, $startingitem);   
        $locations = $mdwsDao->getHospitalLocationsMap($startingitem);   
        $prevend = end($locations);
        $lastitem = $prevend;
        while(is_array($locations) && end($locations) > '' && $queries < $maxqueries)
        {
            $queries++;
            //$morelocations = \raptor_mdwsvista\MdwsUtils::getHospitalLocations($mdwsDao, $lastitem);
            $morelocations = $mdwsDao->getHospitalLocationsMap($lastitem);
            $lastitem = end($morelocations);
            if($prevend >= $lastitem)
            {
                foreach($morelocations as $k=>$v)
                {
                    if($v <= $prevend)
                    {
                        //Wraps starting here.
                        break;
                    }
                    $locations[$k] = $v;
                }
                //We are done
                break;
            }
            $locations = $locations + $morelocations;   //DO NOT USE array_merge function!!!!
            $prevend = $lastitem;
        }
        if($queries >= $maxqueries)
        {
            error_log("WARNING in getAllHospitalLocations stopped queries after $queries executed!!!");
            $locations['getmore'] = '* Get More Locations *';
        }
        if($prependlist !== NULL)
        {
            $locations = array_merge($prependlist, $locations);
        }
        $this->m_oRuntimeResultFlexCache->addToCache($sThisResultName, $locations, CACHE_AGE_SITEVALUES);
        $this->m_oRuntimeResultFlexCache->clearCacheBuilding($sThisResultName);
        return $locations;
    }
    
    /**
     * Leverage caching so only one call is made.
     */
    public function getRawVitalSigns()
    {
        $sThisResultName = $this->m_oContext->getSelectedTrackingID() . '_raw_getVitalSigns'; //patient specific
        $oCachedResult = $this->m_oRuntimeResultFlexCache->checkCache($sThisResultName);
        if($oCachedResult !== NULL)
        {
            //Found it in the cache!
            return $oCachedResult;
        }
        $this->m_oRuntimeResultFlexCache->markCacheBuilding($sThisResultName);
        $mdwsDao = $this->m_oContext->getEhrDao()->getImplementationInstance();
        $soapResult = $mdwsDao->makeQuery('getVitalSigns', NULL);
        //$soapResult = $this->m_oContext->getMdwsClient()->makeQuery('getVitalSigns', NULL);
        $this->m_oRuntimeResultFlexCache->addToCache($sThisResultName, $soapResult, CACHE_AGE_LABS);
        $this->m_oRuntimeResultFlexCache->clearCacheBuilding($sThisResultName);
        return $soapResult;
    }
    
    
    /**
     * Create the following three arrays of data and group them into one returned array.
     * 1 DisplayVitals -- All available vitals formatted for display
     * 2 AllVitals    -- All available vitals for computation use
     * 3 LatestVitals -- The most current values known
     * @return array of 3 arrays
     */
    private function getVitalsData()
    {
        $sThisResultName = 'getVitalsData';
        $aCachedResult = $this->m_oRuntimeResultCache->checkCache($sThisResultName);
        if($aCachedResult !== null)
        {
            //Found it in the cache!
            return $aCachedResult;
        }
        
        $serviceResponse = $this->getRawVitalSigns();
       
        //Initialize the component arrays.
        $displayVitals = array();
        $allVitals = array();
        $aLatestValues = array();
       
        if(!isset($serviceResponse->getVitalSignsResult->arrays->TaggedVitalSignSetArray->count))
        {
            return array($displayVitals, $allVitals, $aLatestValues);
        }
       
        $numTaggedVitals = $serviceResponse->getVitalSignsResult->arrays->TaggedVitalSignSetArray->count;
        if($numTaggedVitals == 0)
        {
            return array($displayVitals, $allVitals, $aLatestValues);
        }
        
        //Initialize the latest values array.
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
        
        $k = 0;
        for($i=0; $i<$numTaggedVitals; $i++)
        {
            // Check to see if any sets vitals were returned. If not, return
            if(!isset($serviceResponse->getVitalSignsResult->arrays->TaggedVitalSignSetArray->sets->VitalSignSetTO))
            {
                    return array($displayVitals, $allVitals, $aLatestValues);
            }
            
            // Check to see if the set of vitals is an object or an array
            $objType = gettype($serviceResponse->getVitalSignsResult->arrays->TaggedVitalSignSetArray->sets->VitalSignSetTO);
            //Finally get the set of vitals
            if ($objType == 'array')
            {
                $vitalsSetTO = $serviceResponse->getVitalSignsResult->arrays->TaggedVitalSignSetArray->sets->VitalSignSetTO[$i];
            } elseif ($objType == 'object') {
                $vitalsSetTO = $serviceResponse->getVitalSignsResult->arrays->TaggedVitalSignSetArray->sets->VitalSignSetTO;
            } else {
                return array($displayVitals, $allVitals, $aLatestValues);
            }

//error_log("LOOK DEBUG RAW VitalSignTO MDWS RESULT>>>".print_r($vitalsSetTO,TRUE));
            
            
            $numVitalsTO=count($vitalsSetTO->vitalSigns->VitalSignTO);
            if($numVitalsTO == 0)
            {
                return $displayVitals;
            }
            
            // Initialize vitals to all blanks so we can be sure to return a value for each column
            $displayVitals[$i]['Date Taken'] = " ";
            $displayVitals[$i]['Temp'] = " ";
            $displayVitals[$i]['Height'] = " ";
            $displayVitals[$i]['Weight'] = " ";
            $displayVitals[$i]['BMI'] = " ";
            $displayVitals[$i]['Blood Pressure'] = " ";
            $displayVitals[$i]['Pulse'] = " ";
            $displayVitals[$i]['Resp'] = " ";
            $displayVitals[$i]['Pain'] = " ";
            $displayVitals[$i]['C/G'] = " ";
            $displayVitals[$i]['Pox'] = " ";
            $displayVitals[$i]['CVP'] = " ";
            $displayVitals[$i]['Blood Glucose'] = " ";

            $defBPUnits = NULL;
            $defPulseUnits = NULL;
            $defPainUnits = NULL;
            $defPOXUnits = '%';
            $defTempUnits = NULL;
            $defWtUnits = NULL;
            $defHtUnits = NULL;
            $defaultUnits = explode(",", $vitalsSetTO->units);
            for($z=0; $z < count($defaultUnits); $z++)
            {
                $tempUnit = explode(":", $defaultUnits[$z]);
                if($tempUnit[0] == 'BP')
                {
                    if(trim($tempUnit[1]) > '')
                    {
                        $defBPUnits = $tempUnit[1];
                    }
                } else 
                if($tempUnit[0] == 'HT')
                {
                    if(trim($tempUnit[1]) > '')
                    {
                        $defHtUnits = $tempUnit[1];
                    }
                } else 
                if($tempUnit[0] == 'PULSE')
                {
                    if(trim($tempUnit[1]) > '')
                    {
                        $defPulseUnits = $tempUnit[1];
                    }
                } else 
                if($tempUnit[0] == 'PAIN')
                {
                    if(trim($tempUnit[1]) > '')
                    {
                        $defPainUnits = $tempUnit[1];
                    }
                } else 
                if($tempUnit[0] == 'POx')
                {
                    //Only assign if not blank.  20150527
                    if(trim($tempUnit[1]) > '')
                    {
                        $defPOXUnits = $tempUnit[1];
                    }
                } else 
                if($tempUnit[0] == 'TEMP')
                {
                    //Only assign if not blank.  20150527
                    if(trim($tempUnit[1]) > '')
                    {
                        $defTempUnits = $tempUnit[1];
                    }
                } else 
                if($tempUnit[0] == 'WT')
                {
                    //Only assign if not blank.  20150527
                    if(trim($tempUnit[1]) > '')
                    {
                        $defWtUnits = $tempUnit[1];
                    }
                }
            }
 
            //Now loop through all the records.
            for($j=0; $j<$numVitalsTO; $j++)
            {
                if($numVitalsTO == 1)
                {
                    $vital = $vitalsSetTO->vitalSigns->VitalSignTO;
                } else {
                    $vital = $vitalsSetTO->vitalSigns->VitalSignTO[$j];
                }
                
                $sDate = isset($vitalsSetTO->timestamp) ? date("m/d/Y h:i a", strtotime($vitalsSetTO->timestamp)) : " ";
                $rawTime = isset($vitalsSetTO->timestamp) ? $vitalsSetTO->timestamp : NULL;
                $name = isset($vital->type->name) ? $vital->type->name : "";
                $value = isset($vital->value1) ? $vital->value1 : "";
                $units = isset($vital->units) ? $vital->units : "";
                $allVitals[$k++] = array('date'=>$sDate, 'name'=>$name, 'value'=>$value, 'units'=>$units, 'rawTime'=>$rawTime);
                if(isset($vital->type->name))
                {
                    $displayVitals[$i]['Date Taken'] = $sDate;
                    if(strcasecmp('temperature', $vital->type->name) == 0)
                    {
                        $thiskey = 'Temp';
                        $displayVitals[$i][$thiskey] = $vital->value1." " 
                                . (trim($units) == '' ? $defTempUnits : $units);
                        if($rawTime !== NULL && ($aLatestValueDate[$thiskey]==NULL || $rawTime > $aLatestValueDate[$thiskey]))
                        {
                            $aLatestValueDate[$thiskey] = $rawTime;
                            $aLatestValues[$thiskey] = $vital->value1;
                        }
                    } elseif(strcasecmp('height', $vital->type->name) == 0)
                    {
                        $thiskey = 'Height';
                        $thisunits = trim($units) == '' ? $defHtUnits : $units;
                        if($thisunits == 'in')
                        {
                            $cms = round($vital->value1*2.54, 1);
                            $displayVitals[$i][$thiskey] = $vital->value1." ".$thisunits." (".$cms." cm)";
                        } else {
                            $displayVitals[$i][$thiskey] = $vital->value1." ".$thisunits;
                        }
                        if($rawTime !== NULL && ($aLatestValueDate[$thiskey]==NULL || $rawTime > $aLatestValueDate[$thiskey]))
                        {
                            $aLatestValueDate[$thiskey] = $rawTime;
                            $aLatestValues[$thiskey] = $vital->value1;
                        }
                    } elseif(strcasecmp('weight', $vital->type->name) == 0){
                        $thiskey = 'Weight';
                        $thisunits = trim($units) == '' ? $defWtUnits : $units;
                        if($thisunits == 'lb')
                        {
                            $kgs = round($vital->value1*0.45359237, 1);
                            $displayVitals[$i][$thiskey] = $vital->value1." ".$thisunits." (".$kgs." kg)";
                        } else {
                            $displayVitals[$i][$thiskey] = $vital->value1." ".$thisunits;
                        }
                        if($vital->value1 > '')
                        {
                            if($rawTime !== NULL && ($aLatestValueDate[$thiskey]==NULL || $rawTime > $aLatestValueDate[$thiskey]))
                            {
                                $aLatestValueDate[$thiskey] = $rawTime;
                                $aLatestValues[$thiskey] = $kgs;
                            }
                        }
                    } elseif(strcasecmp('body mass index', $vital->type->name) == 0) {
                        $thiskey = 'BMI';
                        $displayVitals[$i][$thiskey] = $vital->value1." ".$units;
//drupal_set_message("LOOK BMI RAW>>>".print_r($vital,TRUE));
                        if($rawTime !== NULL && ($aLatestValueDate[$thiskey]==NULL || $rawTime > $aLatestValueDate[$thiskey]))
                        {
                            $aLatestValueDate[$thiskey] = $rawTime;
                            $aLatestValues[$thiskey] = $vital->value1;
                        }
                    } elseif(strcasecmp('blood pressure', $vital->type->name) == 0) {
                        $thiskey = 'Blood Pressure';
                        $displayVitals[$i][$thiskey] = $vital->value1." "  
                                . (trim($units) == '' ? $defBPUnits : $units);
                        if($rawTime !== NULL && ($aLatestValueDate[$thiskey]==NULL || $rawTime > $aLatestValueDate[$thiskey]))
                        {
                            $aLatestValueDate[$thiskey] = $rawTime;
                            $aLatestValues[$thiskey] = $vital->value1;
                        }
                    } elseif(strcasecmp('pulse', $vital->type->name) == 0) {
                        $thiskey = 'Pulse';
                        $displayVitals[$i][$thiskey] = $vital->value1." " 
                                . (trim($units) == '' ? $defPulseUnits : $units);
                        if($rawTime !== NULL && ($aLatestValueDate[$thiskey]==NULL || $rawTime > $aLatestValueDate[$thiskey]))
                        {
                            $aLatestValueDate[$thiskey] = $rawTime;
                            $aLatestValues[$thiskey] = $vital->value1;
                        }
                    } elseif(strcasecmp('respiration', $vital->type->name) == 0){
                        $thiskey = 'Resp';
                        $displayVitals[$i][$thiskey] = $vital->value1." ".$units;
                        if($rawTime !== NULL && ($aLatestValueDate[$thiskey]==NULL || $rawTime > $aLatestValueDate[$thiskey]))
                        {
                            $aLatestValueDate[$thiskey] = $rawTime;
                            $aLatestValues[$thiskey] = $vital->value1;
                        }
                    } elseif(strcasecmp('pain', $vital->type->name) == 0){
                        $thiskey = 'Pain';
                        $displayVitals[$i][$thiskey] = $vital->value1." "
                                . (trim($units) == '' ? $defPainUnits : $units);
                        if($rawTime !== NULL && ($aLatestValueDate[$thiskey]==NULL || $rawTime > $aLatestValueDate[$thiskey]))
                        {
                            $aLatestValueDate[$thiskey] = $rawTime;
                            $aLatestValues[$thiskey] = $vital->value1;
                        }
                    } elseif(strcasecmp('c/g', $vital->type->name) == 0){
                        $thiskey = 'C/G';
                        $displayVitals[$i][$thiskey] = $vital->value1." ".$units;
                        if($rawTime !== NULL && ($aLatestValueDate[$thiskey]==NULL || $rawTime > $aLatestValueDate[$thiskey]))
                        {
                            $aLatestValueDate[$thiskey] = $rawTime;
                            $aLatestValues[$thiskey] = $vital->value1;
                        }
                    } elseif(strcasecmp('Pulse Oxymetry', $vital->type->name) == 0){
                        $thiskey = 'Pox';
                        $displayVitals[$i][$thiskey] = $vital->value1." " 
                                . (trim($units) == '' ? $defPOXUnits : $units);
                        if($rawTime !== NULL && ($aLatestValueDate[$thiskey]==NULL || $rawTime > $aLatestValueDate[$thiskey]))
                        {
                            $aLatestValueDate[$thiskey] = $rawTime;
                            $aLatestValues[$thiskey] = $vital->value1;
                        }
                    } elseif(strcasecmp('cvp', $vital->type->name) == 0){
                        $thiskey = 'CVP';
                        $displayVitals[$i][$thiskey] = $vital->value1." ".$units;
                        if($rawTime !== NULL && ($aLatestValueDate[$thiskey]==NULL || $rawTime > $aLatestValueDate[$thiskey]))
                        {
                            $aLatestValueDate[$thiskey] = $rawTime;
                            $aLatestValues[$thiskey] = $vital->value1;
                        }
                    } elseif(strcasecmp('blood glucose', $vital->type->name) == 0){
                        $thiskey = 'Blood Glucose';
                        $displayVitals[$i][$thiskey] = $vital->value1." ".$units;
                        if($rawTime !== NULL && ($aLatestValueDate[$thiskey]==NULL || $rawTime > $aLatestValueDate[$thiskey]))
                        {
                            $aLatestValueDate[$thiskey] = $rawTime;
                            $aLatestValues[$thiskey] = $vital->value1;
                        }
                    }
                }
            }
        }
        
        $aResult = array($displayVitals, $allVitals, $aLatestValues);
//error_log("LOOK DEBUG FINAL raw vitals MDWS bundle>>>".print_r($aResult,TRUE));
        
        $this->m_oRuntimeResultCache->addToCache($sThisResultName, $aResult);
        return $aResult;
    }

    /**
     * @return array of arrays, each with values for a measure on a date
     */
    function getVitalsDetail()
    {
        if(isset($this->getVitalsData()[0]))
        {
            $details = $this->getVitalsData();
            return $details[0];
        }
        return array(); //Return an empty array.
    }

    /**
     * @return array with only one value per measure for most recent date available
     */
    function getVitalsDetailOnlyLatest()
    {
        if(isset($this->getVitalsData()[2]))    //20140806
        {
            return $this->getVitalsData()[2];
        }
        return array(); //Return an empty array
    }
    
    /**
     * The labels are meant for display to to the user.  The values include units where units are appropriate.
     * @return type array of labels and their values
     */
    function getVitalsSummary()
    {
        $sThisResultName = 'getVitalsSummary';
        $aCachedResult = $this->m_oRuntimeResultCache->checkCache($sThisResultName);
        if($aCachedResult !== NULL)
        {
            //Found it in the cache!
            return $aCachedResult;
        }

        $result = array();
        $parsedVitals   = $this->getVitalsData();
        $displayVitals  = $parsedVitals[0];
        $allVitals      = $parsedVitals[1];

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
        foreach ($sortedVitals as $key => $row) {
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
        
        foreach($sortedVitals as $vital){
            if($nTemp >= $nToFind && $nHR >= $nToFind 
                    && $nBP >= $nToFind && $nHT >= $nToFind 
                    && $nWT >= $nToFind && $nBMI >= $nToFind)
            {
                break;
            }
            if(in_array(strtoupper($vital['name']), $tempLabels)){ // Temp
                if ($nTemp++ < $nToFind){
                   $vital['value'] .= isset($vital['units']) ? " ".$vital['units'] : "";
                   $vital['score'] = "1";
                   $result[] = $vital;
                }
            }
            elseif(in_array(strtoupper($vital['name']), $hrLabels)){ //HR)
                if ($nHR++ < $nToFind){
                   $vital['value'] .= isset($vital['units']) ? " ".$vital['units'] : "";
                   $vital['score'] = "2";
                   $result[] = $vital;
                }
            }
            elseif (in_array(strtoupper($vital['name']), $bpLabels)) { //BP
                if ($nBP++ < $nToFind){
                   $vital['value'] .= isset($vital['units']) ? " ".$vital['units'] : "";
                   $vital['score'] = "3";
                   $result[] = $vital;
                }
            }
            elseif (in_array(strtoupper($vital['name']), $htLabels)) { //HT
                if ($nHT++ < $nToFind){
                   $cms = round($vital['value']*2.54, 1);
                   $vital['value'] .= isset($vital['units']) ? " ".$vital['units']." (".$cms." cms)" : "";
                   $vital['score'] = "4";
                   $result[] = $vital;
                }
            }
            elseif (in_array(strtoupper($vital['name']), $wtLabels)) { //WT
                if ($nWT++ < $nToFind){
                   $kgs = round($vital['value']*0.45359237, 1);
                   $vital['value'] .= isset($vital['units']) ? " ".$vital['units']." (".$kgs." kgs)" : "";
                   $vital['score'] = "5";
                   $result[] = $vital;
                }
            }
            elseif (in_array(strtoupper($vital['name']), $bmiLabels)){ //BMI
                if ($nBMI++ < $nToFind){
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
        foreach ($sortedVitals as $key => $row) {
            $date[$key] = $row['date'];
            $name[$key]  = $row['name'];
            $value[$key] = $row['value'];
            $units[$key] = $row['units'];
            $rawTime[$key] = $row['rawTime'];
            $score[$key] = $row['score'];
        }
        array_multisort($score, SORT_ASC, $sortedVitals);
        $result = array("Temperature" => "", "Heart Rate" => ""
            , "Blood Pressure" => "", "Height" => ""
            , "Weight" => "", "Body Mass Index" => "");
        foreach ($sortedVitals as $vital) 
        {
            $result[$vital['name']] = array("Date of Measurement" => $vital['date']
                    , "Measurement Value" => $vital['value']);
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
        
        $this->m_oRuntimeResultCache->addToCache($sThisResultName, $result);
        return $result;
    }

    /**
     * The alergies detail
     * @return type array of arrays
     */
    function getAllergiesDetail()
    {
        $sThisResultName = 'getAllergiesDetail';
        $aCachedResult = $this->m_oRuntimeResultCache->checkCache($sThisResultName);
        if($aCachedResult !== null)
        {
            //Found it in the cache!
            return $aCachedResult;
        }
        
        //$serviceResponse = $this->m_oContext->getEMRService()->getAllergies();
        //$serviceResponse = $this->m_oContext->getMdwsClient()->makeQuery("getAllergies", NULL);
        $mdwsDao = $this->m_oContext->getEhrDao()->getImplementationInstance();
        $serviceResponse = $mdwsDao->makeQuery("getAllergies", NULL);
        $displayAllergies = array();
        
        if(!isset($serviceResponse->getAllergiesResult->arrays->TaggedAllergyArray->count))
                return $displayAllergies;
        
        $numTaggedAllergies = $serviceResponse->getAllergiesResult->arrays->TaggedAllergyArray->count;
        $rawlist = array();  //Build this to sort it later.
        if($numTaggedAllergies > 0)
        {
            for($i=0; $i<$numTaggedAllergies; $i++)
            {
                // Check to see if any allergies were returned. If not, return
                if(!isset($serviceResponse->getAllergiesResult->arrays->TaggedAllergyArray->allergies->AllergyTO))
                {
                    return $displayAllergies;
                }
                
                // Check to see if allergies is an object or an array
                $objType = gettype($serviceResponse->getAllergiesResult->arrays->TaggedAllergyArray->allergies->AllergyTO);
                //Finally get the allergies
                if ($objType == 'array')
                    $RptTO = $serviceResponse->getAllergiesResult->arrays->TaggedAllergyArray->allergies->AllergyTO[$i];
                elseif ($objType == 'object')
                    $RptTO = $serviceResponse->getAllergiesResult->arrays->TaggedAllergyArray->allergies->AllergyTO;
                else
                    return $displayAllergies;
                
                $tempRpt = array();
                
                $tempRpt['allergenId'] = isset($RptTO->allergenId) ? $RptTO->allergenId : " ";
                $tempRpt['allergenName'] = isset($RptTO->allergenName) ? $RptTO->allergenName : " ";
                $tempRpt['allergenType'] = isset($RptTO->allergenType) ? $RptTO->allergenType : " ";
                $tempRpt['reaction'] = isset($RptTO->reaction) ? $RptTO->reaction : " ";
                $tempRpt['severity'] = isset($RptTO->severity) ? $RptTO->severity : " ";
                $tempRpt['comment'] = isset($RptTO->comment) ? $RptTO->comment : " ";
                $tempRpt['timestamp'] = isset($RptTO->timestamp) ? date("m/d/Y", strtotime($RptTO->timestamp)) : " ";

                //Observer (AuthorTO)
                $tempRpt['observerId'] = isset($RptTO->observer->id) ? $RptTO->location->observer->id : " ";
                $tempRpt['observerName'] = isset($RptTO->observer->name) ? $RptTO->location->observer->name : " ";
                $tempRpt['observerSignature'] = isset($RptTO->observer->signature) ? $RptTO->location->observer->signature : " ";

                //Recorder (AuthorTO)
                $tempRpt['recorderId'] = isset($RptTO->recorder->id) ? $RptTO->location->recorder->id : " ";
                $tempRpt['recorderName'] = isset($RptTO->recorder->name) ? $RptTO->location->recorder->name : " ";
                $tempRpt['recorderSignature'] = isset($RptTO->recorder->signature) ? $RptTO->location->recorder->signature : " ";

                //Reactions (ArrayOfSymptomsTO)
                if(isset($RptTO->reactions)){
                    $objType = gettype($RptTO->reactions);
                    $nReactions = 0;
                    if ($objType == 'array')
                        $nReactions = count($RptTO->reactions);
                    elseif ($objType == 'object')
                        $nReactions = 1;
                    for($r=0; $r<$nReactions; $r++){
                        $reaction = $nReactions == 1 ? $RptTO->reactions : $RptTO->reactions[$r];
                        $reaction = $reaction->SymptomTO;
                        $tempRpt['symptoms'][$r] = isset($reaction->name) ? $reaction->name : " ";
                    }
                }
                else
                    $tempRpt['symptoms'] = array(" ");
                    
                if(isset($RptTO->drugIngredients)){
                    $objType = gettype($RptTO->drugIngredients);
                    $nIngredients = 0;
                    if ($objType == 'array')
                        $nIngredients = count($RptTO->drugIngredients);
                    elseif ($objType == 'object')
                        $nIngredients = 1;
                    for($g=0; $g<$nIngredients; $g++){
                        $ingredient = $nIngredients == 1 ? $RptTO->drugIngredients : $RptTO->drugIngredients[$g];
                        $tempRpt['ingredients'][$g] = isset($ingredient->text) ? $ingredient->text : " ";
                    }
                } else {
                    $tempRpt['ingredients'] = array(" ");
                }

                if(isset($RptTO->drugClasses)){
                    $objType = gettype($RptTO->drugClasses);
                    $nClasses = 0;
                    if ($objType == 'array')
                        $nClasses = count($RptTO->drugClasses);
                    elseif ($objType == 'object')
                        $nClasses = 1;
                    for($c=0; $g<$nClasses; $c++){
                        $class = $nClasses == 1 ? $RptTO->drugClasses : $RptTO->drugClasses[$c];
                        $tempRpt['classes'][$c] = isset($class->text) ? $class->text : " ";
                    }
                } else {
                    $tempRpt['classes'] = array(" ");
                }

                $dtobj = \DateTime::createFromFormat('m/d/Y', $tempRpt['timestamp']);
                if($dtobj != '') //Sometimes we get 'not assessed' with empty timestamp
                {
                    $timestamp = $dtobj->getTimestamp();
                    $sortkey = -1 * $timestamp;
                    if(!isset($rawlist[$sortkey]))
                    {
                        $rawlist[$sortkey] = array();
                    }
                    $rawlist[$sortkey][] = $tempRpt;
                }
            }
        }

        //Sort the result by date, most recent date first 20150623
        ksort($rawlist, SORT_NUMERIC);
        $displayAllergies = array();
        foreach($rawlist as $sortkey=>$onechunk)
        {
            foreach($onechunk as $tempRpt)
            {
                $displayAllergies[] 
                        = array('DateReported'=>$tempRpt['timestamp'], 
                                    'Item'=>$tempRpt['allergenName'], 
                                    'CausativeAgent'=>$tempRpt['allergenType'], 
                                    'SignsSymptoms'=>$this->getSnippetDetailPair($tempRpt['symptoms'],80), 
                                    'DrugClasses'=>$this->getSnippetDetailPair($tempRpt['classes'],80), 
                                    'Originator'=> $tempRpt['observerName'], 
                                    'ObservedHistorical'=>$this->getSnippetDetailPair($tempRpt['comment'])); 
            }
        }
        
        //Return the result
        $this->m_oRuntimeResultCache->addToCache($sThisResultName, $displayAllergies);
        return $displayAllergies;
    }
    
    private function getSnippetDetailPair($details
            , $snippetLen=RAPTOR_DEFAULT_SNIPPET_LEN
            , $emptyText=''
            , $useoffset=0
            )
    {
        $sSnippet = NULL;
        if(is_array($details))
        {
            //Assume first array entry has some text.
            $sDetails = trim($details[$useoffset]);
        } else {
            $sDetails = trim($details);
        }
        if(strlen($sDetails) > $snippetLen)
        {
            $snippet_same_as_detail = FALSE;
            $sSnippet = trim(substr($sDetails, 0, $snippetLen)).'...';  
        } else {
            $snippet_same_as_detail = TRUE;
            $sSnippet = $sDetails;
            if($sSnippet == '')
            {
                $sSnippet = $emptyText;
            }
        }
        return array('Snippet'=>$sSnippet
                , 'Details'=>$sDetails
                , 'SnippetSameAsDetail'=>$snippet_same_as_detail);
    }

    /*
     * Time Date, 
     * Default: The three most recent 
     *      serum creatinine values, 
     *      estimated glomerular filtration rate (eGFR
     * Procedures: most recent 
     *      platelets (PLT), 
     *      protime (PT), 
     *      INR (international normalized ratio), 
     *      prothrombin time (PTT), 
     *      hematocrit (HCT)
     */         
    function getProcedureLabsDetail()
    {
        $sThisResultName = 'getProcedureLabsDetail';
        $aCachedResult = $this->m_oRuntimeResultCache->checkCache($sThisResultName);
        if($aCachedResult !== null)
        {
            //Found it in the cache!
            return $aCachedResult;
        }
        
        $isProc = TRUE; //strpos($proc, "PROCEDURE") !== FALSE;
        $filteredLabs = array();
        $allLabs = $this->getDisplayLabs();       
        
        $foundCreatinine = FALSE;
        $foundPLT = FALSE;
        $foundPT = FALSE;
        $foundINR = FALSE;
        $foundPTT = FALSE;
        $foundHCT = FALSE;

        $nPLT = 0;
        $nPT = 0;
        $nINR = 0;
        $nPTT = 0;
        $nHCT = 0;
        
        // Obtain a list of columns
        $sortedLabs = $allLabs;
        foreach ($sortedLabs as $key => $row) 
        {
            $name[$key]  = $row['name'];
            $value[$key] = $row['value'];
            $rawTime[$key] = $row['rawTime'];
        }
        
        //Only continue if not empty
        if(!empty($name))
        {
            array_multisort($name, SORT_ASC, $rawTime, SORT_DESC, $sortedLabs);

            foreach($sortedLabs as $lab)
            {
                $foundCreatinine = strpos('CREATININE', strtoupper($lab['name'])) !== FALSE;
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

                $rawValue = $lab['value'];
                $value = $alert ? "<span class='medical-value-danger'>** ".$lab['value']." ".$lab['units']." **</span>" : $lab['value']." ".$lab['units'];

                if ($isProc)
                {
                    if($foundHCT)
                    {
                        if ($nHCT++ < 1)
                            $filteredLabs[] = array($lab['date'], $value, " ", " ", " ", " ", $lab['refRange']);
                    }
                    elseif($foundINR){
                        if ($nINR++ < 1)
                            $filteredLabs[] = array($lab['date'], " ", $value, " ", " ", " ", $lab['refRange']);
                    }
                    elseif($foundPT){
                        if ($nPT++ < 1)
                            $filteredLabs[] = array($lab['date'], " ", " ", $value, " ", " ", $lab['refRange']);
                    }
                    elseif($foundPLT){
                        if ($nPLT++ < 1)
                            $filteredLabs[] = array($lab['date'], " ", " ", " ", $value, " ", $lab['refRange']);
                    }
                    elseif($foundPTT){
                        if ($nPTT++ < 1)
                            $filteredLabs[] = array($lab['date'], " ", " ", " ", " ", $value, $lab['refRange']);
                    }
                }
            }
        }

        $this->m_oRuntimeResultCache->addToCache($sThisResultName, $filteredLabs);
        return $filteredLabs;
    }

    /**
     * Display labs array
     */
    public function getDisplayLabs()
    {
        $sThisResultName = $this->m_oContext->getSelectedTrackingID() . '_getDisplayLabs'; //patient specific
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
    function getLabsDetailData()
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
    
    /**
     * @return array of several eGFR metrics
     */
    function getEGFRDetail()
    {
        return $this->getLabsDetailData()[1];
    }
    
    /**
     * The diagnostic labs detail
     * @return type array of arrays
     */
    function getDiagnosticLabsDetail()
    {
        return $this->getLabsDetailData()[0];
    }
    
    /**
     * The pathology report details
     * @return type array of arrays
     */
    function getPathologyReportsDetail($max_reports=1000)
    {
        try
        {
            $mdwsDao = $this->m_oContext->getEhrDao()->getImplementationInstance();
            //$serviceResponse = $this->m_oContext->getMdwsClient()->makeQuery("getSurgicalPathologyReports"
            //        , array('fromDate'=>'0', 'toDate'=>'0', 'nrpts'=>$max_reports));
            $serviceResponse = $mdwsDao->makeQuery("getSurgicalPathologyReports"
                    , array('fromDate'=>'0', 'toDate'=>'0', 'nrpts'=>$max_reports));
            $result = array();
            if(!isset($serviceResponse->getSurgicalPathologyReportsResult
                    ->arrays->TaggedSurgicalPathologyRptArray->count)) return $result;

            $numTaggedRpts = $serviceResponse->getSurgicalPathologyReportsResult
                    ->arrays->TaggedSurgicalPathologyRptArray->count;
            if($numTaggedRpts > 0){
                for($i=0; $i<$numTaggedRpts; $i++){
                    // Check to see if any Rpts were returned. If not, return
                    if(!isset($serviceResponse->getSurgicalPathologyReportsResult
                            ->arrays->TaggedSurgicalPathologyRptArray->rpts)) return $result;

                    // Check to see if it is an object or an array
                    $objType = gettype($serviceResponse->getSurgicalPathologyReportsResult
                            ->arrays->TaggedSurgicalPathologyRptArray->rpts->SurgicalPathologyRpt);
                    //Finally get it
                    if ($objType == 'array')
                        $RptTO = $serviceResponse->getSurgicalPathologyReportsResult
                            ->arrays->TaggedSurgicalPathologyRptArray->rpts->SurgicalPathologyRpt[$i];
                    elseif ($objType == 'object')
                        $RptTO = $serviceResponse->getSurgicalPathologyReportsResult
                            ->arrays->TaggedSurgicalPathologyRptArray->rpts->SurgicalPathologyRpt;
                    else
                        return $result;

                    $tempRpt = array(); 
                    $tempRpt['id'] = isset($RptTO->id) ? $RptTO->id : " ";
                    $tempRpt['title'] = isset($RptTO->title) ? $RptTO->title : " ";
                    $tempRpt['timestamp'] = isset($RptTO->timestamp) ? date("m/d/Y h:i a", strtotime($RptTO->timestamp)) : " ";

                    $tempRpt['authorID'] = isset($RptTO->author->authorID) ? $RptTO->author->authorID : " ";
                    $tempRpt['authorName'] = isset($RptTO->author->authorName) ? $RptTO->author->authorName : " ";
                    $tempRpt['authorSignature'] = isset($RptTO->author->authorSignature) ? $RptTO->author->authorSignature : " ";

                    $tempRpt['facilityTag'] = isset($RptTO->facility->facilityTag) ? $RptTO->facility->facilityTag : " ";
                    $tempRpt['facilityText'] = isset($RptTO->facility->facilityText) ? $RptTO->facility->facilityText : " ";
                    $tempRpt['facilityTextArray'] = isset($RptTO->facility->facilityTextArray) ? implode($RptTO->facility->facilityTextArray) : " ";
                    $tempRpt['facilityTagResults'] = isset($RptTO->facility->facilityTagResults) ? $RptTO->facility->facilityTagResults : " ";

                    $tempRpt['specimenID'] = isset($RptTO->specimen->id) ? $RptTO->specimen->id : " ";
                    $tempRpt['specimenName'] = isset($RptTO->specimen->name) ? $RptTO->specimen->name : " ";
                    $sDateThing = (string) (isset($RptTO->specimen->collectionDate) && $RptTO->specimen->collectionDate > '') ? print_r($RptTO->specimen->collectionDate,TRUE) : 'No Date';;
                    if(trim($sDateThing) == '')
                    {
                        $sDateThing = 'Date Error';
                    }
                    $tempRpt['specimenCollectionDate'] = $sDateThing;
                    $tempRpt['specimenAccessionNum'] = isset($RptTO->specimen->accessionNum) ? $RptTO->specimen->accessionNum : " ";
                    $tempRpt['specimenSite'] = isset($RptTO->specimen->site) ? $RptTO->specimen->site : " ";

                    $tempRpt['specimenFacilityText'] = isset($RptTO->specimen->facility->facilityText) ? $RptTO->facility->facilityText : " ";
                    $tempRpt['specimenFacilityTextArray'] = isset($RptTO->specimen->facility->facilityTextArray) ? implode($RptTO->facility->facilityTextArray) : " ";
                    $tempRpt['specimenFacilityTagResults'] = isset($RptTO->specimen->facility->facilityTagResults) ? $RptTO->facility->facilityTagResults : " ";
                    $tempRpt['specimenFacilityTag'] = isset($RptTO->specimen->facility->facilityTag) ? $RptTO->facility->facilityTag : " ";

                    $tempRpt['clinicalHx'] = isset($RptTO->clinicalHx) ? $RptTO->clinicalHx : "";
                    $tempRpt['clinicalHx'] = nl2br($tempRpt['clinicalHx']);
                    $tempRpt['description'] = isset($RptTO->description) ? $RptTO->description : " ";
                    $tempRpt['exam'] = isset($RptTO->exam) ? $RptTO->exam : " ";
                    $tempRpt['exam'] = nl2br($tempRpt['exam']);
                    $tempRpt['diagnosis'] = isset($RptTO->diagnosis) ? $RptTO->diagnosis : " ";
                    $tempRpt['diagnosis'] = nl2br($tempRpt['diagnosis']);
                    $tempRpt['comment'] = isset($RptTO->comment) ? $RptTO->comment : " ";
                    $tempRpt['comment'] = nl2br($tempRpt['comment']);

                    $aTemp = $this->getSnippetDetailPair($tempRpt['specimenName']);
                    $result[] = array("Title"=>$tempRpt['title']
                            , 'ReportDate' => $tempRpt['specimenCollectionDate']
                            , 'Snippet' => $aTemp['Snippet']
                            , 'Details' => $aTemp['Details']
                            , 'Accession' => $tempRpt['specimenAccessionNum']
                            , 'Exam'=>$tempRpt['exam'], 'Facility'=>$tempRpt['facilityTag']);
                }
            }
            return $result;
        } catch (\Exception $ex) {
            error_log("Failed getPathologyReportsDetail($max_reports) because ".$ex->getMessage());
            throw $ex;
        }
    }

    /**
     * The surgery detail
     * @return type array of arrays
     */
    function getSurgeryReportsDetail()
    {        
        try
        {
            //$serviceResponse = $this->m_oContext->getEMRService()->getSurgeryReportsWithText();
            $mdwsDao = $this->m_oContext->getEhrDao()->getImplementationInstance();
            $serviceResponse = $mdwsDao->makeQuery("getSurgeryReportsWithText", NULL);
            //$serviceResponse = $this->m_oContext->getMdwsClient()->makeQuery("getSurgeryReportsWithText", NULL);

            $result = array();
            $numRpts = 0;
            if(!isset($serviceResponse->getSurgeryReportsWithTextResult->arrays->TaggedSurgeryReportArray->count)) return $result;
            $numTaggedRpts = $serviceResponse->getSurgeryReportsWithTextResult->arrays->TaggedSurgeryReportArray->count;
            if($numTaggedRpts > 0){
                for($i=0; $i<$numTaggedRpts; $i++){
                    // Check to see if any Rpts were returned. If not, return
                    if(!isset($serviceResponse->getSurgeryReportsWithTextResult->arrays->TaggedSurgeryReportArray->rpts)) return $result;

                    // Check to see if it is an object or an array
                    $objType = gettype($serviceResponse->getSurgeryReportsWithTextResult->arrays->TaggedSurgeryReportArray->rpts->SurgeryReportTO);
                    //Finally get it
                    if ($objType == 'array')
                        $RptTO = $serviceResponse->getSurgeryReportsWithTextResult->arrays->TaggedSurgeryReportArray->rpts->SurgeryReportTO[$i];
                    elseif ($objType == 'object')
                        $RptTO = $serviceResponse->getSurgeryReportsWithTextResult->arrays->TaggedSurgeryReportArray->rpts->SurgeryReportTO;
                    else
                        return false;

                    $tempRpt = array(); 
                    $tempRpt['id'] = isset($RptTO->id) ? $RptTO->id : "Untitled";
                    $tempRpt['title'] = isset($RptTO->title) ? $RptTO->title : " ";
                    $tempRpt['timestamp'] = isset($RptTO->timestamp) ? date("m/d/Y h:i a", strtotime($RptTO->timestamp)) : " ";

                    $tempRpt['authorID'] = isset($RptTO->author->authorID) ? $RptTO->author->authorID : " ";
                    $tempRpt['authorName'] = isset($RptTO->author->authorName) ? $RptTO->author->authorName : " ";
                    $tempRpt['authorSignature'] = isset($RptTO->author->authorSignature) ? $RptTO->author->authorSignature : " ";

                    $tempRpt['text'] = isset($RptTO->text) ? $RptTO->text : "No Details Available";
                    $tempRpt['text'] = nl2br($tempRpt['text']);

                    $tempRpt['facilityTag'] = isset($RptTO->facility->tag) ? $RptTO->facility->tag : " ";
                    $tempRpt['facilityText'] = isset($RptTO->facility->text) ? $RptTO->facility->text : " ";
                    $tempRpt['facilityTextArray'] = isset($RptTO->facility->textArray) ? $RptTO->facility->textArray : array(" ");
                    $tempRpt['facilityTagResults'] = isset($RptTO->facility->tagResults) ? $RptTO->facility->tagResults : " ";

                    $tempRpt['status'] = isset($RptTO->status) ? $RptTO->status : " ";

                    $tempRpt['specialtyTag'] = isset($RptTO->specialty->tag) ? $RptTO->specialty->tag : " ";
                    $tempRpt['specialtyText'] = isset($RptTO->specialty->text) ? $RptTO->specialty->text : " ";
                    $tempRpt['specialtyTextArray'] = isset($RptTO->specialty->textArray) ? $RptTO->specialty->textArray : array(" ");
                    $tempRpt['specialtyTagResults'] = isset($RptTO->specialty->tagResults) ? $RptTO->specialty->tagResults : " ";

                    $tempRpt['preOpDx'] = isset($RptTO->preOpDx) ? $RptTO->preOpDx : " ";
                    $tempRpt['postOpDx'] = isset($RptTO->postOpDx) ? $RptTO->postOpDx : " ";
                    $tempRpt['labWork'] = isset($RptTO->labWork) ? $RptTO->labWork : " ";
                    $tempRpt['dictationTimestamp'] = isset($RptTO->dictationTimestamp) ? date("m/d/Y h:i a", strtotime($RptTO->dictationTimestamp)) : " ";
                    $tempRpt['transcriptionTimestamp'] = isset($RptTO->transcriptionTimestamp) ? date("m/d/Y h:i a", strtotime($RptTO->transcriptionTimestamp)) : " ";

                    $fulltext = trim($tempRpt['text']);
                    if(strlen($fulltext) > RAPTOR_DEFAULT_SNIPPET_LEN)
                    {
                        $snippetText = substr($fulltext, 0, RAPTOR_DEFAULT_SNIPPET_LEN).'...';
                    } else {
                        $snippetText = $fulltext;
                    }
                    $result[] = array("Title"=>$tempRpt['title']
                            , "ReportDate"=>$tempRpt['timestamp']
                            , 'Snippet' => $snippetText 
                            , 'Details' => $fulltext
                            );
                }
            }
            return $result;
        } catch (\Exception $ex) {
            error_log("Failed getSurgeryReportsDetail because ".$ex->getMessage());
            throw $ex;
        }
    }
   
    /**
     * The problems detail
     * @return type array of arrays
     */
    function getProblemsListDetail()
    {
        try
        {
            //$serviceResponse = $this->m_oContext->getEMRService()->getProblemList(array('type'=>'active'));
            $mdwsDao = $this->m_oContext->getEhrDao()->getImplementationInstance();
            $serviceResponse = $mdwsDao->makeQuery("getProblemList", array('type'=>'active'));
            //$serviceResponse = $this->m_oContext->getMdwsClient()->makeQuery("getProblemList", array('type'=>'active'));
            $result = array();       
            $numNotes = 0;
            if(!isset($serviceResponse->getProblemListResult->arrays->TaggedProblemArray->count)) return $result;
            $numTaggedNotes = $serviceResponse->getProblemListResult->arrays->TaggedProblemArray->count;
            if($numTaggedNotes > 0){
                for($i=0; $i<$numTaggedNotes; $i++){
                    // Check to see if any notes were returned. If not, return
                    if(!isset($serviceResponse->getProblemListResult->arrays->TaggedProblemArray->problems)) return $result;

                    // Check to see if it is an object or an array
                    $objType = gettype($serviceResponse->getProblemListResult->arrays->TaggedProblemArray->problems->ProblemTO);
                    //Finally get it
                    if ($objType == 'array')
                        $RptTO = $serviceResponse->getProblemListResult->arrays->TaggedProblemArray->problems->ProblemTO[$i];
                    elseif ($objType == 'object')
                        $RptTO = $serviceResponse->getProblemListResult->arrays->TaggedProblemArray->problems->ProblemTO;
                    else
                        return $result;

                    $tempRpt = array(); 
    //                $guid = com_create_guid();
    //                $tempRpt['guid'] = $guid;
                    $tempRpt['id'] = isset($RptTO->id) ? $RptTO->id : " ";
                    $tempRpt['status'] = isset($RptTO->status) ? $RptTO->status : " ";
                    $tempRpt['providerNarrative'] = isset($RptTO->providerNarrative) ? nl2br($RptTO->providerNarrative) : " ";
                    $tempRpt['onsetDate'] = isset($RptTO->onsetDate) ? date("m/d/Y h:i a", strtotime($RptTO->onsetDate)) : " ";
                    $tempRpt['modifiedDate'] = isset($RptTO->modifiedDate) ? date("m/d/Y h:i a", strtotime($RptTO->modifiedDate)) : " ";
                    $tempRpt['exposures'] = isset($RptTO->exposures) ? $RptTO->exposures : " ";
                    $tempRpt['noteNarrative'] = isset($RptTO->noteNarrative) ? nl2br($RptTO->noteNarrative) : " ";

                    $tempRpt['observerID'] = isset($RptTO->observer->id) ? $RptTO->observer->id : " ";
                    $tempRpt['observerName'] = isset($RptTO->observer->name) ? $RptTO->observer->name : " ";
                    $tempRpt['observerSignature'] = isset($RptTO->observer->signature) ? $RptTO->observer->signature : " ";

                    $tempRpt['facilityTag'] = isset($RptTO->facility->tag) ? $RptTO->facility->tag : " ";
                    $tempRpt['facilityText'] = isset($RptTO->facility->text) ? $RptTO->facility->text : " ";

                    $tempRpt['typeId'] = isset($RptTO->type->id) ? $RptTO->type->id : " ";
                    $tempRpt['typeCat'] = isset($RptTO->type->category) ? $RptTO->type->category : " ";
                    $tempRpt['typeName'] = isset($RptTO->type->name) ? $RptTO->type->name : " ";
                    $tempRpt['typeShortName'] = isset($RptTO->type->shortName) ? $RptTO->type->shortName : " ";
                    $tempRpt['typeDataId'] = isset($RptTO->type->dataId) ? $RptTO->type->dataId : " ";
                    $tempRpt['typeDataName'] = isset($RptTO->type->dataName) ? $RptTO->type->dataName : " ";
                    $tempRpt['typeDataType'] = isset($RptTO->type->dataType) ? $RptTO->type->dataType : " ";

                    $tempRpt['comment'] = isset($RptTO->comment) ? nl2br($RptTO->comment) : " ";
                    $tempRpt['organizationalProperties'] = isset($RptTO->organizationalProperties) ? nl2br($RptTO->organizationalProperties) : " ";

                    $providerNarrative = trim($tempRpt['providerNarrative']);
                    if(strlen($providerNarrative) > RAPTOR_DEFAULT_SNIPPET_LEN)
                    {
                        $snippetText = substr($providerNarrative, 0, RAPTOR_DEFAULT_SNIPPET_LEN).'...';
                    } else {
                        $snippetText = $providerNarrative;
                    }
                    $result[] = array(  
                        "Title"=>$snippetText, 
                        "OnsetDate"=>$tempRpt['onsetDate'], 
                        "Snippet" => $snippetText,
                        "Details" => array('Type of Note'=>$tempRpt['typeName'], 
                                        'Provider Narrative'=>$providerNarrative, 
                                        'Note Narrative'=>$tempRpt['noteNarrative'], 
                                        'Status'=>$tempRpt['status'], 
                                        'Observer'=>$tempRpt['observerName'], 
                                        'Comment'=>$tempRpt['comment'], 
                                        'Facility'=>$tempRpt['facilityTag']));

                }
            }
            return $result;
        } catch (\Exception $ex) {
            error_log("Failed getProblemsListDetail because ".$ex->getMessage());
            throw $ex;
        }
    }

    /**
     * The notes detail
     * @return type array of arrays
     */
    function getNotesDetail($usenl2br=FALSE)
    {
        try
        {
            $mdwsDao = $this->m_oContext->getEhrDao()->getImplementationInstance();
            $serviceResponse = $mdwsDao->makeQuery("getNotesWithText", array('fromDate'=>'0', 'toDate'=>'0', 'nNotes'=>0));
            //$serviceResponse = $this->m_oContext->getMdwsClient()->makeQuery("getNotesWithText", array('fromDate'=>'0', 'toDate'=>'0', 'nNotes'=>0));
            $result = array();
            if(!isset($serviceResponse->getNotesWithTextResult->arrays->TaggedNoteArray->count)) return $result;
            $numTaggedNotes = $serviceResponse->getNotesWithTextResult->arrays->TaggedNoteArray->count;
            if($numTaggedNotes > 0){
                for($i=0; $i<$numTaggedNotes; $i++){
                    // Check to see if any notes were returned. If not, return
                    if(!isset($serviceResponse->getNotesWithTextResult->arrays->TaggedNoteArray->notes)) return $result;

                    // Check to see if it is an object or an array
                    $objType = gettype($serviceResponse->getNotesWithTextResult->arrays->TaggedNoteArray->notes->NoteTO);
                    //Finally get it
                    if ($objType == 'array')
                        $RptTO = $serviceResponse->getNotesWithTextResult->arrays->TaggedNoteArray->notes->NoteTO[$i];
                    elseif ($objType == 'object')
                        $RptTO = $serviceResponse->getNotesWithTextResult->arrays->TaggedNoteArray->notes->NoteTO;
                    else
                         return $result;

                    $tempRpt = array(); 
                    $tempRpt['id'] = isset($RptTO->id) ? $RptTO->id : " ";
                    $tempRpt['timestamp'] = isset($RptTO->timestamp) ? date("m/d/Y h:i a", strtotime($RptTO->timestamp)) : " ";
                    $tempRpt['admitTimestamp'] = isset($RptTO->admitTimestamp) ? date("m/d/Y h:i a", strtotime($RptTO->admitTimestamp)) : " ";
                    $tempRpt['dischargeTimestamp'] = isset($RptTO->dischargeTimestamp) ? date("m/d/Y h:i a", strtotime($RptTO->dischargeTimestamp)) : " ";
                    $tempRpt['serviceCategory'] = isset($RptTO->serviceCategory) ? $RptTO->serviceCategory : " ";
                    $tempRpt['localTitle'] = isset($RptTO->localTitle) ? $RptTO->localTitle : " ";
                    $tempRpt['standardTitle'] = isset($RptTO->standardTitle) ? $RptTO->standardTitle : " ";

                    $tempRpt['authorID'] = isset($RptTO->author->authorID) ? $RptTO->author->authorID : " ";
                    $tempRpt['authorName'] = isset($RptTO->author->authorName) ? $RptTO->author->authorName : " ";
                    $tempRpt['authorSignature'] = isset($RptTO->author->authorSignature) ? $RptTO->author->authorSignature : " ";

                    $tempRpt['location'] = isset($RptTO->location) ? $RptTO->location : " ";
                    $tempRpt['facility'] = isset($RptTO->location->name) ? $RptTO->location->name : " ";
                    $tempRpt['text'] = isset($RptTO->text) ? $RptTO->text : "No Details Available";
                    if($usenl2br)
                    {
                        $tempRpt['text'] = nl2br($tempRpt['text']);
                    }

                    $tempRpt['hasAddendum'] = isset($RptTO->hasAddendum) ? $RptTO->hasAddendum : " ";
                    $tempRpt['isAddendum'] = isset($RptTO->isAddendum) ? $RptTO->isAddendum : " ";
                    $tempRpt['originalNoteID'] = isset($RptTO->originalNoteID) ? $RptTO->originalNoteID : " ";
                    $tempRpt['hasImages'] = isset($RptTO->hasImages) ? $RptTO->hasImages : " ";
                    $tempRpt['itemId'] = isset($RptTO->itemId) ? $RptTO->itemId : " ";
                    $tempRpt['approvedBy'] = isset($RptTO->approvedBy) ? $RptTO->approvedBy : " ";
                    $tempRpt['status'] = isset($RptTO->status) ? $RptTO->status : " ";

                    $localTitle = trim($tempRpt['localTitle']);
                    if(strlen($localTitle) > RAPTOR_DEFAULT_SNIPPET_LEN)
                    {
                        $snippetText = substr($localTitle, 0, RAPTOR_DEFAULT_SNIPPET_LEN).'...';
                    } else {
                        $snippetText = $localTitle;
                    }
                    $result[] = array(
                                        "Type"=>$localTitle, 
                                        "Date"=>$tempRpt['timestamp'],
                                        "Snippet" => $snippetText,
                                        "Details" => array('Type of Note'=>$localTitle, 
                                                        'Author'=>$tempRpt['authorName'], 
                                                        'Note Text'=>$tempRpt['text'], 
                                                        'Facility'=>$tempRpt['facility']));
                }
            }
            return $result;
        } catch (\Exception $ex) {
            error_log("Failed getNotesDetail($usenl2br) because ".$ex->getMessage());
            throw $ex;
        }
    }

    /**
     * The report detail
     * @return type array of arrays
     */
    function getRadiologyReportsDetail()
    {
        try
        {
            $mdwsDao = $this->m_oContext->getEhrDao()->getImplementationInstance();
            $serviceResponse = $mdwsDao->makeQuery("getRadiologyReports", array('fromDate'=>'0', 'toDate'=>'0', 'nrpts'=>0));
            //$serviceResponse = $this->m_oContext->getMdwsClient()->makeQuery("getRadiologyReports", array('fromDate'=>'0', 'toDate'=>'0', 'nrpts'=>0));

            $result = array();
            $numRadRpts = 0;
            if(!isset($serviceResponse->getRadiologyReportsResult->arrays->TaggedRadiologyReportArray->count))
                    return false;
            $numTaggedRpts = $serviceResponse->getRadiologyReportsResult->arrays->TaggedRadiologyReportArray->count;
            if($numTaggedRpts > 0){
                for($i=0; $i<$numTaggedRpts; $i++){
                    // Check to see if any Rpts were returned. If not, return
                    if(!isset($serviceResponse->getRadiologyReportsResult->arrays->TaggedRadiologyReportArray->rpts))
                            return false;

                    // Check to see if it is an object or an array
                    $objType = gettype($serviceResponse->getRadiologyReportsResult->arrays->TaggedRadiologyReportArray->rpts->RadiologyReportTO);
                    //Finally get it
                    if ($objType == 'array')
                        $RptTO = $serviceResponse->getRadiologyReportsResult->arrays->TaggedRadiologyReportArray->rpts->RadiologyReportTO[$i];
                    elseif ($objType == 'object')
                        $RptTO = $serviceResponse->getRadiologyReportsResult->arrays->TaggedRadiologyReportArray->rpts->RadiologyReportTO;
                    else
                        return false;

                    $tempRpt = array(); 
                    $tempRpt['accessionNumber'] = isset($RptTO->accessionNumber) ? $RptTO->accessionNumber : " ";
                    $tempRpt['caseNumber'] = isset($RptTO->caseNumber) ? $RptTO->caseNumber : " ";
                    $tempRpt['id'] = isset($RptTO->id) ? $RptTO->id : " ";
                    $tempRpt['title'] = isset($RptTO->title) ? $RptTO->title : " ";
                    $tempRpt['timestamp'] = isset($RptTO->timestamp) ? date("m/d/Y h:i a", strtotime($RptTO->timestamp)) : " ";

                    $tempRpt['authorID'] = isset($RptTO->author->authorID) ? $RptTO->author->authorID : " ";
                    $tempRpt['authorName'] = isset($RptTO->author->authorName) ? $RptTO->author->authorName : "Unknown";
                    $tempRpt['authorSignature'] = isset($RptTO->author->authorSignature) ? $RptTO->author->authorSignature : " ";

                    $tempRpt['text'] = isset($RptTO->text) ? $RptTO->text : "No Details Available";
                    $tempRpt['text'] = nl2br($tempRpt['text']);

                    $tempRpt['facilityTag'] = isset($RptTO->facility->facilityTag) ? $RptTO->facility->facilityTag : " ";
                    $tempRpt['facilityText'] = isset($RptTO->facility->facilityText) ? $RptTO->facility->facilityText : " ";
                    $tempRpt['facilityTextArray'] = isset($RptTO->facility->facilityTextArray) ? $RptTO->facility->facilityTextArray : array(" ");
                    $tempRpt['facilityTagResults'] = isset($RptTO->facility->facilityTagResults) ? $RptTO->facility->facilityTagResults : " ";

                    $tempRpt['status'] = isset($RptTO->status) ? $RptTO->status : " ";
                    $tempRpt['cptCode'] = isset($RptTO->cptCode) ? $RptTO->cptCode : " ";
                    $tempRpt['clinicalHx'] = isset($RptTO->clinicalHx) ? $RptTO->clinicalHx : "";
                    $tempRpt['clinicalHx'] = nl2br($tempRpt['clinicalHx']);
                    $tempRpt['impression'] = isset($RptTO->impression) ? $RptTO->impression : " ";
                    $tempRpt['impression'] = nl2br($tempRpt['impression']);

                    $result[] = 
                            array(//"guid" => $tempRpt['guid'], 
                                "Title"  => $tempRpt['title'],
                                "ReportedDate" => $tempRpt['timestamp'],
                                "Snippet" => substr($tempRpt['title'], 0, RAPTOR_DEFAULT_SNIPPET_LEN).'...',
                                "Details" => array(
                                    "Procedure Name" => $tempRpt['title'],
                                    "Report Status"  => $tempRpt['status'],
                                    "CPT Code"       => $tempRpt['cptCode'],
                                    "Reason For Study" => " ",
                                    "Clinical HX"    => $tempRpt['clinicalHx'],
                                    "Impression"     => $tempRpt['impression'],
                                    "Report"         => $tempRpt['text'],
                                    "Facility"       => $tempRpt['facilityTag'],
                                        ),
                                "AccessionNumber" => $tempRpt['accessionNumber'],
                                "CaseNumber" => $tempRpt['caseNumber'],
                                "ReportID" =>$tempRpt['id'],
                            );
                }
            }
            return $result;
        } catch (\Exception $ex) {
            error_log("Failed getRadiologyReportsDetail because ".$ex->getMessage());
            throw $ex;
        }
    }
}
