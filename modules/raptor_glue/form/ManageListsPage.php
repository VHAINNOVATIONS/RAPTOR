<?php
/**
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
require_once 'FormHelper.php';

/**
 * This page allows user to select from different page options.
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

        module_load_include('php', 'raptor_glue', 'form/EditListHydrationPage');
        $onepage = new EditListHydrationPage();
        if($onepage->canModify())
        {
            $rows .= $this->getRowMarkup($onepage->getURL()
                    ,$onepage->getName()
                    ,$onepage->getDescription());
        }
        
        module_load_include('php', 'raptor_glue', 'form/EditListSedationPage');
        $onepage = new EditListSedationPage();
        if($onepage->canModify())
        {
            $rows .= $this->getRowMarkup($onepage->getURL()
                    ,$onepage->getName()
                    ,$onepage->getDescription());
        }
        
        module_load_include('php', 'raptor_glue', 'form/EditListContrastPage');
        $onepage = new EditListContrastPage();
        if($onepage->canModify())
        {
            $rows .= $this->getRowMarkup($onepage->getURL()
                    ,$onepage->getName()
                    ,$onepage->getDescription());
        }

        module_load_include('php', 'raptor_glue', 'form/EditListRadioisotopePage');
        $onepage = new EditListRadioisotopePage();
        if($onepage->canModify())
        {
            $rows .= $this->getRowMarkup($onepage->getURL()
                    ,$onepage->getName()
                    ,$onepage->getDescription());
        }
        
        module_load_include('php', 'raptor_glue', 'form/EditListExamRoomPage');
        $onepage = new EditListExamRoomPage();
        if($onepage->canModify())
        {
            $rows .= $this->getRowMarkup($onepage->getURL()
                    ,$onepage->getName()
                    ,$onepage->getDescription());
        }
        
        module_load_include('php', 'raptor_glue', 'form/EditListAtRiskMedsPage');
        $onepage = new EditListAtRiskMedsPage();
        if($onepage->canModify())
        {
            $rows .= $this->getRowMarkup($onepage->getURL()
                    ,$onepage->getName()
                    ,$onepage->getDescription());
        }
        
        module_load_include('php', 'raptor_glue', 'form/EditListAtRiskAllergyContrastPage');
        $onepage = new EditListAtRiskAllergyContrastPage();
        if($onepage->canModify())
        {
            $rows .= $this->getRowMarkup($onepage->getURL()
                    ,$onepage->getName()
                    ,$onepage->getDescription());
        }
        
        module_load_include('php', 'raptor_glue', 'form/EditListAtRiskBloodThinnerPage');
        $onepage = new EditListAtRiskBloodThinnerPage();
        if($onepage->canModify())
        {
            $rows .= $this->getRowMarkup($onepage->getURL()
                    ,$onepage->getName()
                    ,$onepage->getDescription());
        }
        
        module_load_include('php', 'raptor_glue', 'form/EditListAtRiskRareContrastPage');
        $onepage = new EditListAtRiskRareContrastPage();
        if($onepage->canModify())
        {
            $rows .= $this->getRowMarkup($onepage->getURL()
                    ,$onepage->getName()
                    ,$onepage->getDescription());
        }
        
        module_load_include('php', 'raptor_glue', 'form/EditListAtRiskRareRadioisotopePage');
        $onepage = new EditListAtRiskRareRadioisotopePage();
        if($onepage->canModify())
        {
            $rows .= $this->getRowMarkup($onepage->getURL()
                    ,$onepage->getName()
                    ,$onepage->getDescription());
        }
        
        module_load_include('php', 'raptor_glue', 'form/EditListBoilerplateProtocolPage');
        $onepage = new EditListBoilerplateProtocolPage();
        if($onepage->canModify())
        {
            $rows .= $this->getRowMarkup($onepage->getURL()
                    ,$onepage->getName()
                    ,$onepage->getDescription());
        }
        
        module_load_include('php', 'raptor_glue', 'form/EditListBoilerplateExamPage');
        $onepage = new EditListBoilerplateExamPage();
        if($onepage->canModify())
        {
            $rows .= $this->getRowMarkup($onepage->getURL()
                    ,$onepage->getName()
                    ,$onepage->getDescription());
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
