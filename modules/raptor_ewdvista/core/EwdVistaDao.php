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

namespace raptor;

require_once 'IEwdVistaDao.php';

/**
 * This is the primary interface implementation to VistA
 *
 * @author Frank Font of SAN Business Consultants
 */
class EwdVistaDao implements IEwdVistaDao
{
    function __construct()
    {
    }

    public function getIntegrationInfo()
    {
        return "EWD TBD"; //TODO get real runtime version number
    }

    public function connectAndLogin($siteCode, $username, $password) 
    {
        throw new \Exception("Not implemented");
    }

    public function disconnect() 
    {
        throw new \Exception("Not implemented");
    }

    public function isAuthenticated() 
    {
        throw new \Exception("Not implemented");
    }

    public function makeQuery($functionToInvoke, $args) 
    {
        throw new \Exception("Not implemented");
    }
}
