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
 */

namespace raptor;

require_once 'AReport.php';

/**
 * This class returns the Admin Information input content
 *
 * @author Frank Font of SAN Business Consultants
 */
class ViewReportContraindicationInputs extends AReport
{
    private static $reqprivs = array();
    private static $menukey = 'raptor/show_ci_inputs';
    private static $reportname = 'Contraindication Inputs';

    function __construct()
    {
        parent::__construct(self::$reqprivs, self::$menukey, self::$reportname);
    }

    public function getDescription() 
    {
        return 'Shows contraindication inputs';
    }

    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues)
    {
        $loaded = module_load_include('inc','raptor_contraindications','core/ContraIndEngine');
        if(!$loaded)
        {
            $msg = 'Failed to load the Contraindication Engine';
            throw new \Exception($msg);      //This is fatal, so stop everything now.
        }

        $oCIE = new \raptor\ContraIndEngine(NULL);
        
        $form["data_entry_area1"] = array(
            '#prefix' => "\n<section class='raptor-report'>\n",
            '#suffix' => "\n</section>\n",
        );
        $form['data_entry_area1']['blurb']['p2'] = array('#type' => 'item'
                , '#markup' => '<p>The flags and measures listed as "database" are configured in the database.  The items listed as "coded" are implemented by programmers in the RAPTOR codebase.  The "database" configured flags and measures can be customized for a deployed site, the "coded" items cannot.</p>');
        $form['data_entry_area1']['blurb']['p3'] = array('#type' => 'item'
                , '#markup' => '<p>Both the flags and the measures listed here can be used in flag formulas, but only boolean items can be used in Rule formulas.  Boolean items are shown in this report as having value type "boolean".  A boolean flag can have one of three values "True","False", or "Null".  A "Null" value occurs when input criteria for a flag or measure is not available or is unknown at evaluation time.</p>');
        $form["data_entry_area1"]['table_container'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="raptor-dialog-table-container">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );

        $rows = "\n";
        $aAllInputs = $oCIE->getSREContext()->getDictionary()->getActiveRuleInputs();
        foreach($aAllInputs as $aInputs)
        {
            $sLocked = isset($aInputs['locked']) && $aInputs['locked'] == 1 ? 'Yes' : 'No';
            $return_type = $aInputs['return'];
            if($return_type > '')
            {
                $rtparts = explode(' ',$return_type);
                $rtclassmarkup = ' class="'.$rtparts[0].'-measure" ';
            } else {
                $rtclassmarkup = '';
            }
            $rows   .= "\n".'<tr>'
                    . '<td>'.$aInputs['source'].'</td>'
                    . '<td>'.$sLocked.'</td>'
                    . '<td><span '.$rtclassmarkup.'>'.$return_type.'</span></td>'
                    . '<td>'.$aInputs['category_nm'].'</td>'
                    . '<td>'.$aInputs['name'].'</td>'
                    . '<td>'.$aInputs['purpose_tx'].'</td>'
                    . '<td>'.$aInputs['criteria_tx'].'</td>'
                    .'</tr>';
        }

        $form["data_entry_area1"]['table_container']['users'] = array('#type' => 'item',
                 '#markup' => '<table id="my-raptor-dialog-table" class="raptor-dialog-table dataTable">'
                            . '<thead><tr>'
                            . '<th>Implementation Location</th>'
                            . '<th>Read Only</th>'
                            . '<th>Value Type</th>'
                            . '<th>Category Date</th>'
                            . '<th>Name</th>'
                            . '<th>Purpose</th>'
                            . '<th>Formula</th>'
                            . '</tr>'
                            . '</thead>'
                            . '<tbody>'
                            . $rows
                            .  '</tbody>'
                            . '</table>');
        
       $form['data_entry_area1']['action_buttons'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="raptor-action-buttons">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );

        $form['data_entry_area1']['action_buttons']['refresh'] = array('#type' => 'submit'
                , '#attributes' => array('class' => array('admin-action-button'), 'id' => 'refresh-report')
                , '#value' => t('Refresh Report'));

        global $base_url;
        $goback = $base_url . '/raptor/viewReports';
        /*
        $form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => '<input class="admin-cancel-button" type="button"'
                . ' value="Cancel"'
                . ' data-redirect="'.$goback.'">');
        */
        $form['data_entry_area1']['action_buttons']['cancel'] = $this->getExitButtonMarkup($goback);
        return $form;
    }
}
