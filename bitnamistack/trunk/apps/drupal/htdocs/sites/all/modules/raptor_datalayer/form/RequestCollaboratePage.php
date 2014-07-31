<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 */

namespace raptor;

require_once(dirname(__FILE__) . '/../config/choices.php');
require_once('FormHelper.php');
//module_load_include('inc', 'raptor_datalayer', 'core/data_user.php');

/**
 * Helper for report pages.
 *
 * @author FrankWin7VM
 */
class RequestCollaboratePage
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
        $tid = $this->m_oContext->getSelectedTrackingID();
        
        $oWL = new \raptor\WorklistData($this->m_oContext);
        $aOneRow = $oWL->getOneWorklistRow($tid);
        $nSiteID = $this->m_oContext->getSiteID();
        
        $nIEN = $tid;
        $nUID = $this->m_oContext->getUID();
        
        $myvalues = array();
        $myvalues['tid'] = $tid;
        $myvalues['procName'] = $aOneRow['Procedure'];
        
        
        $this->m_oContext = \raptor\Context::getInstance();
        $myvalues['tid'] = $this->m_oContext->getSelectedTrackingID();

        //TODO: Pre-populate values for display
        
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
        //TODO - special checks here
        return $bGood;
    }
    
    /**
     * Write the values into the database.
     * Return 0 if there was an error, else 1.
     */
    function updateDatabase($form, $myvalues)
    {
      print_r($myvalues);
      die();
        //Try to create the record now
        $nSiteID = $this->m_oContext->getSiteID();
        $nIEN = $myvalues['tid'];
        $nUID = $this->m_oContext->getUID();
        $sCWFS = $this->m_oTT->getTicketWorkflowState($nSiteID . '-' . $nIEN);
        $updated_dt = date("Y-m-d H:i:s", time());

        //Try to create the record now
        try
        {
            //die('about to insert...<br>' . print_r($myvalues, true));
            $oInsert = db_insert('raptor_ticket_collaboration')
                    ->fields(array(
                      'siteid' => $nSiteID, 
                      'IEN' => $nIEN, 
                      'requester_uid' => $nUID, 
                      'requested_dt' => date("Y-m-d H:i:s", time()), 
                      'requester_notes_tx' => $myvalues['requester_notes_tx'], 
                      'collaborator_uid' => $myvalues['collaborator_uid'], 
                      'viewed_dt' => NULL, 
                      'active_yn' => 1
                    ))
                    ->execute();
        }
        catch(PDOException $e)
        {
            error_log('Failed to collaborate: ' . $e . "\nDetails..." . print_r($oInsert,true));
            //die('Failed to add the user record.  Try again later.');
            form_set_error("searchresults",'Failed to collaborate with this user!');
            return 0;
        }

        $sNewWFS = 'CO';
        $this->m_oTT->setTicketWorkflowState($nSiteID . '-' . $nIEN, $nUID, $sNewWFS, $sCWFS, $updated_dt);

        //Write success message
        drupal_set_message('Collaborated ' . $myvalues['tid'] . ' (' . $myvalues['procName'] .')');
        
        
        return 1;
    }
    
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    private function getSearchResults($form, &$form_state, $disabled, $myvalues)
    {
        $rows = "\n";
        $sSQL = 'SELECT uid, username, usernametitle, firstname, lastname, suffix, prefemail, prefphone, '
                .'role_nm FROM raptor_user_profile WHERE accountactive_yn=1 ORDER BY username';
        $result = db_query($sSQL);
        $nSpecialistCount = 0;

        foreach($result as $item) 
        {
            $uid = $item->uid;
            $fullname = $item->usernametitle . ' ' . $item->firstname . ' ' . $item->lastname . ' ' . $item->suffix;
            
            //Get the modalities.
            $sSQL = 'SELECT modality_abbr FROM raptor_user_modality'
                    .' WHERE specialist_yn=1 and uid='.$uid.' ORDER BY modality_abbr';
            $modalities = '';
            $modalityResult = db_query($sSQL);

            foreach($modalityResult as $localitem) 
            {
                $modalities .= ' ' . $localitem->modality_abbr;
            }
            
            //Get the anatomy keywords.
            $sSQL = 'SELECT keyword FROM raptor_user_anatomy'
                    .' WHERE specialist_yn=1 and uid='.$uid.' ORDER BY keyword';
            $anatomyItems = '';
            $anatomyResult = db_query($sSQL);

            foreach($anatomyResult as $localitem) 
            {
                $anatomyItems .= ' ' . $localitem->keyword;
            }

            if($anatomyItems != '')
            {
                $rows   .= "\n".'<tr><td><input type="radio" name="collaborator_uid" value="'.$item->uid.'"></td><td title="'.$item->username.'">'.$fullname.'</td><td>'.$item->role_nm.'</td><td>'.$modalities.'</td><td>'.$anatomyItems.'</td>'
                        .'<td>'.$item->prefphone.'</td><td>'.$item->prefemail.'</td>'
                        .'</tr>';
                $nSpecialistCount++;
            }
        }

        $elements[] = array('#type' => 'item',
                 '#markup' => '<p>Total of ' . $nSpecialistCount . ' specialist(s) are available for search</p>');
        $elements[] = array('#type' => 'item',
                 '#markup' => '<table class="raptor-dialog-table dataTable">'
                            . '<thead><tr>'
                            . '<th></th>'
                            . '<th>Name</th>'
                            . '<th>Role</th>'
                            . '<th>Modality Specialties</th>'
                            . '<th>Anatomy Specialties</th>'
                            . '<th>Phone</th><th>Email</th>'
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

        $form['data_entry_area1']['toppart']['searchresults'] = $this->getSearchResults($form, $form_state, $disabled, $myvalues);

        //TODO real form fields
        $form['hiddenthings']['tid'] = array('#type' => 'hidden', '#value' => $myvalues['tid']);
        $form['hiddenthings']['procName'] = array('#type' => 'hidden', '#value' => $myvalues['procName']);

        $form['data_entry_area1']['toppart']['requester_notes_tx'] = array(
            '#type'          => 'textarea',
            '#title'         => t('Collaboration Request Notes'),
            '#disabled'      => $disabled,
            '#default_value' => '',
        );
        
        $form['data_entry_area1']['action_buttons']['request'] = array('#type' => 'submit'
                , '#attributes' => array('class' => array('admin-action-button'))
                , '#value' => t('Request Collaboration with Selected RAPTOR User')
                , '#disabled' => TRUE //$disabled  ENABLE IT AFTER WE IMPLEMENT IT!!!!!!!!!!!!!!!
        );
        $form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => '<input class="raptor-dialog-cancel" type="button" value="Cancel">');
        
        return $form;
    }
}

function raptor_fetch_search_results() {
  return '<p>Yabba dabba doo!</p>';
}