<?php
/**
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

abstract class AReport 
{
    private $m_required_privs = NULL;
    private $m_menukey = NULL;
    private $m_name = NULL;

    function __construct($required_privs,$menukey,$reportname)
    {
        $this->m_required_privs = $required_privs;
        $this->m_name = $reportname;
        $this->m_menukey = $menukey;
    }
            
    /**
     * Return the report name
     */
    public function getName()
    {
        return $this->m_name;
    }
    
    /**
     * Return the array of privs required to run this report
     */
    public function getRequiredPrivileges() 
    {
        return $this->m_required_privs;
    }
    
    /**
     * Return the menu key for this report
     */
    public function getMenuKey() 
    {
        return $this->m_menukey;
    }

    /**
     * Return the report description
     */
    abstract function getDescription();
    
    /**
     * Some reports return initial values from this function.
     */
    function getFieldValues()
    {
        return array();
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    abstract function getForm($form, &$form_state, $disabled, $myvalues);
    
    /**
     * Determine if a user has the right privs for running a report.
     * @param array $aCandidatePrivs the user privs
     * @return boolean TRUE if the requirements are satisfied for this user to run the report
     */
    public function hasRequiredPrivileges($aCandidatePrivs)
    {
        try
        {
            $aRequired = $this->getRequiredPrivileges();
            if(count($aRequired) > 0)
            {
                foreach($aRequired as $key => $value)
                {
                    if($aCandidatePrivs[$key] != $value)
                    {
                        return FALSE;
                    }
                }
            }
        } catch (\Exception $ex) {
            throw new \Exception('Unable to check privs for "'.$this->getName().'" because '.$ex->getMessage(),99901,$ex);
        }
        return TRUE;
    }
    
    function getExitButtonMarkup($goback='',$label='Exit')
    {
        if($goback == '')
        {
            $markup = array('#type' => 'item'
                    , '#markup' => '<a class="admin-cancel-button" href="#">'.$label.'</a>');
        } else {
            $markup = array('#type' => 'item'
                    , '#markup' => '<a class="admin-cancel-button" href="'.$goback.'">'.$label.'</a>');
        }
        return $markup;
    }
}
