<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 */

namespace raptor;

require_once(dirname(__FILE__) . '/../config/choices.php');
require_once("FormHelper.php");
require_once('ListsPageHelper.php');

/**
 * This class returns the Admin Information input content
 *
 * @author FrankWin7VM
 */
class EditListRadioisotopePage
{

    private $m_oPageHelper      = null;
    private $m_sTablename       = 'raptor_list_radioisotope';
    private $m_aFieldNames      = array('type_nm','option_tx','ct_yn','mr_yn','nm_yn');
    private $m_aRequiredCols    = array(true,       true,   true,   true,    true);
    private $m_aMaxLenCols      = array(8,          100,    1,      1,       1);
    private $m_aDataTypeCols    = array('t',        't',    'n',    'n',     't');
    private $m_aHelpText        = array('Category','Radioisotope Text','Applies to CT?','Applies to MR?','Applies to NM?');
    private $m_aOrderBy         = array('type_nm','option_tx');    
    
    private $mycount = 0;
    
     //Call same function as in EditUserPage here!
    function __construct()
    {
        $this->m_oPageHelper = new \raptor\ListsPageHelper();
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
        
        //die('look at the values' . print_r($myvalues,true));
        return $myvalues;
    }

    /**
     * Write the values into the database.
     * Return 1 if all okay, else return 0.
     */
    function updateDatabase($myvalues)
    {
        if(!isset($myvalues['raw_list_rows']))
        {
            die("Cannot update user record because missing raw_list_rows in array!\n" . var_dump($myvalues));
        }

        $tablename = $this->m_sTablename;
        $aFieldNames = $this->m_aFieldNames;
        $aOrderBy = $this->m_aOrderBy;
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
            /*
            foreach($errors as $error)
            {
                form_set_error("raw_list_rows",$error);
            }
             * 
             */
            return 0;
        } else {
            $nRows = $this->m_oPageHelper->writeValues($tablename, $aFieldNames, $result['parsedrows']);
            return 1;
        }
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues)
    {
        $form = $this->m_oPageHelper->getForm($form, $form_state, $disabled, $myvalues, $this->m_aHelpText);
        return $form;
    }
}
