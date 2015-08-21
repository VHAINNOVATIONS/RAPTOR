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

/**
 * This class shows the list of available reports for user launch
 *
 * @author Frank Font of SAN Business Consultants
 */
class ManageReportsPage
{

    /**
     * Declare instances of all the reports in this function!!!     
     */
    public static function getReportsList()
    {
        $aReportClassNames  = array();
        
        $aReportClassNames[] = 'ViewReportDeptTicketProcessing';
        $aReportClassNames[] = 'ViewReportUserTicketProcessing';
        $aReportClassNames[] = 'ViewReportQAScores';
        
        $aReportClassNames[] = 'ViewTechSupportConfigDetails';
        $aReportClassNames[] = 'ViewReportRadiationDoseWatch';
        $aReportClassNames[] = 'ViewReportContraindicationRules';
        $aReportClassNames[] = 'ViewReportConversionFormulas';
        $aReportClassNames[] = 'ViewReportRoomReservations';
        $aReportClassNames[] = 'ViewReportUserActivity';
        $aReportClassNames[] = 'ViewTechSupportConfigDetails';
        $aReportClassNames[] = 'ViewEhrDaoPerformance';
        
        return $aReportClassNames;
    }
    
    /**
     * We can walk through the instances to extract metadata about each report
     */
    public static function getReportInstances($aReportClassNames)
    {
        $aReports = array();
        foreach($aReportClassNames as $name)
        {
            $class = "\\raptor\\$name";
            require_once(RAPTOR_REPORTS_PATH . "/report/$name.php");
            $aReports[$name] = new $class();
        }
        return $aReports;
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues)
    {
        $form['data_entry_area1'] = array(
            '#prefix' => "\n<section class='user-admin raptor-dialog-table'>\n",
            '#suffix' => "\n</section>\n",
        );
        $form['data_entry_area1']['table_container'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="raptor-dialog-table-container">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );

        $oContext = \raptor\Context::getInstance();
        $userinfo = $oContext->getUserInfo();
        //$userprivs = $userinfo->getSystemPrivileges();
        $alluserprivs = $userinfo->getAllPrivileges();

        $aReportClassNames  = self::getReportsList();
        $aReports = self::getReportInstances($aReportClassNames);
        
        //Construct a page with all the available reports for the user.
        $rows = "\n";
        $showhiddenreports = TRUE;  //removing this feature isset($myvalues['showhiddenreports']) ? $myvalues['showhiddenreports'] : FALSE;
        global $base_url;
        foreach($aReports as $classname=>$oReport)
        {
            //Can this user run this report?
            $hidefromlist = $oReport->hideFromList();
            if($showhiddenreports || !$hidefromlist)
            {
                if($oReport->hasRequiredPrivileges($alluserprivs))
                {
                    //Yes, make the report available.
                    $name = $oReport->getName(); // . '['.$base_url.']';
                    $description = $oReport->getDescription();
                    $menukey = $oReport->getMenuKey();
                    if($hidefromlist)
                    {
                        $titleattr = ' title="Normally hidden report" ';
                        $prefixattr = '<b>';
                        $suffixattr = '</b>';
                    } else {
                        $titleattr = '';
                        $prefixattr = '';
                        $suffixattr = '';
                    }
                    $rows   .= "\n".'<tr><td '.$titleattr.'><a href="javascript:window.location.href=\'' 
                              . $base_url . '/' 
                              . $menukey.'\'">'.$prefixattr.'View ' 
                              . $name.' Report'.$suffixattr.'</a></td>'
                              .'<td>'.$description.'</td>'
                              .'</tr>';
                }
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
