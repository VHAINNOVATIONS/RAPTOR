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

require_once (RAPTOR_GLUE_MODULE_PATH . '/functions/protocol.inc');
module_load_include('php', 'raptor_datalayer', 'core/data_context');
module_load_include('php', 'raptor_datalayer', 'core/data_ticket_tracking');
module_load_include('php', 'raptor_datalayer', 'core/data_worklist');
module_load_include('php', 'raptor_datalayer', 'core/data_dashboard');
module_load_include('php', 'raptor_datalayer', 'core/data_protocolsupport');
module_load_include('php', 'raptor_datalayer', 'core/data_protocolsettings');


/**
 * This class returns content for the radiation dose tab
 *
 * @author Frank Font of SAN Business Consultants
 */
class GetRadiationDoseHxTab
{
    private $m_oContext;
    private $m_oDD;

    const PROT_PRIMARY_ID = '[Protocol Primary Selection ID] ::=';
    const PROT_PRIMARY_NM = '[Protocol Primary Selection NAME] ::=';
    const PROT_PRIMARY_MODALITY = '[Protocol Primary Selection MODALITY] ::=';
    const PROT_SECONDARY_ID = '[Protocol Secondary Selection ID] ::=';
    const PROT_SECONDARY_NM = '[Protocol Secondary Selection NAME] ::=';
    const PROT_SECONDARY_MODALITY = '[Protocol Secondary Selection MODALITY] ::=';
    
    function __construct($tid = NULL)
    {
        $loaded = module_load_include('php','raptor_datalayer','core/data_dashboard');
        if(!$loaded)
        {
            $msg = 'Failed to load the data_dashboard';
            throw new \Exception($msg);      //This is fatal, so stop everything now.
        }
        
        $this->m_oContext = \raptor\Context::getInstance();
        $this->m_oDD = new \raptor\DashboardData($this->m_oContext);
    }
    
    private function getRowMarkup($linkmarkup,$showprocdate
            ,$procname,$rdose
            ,$cdose,$ddose
            ,$edose,$etype
            ,$qdose,$qtype
            ,$sdose,$stype
            ,$validationstatus)
    {
        if(count($rdose)>0)
        {
            $rdosemarkup = '<ul><li>'.implode('<li>', $rdose).'</ul>';
        } else {
            $rdosemarkup = '';
        }
        if(count($cdose)>0)
        {
            $cdosemarkup = '<ul><li>'.implode('<li>', $cdose).'</ul>';
        } else {
            $cdosemarkup = '';
        }
        if(count($ddose)>0)
        {
            $ddosemarkup = '<ul><li>'.implode('<li>', $ddose).'</ul>';
        } else {
            $ddosemarkup = '';
        }
        if(count($edose)>0)
        {
            $edosemarkup = '<ul><li>'.implode('<li>', $edose).'</ul>';
        } else {
            $edosemarkup = '';
        }
        if(count($qdose)>0)
        {
            $qdosemarkup = '<ul><li>'.implode('<li>', $qdose).'</ul>';
        } else {
            $qdosemarkup = '';
        }
        if(count($sdose)>0)
        {
            $sdosemarkup = '<ul><li>'.implode('<li>', $sdose).'</ul>';
        } else {
            $sdosemarkup = '';
        }
        $onerow = '<td>'.$linkmarkup.'</td>'
              . '<td>'.$showprocdate.'</td>'
              . '<td>'.$procname.'</td>'
              . '<td>'.$rdosemarkup.'</td>'
              . '<td>'.$cdosemarkup.'</td>'
              . '<td>'.$ddosemarkup.'</td>'
              . '<td>'.$edosemarkup.'</td>'
              . '<td>'.$etype.'</td>'
              . '<td>'.$qdosemarkup.'</td>'
              . '<td>'.$qtype.'</td>'
              . '<td>'.$sdosemarkup.'</td>'
              . '<td>'.$stype.'</td>'
              . '<td>'.$validationstatus.'</td>';
        
        return $onerow;
    }
    
    /**
     * Get all the data we need for the form.
     */
    private function getQueryResult($patientid, $myvalues)
    {
        $q1 = db_select('raptor_ticket_exam_radiation_dose','e')
                ->fields('e',array('patientid','dose','uom','dose_type_cd','dose_source_cd'
                    ,'dose_target_area_id'
                    ,'dose_dt','data_provider'
                    ,'siteid','IEN','sequence_position'))
                ->fields('pts', array('primary_protocol_shortname',))
                ->fields('pl', array('protocol_shortname','name'));
        $q1->leftJoin('raptor_ticket_protocol_settings','pts','e.siteid = pts.siteid and e.IEN = pts.IEN');       
        $q1->leftJoin('raptor_protocol_lib','pl','pts.primary_protocol_shortname = pl.protocol_shortname');       
        $q1->condition('patientid',$patientid,'=');

        $q2 = db_select('raptor_patient_radiation_dose','p')
                ->fields('p',array('patientid','dose','uom','dose_type_cd','dose_source_cd'
                    ,'dose_target_area_id'
                    ,'dose_dt','data_provider'));
        $q2->addExpression(500,'siteid');   //TODO replace with actual field from database
        $q2->addExpression(-1,'IEN');       //Indicate not a real IEN value
        $q2->addExpression(0,'sequence_position');     
        $q2->addExpression('NULL','primary_protocol_shortname');     
        $q2->addExpression('NULL','protocol_shortname');     
        $q2->addExpression('NULL','name');     
        $q2->condition('patientid',$patientid,'=');

        //Make sure the result is sorted.
        $q2->orderBy('dose_dt')
           ->orderBy('siteid')
           ->orderBy('IEN')
           ->orderBy('dose_type_cd')     
           ->orderBy('sequence_position');

        $q1->union($q2);

        $result = $q1->execute();
        return $result;
    }
    
    private function updatecollection(&$modalitysummary,&$modalitydetail
            ,$cutdatetimestamp
            ,$modality_abbr
            ,$id
            ,$name
            ,$date
            ,$timestamp)
    {
        $oneitem = array();
        $oneitem['modality'] = $modality_abbr;
        $oneitem['id'] = $id;
        $oneitem['name'] = $name;
        $oneitem['date'] = $date;
        $oneitem['timestamp'] = $timestamp;
        $isinrange = ($cutdatetimestamp < $timestamp);

        //Update the modality grouping too
        $mkey = $oneitem['modality'];
        if(!array_key_exists($mkey,$modalitysummary))
        {
            $modalitydetail[$mkey] = array();
            $modalitysummary[$mkey] = array();
            $modalitysummaryitem = array();
            $modalitysummaryitem['allcount'] = 1;
            if($isinrange)
            {
                $modalitysummaryitem['12mcount'] = 1;            
            }
        } else {
            $modalitysummaryitem = $modalitysummary[$mkey];
            $modalitysummaryitem['allcount'] += 1;
            if($isinrange)
            {
                $modalitysummaryitem['12mcount'] += 1;            
            }
        }
        $modalitysummary[$mkey]= $modalitysummaryitem;
        $modalitydetailgroup = $modalitydetail[$mkey];
        $nkey = $oneitem['id'].'_'.$oneitem['name'];
        if(!array_key_exists($nkey,$modalitydetailgroup))
        {
            //Simply add the new entry
            $ndet = array();
            $ndet['id'] = $oneitem['id'];
            $ndet['name'] = $oneitem['name'];
            $ndet['allcount'] = 1;
            if($isinrange)
            {
                $ndet['12mcount'] = 1;
            }
        } else {
            //Update the exising entry
            $ndet = $modalitydetailgroup[$nkey];
            $ndet['allcount'] += 1;
            if($isinrange)
            {
                $ndet['12mcount'] += 1;
            }
        }
        $modalitydetail[$mkey][$nkey] = $ndet;
    }
    
    /**
     * Scroll through all the RAPTOR VistA notes for this patient
     */
    private function getRadDoseDetails($patientid)
    {
        $infopackage = array();
        $modalitysummary = array();
        $modalitydetail = array();
        $cutdate = mktime(0, 0, 0, date('n')-12, 1, date('y')); //12 months ago
        $cutdatetimestamp = mktime(0, 0, 0, date('n')-12, 1, date('y')); //12 months ago

        $oPSD = new \raptor\ProtocolSupportingData($this->m_oContext);
        $totalnotes = 0;
        $lowts = NULL;
        $hights = NULL;
        $notesdetail = $oPSD->getNotesDetail();
        foreach($notesdetail as $data_row) 
        {
            if($data_row['Type'] == 'RAPTOR NOTE')
            {
                //Parse thie one
                $totalnotes++;
                $sThisTS = $data_row['Date'];
                $thists = strtotime($sThisTS);
                if($lowts == NULL || $lowts > $thists)
                {
                    $lowts = $thists;
                    $sLowTS = $sThisTS;
                }
                if($hights == NULL || $hights < $thists)
                {
                    $hights = $thists;
                    $sHighTS = $sThisTS;
                }
                $sDetail = print_r($data_row["Details"],TRUE);   //Get the entire string contents
                $aDetails = explode("\n",$sDetail);
                $prot_primary_id=NULL;
                $prot_primary_name=NULL;
                $prot_primary_modality_abbr=NULL;
                $prot_secondary_id=NULL;
                $prot_secondary_name=NULL;
                $prot_secondary_modality_abbr=NULL;
                foreach($aDetails as $detail_row)
                {
                    if(($p1 = strpos($detail_row, self::PROT_PRIMARY_ID)) !== FALSE)
                    {
                        $prot_primary_id = trim(substr($detail_row, strlen(self::PROT_PRIMARY_ID)));
                    } else
                    if(($p1 = strpos($detail_row, self::PROT_PRIMARY_NM)) !== FALSE)
                    {
                        $prot_primary_name = trim(substr($detail_row, strlen(self::PROT_PRIMARY_NM)));
                    } else
                    if(($p1 = strpos($detail_row, self::PROT_PRIMARY_MODALITY)) !== FALSE)
                    {
                        $prot_primary_modality_abbr = trim(substr($detail_row, strlen(self::PROT_PRIMARY_MODALITY)));
                    } else
                    if(($p1 = strpos($detail_row, self::PROT_SECONDARY_ID)) !== FALSE)
                    {
                        $prot_secondary_id = trim(substr($detail_row, strlen(self::PROT_SECONDARY_ID)));
                    } else
                    if(($p1 = strpos($detail_row, self::PROT_SECONDARY_NM)) !== FALSE)
                    {
                        $prot_secondary_name = trim(substr($detail_row, strlen(self::PROT_SECONDARY_NM)));
                    } else
                    if(($p1 = strpos($detail_row, self::PROT_SECONDARY_MODALITY)) !== FALSE)
                    {
                        $prot_secondary_modality_abbr = trim(substr($detail_row, strlen(self::PROT_SECONDARY_MODALITY)));
                    } 
                }
                if($prot_primary_modality_abbr !== NULL)
                {
                    $this->updatecollection($modalitysummary
                            ,$modalitydetail
                            ,$cutdatetimestamp
                            ,$prot_primary_modality_abbr
                            ,$prot_primary_id
                            ,$prot_primary_name
                            ,$sThisTS
                            ,$thists);
                }
                if($prot_secondary_modality_abbr !== NULL)
                {
                    $this->updatecollection($modalitysummary
                            ,$modalitydetail
                            ,$cutdatetimestamp
                            ,$prot_secondary_modality_abbr
                            ,$prot_secondary_id
                            ,$prot_secondary_name
                            ,$sThisTS
                            ,$thists);
                }
            }
        }
        
        $infopackage['modalitysummary'] = $modalitysummary;
        $infopackage['modalitydetail'] = $modalitydetail;
        $infopackage['total_notes'] = $totalnotes;
        $infopackage['oldest_note_dt'] = $sLowTS;
        $infopackage['newest_note_dt'] = $sHighTS;
        $infopackage['12m_ago_date'] = $cutdate;
        return $infopackage;
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

        global $base_url;

        $protocoldashboard = $this->m_oDD->getDashboardDetails();
        $patientid=$protocoldashboard['PatientID'];
        $infopackage = $this->getRadDoseDetails($patientid);
        $modalitysummary = $infopackage['modalitysummary'];
        $modalitydetail = $infopackage['modalitydetail'];
        $oldest_note_dt = $infopackage['oldest_note_dt'];
        $newest_raddose_dt = $infopackage['newest_note_dt'];
        $totalnotes = $infopackage['total_notes'];

        $form['data_entry_area1']['table_container']['introblurb'] = array('#type' => 'item',
         '#markup' => '<div class="introblurb">'
            . '<h2>Information presented here is derived only from the available RAPTOR VistA notes.</h2>'
            . '<ul>'
            . '<li>Oldest available RAPTOR VistA note is dated '.$oldest_note_dt
            . '<li>Newest available RAPTOR VistA note is dated '.$newest_raddose_dt
            . '<li>Total RAPTOR VistA notes found is '.$totalnotes
            . '</ul>'
                    //.'<hr><pre>'.print_r($infopackage,TRUE).'</pre>'
            . '</div>');
        
        $rowsmarkup = '';
        $detrowsmarkup = array();
        $foundmodalities = array();
        foreach($modalitysummary as $mkey=>$summaryitem)
        {
            $rowsmarkup .= "\n"
                    .'<tr><td>'
                    .$mkey
                    .'</td><td>'
                    .$summaryitem['12mcount']
                    .'</td><td>'
                    .$summaryitem['allcount']
                    .'</td></tr>';
                
            $modalitydetailgroup = $modalitydetail[$mkey];
            if(!isset($detrowsmarkup[$mkey]))
            {
                $detrowsmarkup[$mkey] = array();
                $foundmodalities[$mkey] = $mkey;
            }
            if($mkey == 'CT')
            {
                //Two facility average dose values
                foreach($modalitydetailgroup as $nkey=>$detailitem)
                {
                    $detrowsmarkup[$mkey][] = "\n"
                            .'<td>'
                            .$mkey
                            .'</td><td>'
                            .$detailitem['id']
                            .'</td><td>'
                            .$detailitem['name']
                            .'</td><td>'
                            .$detailitem['12mcount']
                            .'</td><td>'
                            .$detailitem['allcount']
                            .'</td><td>'
                            .'unavailable'
                            .'</td><td>'
                            .'unavailable'
                            .'</td>';
                }
            } else if($mkey == 'NM') {
                //Only one facility average dose value
                foreach($modalitydetailgroup as $nkey=>$detailitem)
                {
                    $detrowsmarkup[$mkey][] = "\n"
                            .'<td>'
                            .$mkey
                            .'</td><td>'
                            .$detailitem['id']
                            .'</td><td>'
                            .$detailitem['name']
                            .'</td><td>'
                            .$detailitem['12mcount']
                            .'</td><td>'
                            .$detailitem['allcount']
                            .'</td><td>'
                            .'unavailable'
                            .'</td>';
                }
            } else {
                //Other has no facility average dose
                foreach($modalitydetailgroup as $nkey=>$detailitem)
                {
                    $detrowsmarkup[$mkey][] = "\n"
                            .'<td>'
                            .$mkey
                            .'</td><td>'
                            .$detailitem['id']
                            .'</td><td>'
                            .$detailitem['name']
                            .'</td><td>'
                            .$detailitem['12mcount']
                            .'</td><td>'
                            .$detailitem['allcount']
                            .'</td>';
                }
            }
        }
        
        $form['data_entry_area1']['table_container']['dosetotals'] 
                = array('#type' => 'item',
              '#markup' => '<h3>Modality Summaries for Patient</h3>'
            . '<p>The total MODALITY counts available to RAPTOR for this patient.</p>'
            . '<table id="my-raptor-radiationmodalitysummary-table" class="non-search-table">'
            . '<thead>'
            . '<th>Modality</th>'
            . '<th>Past 12 Months</th>'
            . '<th>All Available History</th>'
            . '</thead>'
            . '<tbody>'
            . $rowsmarkup
            . '</tbody>'
            . '</table>'
            );
        
        $donetables = array();
        if(!array_keys($foundmodalities, 'CT'))
        {
            $form["data_entry_area1"]['table_container']['CTdoseaverages'] = array('#type' => 'item',
             '#markup' => '<h3>CT SCAN Procedure Summary -- None found</h3>');
        } else {
            $donetables['CT'] = 'CT';
            $form["data_entry_area1"]['table_container']['CTdoseaverages'] = array('#type' => 'item',
             '#markup' => '<h3>CT SCAN Procedure Summary</h3>'
                . '<p>The total PROTOCOL counts and facility averages available to RAPTOR for this patient.</p>'
                . '<table id="my-raptor-radiationCTdetail-table" class="dataTable">'
                . '<thead>'
                . '<th>Modality</th>'
                . '<th>ID</th>'
                . '<th>Protocol Name</th>'
                . '<th>Past 12 Months</th>'
                . '<th>All Available History</th>'
                . '<th>Facility Exam Dose Estimate CTDIvol (mGy)</th>'
                . '<th>Facility Exam Dose Estimate DLP (mGy*cm)</th>'
                . '</thead>'
                . '<tbody><tr>'
                . implode('</tr><tr>', $detrowsmarkup['CT'])
                . '</tr></tbody>'
                . '</table>'
                );
        }
        if(!array_keys($foundmodalities, 'NM'))
        {
            $form["data_entry_area1"]['table_container']['NMdoseaverages'] = array('#type' => 'item',
             '#markup' => '<h3>Nuclear Medicine Procedure Summary -- None found</h3>');
        } else {
            $donetables['NM'] = 'NM';
            $form["data_entry_area1"]['table_container']['NMdoseaverages'] = array('#type' => 'item',
             '#markup' => '<h3>Nuclear Medicine Procedure Summary</h3>'
                . '<p>The total PROTOCOL counts and facility averages available to RAPTOR for this patient.</p>'
                . '<table id="my-raptor-radiationNMdetail-table" class="dataTable">'
                . '<thead>'
                . '<th>Modality</th>'
                . '<th>ID</th>'
                . '<th>Protocol Name</th>'
                . '<th>Past 12 Months</th>'
                . '<th>All Available History</th>'
                . '<th>Facility Exam Estimate Radionuclide Dose (mCi)</th>'
                . '</thead>'
                . '<tbody><tr>'
                . implode('</tr><tr>', $detrowsmarkup['NM'])
                . '</tr></tbody>'
                . '</table>'
                );
        }
        //Now output all the other tables, if any.
        $othermodalities = array_diff_key($foundmodalities, $donetables);
        foreach($othermodalities as $mkey)
        {
            $donetables[$mkey] = $mkey;
            $form["data_entry_area1"]['table_container'][$mkey . 'doseaverages'] = array('#type' => 'item',
             '#markup' => '<h3>'.$mkey.' Procedure Summary</h3>'
                . '<p>The total PROTOCOL counts and facility averages available to RAPTOR for this patient.</p>'
                . '<table id="my-raptor-radiation'.$mkey.'detail-table" class="dataTable">'
                . '<thead>'
                . '<th>Modality</th>'
                . '<th>ID</th>'
                . '<th>Protocol Name</th>'
                . '<th>Past 12 Months</th>'
                . '<th>All Available History</th>'
                . '</thead>'
                . '<tbody><tr>'
                . implode('</tr><tr>', $detrowsmarkup[$mkey])
                . '</tr></tbody>'
                . '</table>'
                );
        }
        
        
        return $form;
    }
    
    function getAverage($total, $count)
    {
        if($count > 0)
        {
            return $total / $count;
        } else {
            return 0;
        }
    }
    
}
