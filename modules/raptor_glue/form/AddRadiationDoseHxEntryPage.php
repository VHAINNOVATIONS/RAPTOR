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

module_load_include('php', 'raptor_datalayer', 'config/Choices');
module_load_include('php', 'raptor_datalayer', 'core/data_worklist');
module_load_include('php', 'raptor_datalayer', 'core/data_dashboard');

require_once 'FormHelper.php';

/**
 * Implementes the radation dose add page.
 *
 * @author Frank Font of SAN Business Consultants
 */
class AddRadiationDoseHxEntryPage
{
    private $m_oContext = NULL;
    private $m_nUID = NULL;
    private $m_oDD = NULL;

    function __construct()
    {
        $this->m_oContext = \raptor\Context::getInstance();
        $this->m_oDD = new \raptor\DashboardData($this->m_oContext);
    }

    /**
     * Get the values to populate the form.
     * @return type result of the queries as an array
     */
    function getFieldValues()
    {
        //TODO
        $myvalues = array();
        return $myvalues;
    }
    
    /**
     * Some checks to validate the data before we try to save it.
     */
    function looksValid($form, $myvalues)
    {
        $bGood = TRUE;
        //TODO - special checks here
        if(strlen(trim($myvalues['dose'])) < 1)
        {
            drupal_set_message('Must provide a dose value','error');
            //throw new \Exception('blah blah'.$myvalues['dose']);
            $bGood = FALSE;
        }
        
        return $bGood;
    }
    
    /**
     * Write the values into the database.
     * Throws exception if not okay
     */
    function updateDatabase($form, $myvalues)
    {
        $nSiteID = $this->m_oContext->getSiteID();
        $sTrackingID = $this->m_oContext->getSelectedTrackingID();
        $aParts = explode('-',$sTrackingID);    //Allow for older type ticket format
        if(count($aParts) == 1)
        {
            $nIEN = $aParts[0];
        } else {
            $nIEN = $aParts[1];
        }
        $nUID = $this->m_oContext->getUID();
        $updated_dt = date("Y-m-d H:i:s", time());

        //TODO
        return TRUE;
        
        $radiation_dose_tx = '1.88,2.99,3,88.1,99.2';
        $uom = 'MyUOM';
        $dose_dt = $updated_dt;
        $dose_type_cd = 'E';
        $dose_source_cd = 'E';
        $data_provider = 'testing add';
        
        try
        {
            $this->writeRadiationDoseDetails($nSiteID, $nIEN, $nPatientID, $nUID
                , $radiation_dose_tx
                , $uom
                , $dose_dt, $dose_type_cd, $dose_source_cd
                , $data_provider
                , $updated_dt);
        } catch (\Exception $ex) {
            throw $ex;
        }
        
        return TRUE;
    }
    

    private function writeRadiationDoseDetails($nSiteID, $nIEN, $nPatientID, $nUID
            , $radiation_dose_tx
            , $uom
            , $dose_dt, $dose_type_cd, $dose_source_cd
            , $data_provider
            , $updated_dt)
    {
        if($radiation_dose_tx != '')
        {
            $dose_values = explode(',', $radiation_dose_tx);
            $sequence_num = 0;
            foreach($dose_values as $dose)
            {
                $sequence_num++;
                try
                {
                    $oInsert = db_insert('raptor_ticket_exam_radiation_dose')
                            ->fields(array(
                                    'siteid' => $nSiteID,
                                    'IEN' => $nIEN,
                                    'patientid' => $nPatientID,

                                    'sequence_position' => $sequence_num,
                                    'dose' => $dose,
                                    'uom' => $uom,

                                    'dose_dt' => $dose_dt,
                                    'dose_type_cd' => $dose_type_cd,
                                    'dose_source_cd' => $dose_source_cd,
                                    'data_provider' => $data_provider,

                                    'author_uid' => $nUID,
                                    'created_dt' => $updated_dt,
                            ))
                            ->execute();
                } catch (\Exception $ex) {
                        error_log('Failed to create raptor_ticket_exam_radiation_dose: ' . print_r($ex,TRUE));
                        drupal_set_message('Failed to save exam dose information for this ticket because ' . $ex->getMessage(),'error');
                        $bSuccess = FALSE;
                }
            }
        }
    }
    
    
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    public function getForm($form, &$form_state, $disabled, $myvalues)
    {

        $form['data_entry_area1'] = array(
            '#prefix' => "\n<section class='user-profile-dataentry'>\n",
            '#suffix' => "\n</section>\n",
            '#disabled' => $disabled,
        );

        //Demo a field
        $form['data_entry_area1']['toppart']['dose'] = array(
          '#type' => 'textfield', 
          '#title' => t('Dose'), 
          '#default_value' => '', 
          '#size' => 10, 
          '#disabled' => FALSE,
        );        

        
        //TODO
        
        $form['data_entry_area1']['toppart']['placeholder'] = array(
            '#markup'         => '<p>'.t('PLACEHOLDER FOR THE ADD FORM').'</p>',
        );
        
        $form['data_entry_area1']['action_buttons'] = array(
            '#prefix' => "\n<section class='raptor-action-buttons'>\n",
            '#suffix' => "\n</section>\n",
            '#disabled' => $disabled,
        );
        $form['data_entry_area1']['action_buttons']['add'] = array('#type' => 'submit'
                , '#attributes' => array('class' => array('admin-action-button'))
                , '#value' => t('Add This Radiation Dose Entry')
                , '#disabled' => $disabled
        );
        $form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => '<input class="raptor-dialog-cancel" type="button" value="Cancel">');
        
        return $form;
    }
}

