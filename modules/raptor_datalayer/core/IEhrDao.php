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
     * Declare the patient currently being processed
     */
    public function setPatientID($sPatientID);
    
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
     * Clear all cached data for the specified order
     */
    public function invalidateCacheForOrder($tid);
    
    /**
     * Clear all cached data for the specified patient
     */
    public function invalidateCacheForPatient($pid);
    
    public function cancelRadiologyOrder($patientid, $orderFileIen, $providerDUZ, $locationthing, $reasonCode, $cancelesig);
    public function createNewRadiologyOrder($orderChecks, $args);
    public function createUnsignedRadiologyOrder($orderChecks, $args);
    public function getAllHospitalLocationsMap();
    public function getAllergiesDetailMap();
    public function getChemHemLabs();
    public function getDiagnosticLabsDetailMap();
    public function getEGFRDetailMap();
    public function getEncounterStringFromVisit($vistitTo);
    public function getHospitalLocationsMap($startingitem);
    public function getImagingTypesMap();
    public function getImplementationInstance();
    public function getMedicationsDetailMap($atriskmeds = NULL);
    public function getNotesDetailMap();
    public function getOrderOverviewMap();
    public function getOrderableItems($imagingTypeId);
    public function getPathologyReportsDetailMap();
    public function getPendingOrdersMap();
    public function getProblemsListDetailMap();
    public function getProcedureLabsDetailMap();
    public function getProviders($neworderprovider_name);
    public function getRadiologyCancellationReasons();
    public function getRadiologyOrderChecks($args);
    public function getRadiologyOrderDialog($imagingTypeId, $patientId);
    public function getRadiologyReportsDetailMap();
    public function getRawVitalSignsMap();
    public function getSurgeryReportsDetailMap();
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
