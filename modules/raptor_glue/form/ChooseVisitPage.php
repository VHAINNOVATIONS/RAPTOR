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
require_once ('FormHelper.php');

/**
 * Choosing a visit for an action.
 *
 * @author Frank Font of SAN Business Consultants
 */
class ChooseVisitPage
{
    private $m_oContext = null;
    private $m_oTT = null;

    function __construct()
    {
        $this->m_oContext = \raptor\Context::getInstance();
        $this->m_oTT = new \raptor\TicketTrackingData();
    }

    /**
     * Get the values to populate the form.
     * @return type result of the queries as an array
     */
    function getFieldValues()
    {
        $oMdwsDao = $this->m_oContext->getMdwsClient();
        //$myvalues['patientid'] = 'TODO';    //!!!!!
        $myvalues['all_visits'] = \raptor\MdwsUtils::getVisits($oMdwsDao);
        $myvalues['selected_vid'] = NULL;

        return $myvalues;
    }
    
    /**
     * Some checks to validate the data before we try to save it.
     * @param type $form
     * @param type $myvalues
     * @return TRUE or FALSE
     */
    function looksValid($form, $myvalues)
    {
        $bGood = TRUE;
        if(!isset($myvalues['selected_vid']) || ($myvalues['selected_vid'] == ''))
        {
            //drupal_set_message('>>>>myvalues>>>>'.print_r($myvalues,TRUE));
            //die('failed>>>>myvalues>>>>'.print_r($myvalues,TRUE));
            $bGood = FALSE;
            form_set_error('available_visits_table','No visit has been selected');
        }
        return $bGood;
    }
    
    /**
     * This form does not actually write the the database.
     */
    function updateDatabase($form, $myvalues)
    {
        //Write success message
        drupal_set_message('Visit '.$myvalues['selected_vid'].' selected ');
        
        return 1;
    }
    
    
    private function getFormattedDate($visitTimestamp)
    {
        $year = substr($visitTimestamp, 0, 4);
        $month = substr($visitTimestamp, 4, 2);
        $day = substr($visitTimestamp, 6, 2);
        
        $hour = substr($visitTimestamp, 9, 2);
        $minute = substr($visitTimestamp, 11, 2);
        
        return $year.'-'.$month."-".$day . ' ' . $hour . ':' . $minute;
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    private function getTable($form, &$form_state, $disabled, $myvalues)
    {
        $rows = "\n";
        $aAllVisits = $myvalues['all_visits'];
        $nVisitCount = 0;
        foreach($aAllVisits as $aVisit)
        {
            $visitID = 'vid_'.$aVisit['locationId'] . '_' . $aVisit['visitTimestamp'];  //Create our composite identifier for later parsing ease
            if( $visitID == $myvalues['selected_vid'] )
            {
                $bSelected = TRUE;
                $sSelected = ' checked="checked" ';
            } else {
                $bSelected = FALSE;
                $sSelected = ' ';
            }
            if($aVisit['locationId'] != '')
            {
                $sFormattedTimestamp = $this->getFormattedDate($aVisit['visitTimestamp']);
                $rows   .= "\n".'<tr>'
                        . '<td><input type="radio" name="group_vid" value="'.$visitID.'" '
                        . $sSelected
                        . '></td>' //' onclick="copyValueFromSourceToTarget(this,selected_vid)" ></td>'
                        . '<td title="'.$aVisit['visitTimestamp'].'">'.$sFormattedTimestamp.'</td>'
                        . '<td title="'.$aVisit['locationId'].'">'.$aVisit['locationName'].'</td>'
                        . '</tr>';
                $nVisitCount++;
            }
        }
        
        $elements[] = array('#type' => 'item',
                 '#markup' => '<p>Total of ' . $nVisitCount . ' visits exist for this patient</p>');
        $elements[] = array('#type' => 'item',
                 '#markup' => '<table class="raptor-dialog-table dataTable">'
                            . '<thead><tr>'
                            . '<th></th>'
                            . '<th>Date</th>'
                            . '<th>Location</th>'
                            . '</tr>'
                            . '</thead>'
                            . '<tbody>'
                            . $rows
                            . '</tbody>'
                            . '</table>');
        return $elements;
    }

    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    public function getForm($form, &$form_state, $disabled, $myvalues)
    {

        $form['data_entry_area1']['toppart']['available_visits_table'] = $this->getTable($form, $form_state, $disabled, $myvalues);

        //$form['hiddenthings']['patientid'] = array('#type' => 'hidden', '#value' => $myvalues['patientid']);
        //$form['hiddenthings']['selected_vid'] = array('#type' => 'hidden'); //IMPORTANT DO NOT SET A DEFAULT VALUE!!!, '#default_value' => 'NOBODY');

        $form['data_entry_area1']['toppart']['subform_commit_esig'] = array(
          '#type' => 'password', 
          '#title' => t('Vista Electronic Signature'), 
          '#size' => 60, 
          '#maxlength' => 60, 
          '#required' => TRUE,
        );        
        
        $form["data_entry_area1"]['action_buttons'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="raptor-action-buttons">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );
        
        $form['data_entry_area1']['action_buttons']['okay'] = array('#type' => 'submit'
                , '#attributes' => array('class' => array('raptor-action-button'))
                , '#value' => t('Use Selected Visit')
                , '#disabled' => $disabled
        );
        /*
        $form['data_entry_area1']['action_buttons']['submit'] = array('#type' => 'submit'
                , '#attributes' => array('class' => array('raptor-action-button'))
                , '#value' => t('Use Selected Visit')
                , '#disabled' => $disabled
        );
         */
        $form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => '<input class="raptor-dialog-cancel" type="button" value="Cancel">');
        
        return $form;
    }
}

