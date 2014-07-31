<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 */

namespace raptor;

require_once(dirname(__FILE__) . '/../config/choices.php');
require_once("FormHelper.php");

/**
 * This class returns the Admin Information input content
 *
 * @author FrankWin7VM
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
                $keywords .= '<li>';
                if(count($kw2)>0)
                {
                    $kwc++;
                    $keywords .= implode(', ',$kw2);
                } else {
                    $keywords .= 'None';
                }
            }
            if($kwc > 0)
            {
                $keywords .= '<li>';
                if(count($kw3)>0)
                {
                    $kwc++;
                    $keywords .= implode(', ',$kw3);
                } else {
                    $keywords .= 'None';
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
        drupal_add_js("jQuery(document).ready(function){
            jQuery('.raptor-dialog-table').DataTable({
                'pageLength' : 25
            });
        });", array('type' => 'inline', 'scope' => 'footer', 'weight' => 5));

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

        //Create the join query.
        $query = db_select('raptor_protocol_lib','p')
                ->fields('p')
                ->fields('t', array('consent_req_kw','hydration_oral_tx','hydration_iv_tx'))
                ->orderBy('name');
        $query->leftJoin('raptor_protocol_template','t','p.protocol_shortname = t.protocol_shortname');       
        $result = $query->execute();
        foreach($result as $item) 
        {
            $protocol_shortname = $item->protocol_shortname;
            $keywords = $this->getFormattedKeywordsForTable($protocol_shortname);
            $rows .= "\n".'<tr>'
                  . '<td>'.$protocol_shortname.'</td>'
                  . '<td>'.$item->name.'</td>'
                  . '<td>'.$item->modality_abbr.'</td>'
                  . '<td>'.$item->consent_req_kw.'</td>'
                  . '<td>'.$keywords.'</td>'
                  . '<td><ol><li>'.$item->hydration_oral_tx.'<li>'.$item->hydration_iv_tx.'</ol></td>'
                  . '<td></td>'
                  . '<td></td>'
                  . '<td></td>'
                  . '</tr>';
        }

        $form["data_entry_area1"]['table_container']['protocols'] = array('#type' => 'item',
                 '#markup' => '<table id="my-raptor-dialog-table" class="dataTable">'
                            . '<thead>'
                            . '<tr>'
                            . '<th>Short Name</th>'
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
