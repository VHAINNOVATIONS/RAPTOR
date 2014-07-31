<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 */

namespace raptor;

require_once(dirname(__FILE__) . '/../config/choices.php');
require_once('FormHelper.php');
module_load_include('inc', 'raptor_datalayer', 'core/data_user.php');

/**
 * Helper for pages that read/write delimited raw text into tables.
 *
 * @author FrankWin7VM
 */
class ListsPageHelper
{
    public function parseRawText($sRawDelimitedRowsText)
    {
        return explode("\n", $sRawDelimitedRowsText);
    }
    
    /**
     * Return array with parsed content and error details.
     * @param type $aRows input rows of delimited data items
     * @param type $aRequiredCols array with TRUE for each column that requires a value
     * @param type $aMaxLenCols array with max len for value at each column position
     * @param type $aDataTypeCols array t=text, n=numeric
     * @return array with 'errors' and 'parsedrows' keys.
     */
    public function parseRows($aRows, $aRequiredCols, $aMaxLenCols, $aDataTypeCols)
    {
        $result = array();
        $parsed = array();
        $errors = array();
        $expectedColCount = count($aRequiredCols);  //Even if col is not required, we expect fixed number of delimiters
        $nRowNumber = 0;
        foreach($aRows as $row)
        {
            $nRowNumber++;
            $row = trim($row);
            if($row !== '')
            {
                $items = explode('|',$row);
                $nCols = count($items);
                if($nCols < $expectedColCount)
                {
                    $errors[] = 'Too few delimiters on row ' . $nRowNumber . ": Found $nCols but expected $expectedColCount";
                } else if($nCols > $expectedColCount) {
                    $errors[] = 'Too many delimiters on row ' . $nRowNumber . ": Found $nCols but expected $expectedColCount";
                } else {
                    $nColOffset = 0;
                    $colerrors = 0;
                    foreach($items as $item)
                    {
                        $itemlen = strlen(trim($item));
                        $maxlen = $aMaxLenCols[$nColOffset];
                        $fieldtype = $aDataTypeCols[$nColOffset];
                        if($itemlen < 1 && $aRequiredCols[$nColOffset])
                        {
                            $errors[] = 'Missing required value in column '.($nColOffset + 1).' on row ' . $nRowNumber;
                            $colerrors++;
                        } else if($maxlen < $itemlen) {
                            $errors[] = 'Value "'.$item.'" in column '.($nColOffset + 1).' on row ' . $nRowNumber . " should be a number.";
                            $colerrors++;
                        } else if($fieldtype == 'n' && !is_numeric($item)) {
                            
                        }
                        $nColOffset++;
                    }
                    if($colerrors == 0)
                    {
                        $parsed[] = $items;
                    }
                }
            }
        }
        $result['errors'] = $errors;
        $result['parsedrows'] = $parsed;
        return $result;
    }
    
    public function writeValues($tablename, $aFieldNames, $aParsedRows)
    {
        
        //Delete all the existing rows.
        $nDeleted = db_delete($tablename)
                ->execute();
        
        //Now write all the rows.
        foreach($aParsedRows as $aParsedRow)
        {
            try
            {
                $fields = array();
                $nColOffset = 0;
                foreach($aFieldNames as $sFieldName)
                {
                    $fields[$sFieldName] = trim($aParsedRow[$nColOffset]);
                    $nColOffset++;
                }
                $nAdded = db_insert($tablename)
                        ->fields($fields)
                        ->execute();
            } catch (\Exception $e) {
                //Continue
            }
        }
        return count($aParsedRows);
    }

    public function getFieldValues($tablename, $aFieldNames, $aOrderBy)
    {
        
        $sSQLFields = '';
        $col = 0;
        foreach($aFieldNames as $sFieldName)
        {
            $col++;
            if($col > 1)
            {
                $sSQLFields .= ",";
            }
            $sSQLFields .= "`$sFieldName`";
        }
        
        $sSQL = 'SELECT ' . $sSQLFields . ' '
                . ' FROM `' . $tablename . '` '
                . ' ORDER BY '. implode(',',$aOrderBy) .'';
        $result = db_query($sSQL);
        $delimitedrows = array();
        if($result->rowCount()==0)
        {
            error_log('Did NOT find any '.$tablename.' options!');
        } else {
            foreach($result as $record) 
            {
                $delimitedrow='';
                $col=0;
                foreach($record as $fieldvalue)
                {
                    $col++;
                    if($col > 1)
                    {
                        $delimitedrow .= '|';
                    }
                    $delimitedrow .= $fieldvalue;
                };
                $delimitedrows[] = $delimitedrow;
            }
        }
        $sFormattedListText = $this->formatListText($delimitedrows);
        $myvalues = array();
        $myvalues['raw_list_rows'] = $sFormattedListText;
        
        return $myvalues;
    }
    
    /**
     * @return array of all option values for the form
     */
    public function getAllOptions($tablename, $aFieldNames, $aOrderBy)
    {
        $aOptions = array();
        return $aOptions;
    }

    public function formatListText($aRows)
    {
        if(!isset($aRows))
        {
            $sFormatted = '';
        } else {
            $sFormatted = FormHelper::getArrayItemsAsDelimitedText($aRows, "\n");
        }
        return $sFormatted;
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    public function getForm($form, &$form_state, $disabled, $myvalues, $aHelpText)
    {

        $form['data_entry_area1'] = array(
            '#prefix' => "\n<section class='user-profile-dataentry'>\n",
            '#suffix' => "\n</section>\n",
            '#disabled' => $disabled,
        );

        $form['data_entry_area1']['instructions']['basic'] = array(
            '#markup'         => "<h3>Advanced Data Entry Mode</h3><p>Enter one row per item for the list.  Use the | symbol as a delmiter between fields on each row.</p>",
        );        
        
        $form['data_entry_area1']['raw_list_rows'] = array(
            '#title'         => t('List of options'),
            '#maxlength'     => 4096, 
            '#cols'          => 100, 
            '#rows'          => 35,            
            '#description'   => t('One delimited row per list item.'),
            '#type'          => 'textarea',
            '#disabled'      => $disabled,
            '#default_value' => $myvalues['raw_list_rows'], 
        );        
        
        $form['data_entry_area1']['instructions'][] = array(
            '#markup'         => "<h4>Row Format</h4>",
        );        
        
        $helpcols = implode(' | ', $aHelpText);
        $form['data_entry_area1']['instructions'][] = array(
            '#markup'         => "<p>$helpcols<p>",
        );        
        $form['data_entry_area1']['instructions'][] = array(
            '#markup'         => "<p>Note: 0 = No, 1 = Yes.<p>",
        );        
        
       $form['data_entry_area1']['action_buttons'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="raptor-action-buttons">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );
        $form['data_entry_area1']['action_buttons']['create'] = array('#type' => 'submit'
                , '#attributes' => array('class' => array('admin-action-button'))
                , '#value' => t('Save the Data')
                , '#disabled' => $disabled
        );
        $form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => '<input class="admin-cancel-button" type="button" value="Cancel" data-redirect="/drupal/worklist?dialog=managelists">');
        
        return $form;
    }
}
