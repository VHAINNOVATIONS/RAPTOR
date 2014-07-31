<?php namespace raptor;

class MdwsUtils {
    /*
     * Turn the DDR GETS ENTRY results in to an array/dictionary by field #
     */
    public static function parseDdrGetsEntry($soapResult) {
        if (!isset($soapResult) || !isset($soapResult->ddrGetsEntryResult)
                || isset($soapResult->ddrGetsEntryResult->fault)) {
            throw new \Exception("Invalid DDR GETS ENTRY result");
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
            if ($fieldValInternal === "[WORD PROCESSING]") {
                $wpLines = ""; // create so can reference in while loop
                // word processing field - append following lines until reach "$$END$$
                while ($lines[$i+1] != "\$\$END\$\$") {
                    $wpLines = ($wpLines.$lines[++$i]."\n");
                }
                $resultsDict[$pieces[2]] = $wpLines;
                continue;
            }
            $resultsDict[$pieces[2]] = $pieces[3];
        }
        return $resultsDict;
    }
    
    public static function getErrorNumberForException($ex) {
        return 1;
    }
    
    /*
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
    
    /*
     * Convert \DateTime to Vista format
     * Ex 1) MdwsUtils::convertPhpDateTimeToVista(new \DateTime('2010-12-31')) -> '3131231'
     */
    public static function convertPhpDateTimeToVistaDate($phpDateTime) {
        $year = $phpDateTime->format('Y');
        $month = $phpDateTime->format('m');
        $day = $phpDateTime->format('d');
        
        return ($year - 1700).$month.$day;
    }

    /*
     * Convert VistA format: 3101231 -> 2010-12-31
     */
    public static function covertVistaDateTimeToDate($vistaDateTime) {
        $datePart = MdwsUtils::getVistaDateTimePart($vistaDateTime, "date");
        $year = 1700 + substr($datePart, 0, 3);
        $month = substr($datePart, 3, 2);
        $day = substr($datePart, 5, 2);
        
        return $month."-".$day."-".$year;
    }
    
    /*
     * Convert VistA format: 3101231 -> 20101231
     */
    public static function covertVistaDateToYYYYMMDD($vistaDateTime) {
        $datePart = MdwsUtils::getVistaDateTimePart($vistaDateTime, "date");
        $year = 1700 + substr($datePart, 0, 3);
        $month = substr($datePart, 3, 2);
        $day = substr($datePart, 5, 2);
        
        return $year.$month.$day;
    }

    /*
     * Convert 20100101 format -> 2010-01-01
     */
    public static function convertYYYYMMDDToDate($vistaDateTime) {
        $datePart = MdwsUtils::getVistaDateTimePart($vistaDateTime, "date");
        $year = substr($datePart, 0, 4);
        $month = substr($datePart, 4, 2);
        $day = substr($datePart, 6, 2);
        
        return $month."-".$day."-".$year;
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
        $result['teamID'] = isset($RptTO->teamID) ? $RptTO->teamID : " ";
        $result['teamName'] = isset($RptTO->name) ? $RptTO->name : "Unknown";
        $result['teamPcpName'] = isset($RptTO->pcpName) ? $RptTO->pcpName : "Unknown";
        $result['teamAttendingName'] = isset($RptTO->attendingName) ? $RptTO->attendingName : "Unknown";

        return $result;
    }
    
    
    public static function convertSoapVitalsToGraph($typeArray, $vitals, $maxVitals) {
        if (!isset($typeArray) || count($typeArray) === 0) {
            throw new \Exception("Invalid vital types argument");
        }
        if (isset($vitals->getVitalSignsResult->fault)) {
            throw new \Exception($vitals->getVitalSignsResult->fault->message);
        }
        
        if (!isset($maxVitals) || $maxVitals === 0) {
            $maxVitals = 5; // default to 5 per spec
        }
        
        $result = array();
        if (!isset($vitals->getVitalSignsResult->arrays->TaggedVitalSignSetArray) ||
                !isset($vitals->getVitalSignsResult->arrays->TaggedVitalSignSetArray->sets)) {
            return $result;
        }
        $vitalsAryTO = $vitals->getVitalSignsResult->arrays->TaggedVitalSignSetArray;
        $vitalsCount = count($vitalsAryTO->sets->VitalSignSetTO);
        $stopFlag = false;
        for ($i = 0; $i < $vitalsCount; $i++) {
            if ($stopFlag) {
                break;
            }
            
            $currentVitalsSet = NULL;
            if (is_array($vitalsAryTO->sets->VitalSignSetTO)) {
                $currentVitalsSet = $vitalsAryTO->sets->VitalSignSetTO[$i];
            }
            else {
                $currentVitalsSet = $vitalsAryTO->sets->VitalSignSetTO;
            }
            
            $timestamp = MdwsUtils::convertYYYYMMDDToDate($currentVitalsSet->timestamp);
            $signsCount = count($currentVitalsSet->vitalSigns->VitalSignTO);
            $aryForTimestamp = array();
            $aryForTimestamp["date"] = $timestamp;
            for ($j = 0; $j < $signsCount; $j++) {
                // it appears PHP is making arrays with one object stdclass and not array...
                $currentSign = NULL;
                if (is_array($currentVitalsSet->vitalSigns->VitalSignTO)) {
                    $currentSign = $currentVitalsSet->vitalSigns->VitalSignTO[$j];
                }
                else {
                    $currentSign = $currentVitalsSet->vitalSigns->VitalSignTO;
                }
                
                $currentType = $currentSign->type->name;
                
                if (in_array($currentType, $typeArray)) {
                    if ($currentType === "Temperature") {
                        $aryForTimestamp["temperature"] = $currentSign->value1;
                    }
                    else if ($currentType === "Pulse") {
                        $aryForTimestamp["pulse"] = $currentSign->value1;
                    }
                }
            }
            if (count($aryForTimestamp) > 1) { // if we added a vital/not just timestamp
                if (count($result) < $maxVitals) {
                    array_push($result, $aryForTimestamp);
                }
                else {
                    $stopFlag = true; // to break outer for loop
                    break; // if >= max vitals arg then break to avoid unncessary loops/parsing
                }
            }
        }
        
        //for ($i = 0; $i < count($result); $i++) {
        //    error_context_log("Vital date key: ".$result[$i]['date']);
        //}
        //error_context_log("Returned ".count($result)." vitals for graph");
        return $result;
    }

    
    public static function convertSoapLabsToGraph($patientInfo, $egfrFormula, $allLabs, $maxLabs)
    {
        if (!isset($maxLabs) || $maxLabs === 0) {
            $maxLabs = 3; // default to 3 per spec
        }
        $ethnicity = is_null($patientInfo) ? 'white' : $patientInfo['ethnicity'];
        $gender = is_null($patientInfo) ? 'male' : strtoupper($patientInfo['gender']);
        $age = is_null($patientInfo) ? 18 : $patientInfo['age'];
        // @TODO adjust for DOB
        $isAfricanAmerican = (strpos('BLACK', strtoupper($ethnicity)) !== FALSE) ||
                             (strpos('AFRICAN', strtoupper($ethnicity)) !== FALSE);
        $isFemale = $gender === 'FEMALE';

        $nCreatinine = 0;
        $filteredLabs = array();
        $foundCreatinine = FALSE;
        $foundEGFR = FALSE;
        $foundPLT = FALSE;
        $foundPT = FALSE;
        $foundINR = FALSE;
        $foundPTT = FALSE;
        $foundHCT = FALSE;

        $sortedLabs = $allLabs;
        // Obtain a list of columns
        foreach ($sortedLabs as $key => $row) {
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
        $result = array();

        foreach($sortedLabs as $lab)
        {
            if (count($result) >= $maxLabs) {
                break; // per specs - show only last 3 creatinine/egfr results
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
                foreach($sortedLabs as $checkLab){
                    if(strpos('EGFR', strtoupper($checkLab['name'])) !== FALSE){
                        $foundEGFR = TRUE;
                        $eGFR = $checkLab['value'];
                         $eGFRSource = "";
                         break;
                    }
                }
                if(!$foundEGFR)
                {
                    //186 * c^-1.154 * a^
                    if (is_null($egfrFormula)) {
                     /*
                      eGFR (mL/min/1.73 m^2) = 186 * [Serum Creat (mg/dL)]^-1.154 * [Age (years)]^-0.203 * F * (1.212 if African American)
                     [F = 1 if male, F = 0.742 if female]
                      */                
                     $eGFRValue = $rawValue;
                     $F = $isFemale ? 0.742 : 1;
                     $ethnicityCorrection = $isAfricanAmerican ? 1.212 : 1;
                     $eGFR = 186 * pow($eGFRValue, -1.154) * pow($age, -0.203) * $F * $ethnicityCorrection;
                     $eGFR = round($eGFR,0);
                     $eGFRSource = " (calc)";
                    }
                 }
               //$eGFRUnits = " mL/min/1.73 m^2";
               $formattedDate = MdwsUtils::convertYYYYMMDDToDate($lab['rawTime']);
               array_push($result, array('date'=>$formattedDate, 'egfr'=>$eGFR));
            }
        }
        return $result;
    }
    
    static function checkEgfrFormula($egfrFormula) {
        return true;
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
                $displayLabsResult[] = array(
                    'name' => isset($test->name) ? $test->name : " ",
                    'date' => isset($rpt->timestamp) ? date("m/d/Y h:i a", strtotime($rpt->timestamp)) : " ",
                    'value' => isset($result->value) ? $result->value : " ",
                    'units' =>isset($test->units) ? $test->units : " ",
                    'refRange' => isset($test->refRange) ? $test->refRange : " ",
                    'rawTime' => isset($rpt->timestamp) ? $rpt->timestamp : " ");
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
    
    public static function getEncounterStringFromVisit($visitTO) {
        return $visitTO->location->id.';'.$visitTO->timestamp.';A';
    }

    public static function formatNoteText($noteTextArray) {
        if (!is_array($noteTextArray)) {
            throw new \Exception('Invalid note text argument');
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

     public static function getVisits($mdwsDao, $fromDate, $toDate) {
        if (!isset($fromDate) || trim($fromDate) == '') {
            $oneMonthAgo = MdwsUtils::getVistaDate(-30);
            $fromDate = MdwsUtils::covertVistaDateToYYYYMMDD($oneMonthAgo); // TODO - get today-30 date in this format
        }
        if (!isset($toDate) || trim($toDate) == '') {
            $today = MdwsUtils::getVistaDate(0);
            $toDate = MdwsUtils::covertVistaDateToYYYYMMDD($today);
            //$toDate = '20140718'; // TODO - get today's date in this format
        }
        $soapResult = $mdwsDao->makeQuery('getVisits', array('fromDate'=>$fromDate, 'toDate'=>$toDate));
        
        $result = array();
        
        if (!isset($soapResult) || 
                !isset($soapResult->getVisitsResult) || 
                isset($soapResult->getVisitsResult->fault)) {
            throw new \Exception('Invalid getVisits result -> '.$soapResult->getVisitsResult->fault);
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
            array_push($result, $aryItem);
        }
        
        return $result;
    }
  
    public static function verifyNoteTitleMapping($mdwsDao, $noteTitleIEN, $noteTitle) {
        if ($noteTitle == 'RAPTOR NOTE' && $noteTitleIEN == 142) {
            return true;
        }
        if ($noteTitle == 'RAPTOR SAFTEY CHECKLIST' && $noteTitleIEN == 149) {
            return true;
        }
        return false;
        
        // TODO - this is obviously just statically defined right now - need to make
        // call to vista to verify note title IEN matches title
    }
    
}
