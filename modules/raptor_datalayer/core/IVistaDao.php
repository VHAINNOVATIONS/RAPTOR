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
    
    /**
     * Do not spread these calls troughout the code
     */
    function makeQuery($functionToInvoke, $args);
}
