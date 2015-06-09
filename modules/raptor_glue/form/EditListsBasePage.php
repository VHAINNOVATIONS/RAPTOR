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

module_load_include('php', 'raptor_datalayer', 'config/Choices');
require_once 'FormHelper.php';
require_once 'ListsPageHelper.php';

/**
 * This class returns the base page for editing a simple keword list
 *
 * @author Frank Font of SAN Business Consultants
 */
class EditListsBasePage
{

    private $m_oPageHelper      = NULL;
    private $m_sTablename       = NULL;
    private $m_aFieldNames      = NULL;
    private $m_aRequiredCols    = NULL;
    private $m_aMaxLenCols      = NULL;
    private $m_aDataTypeCols    = NULL;
    private $m_aHelpText        = NULL;
    private $m_aOrderBy         = NULL;    
    private $m_sListName        = NULL;
    private $m_sName            = NULL;
    private $m_sDescription     = NULL;
    private $m_aRequiredPrivs   = NULL;
    private $m_bUserCanEdit     = NULL;
    private $m_sURL             = NULL;
    private $mycount = 0;
    
    function __construct($sTablename
            ,$aFieldNames=array('keyword')
            ,$aRequiredCols=array(TRUE)
            ,$aDataTypeCols=array('t')
            ,$aMaxLenCols=array(40)
            ,$aHelpText=array('Keyword')
            ,$aOrderBy=array('keyword')
            ,$required_privs=NULL)
    {
        $this->m_sTablename = $sTablename;
        $this->m_aFieldNames = $aFieldNames;
        $this->m_aHelpText = $aHelpText;
        $this->m_aOrderBy = $aOrderBy;
        $this->m_aRequiredCols = $aRequiredCols;
        $this->m_aDataTypeCols = $aDataTypeCols;
        $this->m_aMaxLenCols = $aMaxLenCols;
        if($required_privs !== NULL)
        {
            $this->m_aRequiredPrivs = $required_privs;
            $oContext = \raptor\Context::getInstance();
            $oUserInfo = $oContext->getUserInfo();
            $userprivs = $oUserInfo->getSystemPrivileges();
            $this->m_bUserCanEdit = $this->canModify($userprivs);
        }
        
        $this->m_oPageHelper = new \raptor\ListsPageHelper();
    }

    public function setRequiredPrivs($privs)
    {
        $this->m_aRequiredPrivs = $privs;
    }
    
    public function getRequiredPrivs()
    {
        return $this->m_aRequiredPrivs;
    }

    public function setName($name)
    {
        $this->m_sName = $name;
    }
    
    public function getName()
    {
        return $this->m_sName;
    }
    
    public function setDescription($description)
    {
        $this->m_sDescription = $description;
    }
    
    public function getDescription()
    {
        return $this->m_sDescription;
    }
    
    public function setURL($url)
    {
        $this->m_sURL = $url;
    }
    
    public function getURL()
    {
        return $this->m_sURL;
    }

    /**
     * Returns TRUE if the user is allowed to modify the list, else FALSE.
     */
    public function canModify($userprivsoverride=NULL)
    {
        if($this->m_aRequiredPrivs == NULL)
        {
            throw new \Exception('Did not declare the required user privileges for this page!');
        }
        if($userprivsoverride == NULL)
        {
            if($this->m_bUserCanEdit == NULL)
            {
                //Figure it out now.
                $oContext = \raptor\Context::getInstance();
                $oUserInfo = $oContext->getUserInfo();
                $userprivs = $oUserInfo->getSystemPrivileges();
                $this->m_bUserCanEdit = $this->canModify($userprivs);
            }
            //Return the value we already computed.
            return $this->m_bUserCanEdit;
        } else {
            //Check specific privs provided.
            foreach($this->m_aRequiredPrivs as $key=>$value)
            {
                if($value > 0)
                {
                    //User MUST have this privilege.
                    if(!key_exists($key, $userprivsoverride) || $userprivsoverride[$key] != 1 )
                    {
                        return FALSE;
                    }
                }
            }
            return TRUE;
        }
    }
    
    public function setListName($sListName)
    {
        $this->m_sListName = $sListName;
    }
    
    public function hasBooleanInput()
    {
        foreach($this->m_aDataTypeCols as $datatype)
        {
            if($datatype == 'b')
            {
               return TRUE; 
            }
        }
        return FALSE;
    }
    
    public function getListName()
    {
        if($this->m_sListName === NULL)
        {
            return $this->m_sTablename;
        }
        return $this->m_sListName;
    }
    
    /**
     * Get the values to populate the form.
     * @return type result of the queries as an array
     */
    function getFieldValues()
    {
        $this->mycount++;
        
        $tablename = $this->m_sTablename;
        $aFieldNames = $this->m_aFieldNames;
        $aOrderBy = $this->m_aOrderBy;
        $myvalues = $this->m_oPageHelper->getFieldValues($tablename, $aFieldNames, $aOrderBy);
        $myvalues['formmode'] = 'E';
        
        return $myvalues;
    }

    
    function looksValid($myvalues)
    {
        if(!isset($myvalues['raw_list_rows']))
        {
            throw new \Exception("Cannot update user record because missing raw_list_rows in array!\n" . print_r($myvalues,TRUE));
        }

        $aRequiredCols = $this->m_aRequiredCols;
        $aMaxLenCols = $this->m_aMaxLenCols;
        $aDataTypeCols = $this->m_aDataTypeCols;
        
        $aRawRows = $this->m_oPageHelper->parseRawText($myvalues['raw_list_rows']);
        $result = $this->m_oPageHelper->parseRows($aRawRows, $aRequiredCols, $aMaxLenCols, $aDataTypeCols);
        
        $errors = $result['errors'];
        if(count($errors) > 0)
        {
            if(count($errors) > 1)
            {
                form_set_error("raw_list_rows",'<ol><li>'.implode('<li>', $errors).'</ol>');
            } else {
                form_set_error("raw_list_rows",$errors[0]);
            }
            $bGood = FALSE;
        } else {
            $bGood = TRUE;
        }
        return $bGood;
    }
    
    /**
     * Write the values into the database.
     * Return 1 if all okay, else return 0.
     */
    function updateDatabase($myvalues)
    {
        if(!isset($myvalues['raw_list_rows']))
        {
            throw new \Exception("Cannot update user record because missing raw_list_rows in array!\n" . print_r($myvalues,TRUE));
        }
        if(!$this->canModify())
        {
            throw new \Exception("This user is NOT allowed to modify the {$this->m_sTablename} list!");
        }

        $tablename = $this->m_sTablename;
        $aFieldNames = $this->m_aFieldNames;
        $aRequiredCols = $this->m_aRequiredCols;
        $aMaxLenCols = $this->m_aMaxLenCols;
        $aDataTypeCols = $this->m_aDataTypeCols;
        
        $aRawRows = $this->m_oPageHelper->parseRawText($myvalues['raw_list_rows']);
        $result = $this->m_oPageHelper->parseRows($aRawRows, $aRequiredCols, $aMaxLenCols, $aDataTypeCols);
        
        $paddedln = $this->getListName(); 
        if($paddedln != '')
        {   
            //Allows for empty name!
            $paddedln = ' '.$paddedln;
        }
        
        $errors = $result['errors'];
        if(count($errors) > 0)
        {
            drupal_set_message('Failed to update the'.$paddedln.' list because '
                    .'<ol><li>'.implode('<li>', $errors).'</ol>','error');
            return FALSE;
        } else {
            $nRows = $this->m_oPageHelper->writeValues($tablename, $aFieldNames, $result['parsedrows']);
            drupal_set_message('Successfully updated'.$paddedln.' list');
            return TRUE;
        }
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues)
    {
        if(!$this->canModify())
        {
            throw new \Exception("This user is NOT allowed to get form because cannot modify the {$this->m_sTablename} list!");
        }
        $form = $this->m_oPageHelper->getForm($form, $form_state
                , $disabled
                , $myvalues
                , $this->m_aHelpText
                , $this->m_aDataTypeCols
                , $this->m_aMaxLenCols);
        return $form;
    }
}
