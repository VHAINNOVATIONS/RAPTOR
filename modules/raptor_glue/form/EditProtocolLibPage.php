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

require_once (RAPTOR_GLUE_MODULE_PATH . '/functions/protocol.inc');

module_load_include('php', 'raptor_datalayer', 'config/Choices');

require_once 'FormHelper.php';
require_once 'ProtocolLibPageHelper.php';
require_once 'ChildEditBasePage.php';

/**
 * This class returns the Admin Information input content
 *
 * @author Frank Font of SAN Business Consultants
 */
class EditProtocolLibPage extends \raptor\ChildEditBasePage
{
    private $m_oPageHelper = null;
    private $m_protocol_shortname = null;

     //Call same function as in EditUserPage here!
    function __construct($protocol_shortname)
    {
        if(!isset($protocol_shortname) || is_numeric($protocol_shortname))
        {
            throw new \Exception("Missing or invalid protocol_shortname value on edit form = [" . $protocol_shortname . ']');
        }
        $this->m_protocol_shortname = $protocol_shortname;
        $this->m_oPageHelper = new \raptor\ProtocolLibPageHelper();
        global $base_url;
        $this->setGobacktoURL($base_url.'/raptor/manageprotocollib');
    }

    /**
     * Get the values to populate the form.
     * @param type $sProtocolName the user id
     * @return type result of the queries as an array
     */
    function getFieldValues()
    {
        $myvalues = $this->m_oPageHelper->getFieldValues($this->m_protocol_shortname);
        return $myvalues;
    }

    /**
     * Validate the proposed values.
     * @param type $form
     * @param type $myvalues
     * @return true if no validation errors detected
     */
    function looksValidFormState($form, &$form_state)
    {
        return $this->m_oPageHelper->looksValidFormState($form, $form_state, 'E');
    }

    /**
     * Write the values into the database.
     */
    function updateDatabase($form, $myvalues)
    {
        $bHappy = TRUE; //Assume no problems.
        $protocol_shortname = $this->m_protocol_shortname;

        $filedetails = $this->m_oPageHelper->getPostedFileDetails($myvalues, $protocol_shortname, 'protocolfile');
        $file = $filedetails['file'];
        $rawfilename = $filedetails['rawfilename'];
        $newfilename = $filedetails['newfilename'];
        $filetype = $filedetails['filetype'];
        $filesize = $filedetails['filesize'];
        $file_blob = $filedetails['file_blob'];
        $fid = $filedetails['fid'];
        
        global $user;
        $updated_dt = date("Y-m-d H:i", time());
        try
        {
            //First copy values for the existing record into the raptor_protocol_lib_replaced table
            $this->m_oPageHelper->copyProtocolLibToReplacedTable($protocol_shortname, $existingfileinfo);
            if($newfilename == NULL)
            {
                $filename = $existingfileinfo['filename'];
                $original_filename = $existingfileinfo['original_filename'];
                $original_file_upload_dt = $existingfileinfo['original_file_upload_dt'];
                $original_file_upload_by_uid = $existingfileinfo['original_file_upload_by_uid'];
                $myvalues['upload_file_now'] = FALSE;
            } else {
                $filename = $newfilename;
                $original_filename = $rawfilename;
                $original_file_upload_dt = $updated_dt;
                $original_file_upload_by_uid = $user->uid;
                $myvalues['upload_file_now'] = TRUE;
            }
            //Important that we add these into the myvalues array so they get into other handlers
            $myvalues['filetype'] = $filetype;
            $myvalues['filesize'] = $filesize;
            $myvalues['filename'] = $filename;
            $myvalues['original_filename'] = $original_filename;
            $myvalues['original_file_upload_dt'] = $original_file_upload_dt;
            $myvalues['original_file_upload_by_uid'] = $original_file_upload_by_uid;

            //Now prepare to change the existing record.
            $yn_attribs = isset($myvalues['yn_attribs']) ? $myvalues['yn_attribs'] : array();
            $contrast_yn = (isset($yn_attribs['C']) && $yn_attribs['C'] === 'C') ? 1 : 0;
            $image_guided_yn = (isset($yn_attribs['IG']) && $yn_attribs['IG'] === 'IG') ? 1 : 0;
            $radioisotope_yn = (isset($yn_attribs['RI']) && $yn_attribs['RI'] === 'RI') ? 1 : 0;
            $sedation_yn = (isset($yn_attribs['S']) && $yn_attribs['S'] === 'S') ? 1 : 0;
            $active_yn = 1;
            $multievent_yn = 0;
            $nUpdated = db_update('raptor_protocol_lib')->fields(array(
                'name' => $myvalues['name'],
                'version' => $myvalues['version'],
                'modality_abbr' => $myvalues['modality_abbr'],
                'service_nm' => '', //hardcoded as empty string always for now $myvalues['service_nm'],
                'lowerbound_weight' => $myvalues['lowerbound_weight'],
                'upperbound_weight' => $myvalues['upperbound_weight'],
                'image_guided_yn' => $image_guided_yn,
                'contrast_yn' => $contrast_yn,
                'radioisotope_yn' => $radioisotope_yn,
                'sedation_yn' => $sedation_yn,
                'multievent_yn' => $multievent_yn,
                'filename' => $filename,
                'original_filename' => $original_filename,
                'original_file_upload_dt' => $original_file_upload_dt,
                'original_file_upload_by_uid' => $original_file_upload_by_uid,
                'active_yn' => $active_yn,
                'updated_dt' => $updated_dt,
                ))
           ->condition('protocol_shortname', $protocol_shortname, '=')
           ->execute();
           
        }
        catch(\Exception $ex)
        {
           error_log("Failed to save protocol into database!\n" . print_r($myvalues, TRUE) . '>>>'. print_r($ex, TRUE));
           drupal_set_message('Failed to save the new protocol because ' . $ex->getMessage(), 'error');
           $bHappy = FALSE;
        }
        
        if($bHappy)
        {
            //Now write all the child records including the file blob upload to database!
            $bHappy = $this->m_oPageHelper->writeChildLibraryRecords($protocol_shortname, $myvalues, TRUE, $file_blob);
        }

        //Provide the right user feedback.
        if($bHappy && isset($myvalues['protocolfile']) 
                && $myvalues['protocolfile'] != NULL 
                && $file_blob !== NULL)
        {
            drupal_set_message('Saved changes to protocol and uploaded file for ' . $protocol_shortname);
        } else if($bHappy) {
            //Returns 1 if everything was okay
            drupal_set_message('Saved changes to protocol ' . $protocol_shortname);
        } else {
            drupal_set_message('Trouble saving changes to protocol ' . $protocol_shortname, 'warning');
        }
        
        return $bHappy;
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
        $form = $this->m_oPageHelper->getForm('E',$form, $form_state, FALSE, $myvalues, 'protocol_container_styles');

        $form['data_entry_area1']['action_buttons'] = array(
            '#type' => 'item',
            '#prefix' => '<div class="raptor-action-buttons">',
            '#suffix' => '</div>',
            '#tree' => TRUE,
        );
        $form['data_entry_area1']['action_buttons']['create'] = array('#type' => 'submit'
                , '#attributes' => array('class' => array('admin-action-button'))
                , '#value' => t('Save Protocol Updates')
                , '#validate' => array('raptor_glue_editprotocollib_form_builder_customvalidate')
                , '#disabled' => $disabled
            );

        global $base_url;
        $worklist_url = $base_url . '/worklist';
        $goback = $this->getGobacktoFullURL();
        $form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => '<input class="admin-cancel-button" id="user-cancel"'
                . ' type="button" value="Cancel"'
                . ' data-redirect="'.$goback.'">');

        return $form;
    }
}
