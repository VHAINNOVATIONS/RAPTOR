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
 * This class returns the configuration edit page
 *
 * @author Frank Font of SAN Business Consultants
 */
class EditProtocolLibraryConfigPage
{

    public function __construct()
    {
        module_load_include('php','raptor_datalayer','core/data_context');
        $oContext = \raptor\Context::getInstance();
        $oUserInfo = $oContext->getUserInfo();
        if(!$oUserInfo->isSiteAdministrator())
        {
            throw new \Exception('The user account does not have privileges for this page.');
        }
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state
            , $disabled
            , $myvalues)
    {
        
        $form['data_entry_area1'] = array(
            '#prefix' => "\n<section class='raptor-list-dataentry'>\n",
            '#suffix' => "\n</section>\n",
            '#disabled' => $disabled,
        );
        
        $aGeneralHelpText[] = 'This module interacts with the Protocol Library for RAPTOR.';
        
        $infonum=0;
        foreach($aGeneralHelpText as $oneitem)
        {
            $infonum++;
            $form['data_entry_area1']['instructions']['custominfo'.$infonum] = array(
                '#markup'         => "<p>$oneitem</p>",
            );        
        }

        $form['data_entry_area1']['downloadexistingpl'] = array(
            '#type'     => 'fieldset',
            '#title'    => t('Download existing protocol library contents'),
            '#attributes' => array(
                'class' => array(
                    'data-entry1-area'
                )
             ),
            '#disabled' => $disabled,
        );
        $form['data_entry_area1']['downloadexistingpl']['info'] = array(
            '#markup' => "<p>Downloading the existing protocol library contents does NOT change any of the content in RAPTOR.  "
            . "Save the downloaded protocol library contents in a safe place.</p>"
            . "",
        );        
        
        $form['data_entry_area1']['downloadexistingpl']['link'] = array(
            '#prefix' => "<ul>",
            '#suffix' => "</ul>",
            );        
        
        global $base_url;
        $exportxml = "$base_url/raptor/protocollib/exportxml";
        $form['data_entry_area1']['downloadexistingpl']['link']['xml'] = array(
            '#markup' => "<li><a href='$exportxml'>Download existing protocol library contents as an XML data file</a>",
        );        
        
        $exportraw = "$base_url/raptor/protocollib/exportdata";
        $form['data_entry_area1']['downloadexistingpl']['link']['raw'] = array(
            '#markup' => "<li><a href='$exportraw'>Download existing protocol library contents as a RAW data file</a>",
        );        
        
        return $form;
    }
    
 }
