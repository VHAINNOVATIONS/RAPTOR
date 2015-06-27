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

require_once 'AReport.php';

/**
 * This class returns the configuration details
 *
 * @author Frank Font of SAN Business Consultants
 */
class ViewTechSupportConfigDetails extends AReport
{
    private static $reqprivs = array('SITEADMIN'=>1);
    private static $menukey = 'raptor/showtechsupportconfigdetails';
    private static $reportname = 'Technical Support Configuration Details';

    function __construct()
    {
        parent::__construct(self::$reqprivs, self::$menukey, self::$reportname);
    }

    public function getDescription() 
    {
        return 'Shows detailed configuration settings of the installation';
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
        $rows = array();
        $profile = drupal_get_profile();
        $rows['Profile'] = "<td>Profile</td><td>$profile</td>";

        $systeminfo_ar = array();
        try
        {
            $systeminfo_ar = system_get_info('module', $profile);  
            if(count($systeminfo_ar) == 0)
            {
                $systeminfo_ar['systeminfo'] = 'Nothing reported for profile!';
            }
        } catch (\Exception $ex) {
            $systeminfo_ar['systeminfo'] = $ex->getMessage();
        }
        foreach($systeminfo_ar as $key=>$value)
        {
            $newkey = "sys $key";
            $newvalue = print_r($value,TRUE);
            $rows[$key] = "<td>$newkey</td><td>$newvalue</td>";
        }

        $themes_ar = array();
        try
        {
            $themes_ar = list_themes();      
        } catch (\Exception $ex) {
            $themes_ar['themesinfo'] = $ex->getMessage();
        }
        foreach($themes_ar as $key=>$value)
        {
            $newkey = "theme $key";
            if($value->status == 1)
            {
                $newvalue = print_r($value,TRUE);
            } else {
                $newvalue = 'not enabled';
            }
            $rows[$key] = "<td>$newkey</td><td>$newvalue</td>";
        }
        
        $drupalinfo = "<div style='text-align: center'>"
                . "<h1>Drupal Configuration Info</h1>"
                . "<table style='margin-left: auto; margin-right: auto; text-align: left;' cellpadding='3' width='600px'>"
                . "<tr><th>Type</th><th>Value</th></tr>"
                . "<tbody>"
                . "<tr>".implode('</tr><tr>',$rows)."</tr>"
                . "</tbody>"
                . "</table>"
                . "</div>";

        $form['data_entry_area1']['table_container']['drupalinfo'] = array('#type' => 'item',
                 '#markup' => $drupalinfo);
                
        ob_start();
        phpinfo();       
        $phpinfo = ob_get_clean();
        
        $form['data_entry_area1']['table_container']['phpinfo'] = array('#type' => 'item',
                 '#markup' => $phpinfo);
        
        $form['data_entry_area1']['action_buttons'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="raptor-action-buttons">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );

        $form['data_entry_area1']['action_buttons']['refresh'] = array('#type' => 'submit'
                , '#attributes' => array('class' => array('admin-action-button'), 'id' => 'refresh-report')
                , '#value' => t('Refresh Report'));
        
        global $base_url;
        $goback = $base_url . '/raptor/viewReports';
        $form['data_entry_area1']['action_buttons']['cancel'] = $this->getExitButtonMarkup($goback);
        return $form;
    }
}
