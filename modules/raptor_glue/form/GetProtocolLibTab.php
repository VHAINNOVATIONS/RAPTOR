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


require_once 'ProtocolInfoUtility.php';


/**
 * This class returns content for the protocol library tab
 *
 * @author Frank Font of SAN Business Consultants
 */
class GetProtocolLibTab
{

    private $m_oContext = null;
    private $m_oUtility = NULL;
    private $m_oMOP = NULL;
    private $m_oLI = NULL;
    private $m_oFRD = NULL;
    private $m_aPatientDD = NULL;
    
    function __construct()
    {
        module_load_include('php', 'raptor_datalayer', 'core/data_dashboard');
        module_load_include('php', 'raptor_datalayer', 'core/data_ticket_tracking');
        module_load_include('php', 'raptor_datalayer', 'core/FacilityRadiationDose');
        
        module_load_include('php', 'raptor_formulas', 'core/MatchOrderToProtocol');
        module_load_include('php', 'raptor_formulas', 'core/LanguageInference');

        module_load_include('inc', 'raptor_glue', 'functions/protocol');
        
        $this->m_oContext = \raptor\Context::getInstance();
        $this->m_oUtility = new \raptor\ProtocolInfoUtility();
        $this->m_oMOP = new \raptor_formulas\MatchOrderToProtocol();
        $this->m_oLI = new \raptor_formulas\LanguageInference();
        $this->m_oFRD = new \raptor\FacilityRadiationDose();
        $oDD = new \raptor\DashboardData($this->m_oContext);
        $this->m_aPatientDD = $oDD->getDashboardDetails();
    }

    /**
     * Get the values to populate the form.
     * @return type result of the queries as an array
     */
    function getFieldValues()
    {
        $myvalues = array();
        $oTT = new \raptor\TicketTrackingData();
        $nIEN = $this->m_oContext->getSelectedTrackingID(); //TODO change response to be SID-IEN
        $sSiteID = $this->m_oContext->getSiteID();
        $sTrackingID = $oTT->getTrackingID($sSiteID,$nIEN);
        if($nIEN > '')
        {
            $oWL = new \raptor\WorklistData($this->m_oContext);
            $aOneRow = $oWL->getDashboardMap();

            $myvalues = array();
            $myvalues['IEN'] = $nIEN;
            $myvalues['procName'] = $aOneRow['Procedure'];
            $myvalues['ticketType'] = $oTT->getTicketProcessingMode($sTrackingID);
            
        }
        return $myvalues;
    }    

    public function getPlainFormattedKeywordsForTable($aKeywords)
    {
            $kw1 = isset($aKeywords[1]) ? $aKeywords[1] : array();
            $kw2 = isset($aKeywords[2]) ? $aKeywords[2] : array();
            $kw3 = isset($aKeywords[3]) ? $aKeywords[3] : array();
            $allKeywords = array_merge($kw1,$kw2,$kw3);
            // Concatenate into a comma-separated list and clean up formatting
            $keywords = implode(",",$allKeywords);
            // Clean up duplicate commas and add a space afterwards
            $keywords = preg_replace("/,+/", ", ", $keywords);
            // Remove trailing commas
            $keywords = preg_replace("/,$/", "", $keywords);
            return $keywords;
    }
    
    public function getFormattedKeywordsForTable($aKeywords)
    {
            $keywords = '';
            $kw1 = isset($aKeywords[1]) ? $aKeywords[1] : array();
            $kw2 = isset($aKeywords[2]) ? $aKeywords[2] : array();
            $kw3 = isset($aKeywords[3]) ? $aKeywords[3] : array();

            $kwc = 0;
            $gotlevel2=FALSE;
            $keywords .= '<div class="keywords"><ol>';
            if(count($kw1)>0)
            {
                $mycontents = implode(', ',$kw1);
                if(trim($mycontents) > '')
                {
                    $kwc++;
                    $keywords .= '<li title="Most significant keywords">';
                    $keywords .= $mycontents;
                }
            }
            if($kwc > 0)
            {
                if(count($kw2)>0)
                { 
                    $mycontents = implode(', ',$kw2);
                    if(trim($mycontents) > '')
                    {
                        $keywords .= '<li title="Moderate significance">';
                        $kwc++;
                        $keywords .= $mycontents;
                        $gotlevel2=TRUE;
                    }
                }
                if(count($kw3)>0)
                {
                    $mycontents = implode(', ',$kw3);
                    if(trim($mycontents) > '')
                    {
                        if(!$gotlevel2)
                        {
                            //Handle the gap.
                            $keywords .= '<li>Empty level2';
                        }
                        $keywords .= '<li title="Least significance">';
                        $kwc++;
                        $keywords .= $mycontents;
                    }
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
        $form['data_entry_area1'] = array(
            '#prefix' => "\n<section class='protocollib-admin raptor-dialog-table'>\n",
            '#suffix' => "\n</section>\n",
        );
        $form['data_entry_area1']['table_container'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="raptor-dialog-table-container">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );
        
        $orderProcName = $myvalues['procName']; //We are scoring relative to this
        $show_scores = ($orderProcName > '');
        
        $kwmap = $this->m_oUtility->getKeywordMap();
        $sitedosebundle = $this->m_oFRD->getSiteDoseTracking();
        $sitedose_summary = $sitedosebundle['summary'];
        
        $rows = "\n";
        
        //Figure out if a selection has already been made.
        $selectedpsn = isset($myvalues['protocol_shortname']) ? $myvalues['protocol_shortname'] : '';
        if($selectedpsn > '')
        {
            $default_linktext = 'replace selection';
        } else {
            $default_linktext = 'select';
        }
        
        $protocol_code_map = array();
        try
        {
            $protocol_code_map = $this->m_oUtility->getAllProtocolCodeMap();
        
            //Create the main join query.
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
            $query->leftJoin('raptor_protocol_template','t'
                    ,'p.protocol_shortname = t.protocol_shortname');
            $query->condition('t.active_yn',1,'=');
            $result = $query->execute();
            $dash_modality = $this->m_aPatientDD['Modality'];
            $image_type = $this->m_aPatientDD['ImageType'];
            foreach($result as $item) 
            {
                $protocol_shortname = $item->protocol_shortname;
                $site_summary_info = $this->m_oFRD->getFacilityDoseInfoCompleteSummary($sitedosebundle, $protocol_shortname);
                $site_summary_show = $site_summary_info['show_text'];
                $site_summary_tip = $site_summary_info['tip'];
                if(count($site_summary_show) > 0)
                {
                    $site_summary_show_tx = '<ol><li>'.implode('<li>',$site_summary_show).'</ol>';
                    $site_summary_tip_tx = implode(', ',$site_summary_tip);
                } else {
                    $site_summary_show_tx = 'None found';
                    $site_summary_tip_tx = 'No facility averages found';
                }
                
                if(isset($protocol_code_map[$protocol_shortname]))
                {
                    $this_protocol_codemap = $protocol_code_map[$protocol_shortname];
                } else {
                    $this_protocol_codemap = array();
                }
                $filename = $item->filename;
                $longname = $item->name;
                $uploaded_dt = $item->updated_dt;   //TODO use original_file_upload_dt
                $modality_abbr = $item->modality_abbr;
                $contrast_yn = $item->contrast_yn;
                if(isset($kwmap[$protocol_shortname]))
                {
                    $keywords =$this->getFormattedKeywordsForTable($kwmap[$protocol_shortname]);
                } else {
                    $keywords = '';
                }
                $cluesmap = $this->m_oLI->getProtocolMatchCluesMap($orderProcName, NULL, $image_type, $dash_modality);

                $scoredetails = $this->m_oMOP->getProtocolMatchScore($cluesmap
                        , $protocol_shortname
                        , $longname
                        , $modality_abbr
                        , $contrast_yn
                        , $kwmap
                        , $this_protocol_codemap);
                $matchscore = $scoredetails['score'];
                if($show_scores)
                {
                    $scorewhy = $scoredetails['why'];
                    $scorewhymarkup = implode('; ', $scorewhy);
                }
                if(trim($filename) > '' && $filename != 'no-filename')
                {
                    $uri = 'public://library/'.$filename;
                    $url = file_create_url($uri);
                    global $base_url;
                    $pageurl = $base_url . '/raptor/viewscannedprotocol?'
                            . 'protocol_shortname='.$protocol_shortname.'&showclose';
                    //$shortnamelink = "<a title='doc uploaded $uploaded_dt' target='_blank' href='$pageurl'>$protocol_shortname</a>";
                    $shortnamelink = "<a title='doc uploaded $uploaded_dt' "
                            . "href='#' onclick='window.open(\"$pageurl\",\"_blank\");"
                            . "return false;'>$protocol_shortname</a>";
                } else {
                    $shortnamelink = $protocol_shortname;
                }

                if($myvalues['ticketType'] !== 'P')
                {
                    $protocolnamecontent = $protocol_shortname;
                } else {
                    $protocolnamecontent = '<a title="change selected protocol for this order" href="#"'
                            . ' class="select-protocol"'
                            . ' data-protocol_shortname="'.$protocol_shortname.'"'
                            . '>'
                            .$default_linktext
                            .'</a>';
                }

                $rows .= "\n".'<tr>'
                      . '<td>'.$shortnamelink.'</td>'
                      . '<td'.($show_scores ? " title='$scorewhymarkup'>$matchscore" : ' title="No score computed">NA').'</td>'
                      . '<td>'.$protocolnamecontent.'</td>'
                      . '<td>'.$longname.'</td>'
                      . '<td>'.$modality_abbr.'</td>'
                      . '<td>'."<span title='$site_summary_tip_tx'>$site_summary_show_tx<span>".'</td>'
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
        } catch (\Exception $ex) {
            error_log("Failed main GetProtocolLibTab because ".$ex->getMessage());
            throw new \Exception("Failed getting protocol library",99123,$ex);
        }

        if($show_scores)
        {
            $matchscore_title='Higher matching scores indicate higher possible relevance to the order';
        } else {
            $matchscore_title='No match score computed because no order text was available';
        }
        $form['data_entry_area1']['table_container']['protocols'] = array('#type' => 'item',
                 '#markup' => '<table id="getprotocollibtab-table" class="dataTable">'
                            . '<thead>'
                            . '<tr>'
                            . '<th title="Each protocol in the library has a unique identifier">Short Name</th>'
                            . '<th title="'.$matchscore_title.'">MS</th>'
                            . '<th title="Shortcut to select the protocol for this order">Select Protocol for Order</th>'
                            . '<th title="The descriptive long name of this protocol">Long Name</th>'
                            . '<th title="Equipment or technology used">Modality</th>'
                            . '<th title="Average radiation dose tracked at facility level">Radiation Dose</th>'
                            . '<th title="Does this protocol require patient consent?">Consent Required</th>'
                            . '<th title="Keywords to help connect protocols to relevant procedures">Keywords</th>'
                            . '<th>Hydration Settings</th>'
                            . '<th>Sedation Settings</th>'
                            . '<th>Contrast Settings</th>'
                            . '<th>Radionuclide Settings</th>'
                            . '</tr>'
                            . '</thead>'
                            . '<tbody>'
                            . $rows
                            .  '</tbody>'
                            . '</table>');
        
        return $form;
    }
}
