<?php
/**
 * @file
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase 2
 * Open Source VA Innovation Project 2011-2014
 * VA Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, et al
 * MDWS Integration and VISTA collaboration: Joel Mewton
 * Contacts: acasertano@sanbusinessconsultants.com, ffont@sanbusinessconsultants.com
 * ------------------------------------------------------------------------------------
 * 
 */ 

namespace raptor;

interface IMdwsDao {
    //put your code here
    public function connectAndLogin($siteCode, $username, $password);
    
    public function connectRemoteSites($applicationPassword);
    
    public function disconnect();
    
    public function makeQuery($functionToInvoke, $args);

    public function makeStatelessQuery($siteCode, $username, $password, $patientId, $functionToInvoke, $args, $multiSiteFlag, $appPwd);
    
    public function isAuthenticated();
}
