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
    public function getIntegrationInfo();
    
    public function connectAndLogin($siteCode, $username, $password);
    
    public function disconnect();
    
    public function isAuthenticated();
    
    public function makeQuery($functionToInvoke, $args);
}
