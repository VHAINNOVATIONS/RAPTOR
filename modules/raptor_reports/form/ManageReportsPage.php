<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 */

namespace raptor;

defined('RAPTOR_REPORTS_PATH')
    or define('RAPTOR_REPORTS_PATH', drupal_get_path('module', 'raptor_reports'));  

/**
 * This class shows the list of available reports
 *
 * @author Frank Font of SAN Business Consultants
 */
class ManageReportsPage
{
    
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

        //Declare instances of all the reports in this array!!!
        $aReportClassNames  = array();
        $aReportClassNames[] = 'ViewReport1Page';
        $aReportClassNames[] = 'ViewReport2Page';
        $aReportClassNames[] = 'ViewReportContraindicationInputs';
        $aReportClassNames[] = 'ViewReportContraindicationRules';
        $aReportClassNames[] = 'ViewReportRoomReservations';
        $aReportClassNames[] = 'ViewReportUserActivity';
        $aReportClassNames[] = 'ViewTechSupportConfigDetails';
        
        $aReports = array();
        foreach($aReportClassNames as $name)
        {
            $class = "\\raptor\\$name";
            require_once(RAPTOR_REPORTS_PATH . "/report/$name.php");
            $aReports[] = new $class();
        }
        
        //Construct a page with all the available reports for the user.
        $rows = "\n";
        global $base_url;
        foreach($aReports as $oReport)
        {
            //Can this user run this report?
            if($oReport->hasRequiredPrivileges($userprivs))
            {
                //Yes, make the report available.
                $name = $oReport->getName(); // . '['.$base_url.']';
                $description = $oReport->getDescription();
                $menukey = $oReport->getMenuKey();
                $rows   .= "\n".'<tr><td><a href="javascript:window.location.href=\''.$base_url.'/'.$menukey.'\'">View '.$name.' Report</a></td>'
                          .'<td>'.$description.'</td>'
                          .'</tr>';
            }
        }
        
        //Finalize the markup.
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
