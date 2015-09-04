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
 * 
 */ 

namespace raptor;

/**
 * The integration interface between RAPTOR and the EHR system.
 */
interface IEhrDao
{
    /**
     * Get user readable information of technology used to integrate with EHR
     */
    public function getIntegrationInfo();

    /**
     * Set the instance info message.  
     */
    public function setCustomInfoMessage($msg);
    
    /**
     * Get the instance info message.
     */
    public function getCustomInfoMessage();
    
    /**
     * We can only pre-cache orders if the DAO implementation is not statefully
     * remembering the last selected order as the current order.
     * 
     * Returns TRUE if critical functions support tracking ID override for precache purposes.
     */
    public function getSupportsPreCacheOrderData();
    
    /**
     * We can only pre-cache patient data if the DAO implementation is not statefully
     * remembering the last selected order as the current order.
     * 
     * Returns TRUE if critical functions support patientId override for precache purposes.
     */
    public function getSupportsPreCachePatientData();
           
    /**
     * Connect and login to the EHR
     */
    public function connectAndLogin($siteCode, $username, $password);
    
    /**
     * Disconnect from the EHR
     */
    public function disconnect();
    
    /**
     * Return TRUE if logged into the EHR
     */
    public function isAuthenticated();
    
    /**
     * Get the patient DUZ associated with an order IEN
     */
    public function getPatientIDFromTrackingID($sTrackingID);

    /**
     * Get the patient information associated with their ID
     */
    public function getPatientMap($pid);
    
    /**
     * Declare the patient currently being processed
     */
    public function setPatientID($sPatientID);
    
    /**
     * Get the patient ID that is currently selected
     */
    public function getSelectedPatientID();
        
    /**
     * Get NULL if no problems, else text of the missing keys.
     */
    public function getVistaAccountKeyProblems();
            
    /**
     * Get array of arrays of all relevant orders.
     */
    public function getWorklistDetailsMap();
    
    /**
     * Get associative array of dashboard for one order.
     */
    public function getDashboardDetailsMap($override_tracking_id=NULL);

    /**
     * Get the EHR User ID of the user currently logged in.
     */
    public function getEHRUserID($fail_if_missing=TRUE);

    /**
     * User readible information about the instance
     */
    public function __toString();

    /**
     * Clear all cached data for this DAO instance.
     */
    public function invalidateCacheForEverything();

    /**
     * Clear all cached data for the specified order
     */
    public function invalidateCacheForOrder($tid);
    
    /**
     * Clear all cached data for the specified patient
     */
    public function invalidateCacheForPatient($pid);
    
    /**
     * Return limited list of providers starting with neworderprovider_name
     */
    public function getProviders($start_name);
    
    public function cancelRadiologyOrder($patientid, $orderFileIen, $providerDUZ, $locationthing, $reasonCode, $cancelesig);
    public function createNewRadiologyOrder($orderChecks, $args);
    public function createUnsignedRadiologyOrder($orderChecks, $args);
    public function getAllHospitalLocationsMap();
    public function getAllergiesDetailMap();
    
    /**
     * IMPORTANT: Return FALSE if the optional $override_patientId is not NULL and not supported.
     */
    public function getChemHemLabs($override_patientId = NULL);
    
    /**
     * IMPORTANT: Return FALSE if the optional $override_patientId is not NULL and not supported.
     */
    public function getDiagnosticLabsDetailMap($override_patientId = NULL);
    
    /**
     * IMPORTANT: Return FALSE if the optional $override_patientId is not NULL and not supported.
     */
    public function getEGFRDetailMap($override_patientId = NULL);
    
    /**
     * IMPORTANT: Return FALSE if the optional $override_patientId is not NULL and not supported.
     */
    public function getProcedureLabsDetailMap($override_patientId = NULL);

    /**
     * IMPORTANT: Return FALSE if the optional $override_patientId is not NULL and not supported.
     */
    public function getRawVitalSignsMap($override_patientId = NULL);
    
    public function getEncounterStringFromVisit($vistitTo);
    public function getHospitalLocationsMap($startingitem);
    public function getImagingTypesMap();
    public function getMedicationsDetailMap($atriskmeds = NULL);
    public function getNotesDetailMap();
    public function getOrderOverviewMap();
    public function getOrderableItems($imagingTypeId);

    /**
     * IMPORTANT: Return FALSE if the optional $override_patientId is not NULL and not supported.
     */
    public function getPathologyReportsDetailMap($override_patientId = NULL);
    public function getPendingOrdersMap();
    public function getProblemsListDetailMap();
    
    public function getRadiologyCancellationReasons();
    public function getRadiologyOrderChecks($args);
    public function getRadiologyOrderDialog($imagingTypeId, $patientId);
    
    /**
     * IMPORTANT: Return FALSE if the optional $override_patientId is not NULL and not supported.
     */
    public function getRadiologyReportsDetailMap($override_patientId = NULL);
    
    /**
     * IMPORTANT: Return FALSE if the optional $override_patientId is not NULL and not supported.
     */
    public function getSurgeryReportsDetailMap($override_patientId = NULL);
    public function getUserSecurityKeys();
    public function getVisits();
    public function getVitalsDetailMap();
    public function getVitalsDetailOnlyLatestMap();
    public function getVitalsSummaryMap();
    public function isProvider();
    public function signNote($newNoteIen, $eSig);
    public function userHasKeyOREMAS();
    public function validateEsig($eSig);
    public function verifyNoteTitleMapping($checkVistaNoteIEN, $checkVistaNoteTitle);
    public function writeRaptorGeneralNote($noteTextArray, $encounterString, $cosignerDUZ);
    public function writeRaptorSafetyChecklist($aChecklistData, $encounterString, $cosignerDUZ);
   
}
