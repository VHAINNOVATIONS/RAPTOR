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

require_once ('data_context.php');


/**
 * The RuntimeResultCache is a singleton that caches results at runtime.
 *
 * @author Frank Font of SAN Business Consultants
 */
class RuntimeResultCache 
{
    private $m_oContext = NULL;
    private $m_sGroupName = NULL;
    private $m_aRuntimeResultCache = NULL;
    private static $m_aGroups = array();
    
    private function __construct($oContext, $sGroupName)
    {
        $this->m_oContext = $oContext;
        $this->m_sGroupName = $sGroupName;
        $this->m_aRuntimeResultCache = array();
        error_log('Created cache for ['.$this->m_sGroupName.']');
    }
    
    /**
     * Get the existing cache for the group or create a new one
     * @param type $oContext current conext instance
     * @param type $sGroupName name of the cache array
     * @param type $bReset if TRUE then no existing cache is returned, only new empty one
     * @return instance of RuntimeResultCache class
     */
    public static function getInstance($oContext, $sGroupName, $bReset=FALSE)
    {
        if(!isset(RuntimeResultCache::$m_aGroups[$sGroupName]) || $bReset )
        {
            RuntimeResultCache::$m_aGroups[$sGroupName] = new RuntimeResultCache($oContext,$sGroupName);
        }
        return RuntimeResultCache::$m_aGroups[$sGroupName];
    }
    
    /**
     * Add the result to the cache.
     * @param type $sThisResultName
     * @param type $aResult
     */
    public function addToCache($sThisResultName,$aResult)
    {
        $nCLU = $this->m_oContext->getLastUpdateTimestamp();
        if(!isset($this->m_aRuntimeResultCache[$nCLU]))
        {
            //Remove any existing keys since they are old now.
            $this->m_aRuntimeResultCache = array();
            
            //Create cache key for this.
            $this->m_aRuntimeResultCache[$nCLU] = array();
        }
        $this->m_aRuntimeResultCache[$nCLU][$sThisResultName] = $aResult;
    }
    
    /**
     * Side effect of the check is that it prepares the cache to accept a new result.
     * @param type $resultName
     * @return NULL if not found in cache, else the result from the cache.
     */
    public function checkCache($sThisResultName)
    {
        $aResult = NULL;    //Assume no result cached.
        $nCLU = $this->m_oContext->getLastUpdateTimestamp();
        //error_log('Trying cache for ['.$this->m_sGroupName.']['.$sThisResultName.'] using '.$nCLU);
        if(isset($this->m_aRuntimeResultCache[$nCLU]))
        {
            //Yes, we have a key do we have cache for this call?
            if(isset($this->m_aRuntimeResultCache[$nCLU][$sThisResultName]))
            {
                //Short circut now to return the cached result.
                //error_log('Successfully hit cache for ['.$this->m_sGroupName.']['.$sThisResultName.'] using '.$nCLU);
                $aResult = $this->m_aRuntimeResultCache[$nCLU][$sThisResultName];
            }
        } else {
            //Remove any existing keys since they are old now.
            $this->m_aRuntimeResultCache = array();
            
            //Create cache key for this.
            $this->m_aRuntimeResultCache[$nCLU] = array();
        }
        return $aResult;
    }
}
