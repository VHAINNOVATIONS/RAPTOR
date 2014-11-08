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

require_once ("data_utility.php");
require_once ("data_user.php");

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
        return $this->m_aUserMap[$nUID];
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
