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
 * This class returns content for the about page
 *
 * @author Frank Font of SAN Business Consultants
 */
class ViewAbout
{

    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state)
    {
        global $base_url;

        $mdwsversion = '2.5';   //TODO get from a query
        $logomarkup = "<img style='float:right;' alt='RAPTOR Logo' src='$base_url/sites/all/modules/raptor_glue/images/raptor_large_logo.png'>";
        $html = '<div id="about-dialog" style="margin-left:auto;margin-right:auto;">'
                . '<table width="100%">'
                . '<tr>'
                . '<td>'
                . $logomarkup
                . '</td>'
                . '<td style="vertical-align:top">'
                . '<b>RAPTOR Version Information</b>'
                . '<ul>'
                . '<li>App Build:'.RAPTOR_BUILD_ID.'</li>'
                . '<li>Machine ID:'.RAPTOR_CONFIG_ID.'</li>'
                . '<li>MDWS version: '.$mdwsversion.'</li>'
                . '<li>VISTA Site: '.VISTA_SITE.'</li>'
                . '</ul>'
                . '<b>Site Customization Version Information</b>'
                . '<ul>'
                . '<li>General: '.GENERAL_DEFS_VERSION_INFO.'</li>'
                . '<li>Workflow: '.WORKFLOW_DEFS_VERSION_INFO.'</li>'
                . '<li>Time:'.TIME_DEFS_VERSION_INFO.'</li>'
                . '<li>VistA:'.VISTA_DEFS_VERSION_INFO.'</li>'
                . '</ul>'
                . '</td>'
                . '</tr>'
                . '</table>'
                . '</div> <!-- End about-dialog div -->';

        $form = array();
        $form[] = array('#markup' => $html );

        return $form;
    }
}
