<?php
/**
 * @file
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase 2
 * Open Source VA Innovation Project 2011-2014
 * VA Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, et al
 * Contacts: acasertano@sanbusinessconsultants.com, ffont@sanbusinessconsultants.com
 * ------------------------------------------------------------------------------------
 * 
 */ 


namespace raptor;

require_once 'data_worklist.php';
require_once 'RuntimeResultCache.php';

/**
 * The dashboard is key information about the currently selected
 * patient and their procedure.
 *
 * @author SAN
 */
class DashboardData 
{
    private $m_oContext = NULL;
    private $m_oRuntimeResultCache;    //Cache results.

    function __construct($oContext)
    {
        $this->m_oContext = $oContext;
        $this->m_oRuntimeResultCache = \raptor\RuntimeResultCache::getInstance($this->m_oContext,'DashboardData');
        //$this->m_oRuntimeResultFlexCache = \raptor\RuntimeResultFlexCache::getInstance('DashboardData');
    }
    
    /**
     * Get the boilerplate details for the requested row
     */
    function getDashboardDetails()
    {
        $sThisResultName = 'getDashboardDetails';
        $aCachedResult = $this->m_oRuntimeResultCache->checkCache($sThisResultName);
        if($aCachedResult !== null)
        {
            //Found it in the cache!
            return $aCachedResult;
        }
        
        $wl = new WorklistData($this->m_oContext);
        $aResult = $wl->getDashboardMap();
        
        $this->m_oRuntimeResultCache->addToCache($sThisResultName, $aResult);
        return $aResult;
    }
}
