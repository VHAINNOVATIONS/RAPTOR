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

require_once 'AReport.php';


/**
 * This class returns the rules report
 *
 * @author Frank Font of SAN Business Consultants
 */
class ViewReportContraindicationRules extends AReport
{

    public function getName() 
    {
        return 'Contraindication Rules';
    }

    public function getDescription() 
    {
        return 'Shows contraindication rules';
    }

    public function getRequiredPrivileges() 
    {
        $aRequire = array();    //Everybody can run this
        return $aRequire;
    }
    
    public function getMenuKey() 
    {
        return 'raptor/show_ci_rules';
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
        $oSREContext = $oCIE->getSREContext();
        $basereploaded = module_load_include('php','simplerulesengine_ui','report/ReportRules');
        $aURLS = array('return'=>'raptor/viewReports');
        $oReport = new \simplerulesengine\ReportRules($oSREContext, NULL);
        $form = $oReport->getForm($form, $form_state, $disabled, $myvalues);

        
        $form['data_entry_area1']['top_blurb'] 
                = array(
            '#markup' => "\n<p>Contraindication warnings are displayed to users at runtime when the rule formula evaluates to a value of True.  Formulas are built by using simple boolean logic on available boolean inputs.</p>"
                    . "\n<p>The rule formulas can be edited by RAPTOR users that have sufficient priviledges.</p>",
        );
        
        
        $form['data_entry_area1']['action_buttons'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="raptor-action-buttons">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );

        $form['data_entry_area1']['action_buttons']['refresh'] = array('#type' => 'submit'
                , '#attributes' => array('class' => array('admin-action-button'), 'id' => 'refresh-report')
                , '#value' => t('Refresh Report'));
        
        $form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => '<input class="admin-cancel-button" type="button" value="Exit" data-redirect="/drupal/worklist?dialog=viewReports">');
        
        return $form;
    }
}
