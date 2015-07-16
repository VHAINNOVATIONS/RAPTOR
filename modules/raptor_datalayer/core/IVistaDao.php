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
 * The core VistA integration functions that are required by RAPTOR
 * 
 */ 

namespace raptor;

interface IVistaDao
{
    /**
     * Get user readable information of technology used to integrate with EHR
     */
    public function getIntegrationInfo();
    
    /**
     * Initialize the EHR client
     */
    public function initClient();
    
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
    public function getDashboardDetailsMap($override_tracking_id);
    
    public function createNewRadiologyOrder($orderChecks, $args);
    public function createUnsignedRadiologyOrder($orderChecks, $args);
    public function getOrderableItems($imagingTypeId);
    public function getRadiologyOrderChecks($args);
    public function getRadiologyOrderDialog($imagingTypeId, $patientId);
    public function getProviders($neworderprovider_name);
    public function getUserSecurityKeys($userDuz);
    public function isProvider($myDuz);
    public function userHasKeyOREMAS($myDuz);
    public function cancelRadiologyOrder($patientid,$orderFileIen,$providerDUZ,$locationthing,$reasonCode, $cancelesig);
    public function convertSoapLabsToGraph($patientInfo, $egfrFormula, $allLabs, $limitMaxLabs=1000);
    public function convertSoapVitalsToGraph($vitalsdata, $soapResult, $max_dates);
    public function getChemHemLabs();
    public function getEncounterStringFromVisit($vistitTo);
    public function getHospitalLocations($startingitem);
    public function getOrderDetails($myIEN);
    public function getRadiologyCancellationReasons();
    public function getVisits();
    public function signNote($newNoteIen, $userDuz, $eSig);
    public function validateEsig($eSig);
    public function verifyNoteTitleMapping($checkVistaNoteIEN, $checkVistaNoteTitle);
    public function writeRaptorGeneralNote($noteTextArray, $encounterString, $cosignerDUZ);
    public function writeRaptorSafetyChecklist($aChecklistData, $encounterString, $cosignerDUZ);
}
