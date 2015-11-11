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
 * Copyright 2015 SAN Business Consultants, a Maryland USA company (sanbusinessconsultants.com)
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ------------------------------------------------------------------------------------
 * 
 */ 

namespace raptor;

require_once 'UserInfo.php';

/**
 * The context has all the details relevant to a user
 *
 * @author Frank Font of SAN Business Consultants
 */
class AllUsers 
{
    
    function __construct()
    {
        $this->m_aUserMap = NULL;
    }
    
    public function getByUID($nUID)
    {
        $this->loadMap();
        if(isset($this->m_aUserMap[$nUID]))
        {
            try
            {
                return $this->m_aUserMap[$nUID];
            } catch (\Exception $ex) {
                error_log('Did NOT find UID='.$nUID.' because '.$ex->getMessage());
                return NULL;
            }
        } else {
            error_log('Did NOT find UID='.$nUID.' in map! '.print_r($this->m_aUserMap,TRUE));
            return NULL;
        }
    }
    
    private function loadMap()
    {
        if($this->m_aUserMap == NULL)
        {
            try
            {
                //Read the values from the database.
                $this->m_aUserMap = array();
                $sSQL = 'SELECT uid FROM raptor_user_profile';
                $result = db_query($sSQL);
                foreach($result as $item) 
                {
                    $this->m_aUserMap[$item->uid] = new \raptor\UserInfo($item->uid);    
                }
            } catch (\Exception $ex) {
                throw new \Exception('Unable to read the user information because '.$ex);
            }
        }
    }
    
}
