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

module_load_include('php', 'raptor_datalayer', 'config/Choices');
require_once ('FormHelper.php');

/**
 * This class returns the Admin Information input content
 *
 * @author Frank Font of SAN Business Consultants
 */
class ManageListsPage
{

    private function getRowMarkup($url,$name,$description)
    {
        $rowmarkup  = "\n".'<tr><td><a href="'.$url.'">'.$name.'</a></td>'
                  .'<td>'.$description.'</td>'
                  .'</tr>';
        return $rowmarkup;
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues)
    {
        $form["data_entry_area1"] = array(
            '#prefix' => "\n<section class='user-admin raptor-dialog-table'>\n",
            '#suffix' => "\n</section>\n",
        );
        $form["data_entry_area1"]['table_container'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="raptor-dialog-table-container">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );

        $oContext = \raptor\Context::getInstance();
        $userinfo = $oContext->getUserInfo();
        $userprivs = $userinfo->getSystemPrivileges();

        global $base_url;
        
        $rows = "\n";
        if($userprivs['ELHO1'] == 1)
        {
            $rows   .= "\n".'<tr><td><a href="'.$base_url.'/raptor/edithydrationoptions">Edit Hydration Options</a></td>'
                      .'<td>Hydration options are selectable during the protocol process.</td>'
                      .'</tr>';
        }
        if($userprivs['ELSO1'] == 1)
        {
            $rows   .= "\n".'<tr><td><a href="'.$base_url.'/raptor/editsedationoptions">Edit Sedation Options</a></td>'
                      .'<td>Sedation options are selectable during the protocol process.</td>'
                      .'</tr>';
        }
        if($userprivs['ELCO1'] == 1)
        {
            $rows   .= "\n".'<tr><td><a href="'.$base_url.'/raptor/editcontrastoptions">Edit Contrast Options</a></td>'
                      .'<td>Contrast options are selectable during the protocol process.</td>'
                      .'</tr>';
        }
        if($userprivs['ELRO1'] == 1)
        {
            $rows   .= "\n".'<tr><td><a href="'.$base_url.'/raptor/editradioisotopeoptions">Edit Radionuclide Options</a></td>'
                      .'<td>Radionuclide options are selectable during the protocol process.</td>'
                      .'</tr>';
        }
        if($userprivs['EERL1'] == 1)
        {
            $rows   .= "\n".'<tr><td><a href="'.$base_url.'/raptor/editexamroomoptions">Edit Examination Room Options</a></td>'
                      .'<td>Exam room options are selectable during the scheduling process.</td>'
                      .'</tr>';
        }
        if($userprivs['EARM1'] == 1)
        {
            $url = $base_url.'/raptor/editatriskmeds';
            $name = 'Edit At Risk Medications List';
            $description = 'These keywords are used to highlight medical history of a patient.';
            $rows .= $this->getRowMarkup($url,$name,$description);
            
            $url = $base_url.'/raptor/editatriskallergycontrast';
            $name = 'Edit Allergy Contrast List';
            $description = 'These keywords are used to detect possible contrast allergies in patient.';
            $rows .= $this->getRowMarkup($url,$name,$description);
            
            $url = $base_url.'/raptor/editatriskbloodthinner';
            $name = 'Edit Blood Thinner List';
            $description = 'These keywords are used to detect possible blood thinner use by patient.';
            $rows .= $this->getRowMarkup($url,$name,$description);
            
            $url = $base_url.'/raptor/editatriskrarecontrast';
            $name = 'Edit Rare or Controlled Contrast List';
            $description = 'These keywords are used to detect selection of a rare or controlled contrast which may require advanced procurement or special ordering process.';
            $rows .= $this->getRowMarkup($url,$name,$description);
            
            $url = $base_url.'/raptor/editatriskrareradioisotope';
            $name = 'Edit Rare or Controlled Radionuclide List';
            $description = 'These keywords are used to detect selection of a rare or controlled radionuclide which may require advanced procurement or special ordering process.';
            $rows .= $this->getRowMarkup($url,$name,$description);
        }
        $form["data_entry_area1"]['table_container']['lists'] = array('#type' => 'item',
                 '#markup' => '<table class="raptor-dialog-table">'
                            . '<thead><tr><th>Action</th><th>Description</th></tr></thead>'
                            . '<tbody>'
                            . $rows
                            . '</tbody>'
                            . '</table>');
       $form['data_entry_area1']['action_buttons'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="raptor-action-buttons">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );
       
        $form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => '<input class="raptor-dialog-cancel" type="button" value="Exit" />');        

        return $form;
    }
}
