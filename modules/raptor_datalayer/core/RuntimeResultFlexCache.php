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

require_once 'data_context.php';


/**
 * The RuntimeResultFlexCache is a singleton that caches results at runtime.
 * This session level cache has a configurable expiration time.
 *
 * @author Frank Font of SAN Business Consultants
 */
class RuntimeResultFlexCache 
{
    private $m_sGroupName = NULL;
    private static $m_aGroups = array();
    
    private function __construct($sGroupName)
    {
        $this->m_sGroupName = $sGroupName;
    }
    
    /**
     * Get the existing cache for the group or create a new one
     * @return instance of RuntimeResultFlexCache class
     */
    public static function getInstance($sGroupName, $bReset=FALSE)
    {
        if(!isset(RuntimeResultFlexCache::$m_aGroups[$sGroupName]) || $bReset )
        {
            RuntimeResultFlexCache::$m_aGroups[$sGroupName] = new RuntimeResultFlexCache($sGroupName);
            if(!isset($_SESSION['RuntimeResultFlexCache']))
            {
                $cacheroot = array();
            } else {
                $cacheroot = $_SESSION['RuntimeResultFlexCache'];
            }
            if(!isset($cacheroot[$sGroupName]) || $bReset)
            {
                $cacheroot[$sGroupName] = array();
                $_SESSION['RuntimeResultFlexCache'] = $cacheroot;
            }
        }
        return RuntimeResultFlexCache::$m_aGroups[$sGroupName];
    }
    
    /**
     * Add the result data to the cache.
     */
    public function addToCache($sThisResultName,$aResult,$nMaxDataAgeSeconds=600)
    {
        $cacheroot = $_SESSION['RuntimeResultFlexCache'];
        if($this->m_sGroupName == NULL || !isset($cacheroot[$this->m_sGroupName]))
        {
            throw new \Exception("The RuntimeResultFlexCache must be initialized with a group name BEFORE you can add $sThisResultName!");
        }
        $groupcache = $cacheroot[$this->m_sGroupName];
        if(!isset($groupcache[$sThisResultName]))
        {
            $groupcache[$sThisResultName] = array();
        }
        $groupcache[$sThisResultName]['creation_time'] = time();
        $groupcache[$sThisResultName]['max_age_seconds'] = $nMaxDataAgeSeconds;
        $groupcache[$sThisResultName]['data'] = $aResult;
        $cacheroot[$this->m_sGroupName] = $groupcache;
        $_SESSION['RuntimeResultFlexCache'] = $cacheroot;
    }
    
    /**
     * Side effect of the check is that it prepares the cache to accept a new result.
     * @return NULL if not found in cache, else the result from the cache.
     */
    public function checkCache($sThisResultName)
    {
        $cacheroot = $_SESSION['RuntimeResultFlexCache'];
        if($this->m_sGroupName == NULL || !isset($cacheroot[$this->m_sGroupName]))
        {
            throw new \Exception("The RuntimeResultFlexCache must be initialized with a group name BEFORE you can read $sThisResultName!");
        }
        $groupcache = $cacheroot[$this->m_sGroupName];
        if(isset($groupcache[$sThisResultName]))
        {
            $foundcache = $groupcache[$sThisResultName];
            //Make sure cache data is not too old
            $currenttime = time();
            $createdtime = $foundcache['creation_time'];
            $data_age = $currenttime - $createdtime;
            $max_age_seconds = $foundcache['max_age_seconds'];                
            if($data_age > 0)// $max_age_seconds)
            {
                //Cache data is stale, kill it.
                $groupcache[$sThisResultName] = array();
                $_SESSION['RuntimeResultFlexCache'] = $cacheroot;
            } else {
                //We have good cache data, use it.
                $aResult = $foundcache['data'];
            }
        }
        return $aResult;
    }
}
