<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 */

namespace raptor;

require_once(dirname(__FILE__) . '/../config/choices.php');
require_once("FormHelper.php");
require_once("ProtocolLibPageHelper.php");

/**
 * This class returns the Admin Information input content
 *
 * @author FrankWin7VM
 */
class AddProtocolLibPage
{
    private $m_oPageHelper = null;
    
     //Call same function as in EditUserPage here!
    function __construct()
    {
        $this->m_oPageHelper = new \raptor\ProtocolLibPageHelper();
    }

    /**
     * Get the values to populate the form.
     * @return type result of the queries as an array
     */
    function getFieldValues()
    {
        $myvalues = $this->m_oPageHelper->getFieldValues(null);
        $myvalues['DefaultValues'] = null;
        $myvalues['protocol_shortname'] = null;
        $myvalues['name'] = null;
        $myvalues['version'] = 1;
        $myvalues['modality_abbr'] = null;
        $myvalues['active_yn'] = 1;
        $myvalues['service_nm'] = null;
        $myvalues['lowerbound_weight'] = null;
        $myvalues['upperbound_weight'] = null;
        $myvalues['image_guided_yn'] = 0;
        $myvalues['contrast_yn'] = 0;
        $myvalues['radioisotope_yn'] = 0;
        $myvalues['sedation_yn'] = 0;
        $myvalues['yn_attribs'] = array('IG'=>$myvalues['image_guided_yn'],'C'=>$myvalues['contrast_yn'],'RI'=>$myvalues['radioisotope_yn'],'S'=>$myvalues['sedation_yn']);
        $myvalues['filename'] = null;
        $myvalues['protocolnotes_tx'] = NULL;        
        return $myvalues;
    }

    /**
     * Validate the proposed values.
     * @param type $form
     * @param type $myvalues
     * @return true if no validation errors detected
     */
    function looksValid($form, $myvalues)
    {
        return $this->m_oPageHelper->looksValid($form, $myvalues, 'A');
    }
    
    /**
     * Write the values into the database.
     */
    function updateDatabase($form, $myvalues)
    {
        $bHappy = TRUE; //Assume no problems.

        //drupal_set_message('>>>myvalues>>>'.print_r($myvalues,TRUE));

        //Perform some data quality checks now.
        if(!isset($myvalues['protocol_shortname']) || trim($myvalues['protocol_shortname']) == '')
        {
            die("Cannot insert record because missing protocol_shortname in array!\n" . var_dump($myvalues));
        }

        $updated_dt = date("Y-m-d H:i", time());
        $protocol_shortname = $myvalues['protocol_shortname'];

      try
      {



        // $myvalues[yn_attribs] => Array ( [IG] => IG [C] => C [RI] => RI [S] => S )
        $yn_attribs = isset($myvalues['yn_attribs']) ? $myvalues['yn_attribs'] : array();
        $contrast_yn = (isset($yn_attribs['C']) && $yn_attribs['C'] === 'C') ? 1 : 0;
        $image_guided_yn = (isset($yn_attribs['IG']) && $yn_attribs['IG'] === 'IG') ? 1 : 0;
        $radioisotope_yn = (isset($yn_attribs['RI']) && $yn_attribs['RI'] === 'RI') ? 1 : 0;
        $sedation_yn = (isset($yn_attribs['S']) && $yn_attribs['S'] === 'S') ? 1 : 0;
        $multievent_yn = 0;

        //drupal_set_message('>>>hydration_iv_tx>>>['.$myvalues['hydration_iv_id'].']vs['.$myvalues['hydration_iv_customtx'].']');

        $lbw = (isset($myvalues['lowerbound_weight']) && is_numeric($myvalues['lowerbound_weight']) ? $myvalues['lowerbound_weight'] : 0);
        $ubw = (isset($myvalues['upperbound_weight']) && is_numeric($myvalues['upperbound_weight']) ? $myvalues['upperbound_weight'] : 0);
        db_insert('raptor_protocol_lib')
          ->fields(array(
            'protocol_shortname' => $protocol_shortname,
            'name' => $myvalues['name'],
            'version' => $myvalues['version'],    
            'modality_abbr' => $myvalues['modality_abbr'],
            'service_nm' => '', //hardcoded as empty string always for now $myvalues['service_nm'],
            'lowerbound_weight' => $lbw,
            'upperbound_weight' => $ubw,
            'image_guided_yn' => $image_guided_yn,     
            'sedation_yn' => $sedation_yn,         
            'contrast_yn' => $contrast_yn,         
            'radioisotope_yn' => $radioisotope_yn,         
            'multievent_yn' => $multievent_yn,         
            'filename' => (isset($myvalues['filename']) ? $myvalues['filename'] : 'no-filename'),
            'active_yn' => (isset($myvalues['active_yn']) ? 1 : 0),
            'updated_dt' => $updated_dt,
            ))
              ->execute(); 

            //Now write all the child records.
            $bHappy = $this->m_oPageHelper->writeChildRecords($protocol_shortname, $myvalues);
      }
      catch(\Exception $ex)
      {
        error_log("code=".$ex->getCode()."\nFailed to add protocol into database \n" . print_r($myvalues, TRUE) . '>>>'. print_r($ex, TRUE));
        drupal_set_message('Failed to add the new protocol because ' . $ex, 'error');
        return 0;
      }
        //die('TODO -- db_insert into raptor_protocol_lib table!>>>' . print_r($myvalues, true));
        
      if(!$bHappy)
      {
        //Assume messages have already been posted, return failed from this method.
        return 0;
      }  
      //Returns 1 if everything was okay.
      drupal_set_message('Added new protocol ' . $protocol_shortname);
      return 1;
    }


    /**
     * @return array of all option values for the form
     */
    function getAllOptions()
    {
        return $this->m_oPageHelper->getAllOptions();
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues)
    {
        $form = $this->m_oPageHelper->getForm('A',$form, $form_state, FALSE, $myvalues, 'protocol_container_styles');

        $protocol_shortname = '';
        
        /*
        $form['data_entry_area1']['toppart']['protocol_shortname'] = array(
          '#type' => 'textfield', 
          '#title' => t('Short Name'), 
          '#value' => $protocol_shortname, 
          '#size' => 20, 
          '#maxlength' => 20, 
          '#required' => TRUE,
          '#description' => t('The unique short name for this protocol'),
          '#disabled' => FALSE,
        );        
        $form['data_entry_area1']['toppart']['name'] = array(
          '#type' => 'textfield', 
          '#title' => t('Long Name'), 
          '#default_value' => $myvalues['name'], 
          '#size' => 128, 
          '#maxlength' => 128, 
          '#required' => TRUE,
          '#description' => t('The unique long name for this protocol'),
          '#disabled' => FALSE,
        );   
        */     
        
        //Replace the buttons
       $form['data_entry_area1']['action_buttons'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="raptor-action-buttons">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );
        $form['data_entry_area1']['action_buttons']['create'] = array('#type' => 'submit'
                , '#attributes' => array('class' => array('admin-action-button'), 'id' => 'admin-action-button-add')
                , '#validate' => array('raptor_datalayer_addprotocollib_form_builder_customvalidate')
                , '#value' => t('Add Protocol'));
 
        $form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => '<input class="admin-cancel-button" id="user-cancel" type="button" value="Cancel" data-redirect="/drupal/worklist?dialog=manageProtocolLib">');
        
        return $form;
    }
}
