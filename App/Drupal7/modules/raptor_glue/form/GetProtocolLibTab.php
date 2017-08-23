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

require_once (RAPTOR_GLUE_MODULE_PATH . '/functions/protocol.inc');


/**
 * This class returns content for the protocol library tab
 *
 * @author Frank Font of SAN Business Consultants
 */
class GetProtocolLibTab
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
                    $myvalues['keywords1'][] = $item->keyword;
                } else
                if($item->weightgroup == 2)
                {
                    $myvalues['keywords2'][] = $item->keyword;
                } else
                if($item->weightgroup == 3)
                {
                    $myvalues['keywords3'][] = $item->keyword;
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
            $kw1 = isset($aKeywords['keywords1']) ? $aKeywords['keywords1'] : array();
            $kw2 = isset($aKeywords['keywords2']) ? $aKeywords['keywords2'] : array();
            $kw3 = isset($aKeywords['keywords3']) ? $aKeywords['keywords3'] : array();
            $allKeywords = array_merge($kw1,$kw2,$kw3);
            // Concatenate into a comma-separated list and clean up formatting
            $keywords = implode(",",$allKeywords);
            // Clean up duplicate commas and add a space afterwards
            $keywords = preg_replace("/,+/", ", ", $keywords);
            // Remove trailing commas
            $keywords = preg_replace("/,$/", "", $keywords);
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

        $rows = "\n";
        
        $selectedpsn = isset($myvalues['protocol_shortname']) ? $myvalues['protocol_shortname'] : '';
                
        if($selectedpsn > '')
        {
            $default_linktext = 'replace selection';
        } else {
            $default_linktext = 'select';
        }

        //Create the join query.
        $query = db_select('raptor_protocol_lib','p')
                ->fields('p')
                ->fields('t', array(
                    'consent_req_kw',
                    'hydration_oral_tx', 
                    'hydration_iv_tx', 
                    'sedation_iv_tx', 
                    'sedation_oral_tx', 
                    'contrast_iv_tx', 
                    'contrast_enteric_tx', 
                    'radioisotope_iv_tx',
                    'radioisotope_enteric_tx'
                ))
                ->orderBy('name');
        $query->leftJoin('raptor_protocol_template','t','p.protocol_shortname = t.protocol_shortname');       
        $result = $query->execute();
        foreach($result as $item) 
        {
            $protocol_shortname = $item->protocol_shortname;
            $keywords = $this->getFormattedKeywordsForTable($protocol_shortname);
            $rows .= "\n".'<tr>'
                  . '<td>'.$protocol_shortname.'</td>'
                  . '<td><a href="#" class="select-protocol" data-protocol_shortname="'.$protocol_shortname.'">'.$default_linktext.'</a></td>'
                  . '<td>'.$item->name.'</td>'
                  . '<td>'.$item->modality_abbr.'</td>'
                  . '<td>'.$item->consent_req_kw.'</td>'
                  . '<td>'.$keywords.'</td>'
                  . '<td><ul>'
                    .(!empty($item->hydration_oral_tx) ? '<li>'.$item->hydration_oral_tx.'</li>' : '')
                    .(!empty($item->hydration_iv_tx) ? '<li>'.$item->hydration_iv_tx.'</li>' : '')
                  . '</ul></td>'
                  . '<td><ul>'
                    .(!empty($item->sedation_oral_tx) ? '<li>'.$item->sedation_oral_tx.'</li>' : '')
                    .(!empty($item->sedation_iv_tx) ? '<li>'.$item->sedation_iv_tx.'</li>' : '')
                  . '</ul></td>'
                  . '<td><ul>'
                    .(!empty($item->contrast_enteric_tx) ? '<li>'.$item->contrast_enteric_tx.'</li>' : '')
                    .(!empty($item->contrast_iv_tx) ? '<li>'.$item->contrast_iv_tx.'</li>' : '')
                  . '</ul></td>'
                  . '<td><ul>'
                    .(!empty($item->radioisotope_enteric_tx) ? '<li>'.$item->radioisotope_enteric_tx.'</li>' : '')
                    .(!empty($item->radioisotope_iv_tx) ? '<li>'.$item->radioisotope_iv_tx.'</li>' : '')
                  . '</ul></td>'
                  . '</tr>';
        }

        $form["data_entry_area1"]['table_container']['protocols'] = array('#type' => 'item',
                 '#markup' => '<table id="my-raptor-dialog-table" class="dataTable">'
                            . '<thead>'
                            . '<tr>'
                            . '<th>Short Name</th>'
                            . '<th>Select Protocol for Order</th>'
                            . '<th>Long Name</th>'
                            . '<th>Modality</th>'
                            . '<th>Consent Required</th>'
                            . '<th>Keywords</th>'
                            . '<th>Hydration Settings</th>'
                            . '<th>Sedation Settings</th>'
                            . '<th>Contrast Settings</th>'
                            . '<th>Radioisotope Settings</th>'
                            . '</tr>'
                            . '</thead>'
                            . '<tbody>'
                            . $rows
                            .  '</tbody>'
                            . '</table>');
        
        return $form;
    }
}
