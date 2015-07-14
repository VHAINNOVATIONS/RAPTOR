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
require_once 'data_context.php';
require_once 'RuntimeResultFlexCache.php';

/**
 * This is the primary interface abstraction to VistA
 *
 * @author Frank Font of SAN Business Consultants
 */
class VistaDao implements IVistaDao
{
    private $m_implclass = NULL;
    private $m_oContext = NULL;
    private $m_oRuntimeResultFlexCache;    //Cache results.
    
    function __construct()
    {
        module_load_include('php', 'raptor_datalayer', 'config/vista_integration');
        $name = VISTA_INT_IMPL_DAO_CLASSNAME;
        $class = "\\raptor\\$name";
        $this->m_implclass = new $class();
        
        $this->m_oContext = \raptor\Context::getInstance();
        $uid = $this->m_oContext->getUID();
        $this->m_oRuntimeResultFlexCache = \raptor\RuntimeResultFlexCache::getInstance("VistaDao[$uid]");
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

    public function getWorklistDetailsMap()
    {
        //TODO!!!
    }
    
    /**
     * Gets dashboard details for the currently selected ticket of the session
     * 
     * !!! IMPORTANT TODO --- MAKE THE OVERRIDE NOT STATEFUL so we can precache!!!!!!
     */
    function getDashboardDetailsMap($override_tracking_id=NULL)
    {
        if($override_tracking_id == NULL)
        {
            $tid = $this->m_oContext->getSelectedTrackingID();
        } else {
            $tid = $override_tracking_id;
        }
        $sThisResultName = "getDashboardDetailsMap[$tid]";
        $aCachedResult = $this->m_oRuntimeResultFlexCache->checkCache($sThisResultName);
        if($aCachedResult !== NULL)
        {
            //Found it in the cache!
            return $aCachedResult;
        }
        
        $wl = new WorklistData($this->m_oContext);
        $aResult = $wl->getDashboardMap();
        
        $this->m_oRuntimeResultFlexCache->addToCache($sThisResultName, $aResult, CACHE_AGE_SITEVALUES);
        return $aResult;
    }
    
    /**
     * Not intended as a primary public interface
     */
    function makeQuery($functionToInvoke, $args) 
    {
        return $this->m_implclass->makeQuery($functionToInvoke, $args);
    }
}
