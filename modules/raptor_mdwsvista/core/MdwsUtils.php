<?php
/**
 * @file
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase 2
 * Open Source VA Innovation Project 2011-2015
 * VA Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, et al
 * MDWS Integration and VISTA collaboration: Joel Mewton
 * Contacts: acasertano@sanbusinessconsultants.com, ffont@sanbusinessconsultants.com
 * ------------------------------------------------------------------------------------
 * 
 */ 

namespace raptor;

require_once 'MdwsUserUtils.php';

class MdwsUtils {
    
    public static function getVariableValue($mdwsDao, $arg) {
        $soapResult = $mdwsDao->makeQuery('getVariableValue', array('arg'=>$arg));
        
        if (isset($soapResult->getVariableValueResult->fault)) {
            throw new \Exception('Error calling GVV: '.$soapResult->getVariableValueResult->fault->message);
        }
        
        return $soapResult->getVariableValueResult->text;
    }
    
    /**
     * Turn the DDR GETS ENTRY results in to an array/dictionary by field #
     */
    public static function parseDdrGetsEntry($soapResult) {
        if (!isset($soapResult) || !isset($soapResult->ddrGetsEntryResult)
                || isset($soapResult->ddrGetsEntryResult->fault)) {
            throw new \Exception("Invalid DDR GETS ENTRY result=".print_r($soapResult,TRUE));
        }
        $resultsDict = array();
        $lines = $soapResult->ddrGetsEntryResult->text->string;
        for ($i = 0; $i < count($lines); $i++) {
            $pieces = explode("^", $lines[$i]);
            if (count($pieces) < 4) {
                continue;
            }
            $fieldNo = $pieces[2];
            $fieldValInternal = $pieces[3];
            $fieldValExternal = '';
            if (count($pieces) > 4) {
                $fieldValExternal = $pieces[4];
            }
            if ($fieldValInternal === "[WORD PROCESSING]") {
                $wpLines = ""; // create so can reference in while loop
                // word processing field - append following lines until reach "$$END$$
                while ($lines[$i+1] != "\$\$END\$\$") {
                    $wpLines = ($wpLines.$lines[++$i]."\n");
                }
                $resultsDict[$fieldNo] = $wpLines;
                continue;
            }
            // use external if value is available and not empty and not the same as internal, then
            if (isset($fieldValExternal) && $fieldValExternal != '' && $fieldValExternal != $fieldValInternal) {
                $resultsDict[$fieldNo] = $fieldValExternal;
            } else {
                $resultsDict[$fieldNo] = $fieldValInternal;
            }
        }
        return $resultsDict;
    }
    
    /**
     * Turn the DDR GETS ENTRY results in to an array/dictionary by field #
     */
    public static function parseDdrGetsEntryInternalAndExternal($soapResult) {
        if (!isset($soapResult) || !isset($soapResult->ddrGetsEntryResult)
                || isset($soapResult->ddrGetsEntryResult->fault)) {
            throw new \Exception("Invalid DDR GETS ENTRY result=".print_r($soapResult,TRUE));
        }
        $resultsDict = array();
        $lines = $soapResult->ddrGetsEntryResult->text->string;
        for ($i = 0; $i < count($lines); $i++) {
            $pieces = explode("^", $lines[$i]);
            if (count($pieces) < 4) {
                continue;
            }
            $fieldNo = $pieces[2];
            $fieldValInternal = $pieces[3];
            $fieldValExternal = '';
            if (count($pieces) > 4) {
                $fieldValExternal = $pieces[4];
            }
            if ($fieldValInternal === "[WORD PROCESSING]") {
                $wpLines = ""; // create so can reference in while loop
                // word processing field - append following lines until reach "$$END$$
                while ($lines[$i+1] != "\$\$END\$\$") {
                    $wpLines = ($wpLines.$lines[++$i]."\n");
                }
                $resultsDict[$fieldNo] = $wpLines;
                continue;
            }
            $resultsDict[$fieldNo] = array('I'=>$fieldValInternal, 'E'=>$fieldValExternal);
        }
        return $resultsDict;
    }
    
    public static function getErrorNumberForException($ex) {
        return 1;
    }
    
    /**
     * Using the current system time (with an optional offset, get date in VistA format
     */
    public static function getVistaDate($dateOffset) {
        $curDt = new \DateTime();
        
        if ($dateOffset < 0) {
            $dateOffset = abs($dateOffset);
            $curDt->sub(new \DateInterval('P'.$dateOffset.'D'));
        }
        else if ($dateOffset > 0) {
            $curDt->add(new \DateInterval('P'.$dateOffset.'D'));
        }
        else {
            // do nothing - current timestamp works
        }
        
        return MdwsUtils::convertPhpDateTimeToVistaDate($curDt);
    }
    
    /**
     * Convert \DateTime to Vista format
     * Ex 1) MdwsUtils::convertPhpDateTimeToVista(new \DateTime('2010-12-31')) -> '3131231'
     */
    public static function convertPhpDateTimeToVistaDate($phpDateTime) {
        $year = $phpDateTime->format('Y');
        $month = $phpDateTime->format('m');
        $day = $phpDateTime->format('d');
        
        return ($year - 1700).$month.$day;
    }

    /**
     * Convert VistA format: 3101231 -> 2010-12-31
     */
    public static function covertVistaDateTimeToDate($vistaDateTime) {
        $datePart = MdwsUtils::getVistaDateTimePart($vistaDateTime, "date");
        $year = 1700 + substr($datePart, 0, 3);
        $month = substr($datePart, 3, 2);
        $day = substr($datePart, 5, 2);
        
        return $month."-".$day."-".$year;
    }
    
    /**
     * Convert VistA format: 3101231 -> 20101231
     */
    public static function covertVistaDateToYYYYMMDD($vistaDateTime) {
        $datePart = MdwsUtils::getVistaDateTimePart($vistaDateTime, "date");
        $year = 1700 + substr($datePart, 0, 3);
        $month = substr($datePart, 3, 2);
        $day = substr($datePart, 5, 2);
        
        return $year.$month.$day;
    }

    /**
     * Convert 20100101 format -> 2010-01-01
     */
    public static function convertYYYYMMDDToDate($vistaDateTime) {
        $datePart = MdwsUtils::getVistaDateTimePart($vistaDateTime, "date");
        $year = substr($datePart, 0, 4);
        $month = substr($datePart, 4, 2);
        $day = substr($datePart, 6, 2);
        
        return $month."-".$day."-".$year;
    }
    
    /**
     * Convert 20100101.083400 format -> 2010-01-01 083400
     */
    public static function convertYYYYMMDDToDatetime($vistaDateTime) {
        $datePart = MdwsUtils::getVistaDateTimePart($vistaDateTime, "date");
        $timePart = MdwsUtils::getVistaDateTimePart($vistaDateTime, "time");
        $year = substr($datePart, 0, 4);
        $month = substr($datePart, 4, 2);
        $day = substr($datePart, 6, 2);
        
        return $month."-".$day."-".$year." ".$timePart;
    }
    
    /*
     * Fetch either the date or time part of a VistA date. 
     * Ex 1) MdwsUtils::getVistaDateTimePart('3101231.0930', 'date') -> '3101231'
     * Ex 2) MdwsUtils::getVistaDateTimePart('3101231.0930', 'time') -> '0930'
     * Ex 3) MdwsUtils::getVistaDateTimePart('3101231', 'time') -> '000000' (defaults to midnight if not time part)
     */
    public static function getVistaDateTimePart($vistaDateTime, $dateOrTime) {
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
    
    /**
     * NOT USED SO MARKING DEPRECATED 20150601
     * @deprecated since 20150601
     */
    public static  function getPatientFromSelectResult($serviceResponse) {
        $result = array();
        
        if(!isset($serviceResponse->selectResult)) {
            return $result;
        }
        
        $RptTO = $serviceResponse->selectResult;
        if(isset($RptTO->fault)) { 
            return $result;
        }
        
        $result['patientName'] = isset($RptTO->name) ? $RptTO->name : " ";
        $result['ssn'] = isset($RptTO->ssn) ? $RptTO->ssn : " ";
        $result['gender'] = isset($RptTO->gender) ? $RptTO->gender : " ";
        $result['dob'] = isset($RptTO->dob) ? date("m/d/Y", strtotime($RptTO->dob)) : " ";
        $result['ethnicity'] = isset($RptTO->ethnicity) ? $RptTO->ethnicity : " ";
        $result['age'] = isset($RptTO->age) ? $RptTO->age : " ";
        $result['maritalStatus'] = isset($RptTO->maritalStatus) ? $RptTO->maritalStatus : " ";
        $result['age'] = isset($RptTO->age) ? $RptTO->age : " ";
        $result['mpiPid'] = isset($RptTO->mpiPid) ? $RptTO->mpiPid : " ";
        $result['mpiChecksum'] = isset($RptTO->mpiChecksum) ? $RptTO->mpiChecksum : " ";
        $result['localPid'] = isset($RptTO->localPid) ? $RptTO->localPid : " ";
        $result['sitePids'] = isset($RptTO->sitePids) ? $RptTO->sitePids : " ";
        $result['vendorPid'] = isset($RptTO->vendorPid) ? $RptTO->vendorPid : " ";
        if(isset($RptTO->location))
        {
            $aLocation = $RptTO->location;
            $room = "Room: ";
            $room .=isset($aLocation->room)? $aLocation->room : " ";
            $bed =  "Bed: ";
            $bed .= (isset($aLocation->bed) ? $aLocation->bed : " " );
            $result['location'] = $room." / ".$bed;
        }
        else
        {
            $result['location'] = "Room:? / Bed:? ";
        }
        $result['cwad'] = isset($RptTO->cwad) ? $RptTO->cwad : " ";
        $result['restricted'] = isset($RptTO->restricted) ? $RptTO->restricted : " ";
        
        $result['admitTimestamp'] = isset($RptTO->admitTimestamp) ? date("m/d/Y h:i a", strtotime($RptTO->admitTimestamp)) : " ";
        
        $result['serviceConnected'] = isset($RptTO->serviceConnected) ? $RptTO->serviceConnected : " ";
        $result['scPercent'] = isset($RptTO->scPercent) ? $RptTO->scPercent : " ";
        $result['inpatient'] = isset($RptTO->inpatient) ? $RptTO->inpatient : " ";
        $result['deceasedDate'] = isset($RptTO->deceasedDate) ? $RptTO->deceasedDate : " ";
        $result['confidentiality'] = isset($RptTO->confidentiality) ? $RptTO->confidentiality : " ";
        $result['needsMeansTest'] = isset($RptTO->needsMeansTest) ? $RptTO->needsMeansTest : " ";
        $result['patientFlags'] = isset($RptTO->patientFlags) ? $RptTO->patientFlags : " ";
        $result['cmorSiteId'] = isset($RptTO->cmorSiteId) ? $RptTO->cmorSiteId : " ";
        $result['activeInsurance'] = isset($RptTO->activeInsurance) ? $RptTO->activeInsurance : " ";
        $result['isTestPatient'] = isset($RptTO->isTestPatient) ? $RptTO->isTestPatient : " ";
        $result['currentMeansStatus'] = isset($RptTO->currentMeansStatus) ? $RptTO->currentMeansStatus : " ";
        $result['hasInsurance'] = isset($RptTO->hasInsurance) ? $RptTO->hasInsurance : " ";
        $result['preferredFacility'] = isset($RptTO->preferredFacility) ? $RptTO->preferredFacility : " ";
        $result['patientType'] = isset($RptTO->patientType) ? $RptTO->patientType : " ";
        $result['isVeteran'] = isset($RptTO->isVeteran) ? $RptTO->isVeteran : " ";
        $result['isLocallyAssignedMpiPid'] = isset($RptTO->isLocallyAssignedMpiPid) ? $RptTO->isLocallyAssignedMpiPid : " ";
        $result['sites'] = isset($RptTO->sites) ? $RptTO->sites : " ";
        $result['teamID'] = isset($RptTO->team) ? $RptTO->team->id : " ";
        $result['teamName'] = isset($RptTO->team) ? $RptTO->team->name : "Unknown";
        $result['teamPcpName'] = isset($RptTO->team) ? $RptTO->team->pcpName : "Unknown";
        $result['teamAttendingName'] = isset($RptTO->team) ? $RptTO->team->attendingName : "Unknown";

        return $result;
    }
    
    /**
     * Default max returned dates is 5 unless you provide a different value.
     */
    public static function convertSoapVitalsToGraph($typeArray, $vitals, $max_dates=5)
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
            error_log("ERROR detected in convertSoapVitalsToGraph fault=$errmsg");
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
            
            $just_date = MdwsUtils::convertYYYYMMDDToDate($currentVitalsSet->timestamp);
            $datetime = MdwsUtils::convertYYYYMMDDToDatetime($currentVitalsSet->timestamp);
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
        
        /*
        error_log('Done convertSoapVitalsToGraph as user '
                .$user->name
                .' maxdates='.$max_dates
                .' found '.count($result)
                .' data points!'
                .'\nDETAILS>>>'.print_r($result,TRUE));
         */
        return $result;
    }

    /**
     * NOTE: SOME CODE REDUNDANCY WITH data_protocolsupport.php LABS EGFR LOGIC!
     */
    public static function convertSoapLabsToGraph($patientInfo, $egfrFormula, $allLabs, $limitMaxLabs=1000)
    {
        
        module_load_include('php', 'raptor_formulas', 'core/Labs');
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
                $formattedDate = MdwsUtils::convertYYYYMMDDToDate($lab['rawTime']);
                $datetime = MdwsUtils::convertYYYYMMDDToDatetime($lab['rawTime']);  //added 20141104 
                $result[] = array('date'=>$formattedDate, 'egfr'=>$eGFR, 'datetime'=>$datetime);
            }
        }
        return $result;
    }
    
    public static function getChemHemLabs($mdwsDao)
    {
        $displayLabsResult = array();
        
        $today = getDate();
        $toDate = "".($today['year']+1)."0101";
        $fromDate = "".($today['year'] - 20)."0101";

       // $serviceResponse = $this->m_oContext->getEMRService()->getChemHemReports(array('fromDate'=>$fromDate,'toDate'=>$toDate,'nrpts'=>'0'));
        $serviceResponse = $mdwsDao->makeQuery("getChemHemReports", array('fromDate'=>$fromDate,'toDate'=>$toDate,'nrpts'=>'0'));
        
        $blank = " ";
        if(!isset($serviceResponse->getChemHemReportsResult->arrays->TaggedChemHemRptArray->count))
                return $displayLabsResult;;
        $numTaggedRpts = $serviceResponse->getChemHemReportsResult->arrays->TaggedChemHemRptArray->count;
        if($numTaggedRpts == 0)
            return $displayLabsResult;
        
        for($i=0; $i<$numTaggedRpts; $i++){ //ChemHemRpts
            // Check to see if the set of rpts is an object or an array
            if (is_array($serviceResponse->getChemHemReportsResult->arrays->TaggedChemHemRptArray->rpts->ChemHemRpt)){
                $rpt = $serviceResponse->getChemHemReportsResult->arrays->TaggedChemHemRptArray->rpts->ChemHemRpt[$i];
            }
            else {
                $rpt = $serviceResponse->getChemHemReportsResult->arrays->TaggedChemHemRptArray->rpts->ChemHemRpt;
            }

            $specimen = $rpt->specimen;
            $nResults = is_array($rpt->results->LabResultTO) ? count($rpt->results->LabResultTO) : 1;
            for($j = 0; $j< $nResults; $j++){
                $result = is_array($rpt->results->LabResultTO) ? $rpt->results->LabResultTO[$j] : $rpt->results->LabResultTO;
                $test = $result->test;
                if(isset($rpt->timestamp))
                {
                    $just_date = MdwsUtils::convertYYYYMMDDToDate($rpt->timestamp);
                    $datetime = MdwsUtils::convertYYYYMMDDToDatetime($rpt->timestamp);  //added 20141104 
                    $displayLabsResult[] = array(
                        'name' => isset($test->name) ? $test->name : " ",
                        'date' => $just_date,   //isset($rpt->timestamp) ? date("m/d/Y h:i a", strtotime($rpt->timestamp)) : " ",
                        'datetime' => $datetime,   //isset($rpt->timestamp) ? date("m/d/Y h:i a", strtotime($rpt->timestamp)) : " ",
                        'value' => isset($result->value) ? $result->value : " ",
                        'units' =>isset($test->units) ? $test->units : " ",
                        'refRange' => isset($test->refRange) ? $test->refRange : " ",
                        'rawTime' => isset($rpt->timestamp) ? $rpt->timestamp : " ");
                }
            }
        }
        return $displayLabsResult;
    }
    
    public static function writeRaptorGeneralNote(
            $mdwsDao,
            $noteTextArray,
            $encounterString, 
            $cosignerDUZ) {
        return MdwsUtils::writeProgressNote
                ($mdwsDao, VISTA_NOTEIEN_RAPTOR_GENERAL, $noteTextArray, $encounterString, $cosignerDUZ);
    }

    public static function writeRaptorSafetyChecklist(
            $mdwsDao,
            $noteTextArray,
            $encounterString, 
            $cosignerDUZ) {
        return MdwsUtils::writeProgressNote
                ($mdwsDao, VISTA_NOTEIEN_RAPTOR_SAFETY_CKLST, $noteTextArray, $encounterString, $cosignerDUZ);
    }

    public static function  writeProgressNote(
            $mdwsDao, 
            $raptorNoteTitleIEN, 
            $noteTextArray, 
            $encounterString, 
            //$noteAuthorDUZ, - the logged in user will ALWAYS be the author
            $cosignerDUZ) {
        
        $formattedNoteText = MdwsUtils::formatNoteText($noteTextArray);
        
        $writeNoteArgAry = array('titleIEN'=>$raptorNoteTitleIEN,
                                    'encounterString'=>$encounterString,
                                    'text'=>$formattedNoteText,
                                    'authorDUZ'=>$mdwsDao->getDUZ(),
                                    'cosignerDUZ'=>$cosignerDUZ,
                                    'consultIEN'=>'',
                                    'prfIEN'=>'');
        
        $newNoteIen = $mdwsDao->makeQuery('writeNote', $writeNoteArgAry)->writeNoteResult->id;
        
        return $newNoteIen;
    }
    
    public static function getHospitalLocations($mdwsDao,$target = '') {
        $soapResult = $mdwsDao->makeQuery('getHospitalLocations', array('target'=>$target, 'direction'=>''));
        
        if (!isset($soapResult) || 
                !isset($soapResult->getHospitalLocationsResult) || 
                isset($soapResult->getHospitalLocationsResult->fault)) {
            throw new \Exception('Unable to get locations -> '.print_r($soapResult, true));
        }

        $locations = array();
        $locationTOs = is_array($soapResult->getHospitalLocationsResult->locations->HospitalLocationTO) ? 
                            $soapResult->getHospitalLocationsResult->locations->HospitalLocationTO :
                            array($soapResult->getHospitalLocationsResult->locations->HospitalLocationTO); 

        foreach ($locationTOs as $locTO) {
            $locations[$locTO->id] = $locTO->name;
        }
        return $locations;
    }

    /**
     * Cancel one radiology order
     * @throws \Exception
     */
    public static function cancelRadiologyOrder($mdwsDao,$patientIen,$orderIen,$providerDuz,$locationIen,$reasonCode,$eSig) {
        error_log('In cancelRadiologyOrder with params reasoncode=['.$reasonCode.'] and IEN=['.$orderIen.']');
        
        $soapResult = NULL;
        if (isset($eSig) && $eSig != '') {
            $soapResult = $mdwsDao->makeQuery('discontinueAndSignRadiologyOrder', 
                    array('patientId'=>$patientIen, 
                        'orderIen'=>$orderIen, 
                        'providerDuz'=>$providerDuz, 
                        'locationIen'=>$locationIen, 
                        'reasonIen'=>$reasonCode, 
                        'eSig'=>$eSig));
        } else {
            $soapResult = $mdwsDao->makeQuery('discontinueRadiologyOrder', 
                    array('patientId'=>$patientIen, 
                        'orderIen'=>$orderIen, 
                        'providerDuz'=>$providerDuz, 
                        'locationIen'=>$locationIen, 
                        'reasonIen'=>$reasonCode));            
        }

        // homogenize / handle both call signature result property names
        $inner = isset($soapResult->discontinueAndSignRadiologyOrderResult) ?
                $soapResult->discontinueAndSignRadiologyOrderResult :
                $soapResult->discontinueRadiologyOrderResult;
        
        if (!isset($soapResult) || !isset($inner) || isset($inner->fault)) {
            throw new \Exception('Unable to cancel order -> '.print_r($soapResult, true));
        }

        if ($inner->id == '') {
            throw new \Exception('Did not receive new order ID string when canceling... -> '.print_r($soapResult, true));
        }
        
        error_log('No errors from cancelRadiologyOrder with params reasoncode=['.$reasonCode.'] and IEN=['.$orderIen.']');
        return; // return nothing on success
    }
    
    /**
     * @return array with reasons we can use for canceling an order
     */
    public static function getRadiologyCancellationReasons($mdwsDao) {
        
        $soapResult = $mdwsDao->makeQuery('getRadiologyCancellationReasons', array());
        
        if (!isset($soapResult) || 
            !isset($soapResult->getRadiologyCancellationReasonsResult) || 
            isset($soapResult->getRadiologyCancellationReasonsResult->fault)) {
            throw new \Exception('Invalid getRadiologyCancellationReasons result -> '.print_r($soapResult, true));
        }
        
        $resultAry = array();
        $cancelReasonTOs = is_array($soapResult->getRadiologyCancellationReasonsResult->reasons->RadiologyCancellationReasonTO) ? 
                            $soapResult->getRadiologyCancellationReasonsResult->reasons->RadiologyCancellationReasonTO :
                            array($soapResult->getRadiologyCancellationReasonsResult->reasons->RadiologyCancellationReasonTO); 

        foreach ($cancelReasonTOs as $reasonTO) {
            $resultAry[$reasonTO->id] = $reasonTO->name;
        }

        return $resultAry;
   }
    
    public static function getEncounterStringFromVisit($visitTO) {
        if($visitTO == NULL)
        {
            throw new \Exception('Cannot pass a NULL visitTo into getEncounterStringFromVisit!');
        }
        if(!isset($visitTO->location->id) || $visitTO->location->id == '')
        {
            throw new \Exception('Did not get a valid location for '.print_r($visitTO,TRUE));
        }
        return $visitTO->location->id.';'.$visitTO->timestamp.';A';
    }

    public static function formatNoteText($noteTextArray) {
        if (!is_array($noteTextArray)) {
            throw new \Exception('Invalid note text argument>>>'.print_r($noteTextArray,TRUE));
        }
        
        $formatted = '';
        for ($i = 0; $i < count($noteTextArray); $i++) {
            if ($i == 0) { // don't insert | for new line first time through'
                $formatted = $noteTextArray[$i];
            }
            else {
                $formatted = $formatted.'|'.$noteTextArray[$i];
            }
        }
        
        return $formatted;
    }

     public static function getVisits($mdwsDao, $fromDate='', $toDate='') {
         
         try
         {
            if (!isset($fromDate) || trim($fromDate) == '') {
                $oneMonthAgo = MdwsUtils::getVistaDate(-1 * DEFAULT_GET_VISIT_DAYS);
                $fromDate = MdwsUtils::covertVistaDateToYYYYMMDD($oneMonthAgo); // TODO - get today-30 date in this format
            }
            if (!isset($toDate) || trim($toDate) == '') {
                $today = MdwsUtils::getVistaDate(0);
                $toDate = MdwsUtils::covertVistaDateToYYYYMMDD($today);
                //$toDate = '20140718'; // TODO - get today's date in this format
            }
            $soapResult = $mdwsDao->makeQuery('getVisits', array('fromDate'=>$fromDate, 'toDate'=>$toDate));
            //$soapResult = $mdwsDao->makeQuery('getAllMeds', NULL);
            //$soapResult = $mdwsDao->makeQuery('getAllMeds', null); // TODO - remove this line and uncomment line above
            $result = array();
            //return $result; // TODO - remove this line

            if (!isset($soapResult) || 
                    !isset($soapResult->getVisitsResult) || 
                    isset($soapResult->getVisitsResult->fault)) {
                throw new \Exception('Invalid getVisits result -> '.print_r($soapResult, true));
                       // . "\n<br>MdwsDao=". $mdwsDao
                       // . "\n<br>Which of these is TRUE? 1=[".!isset($soapResult->getVisitsResult).'] or 2=['.isset($soapResult->getVisitsResult->fault).']'
                       // . "\n<br>". 'RAW SOAP RESULT='.print_r($soapResult,TRUE));
            }
            
            // check for zero results
            if (!isset($soapResult->getVisitsResult->count) ||
                    $soapResult->getVisitsResult->count == 0) {
                return $result; // TBD - return null or empty array?
            }

            // homogenize result of 1 to array
            $visitAry = is_array($soapResult->getVisitsResult->visits->VisitTO) ? 
                            $soapResult->getVisitsResult->visits->VisitTO :
                            array($soapResult->getVisitsResult->visits->VisitTO); 

            foreach ($visitAry as $visit) {
                $aryItem = array(
                    'locationName' => $visit->location->name,
                    'locationId' => $visit->location->id,
                    'visitTimestamp' => $visit->timestamp,
                    'visitTO' => $visit
                );
                $result[] = $aryItem;   //Already acending
                //array_push($result, $aryItem);
            }
            $aSorted = array_reverse($result); //Now this is descrnding.

            return $aSorted;
         } catch (\Exception $ex) {
             throw new \Exception('Trouble in getVisits because ' . $ex);
         }
         
    }
  
    public static function verifyNoteTitleMapping($mdwsDao, $noteTitleIEN, $noteTitle) {
        $soapResult = $mdwsDao->makeQuery('getNoteTitles', array('target'=>$noteTitle, 'direction'=>''));
        
        if (!isset($soapResult) || !isset($soapResult->getNoteTitlesResult)
                || isset($soapResult->getNoteTitlesResult->fault)
                || !isset($soapResult->getNoteTitlesResult->results)) {
            throw new \Exception('Invalid results when attempting to verify note title/IEN: '.print_r($soapResult, TRUE));
        }
        if (!is_array($soapResult->getNoteTitlesResult->results->TaggedText->taggedResults->TaggedText)) 
        {
            $rsltAry = array($soapResult->getNoteTitlesResult->results->TaggedText->taggedResults->TaggedText);
        } else {
            $rsltAry = $soapResult->getNoteTitlesResult->results->TaggedText->taggedResults->TaggedText;
        }
        foreach ($rsltAry as $rslt) 
        {
            $theIEN=$rslt->tag;
            if($noteTitleIEN == $theIEN)
            {
                $titlesAry=$rslt->textArray->string;
                if(!is_array($titlesAry))
                {
                    $titlesAry = array($titlesAry);
                }
                foreach($titlesAry as $title)
                {
                    if($noteTitle == $title)
                    {
                        return TRUE;
                    }
                }
            }
        }
        // if not found, return false
        return FALSE;
    }

    public static function validateEsig($mdwsDao, $eSig) {
        $soapResult = $mdwsDao->makeQuery('isValidEsig', array('esig'=>$eSig));

        if (!isset($soapResult) || !isset($soapResult->isValidEsigResult) || isset($soapResult->isValidEsigResult->fault)) {
            throw new \Exception('Invalid electronic signature code -> '.print_r($soapResult, true));
        }

        if ($soapResult->isValidEsigResult->text == 'FALSE') {
            return FALSE;
        }
        return TRUE;
    }
    
    public static function signNote($mdwsDao, $noteIen, $userDuz, $eSig) {
        $soapResult = $mdwsDao->makeQuery('signNote', array(
            'noteIEN'=>$noteIen, 
            'userDUZ'=>$userDuz, 
            'esig'=>$eSig));

        if (!isset($soapResult) || !isset($soapResult->signNoteResult) || isset($soapResult->signNoteResult->fault)) {
                throw new \Exception('Invalid sign note result -> '.print_r($soapResult, true));
        }

        return TRUE;
    }    
    
    public static function selectPatient($mdwsDao, $pid) {
        if(!isset($pid) || $pid == null || $pid == '')
        {
            error_log('Cannot get patient if pid is not provided!');
            return null;
        }
        
        $serviceResponse = $mdwsDao->makeQuery("select", array('DFN'=>$pid));

        $result = array();
        if(!isset($serviceResponse->selectResult))
                return $result;
        
        $RptTO = $serviceResponse->selectResult;
        if(isset($RptTO->fault))
        { 
            return $result;
        }
        $result['patientName'] = isset($RptTO->name) ? $RptTO->name : " ";
        $result['ssn'] = isset($RptTO->ssn) ? $RptTO->ssn : " ";
        $result['gender'] = isset($RptTO->gender) ? $RptTO->gender : " ";
        $result['dob'] = isset($RptTO->dob) ? date("m/d/Y", strtotime($RptTO->dob)) : " ";
        $result['ethnicity'] = isset($RptTO->ethnicity) ? $RptTO->ethnicity : " ";
        $result['age'] = isset($RptTO->age) ? $RptTO->age : " ";
        $result['maritalStatus'] = isset($RptTO->maritalStatus) ? $RptTO->maritalStatus : " ";
        $result['age'] = isset($RptTO->age) ? $RptTO->age : " ";
        $result['mpiPid'] = isset($RptTO->mpiPid) ? $RptTO->mpiPid : " ";
        $result['mpiChecksum'] = isset($RptTO->mpiChecksum) ? $RptTO->mpiChecksum : " ";
        $result['localPid'] = isset($RptTO->localPid) ? $RptTO->localPid : " ";
        $result['sitePids'] = isset($RptTO->sitePids) ? $RptTO->sitePids : " ";
        $result['vendorPid'] = isset($RptTO->vendorPid) ? $RptTO->vendorPid : " ";
        if(isset($RptTO->location))
        {
            $aLocation = $RptTO->location;
            $room = "Room: ";
            $room .=isset($aLocation->room)? $aLocation->room : " ";
            $bed =  "Bed: ";
            $bed .= (isset($aLocation->bed) ? $aLocation->bed : " " );
            $result['location'] = $room." / ".$bed;
        }
        else
        {
            $result['location'] = "Room:? / Bed:? ";
        }
        $result['cwad'] = isset($RptTO->cwad) ? $RptTO->cwad : " ";
        $result['restricted'] = isset($RptTO->restricted) ? $RptTO->restricted : " ";
        
        $result['admitTimestamp'] = isset($RptTO->admitTimestamp) ? date("m/d/Y h:i a", strtotime($RptTO->admitTimestamp)) : " ";
        
        $result['serviceConnected'] = isset($RptTO->serviceConnected) ? $RptTO->serviceConnected : " ";
        $result['scPercent'] = isset($RptTO->scPercent) ? $RptTO->scPercent : " ";
        $result['inpatient'] = isset($RptTO->inpatient) ? $RptTO->inpatient : " ";
        $result['deceasedDate'] = isset($RptTO->deceasedDate) ? $RptTO->deceasedDate : " ";
        $result['confidentiality'] = isset($RptTO->confidentiality) ? $RptTO->confidentiality : " ";
        $result['needsMeansTest'] = isset($RptTO->needsMeansTest) ? $RptTO->needsMeansTest : " ";
        $result['patientFlags'] = isset($RptTO->patientFlags) ? $RptTO->patientFlags : " ";
        $result['cmorSiteId'] = isset($RptTO->cmorSiteId) ? $RptTO->cmorSiteId : " ";
        $result['activeInsurance'] = isset($RptTO->activeInsurance) ? $RptTO->activeInsurance : " ";
        $result['isTestPatient'] = isset($RptTO->isTestPatient) ? $RptTO->isTestPatient : " ";
        $result['currentMeansStatus'] = isset($RptTO->currentMeansStatus) ? $RptTO->currentMeansStatus : " ";
        $result['hasInsurance'] = isset($RptTO->hasInsurance) ? $RptTO->hasInsurance : " ";
        $result['preferredFacility'] = isset($RptTO->preferredFacility) ? $RptTO->preferredFacility : " ";
        $result['patientType'] = isset($RptTO->patientType) ? $RptTO->patientType : " ";
        $result['isVeteran'] = isset($RptTO->isVeteran) ? $RptTO->isVeteran : " ";
        $result['isLocallyAssignedMpiPid'] = isset($RptTO->isLocallyAssignedMpiPid) ? $RptTO->isLocallyAssignedMpiPid : " ";
        $result['sites'] = isset($RptTO->sites) ? $RptTO->sites : " ";
        $result['teamID'] = isset($RptTO->teamID) ? $RptTO->teamID : " ";
        $result['teamName'] = isset($RptTO->name) ? $RptTO->name : "Unknown";
        $result['teamPcpName'] = isset($RptTO->pcpName) ? $RptTO->pcpName : "Unknown";
        $result['teamAttendingName'] = isset($RptTO->attendingName) ? $RptTO->attendingName : "Unknown";
        $result['mpiPid'] = isset($RptTO->mpiPid) ? $RptTO->mpiPid : "Unknown";
        $result['mpiChecksum'] = isset($RptTO->mpiChecksum) ? $RptTO->mpiChecksum : "Unknown";

        return $result;
    }

    /**
     * JOEL RECOMMENDS DELETING THIS AND USING RESULT FROM data_worklist::getDashboardMap
     * @DEPRECATED
     */
    public static function getOrderDetails($mdwsDao, $radiologyOrderIen)
    {
         $ddrResult = MdwsUtils::parseDdrGetsEntryInternalAndExternal
            ($mdwsDao->makeQuery("ddrGetsEntry", array(
                'file'=>'75.1', 
                'iens'=>($radiologyOrderIen.','),
                'flds'=>'*', 
                'flags'=>'IEN'
            )));
         $orderFileRec = MdwsUtils::parseDdrGetsEntryInternalAndExternal
            ($mdwsDao->makeQuery("ddrGetsEntry", array(
                'file'=>'100', 
                'iens'=>($ddrResult['7']['I'].','),
                'flds'=>'*', 
                'flags'=>'IEN'
            )));

        $details = array();
        $details['orderingPhysicianDuz'] = $ddrResult['14']['I']; // get internal value of ordering provider field
        $details['orderFileStatus'] = $orderFileRec['5']['E'];
        // may be more to return here in the future
        return $details;
    }
      
}


