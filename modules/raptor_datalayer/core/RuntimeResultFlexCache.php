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
    private $m_nCreatedTime = NULL;
    private $m_sGroupName = NULL;
    private $m_uid = NULL;
    private static $m_aGroups = array();
    
    private function __construct($sGroupName)
    {
        $this->m_nCreatedTime = microtime();
        $this->m_sGroupName = $sGroupName;
        global $user;
        $this->m_uid = $user->uid;
    }
    
    public function __toString()
    {
        try
        {
            $flagroot = $_SESSION['RuntimeResultFlexCache_flags'];
            $groupflag = $flagroot[$this->m_sGroupName];
            $groupflagkeys = array_keys($groupflag);
            $cacheroot = $_SESSION['RuntimeResultFlexCache'];
            $groupcache = $cacheroot[$this->m_sGroupName];
            $groupcachekeys = array_keys($groupcache);
            
            return "RuntimeResultFlexCache Instance for group "
            . "{$this->m_sGroupName} created {$this->m_nCreatedTime}..."
            . "\n\t{$this->m_nCreatedTime}\tFLAG KEYS=" . print_r($groupflagkeys,TRUE)
            . "\n\t{$this->m_nCreatedTime}\tCACHE KEYS=" . print_r($groupcachekeys,TRUE)
            . "\n\t{$this->m_nCreatedTime}\tFLAG DETAILS=" . print_r($groupflag,TRUE)
            . "\n\t{$this->m_nCreatedTime}\tCACHE DETAILS=" . print_r($groupcache,TRUE);
        } catch (\Exception $ex) {
            return "RuntimeResultFlexCache Instance for group "
            . "{$this->m_sGroupName} created {$this->m_nCreatedTime} trouble in tostring->" 
            . print_r($ex,TRUE);
        }
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
                $flagroot = array();
            } else {
                $cacheroot = $_SESSION['RuntimeResultFlexCache'];
                $flagroot = $_SESSION['RuntimeResultFlexCache_flags'];
            }
            if(!isset($cacheroot[$sGroupName]) || $bReset)
            {
                $cacheroot[$sGroupName] = array();
                $_SESSION['RuntimeResultFlexCache'] = $cacheroot;
                $flagroot[$sGroupName] = array();
                $_SESSION['RuntimeResultFlexCache_flags'] = $flagroot;
            }
        }
        return RuntimeResultFlexCache::$m_aGroups[$sGroupName];
    }

    /**
     * Mark a cache as building
     */
    public function markCacheBuilding($sThisResultName,$nRetrySeconds=5,$nFailTimeoutSeconds=100)
    {
        $this->updateCacheFlag($sThisResultName,'building',TRUE,$nRetrySeconds,$nFailTimeoutSeconds);
    }

    /**
     * Mark a cache as building
     */
    public function isCacheBuilding($sThisResultName)
    {
        error_log("DEBUG isCacheBuilding FLEXCACHE top>>>".$this);
        return ($this->getCacheFlagValue($sThisResultName,'building') == TRUE);
    }

    /**
     * If 0 then no need to wait, else try again after result seconds.
     */
    public function getCacheBuildingRetrySeconds($sThisResultName)
    {
        $foundinfo = $this->getCacheFlagInfo($sThisResultName,'building');
        if($foundinfo == NULL)
        {
error_log("DEBUG FLEXCACHE getCacheBuildingRetrySeconds got NULL $this");            
            return 0;
        }
error_log("DEBUG FLEXCACHE getCacheBuildingRetrySeconds got data $this\n\tDATA=".print_r($foundinfo,TRUE));            
        return $foundinfo['retry_seconds'];
    }
    
    /**
     * Mark a cache as building
     */
    public function clearCacheBuilding($sThisResultName,$nRetrySeconds=5,$nFailTimeoutSeconds=100)
    {
        $this->clearCacheFlag($sThisResultName,'building');
    }
    
    /**
     * Update a cache flag values
     */
    private function updateCacheFlag($sThisResultName,$flagname,$flagvalue,$nRetrySeconds=5,$nFailTimeoutSeconds=100)
    {
        $flagroot = $_SESSION['RuntimeResultFlexCache_flags'];
        if($this->m_sGroupName == NULL)
        {
            throw new \Exception("The RuntimeResultFlexCache must be initialized with a group name BEFORE you can set flag[$flagname]=[$flagvalue] of $sThisResultName!");
        }
        $this->startUserCriticalSection();
        $groupflag = $flagroot[$this->m_sGroupName];
        if(!isset($groupflag[$sThisResultName]))
        {
            $groupflag[$sThisResultName] = array();
        }
        $groupflag[$sThisResultName][$flagname]['value'] = $flagvalue;
        $groupflag[$sThisResultName][$flagname]['creation_time'] = time();
        $groupflag[$sThisResultName][$flagname]['retry_seconds'] = $nRetrySeconds;
        $groupflag[$sThisResultName][$flagname]['fail_timeout_seconds'] = $nFailTimeoutSeconds;
        $flagroot[$this->m_sGroupName] = $groupflag;
        $_SESSION['RuntimeResultFlexCache_flags'] = $flagroot;
        
        $this->updateRaptorCacheFlag(
            $nRetrySeconds
            ,$nFailTimeoutSeconds
            ,$sThisResultName
            ,$flagvalue);        
        
        
error_log("DEBUG updateCacheFlag FLEXCACHE bottom>>>".$this);
        $this->endUserCriticalSection();
    }

    private function getRaptorCacheFlagInfo($flagname)
    {
        $result = db_select('raptor_cache_flag', 'u')
                    ->condition('uid', $this->m_uid, '=')
                    ->condition('group_name', $this->m_sGroupName,'=')
                    ->condition('item_name', $flagname,'=')
                    ->execute();
        return $result->fetchAssoc();    
    }

    private function getRaptorCacheDataInfo($item_name)
    {
        $result = db_select('raptor_cache_data', 'u')
                    ->condition('uid', $this->m_uid, '=')
                    ->condition('group_name', $this->m_sGroupName,'=')
                    ->condition('item_name', $item_name,'=')
                    ->execute();
        return $result->fetchAssoc();    
    }
    
    private function clearRaptorCacheFlag($item_name)
    {
            $query = db_delete('raptor_cache_flag')
                ->condition('uid', $this->m_uid,'=')
                ->condition('group_name', $this->m_sGroupName,'=')
                ->condition('item_name', $item_name,'=')
                ->execute();    
    }
    
    private function clearRaptorCacheData($item_name)
    {
            $query = db_delete('raptor_cache_data')
                ->condition('uid', $this->m_uid,'=')
                ->condition('group_name', $this->m_sGroupName,'=')
                ->condition('item_name', $item_name,'=')
                ->execute();    
    }
    
    private function updateRaptorCacheData(
            $retry_delay
            ,$max_age
            ,$item_name
            ,$item_data)
    {
        try
        {
            if($item_data != NULL)
            {
                $myblob = serialize($item_data);
            } else {
                $myblob = NULL;
            }
            $created_dt = date("Y-m-d H:i:s", time());
            db_merge('raptor_cache_data')
                ->key(array('uid'=> $this->m_uid,
                    'group_name'=> $this->m_sGroupName,
                    'item_name' => $item_name,
                ))
                ->fields(array(
                    'uid' => $this->m_uid,    
                    'group_name' => $this->m_sGroupName,
                    'created_dt' => $created_dt,
                    'retry_delay' => $retry_delay,    
                    'max_age' => $max_age,    
                    'item_name' => $item_name,
                    'item_data' => $myblob,
                ))
                ->execute();
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    private function updateRaptorCacheFlag(
             $retry_delay
            ,$max_age
            ,$item_name
            ,$item_value)
    {
        try
        {
            $created_dt = date("Y-m-d H:i:s", time());
            db_merge('raptor_cache_flag')
                ->key(array('uid'=>$this->m_uid,
                    'group_name'=>$this->m_sGroupName,
                    'item_name' => $item_name,
                ))
                ->fields(array(
                    'uid' => $this->m_uid,    
                    'group_name' => $this->m_sGroupName,
                    'created_dt' => $created_dt,
                    'retry_delay' => $retry_delay,    
                    'max_age' => $max_age,    
                    'item_name' => $item_name,
                    'item_value' => $item_value,
                ))
                ->execute();
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Get the flag value
     */
    private function getCacheFlagValue($sThisResultName,$flagname)
    {
        $foundcache = getCacheFlagInfo($sThisResultName,$flagname);
        if($foundcache == NULL)
        {
error_log("DEBUG getCacheFlagValue($flagname) FLEXCACHE is NULL!");
            return NULL;
        }
error_log("DEBUG getCacheFlagValue($flagname) FLEXCACHE is [ {$foundcache[$flagname]['value']} ] !");
        return $foundcache[$flagname]['value'];
    }

    private function startUserCriticalSection()
    {
        $lockname = 'raptor.' . $this->m_uid . '.' . $this->m_sGroupName;
        try
        {
            $sSQL = "SELECT GET_LOCK('$lockname', 5)";  //Timeout after 5 seconds
            $result = db_query($sSQL);
            $lock = $result->fetchColumn(0);
            $tries=1;
            while($lock != '1')
            {
                if($tries > 5)
                {
                    throw new \Exception("Failed startUserCriticalSection from instance {$this->m_nCreatedTime} after $tries tries!");
                }
                error_log("Warning startUserCriticalSection from {$this->m_nCreatedTime} on try $tries will try again soon...");
                $result = db_query($sSQL);
                $lock = $result->fetchCol();    
                $tries++;
            }
            error_log("DEBUG FLEXCACHE startUserCriticalSection($lockname) bottom $this");
        } catch (\Exception $ex) {
            error_log("Trouble in startUserCriticalSection($lockname)>>>".print_r($ex,TRUE));
            throw $ex;
        }
    }

    private function endUserCriticalSection()
    {
        $lockname = 'raptor.' . $this->m_uid . '.' . $this->m_sGroupName;
        try
        {
            $sSQL = "SELECT RELEASE_LOCK('$lockname')";
            $result = db_query($sSQL);
            error_log("DEBUG FLEXCACHE endUserCriticalSection($lockname) bottom $this");
        } catch (\Exception $ex) {
            //Do NOT throw the exception, just log it.
            error_log("Trouble in endUserCriticalSection($lockname)>>>".print_r($ex,TRUE));
        }
    }
    
    /**
     * Get the flag information
     */
    private function getCacheFlagInfo($sThisResultName,$flagname)
    {
        $flagroot = $_SESSION['RuntimeResultFlexCache_flags'];
        if($this->m_sGroupName == NULL)
        {
            throw new \Exception("The RuntimeResultFlexCache must be initialized with a group name BEFORE you can read flag[$flagname] of $sThisResultName!");
        }
        $this->startUserCriticalSection();
        try
        {
            $groupflag = $flagroot[$this->m_sGroupName];
            if(!isset($groupflag[$sThisResultName]) || !isset($groupflag[$sThisResultName][$flagname]))
            {
                //Not set.
error_log("DEBUG getCacheFlagInfo($flagname) FLEXCACHE is NOT SET!>>>".print_r($groupflag,TRUE)."\n\tALL STUFF=$this");
            $this->endUserCriticalSection();
                return NULL;
            }
            $foundcache = $groupflag[$sThisResultName];
            //It exists, but is it still valid?
            $currenttime = time();
            $creation_time = $foundcache['creation_time'];
            $flag_age = $currenttime - $creation_time;
            $fail_timeout_seconds = $foundcache['fail_timeout_seconds'];                
            if($flag_age > $fail_timeout_seconds)
            {
                //Kill it.
error_log("DEBUG getCacheFlagInfo($flagname) FLEXCACHE is NOW NULL!");
                $this->clearCacheFlag($sThisResultName, $flagname);
            $this->endUserCriticalSection();
                return NULL;
            }
error_log("DEBUG getCacheFlagInfo($flagname) FLEXCACHE is ".print_r($foundcache,TRUE));
            $this->endUserCriticalSection();
            return $foundcache;
        } catch (\Exception $ex) {
error_log("FAILED getCacheFlagInfo($flagname) FLEXCACHE >>>".print_r($ex));
            $this->endUserCriticalSection();    //Always end it
            throw $ex;
        }
    }
    
    /**
     * Update a cache flag action
     */
    private function clearCacheFlag($sThisResultName,$flagname)
    {
        $flagroot = $_SESSION['RuntimeResultFlexCache_flags'];
        if($this->m_sGroupName == NULL)
        {
            throw new \Exception("The RuntimeResultFlexCache must be initialized with a group name BEFORE you can clear flag[$flagname] of $sThisResultName!");
        }
        $this->startUserCriticalSection();
        $groupflag = $flagroot[$this->m_sGroupName];
        if(!isset($groupflag[$sThisResultName]) || !isset($groupflag[$sThisResultName][$flagname]))
        {
            //Already missing.
        $this->endUserCriticalSection();
            return FALSE;
        }
        unset($groupflag[$sThisResultName][$flagname]);
        $flagroot[$this->m_sGroupName] = $groupflag;
        $_SESSION['RuntimeResultFlexCache_flags'] = $flagroot;
        
        $this->clearRaptorCacheFlag($sThisResultName);
        $this->endUserCriticalSection();
        return TRUE;
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
        
        $this->updateRaptorCacheData(
            5
            ,$nMaxDataAgeSeconds
            ,$sThisResultName
            ,$aResult);        
        
        $this->clearCacheBuilding($sThisResultName);
    }
    
    /**
     * Side effect of the check is that it prepares the cache to accept a new result.
     * @return NULL if not found in cache, else the result from the cache.
     */
    public function checkCache($sThisResultName)
    {
        //See if we are already building a cache.
        $aResult = NULL;
        error_log("FLEXCACHE checking1 for cache retry seconds.>>>$this");
        $retry_seconds = $this->getCacheBuildingRetrySeconds($sThisResultName);
        error_log("FLEXCACHE checking2 for cache got result of $retry_seconds retry seconds.>>>$this");
        while($retry_seconds > 0)
        {
            error_log("FLEXCACHE Waiting for $sThisResultName to build, will retry in $retry_seconds seconds.");
            sleep($retry_seconds);
            $retry_seconds = $this->getCacheBuildingRetrySeconds($sThisResultName);
        }
        //Now check for an available cache.
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
            $creation_time = $foundcache['creation_time'];
            $data_age = $currenttime - $creation_time;
            $max_age_seconds = $foundcache['max_age_seconds'];                
            if($data_age > $max_age_seconds)
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
