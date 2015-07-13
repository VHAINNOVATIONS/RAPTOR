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

require_once 'IVistaDao.php';
require_once 'vista_integration.php';

/**
 * This is the primary interface abstraction to VistA
 *
 * @author Frank Font of SAN Business Consultants
 */
class VistaDao implements IVistaDao
{
    private $m_implclass = NULL;
    
    function __construct()
    {
        $name = VISTA_INT_IMPL_DAO_CLASSNAME;
        $class = "\\raptor\\$name";
        $this->m_implclass = new $class();
    }

    public function getIntegrationInfo() 
    {
        return $this->m_implclass->getIntegrationInfo();
    }

    public function connectAndLogin($siteCode, $username, $password) 
    {
        return $this->m_implclass->connectAndLogin($siteCode, $username, $password);
    }

    public function disconnect() 
    {
       return $this->m_implclass->disconnect();
    }

    public function isAuthenticated() 
    {
       return $this->m_implclass->isAuthenticated();
    }

    public function makeQuery($functionToInvoke, $args) 
    {
        return $this->m_implclass->makeQuery($functionToInvoke, $args);
    }
}
