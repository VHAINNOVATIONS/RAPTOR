<?php
/**
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

require_once('AReport.php');

/**
 * This class returns the Admin Information input content
 *
 * @author Frank Font of SAN Business Consultants
 */
class ViewReportContraindicationInputs extends AReport
{

    public function getName() 
    {
        return 'Contraindication Inputs';
    }

    public function getDescription() 
    {
        return 'Shows contraindication inputs';
    }

    public function getRequiredPrivileges() 
    {
        $aRequire = array();    //Everybody can run this
        return $aRequire;
    }
    
    public function getMenuKey() 
    {
        return 'raptor/show_ci_inputs';
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
        $form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => '<input class="admin-cancel-button" type="button"'
                . ' value="Cancel"'
                . ' data-redirect="'.$goback.'">');
        
        return $form;
    }
}
