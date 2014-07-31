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
        //$result = db_query('SELECT protocol_shortname, name, version, modality_abbr, image_guided_yn, contrast_yn, sedation_yn, filename, active_yn, updated_dt FROM raptor_protocol_lib ORDER BY protocol_shortname');
        $result = db_select('raptor_protocol_lib', 'p')
                ->fields('p')
                ->orderBy('protocol_shortname')
                ->execute();
        foreach($result as $item) 
        {
            $protocol_shortname = $item->protocol_shortname;
            $keywords = $this->getFormattedKeywordsForTable($protocol_shortname);
            $rows .= "\n".'<tr>'
                  . '<td>'.$protocol_shortname.'</td>'
                  . '<td>'.$item->name.'</td>'
                  . '<td>'.$item->modality_abbr.'</td>'
                  . '<td>'.$item->version.'</td>'
                  . '<td>'.$keywords.'</td>'
                  . '<td><a href="/drupal/raptor_datalayer/viewprotocollib?protocol_shortname='.$item->protocol_shortname.'">View</a></td>'
                  . '<td><a href="/drupal/raptor_datalayer/editprotocollib?protocol_shortname='.$item->protocol_shortname.'">Edit</a></td>'
                  . '<td><a href="/drupal/raptor_datalayer/deleteprotocollib?protocol_shortname='.$item->protocol_shortname.'">Delete</a></td></tr>';
        }

        $form["data_entry_area1"]['table_container']['protocols'] = array('#type' => 'item',
                 '#markup' => '<table id="my-raptor-dialog-table" class="dataTable">'
                            . '<thead>'
                            . '<tr>'
                            . '<th>Short Name</th>'
                            . '<th>Long Name</th>'
                            . '<th>Modality</th>'
                            . '<th>Version</th>'
                            . '<th>Keywords</th>'
                            . '<th>View</th>'
                            . '<th>Edit</th>'
                            . '<th>Delete</th>'
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
        $form['data_entry_area1']['action_buttons']['create'] = array('#type' => 'item'
                , '#markup' => '<input class="raptor-dialog-submit" type="button" value="Add Protocol" data-redirect="/drupal/raptor_datalayer/addprotocollib" />');
        $form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => '<input class="raptor-dialog-cancel" type="button" value="Cancel" />');        
        
        
        return $form;
    }
}
