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

module_load_include('inc', 'raptor_glue', 'functions/protocol');
module_load_include('php', 'raptor_datalayer', 'config/Choices');
module_load_include('php', 'raptor_formulas', 'core/LanguageInference');

require_once 'FormHelper.php';

/**
 * This class returns the Admin Information input content
 *
 * @author Frank Font of SAN Business Consultants
 */
class ManageProtocolLibPage
{
    public function getKeywords($protocol_shortname)
    {
        $myvalues = array();
        $keyword_result = db_select('raptor_protocol_keywords','r')
                ->fields('r')
                ->condition('protocol_shortname',$protocol_shortname,'=')
                ->execute();
        $myvalues['thekey'] = $protocol_shortname;
        if($keyword_result->rowCount()!==0)
        {
            foreach($keyword_result as $item) 
            {
                if($item->weightgroup == 1)
                {
                    $kw = trim($item->keyword);
                    if(strlen($kw)>0)   //Do not treat empty string as a keyword
                    {
                        $myvalues['keywords1'][] = $kw;
                    }
                } else
                if($item->weightgroup == 2)
                {
                    $kw = trim($item->keyword);
                    if(strlen($kw)>0)   //Do not treat empty string as a keyword
                    {
                        $myvalues['keywords2'][] = $kw;
                    }
                } else
                if($item->weightgroup == 3)
                {
                    $kw = trim($item->keyword);
                    if(strlen($kw)>0)   //Do not treat empty string as a keyword
                    {
                        $myvalues['keywords3'][] = $kw;
                    }
                } else {
                    die("Invalid weightgroup value for filter=" . print_r($filter, true));
                }
            }
        }
        return $myvalues;
    }
    
    public function getFormattedKeywordsForTable($protocol_shortname)
    {
            $aKeywords = $this->getKeywords($protocol_shortname);
            $keywords = '';
            $kw1 = isset($aKeywords['keywords1']) ? $aKeywords['keywords1'] : array();
            $kw2 = isset($aKeywords['keywords2']) ? $aKeywords['keywords2'] : array();
            $kw3 = isset($aKeywords['keywords3']) ? $aKeywords['keywords3'] : array();

            $kwc = 0;
            $keywords .= '<div class="keywords"><ol>';
            if(count($kw1)>0)
            {
                $kwc++;
                $keywords .= '<li>';
                $keywords .= implode(', ',$kw1);
            }
            if($kwc > 0)
            {
                if(count($kw2)>0)
                {
                    $keywords .= '<li>';
                    $kwc++;
                    $keywords .= implode(', ',$kw2);
                }
                if(count($kw3)>0)
                {
                    if(count($kw2)==0)
                    {
                        $keywords .= '<li>Empty level2';
                    }
                    $keywords .= '<li>';
                    $kwc++;
                    $keywords .= implode(', ',$kw3);
                }
            }
            if($kwc == 0)
            {
                $keywords = 'No keywords';
            } else {
                $keywords .= '</ol></div>';
            }
            return $keywords;
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues)
    {
        $form["data_entry_area1"] = array(
            '#prefix' => "\n<section class='protocollib-admin raptor-dialog-table'>\n",
            '#suffix' => "\n</section>\n",
        );
        $form["data_entry_area1"]['table_container'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="raptor-dialog-table-container">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );

	global $base_url;
        $language_infer = new \raptor_formulas\LanguageInference();
		
        $showDeleteOption = TRUE;
        
        $rows = "\n";
        $result = db_select('raptor_protocol_lib', 'p')
                ->fields('p')
                ->orderBy('protocol_shortname')
                ->execute();
        foreach($result as $item) 
        {
            $protocol_shortname = $item->protocol_shortname;
            if($item->original_file_upload_dt == NULL)
            {
                $docuploadedmarkup = 'No';
            } else {
                $docuploadedmarkup = '<span class="hovertips" title="uploaded '
                        .$item->original_filename
                        .' on '
                        .$item->original_file_upload_dt.'">Yes</span>';
            }
            $keywords = $this->getFormattedKeywordsForTable($protocol_shortname);
            $active_markup = $item->active_yn == 1 ? '<b>Yes</b>' : 'No';
            $declaredHasContrast = $item->contrast_yn == 1 ? TRUE : FALSE;
            $hasSedation = $item->sedation_yn == 1 ? '<b>Yes</b>' : 'No';
            $hasRadioisotope = $item->sedation_yn == 1 ? '<b>Yes</b>' : 'No';
            $fullname = $item->name;
            $infered_hasContrast = $language_infer->inferContrastFromPhrase($fullname);
            $hasContrastMarkup = $declaredHasContrast ? '<b>Yes</b>' : 'No';
            if($infered_hasContrast !== NULL)
            {
                if(!(
                        ($declaredHasContrast && $infered_hasContrast) || 
                        (!$declaredHasContrast && !$infered_hasContrast))
                    )
                {
                    if($infered_hasContrast)
                    {
                        $troublemsg = "protocol long name implies YES contrast";
                    } else {
                        $troublemsg = "protocol long name implies NO contrast";
                    }
                    $hasContrastMarkup = "<span class='medical-health-warn' title='$troublemsg'>!!! $hasContrastMarkup !!!</span>";
                }
            }
            if(!$showDeleteOption)
            {
                $deleteActionMarkup = '';
            } else {
                $deleteActionMarkup = '<a href="'.$base_url.'/raptor/deleteprotocollib?protocol_shortname='.$item->protocol_shortname.'">Delete</a>';
            }
            $rows .= "\n".'<tr>'
                  . '<td>'.$protocol_shortname.'</td>'
                  . '<td>'.$fullname.'</td>'
                  . '<td>'.$active_markup.'</td>'
                  . '<td>'.$hasContrastMarkup.'</td>'
                  . '<td>'.$hasSedation.'</td>'
                  . '<td>'.$hasRadioisotope.'</td>'
                  . '<td>'.$item->modality_abbr.'</td>'
                  . '<td>'.$item->version.'</td>'
                  . '<td>'.$docuploadedmarkup.'</td>'
                  . '<td>'.$keywords.'</td>'
                  . '<td><a href="'.$base_url.'/raptor/viewprotocollib?protocol_shortname='.$item->protocol_shortname.'">View</a></td>'
                  . '<td><a href="'.$base_url.'/raptor/editprotocollib?protocol_shortname='.$item->protocol_shortname.'">Edit</a></td>'
                  . "<td>$deleteActionMarkup</td>"
                  . '</tr>';
        }

        $form["data_entry_area1"]['table_container']['protocols'] = array('#type' => 'item',
                 '#markup' => '<table id="my-raptor-dialog-table" class="dataTable">'
                            . '<thead>'
                            . '<tr>'
                            . '<th title="System unique identifier for the protocol">Short Name</th>'
                            . '<th title="Full name of the protocol">Long Name</th>'
                            . '<th title="Only active protocols are available for use on new exams">Is Active</th>'
                            . '<th title="Has contrast">C</th>'
                            . '<th title="Has sedation">S</th>'
                            . '<th title="Has radioisotope">R</th>'
                            . '<th title="The equipment context for this protocol">Modality</th>'
                            . '<th title="Value increases with each saved edit">Version</th>'
                            . '<th title="The scanned document">Doc Uploaded</th>'
                            . '<th title="Keywords used for matching this protocol programatically">Keywords</th>'
                            . '<th title="Just view the protocol">View</th>'
                            . '<th title="Edit the protocol details">Edit</th>'
                            . '<th title="Remove this protocol from the library">Delete</th>'
                            . '</tr>'
                            . '</thead>'
                            . '<tbody>'
                            . $rows
                            .  '</tbody>'
                            . '</table>');
        
       $form['data_entry_area1']['action_buttons'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="raptor-action-buttons">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );
        $form['data_entry_area1']['action_buttons']['createlink'] 
                = array('#type' => 'item'
                , '#markup' => '<a class="button" href="'
            .$base_url.'/raptor/addprotocollib">Add Protocol</a>');

        $form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => '<input class="raptor-dialog-cancel" type="button" value="Exit" />');        
        
        
        return $form;
    }
}
