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
 * This database level cache has a configurable expiration time.
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
            $result = db_select('raptor_cache_flag', 'u')
                        ->fields('u')
                        ->condition('uid', $this->m_uid, '=')
                        ->execute();
            $flagstuff = array();
            if($result->rowCount() > 0)
            {
                while($record = $result->fetchAssoc())
                {
                    $item_name = $record['item_name'];
                    $flag_name = $record['flag_name'];
                    $flagstuff[] = "{$this->m_sGroupName}.{$item_name}.{$flag_name}";
                }
            }
            $result = db_select('raptor_cache_data', 'u')
                        ->fields('u')
                        ->condition('uid', $this->m_uid, '=')
                        ->execute();
            $datastuff = array();
            if($result->rowCount() > 0)
            {
                while($record = $result->fetchAssoc())
                {
                    $item_name = $record['item_name'];
                    $datastuff[] = "{$this->m_sGroupName}.{$item_name}";
                }
            }
            return "RuntimeResultFlexCache Instance for {$this->m_uid}"
            . " in group {$this->m_sGroupName}"
            . " created {$this->m_nCreatedTime}"
            . "\n\tFLEXCACHE FLAGS=".(count($flagstuff) == 0 ? print_r($flagstuff,TRUE) : 'NONE')
            . "\n\tFLEXCACHE DATA=".(count($datastuff) == 0 ? print_r($datastuff,TRUE) : 'NONE');
        } catch (\Exception $ex) {
            return "RuntimeResultFlexCache Instance for {$this->m_uid}"
            . " in group {$this->m_sGroupName}"
            . " created {$this->m_nCreatedTime} trouble in tostring->" 
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
        }
        return RuntimeResultFlexCache::$m_aGroups[$sGroupName];
    }

    /**
     * Mark a cache as building
     */
    public function markCacheBuilding($sThisResultName,$nRetrySeconds=5,$nFailTimeoutSeconds=100)
    {
        $this->updateRaptorCacheFlag($nRetrySeconds, $nFailTimeoutSeconds, $sThisResultName, 'building', TRUE);
    }

    /**
     * Mark a cache as building
     */
    public function isCacheBuilding($sThisResultName)
    {
        return ($this->getRaptorCacheFlagValue($sThisResultName,'building') == TRUE);
    }

    /**
     * If 0 then no need to wait, else try again after result seconds.
     */
    public function getCacheBuildingRetrySeconds($sThisResultName)
    {
        $foundinfo = $this->getRaptorCacheFlagInfo($sThisResultName,'building');
        if(!isset($foundinfo['retry_delay']))
        {
            return 0;
        }
        return $foundinfo['retry_delay'];
    }
    
    /**
     * Mark a cache as building
     */
    public function clearCacheBuilding($sThisResultName)
    {
        $this->clearRaptorCacheFlag($sThisResultName,'building');
    }
    
    private function getRaptorCacheFlagInfo($item_name, $flag_name)
    {
        try
        {
            $result = db_select('raptor_cache_flag', 'u')
                        ->fields('u')
                        ->condition('uid', $this->m_uid, '=')
                        ->condition('group_name', $this->m_sGroupName,'=')
                        ->condition('item_name', $item_name,'=')
                        ->condition('flag_name', $flag_name,'=')
                        ->execute();
            $foundinfo = $result->fetchAssoc();
            if(!isset($foundinfo['flag_name']))
            {
                //We do not have it.
                return NULL;
            }
            //Make sure not timed out.
            $current_time = time();
            $created_dt = strtotime($foundinfo['created_dt']);
            $flag_age = $current_time - $created_dt;
            $max_age = $foundinfo['max_age'];                
            if($flag_age > $max_age)
            {
                //Kill it.
                $this->clearRaptorCacheFlag($item_name, $flag_name);
                return NULL;
            }
            return $foundinfo;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    private function getRaptorCacheFlagValue($item_name, $flag_name)
    {
        $foundinfo = $this->getRaptorCacheFlagInfo($item_name, $flag_name);
        if(isset($foundinfo['flag_value']))
        {
            return $foundinfo['flag_value'];
        }
        return NULL;
    }
    
    private function getRaptorCacheDataInfo($item_name)
    {
        $result = db_select('raptor_cache_data', 'u')
                    ->fields('u')
                    ->condition('uid', $this->m_uid, '=')
                    ->condition('group_name', $this->m_sGroupName,'=')
                    ->condition('item_name', $item_name,'=')
                    ->execute();
            $foundinfo = $result->fetchAssoc();
            if(!isset($foundinfo['item_data']))
            {
                //We do not have it.
                return NULL;
            }
            //Make sure not timed out.
            $current_time = time();
            $created_dt = strtotime($foundinfo['created_dt']);
            $data_age = $current_time - $created_dt;
            $max_age = $foundinfo['max_age'];                
            if($data_age > $max_age)
            {
                //Kill it.
                $this->clearRaptorCacheData($item_name);
                return NULL;
            }
            return $foundinfo;
    }
    
    private function clearRaptorCacheFlag($item_name,$flag_name)
    {
            $query = db_delete('raptor_cache_flag')
                ->condition('uid', $this->m_uid,'=')
                ->condition('group_name', $this->m_sGroupName,'=')
                ->condition('item_name', $item_name,'=')
                ->condition('flag_name', $flag_name,'=')
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
            ,$flag_name
            ,$flag_value)
    {
        try
        {
            $created_dt = date("Y-m-d H:i:s", time());
            db_merge('raptor_cache_flag')
                ->key(array('uid'=>$this->m_uid,
                    'group_name'=>$this->m_sGroupName,
                    'item_name' => $item_name,
                    'flag_name' => $flag_name,
                ))
                ->fields(array(
                    'uid' => $this->m_uid,    
                    'group_name' => $this->m_sGroupName,
                    'created_dt' => $created_dt,
                    'retry_delay' => $retry_delay,    
                    'max_age' => $max_age,    
                    'item_name' => $item_name,
                    'flag_name' => $flag_name,
                    'flag_value' => $flag_value,
                ))
                ->execute();
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    private function inUserCriticalSection()
    {
        $lockname = 'raptor.' . $this->m_uid . '.' . $this->m_sGroupName;
        try
        {
            $sSQL = "SELECT IS_FREE_LOCK('$lockname')";
            $result = db_query($sSQL);
            $lock = $result->fetchColumn(0);
            return $lock == '1';
        } catch (\Exception $ex) {
            error_log("Trouble in inUserCriticalSection($lockname)>>>".print_r($ex,TRUE));
            throw $ex;
        }
    }
    
    private function startUserCriticalSection()
    {
        $lockname = 'raptor.' . $this->m_uid . '.' . $this->m_sGroupName;
        try
        {
            $sSQL = "SELECT GET_LOCK('$lockname', 2)";  //Timeout after 2 seconds
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
        } catch (\Exception $ex) {
            //Do NOT throw the exception, just log it.
            error_log("Trouble in endUserCriticalSection($lockname)>>>".print_r($ex,TRUE));
        }
    }
    
    /**
     * Add the result data to the cache.
     */
    public function addToCache($sThisResultName,$aResult,$nMaxDataAgeSeconds=600)
    {
        $this->updateRaptorCacheData(
            5
            ,$nMaxDataAgeSeconds
            ,$sThisResultName
            ,$aResult);        
    }
    
    /**
     * @return NULL if not found in cache, else the result from the cache.
     */
    public function checkCache($sThisResultName)
    {
        //See if we are already building a cache.
        $retry_seconds = $this->getCacheBuildingRetrySeconds($sThisResultName);
        while($retry_seconds > 0)
        {
            sleep($retry_seconds);
            $retry_seconds = $this->getCacheBuildingRetrySeconds($sThisResultName);
        }
        $foundinfo = $this->getRaptorCacheDataInfo($sThisResultName);
        if(isset($foundinfo['item_data']))
        {
            return unserialize($foundinfo['item_data']);
        }
        return NULL;
    }
}
