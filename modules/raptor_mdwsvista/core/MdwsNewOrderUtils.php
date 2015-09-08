<?php
/**
 * @file
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase 2
 * Open Source VA Innovation Project 2011-2015
 * VA Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, Joel Mewton, et al
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

namespace raptor_mdwsvista;

require_once 'MdwsStringUtils.php';

class MdwsNewOrderUtils {

    public static function getImagingTypes($mdwsDao) 
    {
        //$result = array();
        //$result['37'] = 'ANGIO/NEURO/INTERVENTIONAL';
        //$result['5'] = 'MRI';
        //return $result;
        
        try
        {
            $soapResult = $mdwsDao->makeQuery('getImagingOrderTypes', array());

            if (isset($soapResult->getImagingOrderTypesResult->fault)) {
                throw new \Exception($soapResult->getImagingOrderTypesResult->fault->message);
            }

            $result = array();
            if (!isset($soapResult->getImagingOrderTypesResult->OrderTypeTO) ||
                    count($soapResult->getImagingOrderTypesResult->OrderTypeTO) == 0) {
                //Just return the empty array.
                return $result;
            }
            //$imagingTypes = $soapResult->getImagingOrderTypesResult->OrderTypeTO;
            $orderTypeTO = $soapResult->getImagingOrderTypesResult->OrderTypeTO;
            if(!is_array($orderTypeTO))
            {
                throw new \Exception("Expected an array for orderTypeTO but got this instead ".print_r($orderTypeTO,TRUE));
            }
            $typeCount = count($soapResult->getImagingOrderTypesResult->OrderTypeTO);

            for ($i = 0; $i < $typeCount; $i++) 
            {
                $id = $soapResult->getImagingOrderTypesResult->OrderTypeTO[$i]->id;
                $name = $soapResult->getImagingOrderTypesResult->OrderTypeTO[$i]->name1;
                $result[$id] = $name;
            }

            return $result;
        } catch (\Exception $ex) {
            throw new \Exception("Failed getImagingTypes because " . $ex->getMessage(), 99765, $ex);
        }
    }
    
    /**
     * This call returns the ENTIRE list of orderable items for an imaging ID type.
     * The call to getRadiologyOrderDialog's shortList and commonProcedures are a SUBSET of the results.
     */
    public static function getOrderableItems($mdwsDao, $imagingTypeId) {
        
        $soapResult = $mdwsDao->makeQuery('getOrderableItems', array('dialogId' => $imagingTypeId));
        
        if (isset($soapResult->getOrderableItemsResult->fault)) {
            throw new \Exception($soapResult->getOrderableItemsResult->fault->message);
        }
        
        $result = array();
        if (!isset($soapResult->getOrderableItemsResult->OrderTypeTO) ||
                count($soapResult->getOrderableItemsResult->OrderTypeTO) == 0) {
            //Just return the empty array.
            return $result;
        }
        
        $orderableItems = $soapResult->getOrderableItemsResult->OrderTypeTO;
        $typeCount = count($soapResult->getOrderableItemsResult->OrderTypeTO);
        
        for ($i = 0; $i < $typeCount; $i++) 
        {
            $id = $orderableItems[$i]->id;
            $name = $orderableItems[$i]->name1;
            $requiresApproval = $orderableItems[$i]->requiresApproval;
            $result[$id] = array('name'=>$name, 'requiresApproval'=>$requiresApproval);
        }
error_log("LOOK getOrderableItems($imagingTypeId) clean result>>> " . print_r($result,TRUE));        
        return $result;
    }
    
    public static function getRadiologyOrderDialog($mdwsDao, $imagingTypeId, $patientId) {       
        $soapResult = $mdwsDao->makeQuery('getRadiologyOrderDialog', array('patientId'=>$patientId, 'dialogId' => $imagingTypeId));
        
        if (isset($soapResult->getRadiologyOrderDialogResult->fault)) {
            throw new \Exception($soapResult->getRadiologyOrderDialogResult->fault->message);
        }
        
        $result = array();
        
        $dialog = $soapResult->getRadiologyOrderDialogResult;
        
        $result['contractOptions'] = MdwsNewOrderUtils::getKeyValuePairsFromTaggedTextArray($dialog->contractOptions);
        $result['sharingOptions'] = MdwsNewOrderUtils::getKeyValuePairsFromTaggedTextArray($dialog->sharingOptions);
        $result['researchOptions'] = MdwsNewOrderUtils::getKeyValuePairsFromTaggedTextArray($dialog->researchOptions);
        $result['categories'] = MdwsNewOrderUtils::getKeyValuePairsFromTaggedTextArray($dialog->categories);
        $result['modifiers'] = MdwsNewOrderUtils::getKeyValuePairsFromTaggedTextArray($dialog->modifiers);
        $result['urgencies'] = MdwsNewOrderUtils::getKeyValuePairsFromTaggedTextArray($dialog->urgencies);
        $result['transports'] = MdwsNewOrderUtils::getKeyValuePairsFromTaggedTextArray($dialog->transports);
        $result['submitTo'] = MdwsNewOrderUtils::getKeyValuePairsFromTaggedTextArray($dialog->submitTo);
        
        // common procedures for dialog
        $result['commonProcedures'] = array();
        if (isset($dialog->commonProcedures) 
                && isset($dialog->commonProcedures->ClinicalProcedureTO)
                && count($dialog->commonProcedures->ClinicalProcedureTO) > 0) {
            if (!is_array($dialog->commonProcedures->ClinicalProcedureTO)) {
                //20150525
                $dialog->commonProcedures->ClinicalProcedureTO = array($dialog->commonProcedures->ClinicalProcedureTO);
            }            
            $commonProcs = array();
            $commonProcCount = count($dialog->commonProcedures->ClinicalProcedureTO);
            for ($i = 0; $i < $commonProcCount; $i++) {
                $procId = $dialog->commonProcedures->ClinicalProcedureTO[$i]->id;
                $procName = $dialog->commonProcedures->ClinicalProcedureTO[$i]->name;
                $commonProcs[$procId] = $procName;
            }
            $result['commonProcedures'] = $commonProcs;
        }
        
        // short list of procedures for dialog
        $result['shortList'] = array();
        if (isset($dialog->shortList) 
                && isset($dialog->shortList->ClinicalProcedureTO)
                && count($dialog->shortList->ClinicalProcedureTO) > 0) {
            $shortList = array();
            if (!is_array($dialog->shortList->ClinicalProcedureTO)) {
                //20150525
                $dialog->shortList->ClinicalProcedureTO = array($dialog->shortList->ClinicalProcedureTO);
            }            
            $shortListCount = count($dialog->shortList->ClinicalProcedureTO);
            for ($i = 0; $i < $shortListCount; $i++) {
                $procId = $dialog->shortList->ClinicalProcedureTO[$i]->id;
                $procName = $dialog->shortList->ClinicalProcedureTO[$i]->name;
                $shortList[$procId] = $procName;
            }
            $result['shortList'] = $shortList;
        }

        // last 7 days of exams for patient
        $result['last7DaysExams'] = array();
        if (isset($dialog->lastSevenDaysExams) 
                && isset($dialog->lastSevenDaysExams->ImagingExamTO)
                && count($dialog->lastSevenDaysExams->ImagingExamTO) > 0) {
            $exams = array();
            if (!is_array($dialog->lastSevenDaysExams->ImagingExamTO)) {
                //20150525
                $dialog->lastSevenDaysExams->ImagingExamTO = array($dialog->lastSevenDaysExams->ImagingExamTO);
            }            
            $examsCount = count($dialog->lastSevenDaysExams->ImagingExamTO);
            for ($i = 0; $i < $examsCount; $i++) {
                $examId = $dialog->lastSevenDaysExams->ImagingExamTO[$i]->id;
                $examName = $dialog->lastSevenDaysExams->ImagingExamTO[$i]->name;
                $exams[$procId] = $examName;
            }
            $result['last7DaysExams'] = $exams;
        }
                
        return $result;
    }

    public static function getKeyValuePairsFromTaggedTextArray($taggedTextArray) {
        $result = array();
        $count = 0;
        if (isset($taggedTextArray)
                && isset($taggedTextArray->results) 
                && isset($taggedTextArray->results->TaggedText) 
                && count($taggedTextArray->results->TaggedText) > 0) {
            $count = count($taggedTextArray->results->TaggedText);
        }
        else {
            return $result;
        }
        
        $ttaRef = $taggedTextArray->results->TaggedText;
        if ($count == 1) {
            $ttaRef = array($taggedTextArray->results->TaggedText);
        }
        
        for ($i = 0; $i < $count; $i++) 
        {
            $id = $ttaRef[$i]->tag;
            $name = $ttaRef[$i]->text;
            $result[$id] = $name;
        }
        return $result;
    }

    /**
     * Create an order but do NOT sign it. This function calls createNewRadiologyOrder
     * but blanks 'eSig' index of arg array first, if present
     */
    public static function createUnsignedRadiologyOrder($mdwsDao, $orderChecks, $args) {
        if (isset($args['eSig'])) {
            $args['eSig'] = ''; // blank this since this function explicitly doesn't sign the created order
        }
        return MdwsNewOrderUtils::createNewRadiologyOrder($mdwsDao, $orderChecks, $args);
    }

    /**
     * Create a signed a new order.
     */
    public static function createNewRadiologyOrder($mdwsDao, $orderChecks, $args) {
        $patientId = $args['patientId'];
        $duz = $args['requestingProviderDuz']; // changed 2/7/2015 to passed in arg
        $locationIEN = $args['locationIEN'];
        $dlgDisplayGroupId = $args['imagingTypeId'];
        $orderableItemIen = $args['orderableItemId'];
        $urgencyCode = $args['urgencyCode'];
        $modeCode = $args['modeCode'];
        $classCode = $args['classCode'];
        $contractSharingIen = $args['contractSharingIen'];
        $submitTo = $args['submitTo'];
        $pregnant = isset($args['pregnant']) ? $args['pregnant'] : '';
        $isolation = isset($args['isolation']) ? $args['isolation'] : '';
        $reasonForStudy = $args['reasonForStudy'];
        $clinicalHx = \raptor_mdwsvista\MdwsStringUtils::joinStrings($args['clinicalHx'], '|'); // 'Line 1|followed by 2|and three';
        $startDateTime = \raptor_mdwsvista\MdwsStringUtils::convertPhpDateTimeToISO($args['startDateTime']);
        $preOpDateTime = isset($args['preOpDateTime']) ? \raptor_mdwsvista\MdwsStringUtils::convertPhpDateTimeToISO($args['preOpDateTime']) : '';
        $modifierIds = \raptor_mdwsvista\MdwsStringUtils::joinStrings($args['modifierIds'], '|');
        $eSig = isset($args['eSig']) ? $args['eSig'] : '';
        $orderCheckOverrideReason = isset($args['orderCheckOverrideReason']) ? $args['orderCheckOverrideReason'] : '';
        
        $soapResult = $mdwsDao->makeQuery('saveNewRadiologyOrder', array(
            'patientId'=>$patientId,
            'duz'=>$duz,
            'locationIEN'=>$locationIEN,
            'dlgDisplayGroupId'=>$dlgDisplayGroupId,
            'orderableItemIen'=>$orderableItemIen,
            'urgencyCode'=>$urgencyCode,
            'modeCode'=>$modeCode,
            'classCode'=>$classCode,
            'contractSharingIen'=>$contractSharingIen,
            'submitTo'=>$submitTo,
            'pregnant'=>$pregnant,
            'isolation'=>$isolation,
            'reasonForStudy'=>$reasonForStudy,
            'clinicalHx'=>$clinicalHx,
            'startDateTime'=>$startDateTime,
            'preOpDateTime'=>$preOpDateTime,
            'modifierIds'=>$modifierIds,
            'eSig'=>$eSig,
            'orderCheckOverrideReason'=>$orderCheckOverrideReason
        ));

        if (isset($soapResult->fault)) {
            throw new \Exception('There was a problem creating the order: '.$soapResult->fault->message);
        }
        $soapResult = $soapResult->saveNewRadiologyOrderResult;
        if (isset($soapResult->fault)) {
            throw new \Exception('There was a problem creating the order: '.$soapResult->fault->message);
        }
        // TODO - need to verify isset()
        $order = array();
        $order['id'] = $soapResult->id;
        $order['timestamp'] = $soapResult->timestamp;
        $order['startDate'] = $soapResult->startDate;
        $order['status'] = $soapResult->status;
        $order['sigStatus'] = $soapResult->sigStatus;
        $order['text'] = $soapResult->text;
        $order['detail'] = isset($soapResult->detail) ? $soapResult->detail : '';
        if ($eSig != '') { // if didn't sign then it wasn't released to service so don't look for it!!
            // find most recent 75.1 record corresponding to this new order ID
            $order['radiologyOrderId'] = MdwsNewOrderUtils::getRadiologyOrderIenFromOrderId($mdwsDao, $order['id']);
        }
        return $order;
    }
    
    /**
     * NOTE: ONLY USE THIS CALL FOR NEWLY CREATED ORDERS!!! THE QUERY MAY TIMEOUT 
     * IN PRODUCTION OTHERWISE AND CAUSE A LARGE VISTA LOAD RESULTING IN OI&T's WRATH!!!
     * 
     * Searches the rad/nuc med orders (75.1) file for order ID from file 100 
     * WARNING: Using VistA to filter was FAILING in test, so we search on client side.
     */
    public static  function getRadiologyOrderIenFromOrderId($mdwsDao, $orderId, $maxrecordschecked=500) {
        //$orderId = '34436;1';
        $orderCount = -1;
        try
        {
            $semiColonIdx = strpos($orderId, ';');
            if ($semiColonIdx) {
                $orderId = substr($orderId, 0, $semiColonIdx);
            }

            $result = $mdwsDao->makeQuery('ddrLister', array(
                'file'=>'75.1', 
                'iens'=>'',   
                'fields'=>'.01;7', 
                'flags'=>'IPB',      
                'maxrex'=>"$maxrecordschecked",   
                'from'=>'',      
                'part'=>'',        
                'xref'=>'#',        
                'screen'=>'',//'screen'=> 'I ($P(^(0),U,7)='.$orderId.')', 
                'identifier'=>''
            ));

            if (!isset($result) || !isset($result->ddrListerResult)
                    || isset($result->ddrListerResult->fault) 
                    || !isset($result->ddrListerResult->text)) {
                throw new \Exception('Error when attempting to locate radiology order IEN by Order file IEN: '.print_r($result,TRUE));
            }

            $orderCount = count($result->ddrListerResult->text->string);
            for ($i = $orderCount-1; $i >= 0; $i--) {
                $resultPieces = explode('^', $result->ddrListerResult->text->string[$i]);
                $radFileOrderIen = $resultPieces[2];    
                if($radFileOrderIen == $orderId)
                {
                    return $resultPieces[0];
                }
            }
        } catch (\Exception $ex) {
            error_log("Caught exception in getRadiologyOrderIenFromOrderId($orderId) "
                    . "with ordercount=$orderCount Error Message=".$ex->getMessage());
            throw $ex;
        }

        throw new \Exception("Verification of matching record in file 75.1 "
                . "failed to find orderid=$orderId ($orderCount rows) "
                . "in the bottom $maxrecordschecked records!"
                . "\nResult Details>>> ".print_r($result,TRUE));
    }

    public static function getRadiologyOrderChecks($mdwsDao, $args) {
        $patientId = $args['patientId'];
        $orderDt = \raptor_mdwsvista\MdwsStringUtils::convertPhpDateTimeToISO($args['startDateTime']);;
        $locationId = $args['locationIEN'];
        $orderableItemIEN = $args['orderableItemId'];
        
        $soapResult = $mdwsDao->makeQuery('getOrderChecks', 
            array('patientId'=>$patientId, 
                'orderStartDateTime'=>$orderDt, 
                'locationId'=>$locationId, 
                'orderableItem'=>$orderableItemIEN));
        
        $soapResult = $soapResult->getOrderChecksResult;
        
        // massage order check result of 1 to array
        if (!is_array($soapResult->OrderCheckTO)) {
            $soapResult = array($soapResult->OrderCheckTO);
        }
        else {
            $soapResult = $soapResult->OrderCheckTO;
        }
        
        if (isset($soapResult[0]->fault)) {
            error_log('SOAP FAULT FOUND>>>'.print_r($soapResult,TRUE));
            throw new \Exception('There was a problem fetching order checks: '.$soapResult[0]->fault->message);
        }
        
        $result = array();
                
        $orderCheckCount = count($soapResult);
        
        for ($i = 0; $i < $orderCheckCount; $i++) {
            $id = $soapResult[$i]->id;
            $name = $soapResult[$i]->name;
            $level = $soapResult[$i]->level;
            
            $tmp = array();
            $tmp['name'] = $name;
            $tmp['level'] = $level;
			$tmp['needsOverride'] = ($level == '1');
            
            $result[$id] = $tmp;
        }
        
        return $result;
    }   
}