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


/**
 * This class returns content for the radiation dose tab
 *
 * @author Frank Font of SAN Business Consultants
 */
class GetRadiationDoseHxTab
{
    private $m_oContext;
    private $m_oDD;
    
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

        $rows = array();
        $avgvalue_rows = array();
        global $base_url;

        $protocoldashboard = $this->m_oDD->getDashboardDetails();
        $patientid=$protocoldashboard['PatientID'];
        $result = $this->getQueryResult($patientid, $myvalues);
        
        $trackingid = '';
        $groupingkey='';
        $showprocdate = '';
        $procname = '';
        $etype = '';
        $qtype = '';
        $stype = '';
        $validationstatus = 'Unvalidated';  //TODO
        $rdose = array();
        $cdose = array();
        $ddose = array();
        $edose = array();
        $qdose = array();
        $sdose = array();
        $tdose = array();
        $hdose = array();
        
        $avg_rdose = 0.0;
        $avg_cdose = 0.0;
        $avg_ddose = 0.0;
        $avg_edose = 0.0;
        $avg_qdose = 0.0;
        $avg_sdose = 0.0;
        $avg_tdose = 0.0;
        $avg_hdose = 0.0;

        $total_rdose = 0.0;
        $total_cdose = 0.0;
        $total_ddose = 0.0;
        $total_edose = 0.0;
        $total_qdose = 0.0;
        $total_sdose = 0.0;
        $total_tdose = 0.0;
        $total_hdose = 0.0;

        $count_rdose = 0;
        $count_cdose = 0;
        $count_ddose = 0;
        $count_edose = 0;
        $count_qdose = 0;
        $count_sdose = 0;
        $count_tdose = 0;   //Not radiation!
        $count_hdose = 0;   //Not radiation!
        
        foreach($result as $item) 
        {
            $prevshowprocdate = $showprocdate;
            $prevprocname = $procname;
            $prevetype = $etype;
            $prevqtype = $qtype;
            $prevstype = $stype;
            $prevgroupingkey = $groupingkey;
            $siteid = isset($item->siteid) ? $item->siteid : '';
            $IEN = isset($item->IEN) ? $item->IEN : '';
            $trackingid = $siteid.'-'.$IEN;
            $etype = ($item->dose_type_cd == 'A') ? 'Actual' : 'Estimated';
            $qtype = ($item->dose_type_cd == 'A') ? 'Actual' : 'Estimated';
            $stype = ($item->dose_type_cd == 'A') ? 'Actual' : 'Estimated';
            $procname = $item->name;
            $groupingkey = $item->dose_dt . '_' 
                    . $item->patientid . '_' 
                    . $trackingid . '_' 
                    . $item->dose_type_cd;
            if($prevshowprocdate > '' && $prevgroupingkey != $groupingkey)
            {
                $key = urlencode($groupingkey);
                $linkmarkup = '<a href="#" todohref='
                        .$base_url
                        .'/raptor/editradiationdosehxentry?key='.$key.'">'
                        . 'edit</a>';
                $rows[] = $this->getRowMarkup($linkmarkup,$prevshowprocdate
                        ,$prevprocname,$rdose,$cdose,$ddose
                        ,$edose,$prevetype
                        ,$qdose,$prevqtype
                        ,$sdose,$prevstype
                        ,$validationstatus);

                //Initialize the value accumulators
                $rdose = array();
                $cdose = array();
                $ddose = array();
                $edose = array();
                $qdose = array();
                $sdose = array();
                $tdose = array();   //Not radiation!
                $hdose = array();   //Not radiation!
            }
            $showprocdate = date('Y-m-d',strtotime($item->dose_dt));
            //Add this stuff into our accumulators
            if($item->dose_source_cd == 'R')
            {
                //Radioisotope
                $rdose[] = $item->dose;
                $total_rdose += $item->dose;
                $count_rdose++;
            } else if($item->dose_source_cd == 'C'){
                //device CTDIvol
                $cdose[] = $item->dose;
                $total_cdose += $item->dose;
                $count_cdose++;
            } else if($item->dose_source_cd == 'D'){
                //device DLP
                $ddose[] = $item->dose;
                $total_ddose += $item->dose;
                $count_ddose++;
            } else if($item->dose_source_cd == 'Q'){
                //device Air Kerma
                $qdose[] = $item->dose;
                $total_qdose += $item->dose;
                $count_qdose++;
            } else if($item->dose_source_cd == 'S'){
                //device DAP
                $sdose[] = $item->dose;
                $total_sdose += $item->dose;
                $count_sdose++;
            } else if($item->dose_source_cd == 'T'){
                //device Time
                $tdose[] = $item->dose;
                $total_tdose += $item->dose;
                $count_tdose++;
            } else if($item->dose_source_cd == 'H'){
                //device Rate
                $hdose[] = $item->dose;
                $total_hdose += $item->dose;
                $count_hdose++;
            } else if($item->dose_source_cd == 'E'){
                //device Other
                $edose[] = $item->dose;
                $total_edose += $item->dose;
                $count_edose++;
            } else {
                //Stop all processing if we are here.
                throw new \Exception('Did NOT recognize dose_source_cd value as ['
                        .$item->dose.']');
            }
            if($prevshowprocdate > '')
            {
                $ravg = number_format($this->getAverage($total_rdose, $count_rdose)
                        , 2, '.', '');
                $cavg = number_format($this->getAverage($total_cdose, $count_cdose)
                        , 2, '.', '');
                $davg = number_format($this->getAverage($total_ddose, $count_ddose)
                        , 2, '.', '');
                $eavg = number_format($this->getAverage($total_edose, $count_edose)
                        , 2, '.', '');

                $qavg = number_format($this->getAverage($total_qdose, $count_qdose)
                        , 2, '.', '');
                $savg = number_format($this->getAverage($total_sdose, $count_sdose)
                        , 2, '.', '');
                
                $sum_alldose = $total_rdose + $total_cdose + $total_ddose + $total_edose
                        + $total_qdose + $total_sdose;
                $count_alldose = $count_rdose + $count_cdose 
                        + $count_ddose + $count_edose
                        + $count_qdose + $count_sdose;
                $total_avg = number_format($this->getAverage($sum_alldose, $count_alldose), 2, '.', '');
                $avgvalue_rows[$prevshowprocdate] = '<td>'.$prevshowprocdate.'</td>'
                        . '<td>'.$ravg.'</td>'
                        . '<td>'.$cavg.'</td>'
                        . '<td>'.$davg.'</td>'
                        . '<td>'.$eavg.'</td>'
                        . '<td>'.$qavg.'</td>'
                        . '<td>'.$savg.'</td>'
                        . '<td>'.$total_avg.'</td>';                
            }
        }
        if(count($rdose) > 0 || count($cdose) > 0 || count($ddose) > 0 || count($edose) > 0 )
        {
            $key = urlencode($groupingkey);
            $linkmarkup = '<a href="#" todohref='.$base_url
                    .'/raptor/editradiationdosehxentry?key='.$key.'">edit</a>';
            
            $rows[] = $this->getRowMarkup($linkmarkup,$showprocdate,$procname,$rdose,$cdose,$ddose,
                    $edose,$etype,
                    $qdose,$qtype,
                    $sdose,$stype,
                    $validationstatus);
            
                $ravg = number_format($this->getAverage($total_rdose, $count_rdose)
                        , 2, '.', '');
                $cavg = number_format($this->getAverage($total_cdose, $count_cdose)
                        , 2, '.', '');
                $davg = number_format($this->getAverage($total_ddose, $count_ddose)
                        , 2, '.', '');
                $eavg = number_format($this->getAverage($total_edose, $count_edose)
                        , 2, '.', '');
                $qavg = number_format($this->getAverage($total_qdose, $count_qdose)
                        , 2, '.', '');
                $savg = number_format($this->getAverage($total_sdose, $count_sdose)
                        , 2, '.', '');
                
                $sum_alldose = $total_rdose + $total_cdose + $total_ddose + $total_edose;
                $count_alldose = $count_rdose + $count_cdose + $count_ddose + $count_edose;
                $total_avg = number_format(($sum_alldose / $count_alldose), 2, '.', '');
                $avgvalue_rows[$showprocdate] = '<td>'.$showprocdate.'</td>'
                        . '<td>'.$ravg.'</td>'
                        . '<td>'.$cavg.'</td>'
                        . '<td>'.$davg.'</td>'
                        . '<td>'.$eavg.'</td>'
                        . '<td>'.$qavg.'</td>'
                        . '<td>'.$savg.'</td>'
                        . '<td>'.$total_avg.'</td>';                
            
            $rowsmarkup = "\n<tr>".implode("</tr>\n<tr>",$rows).'</tr>';
        } else {
            $rowsmarkup = '<!-- NOTHING -->';
        }

        if(count($avgvalue_rows) > 0)
        {
            $avgrowsmarkup = '';
            foreach($avgvalue_rows as $k=>$v)
            {
                $avgrowsmarkup .= "\n<tr>".$v.'</tr>';
            }
        } else {
            $avgrowsmarkup = '<!-- NOTHING -->';
        }
        
        //Format the simple sums.
        $rsum = number_format($total_rdose, 2, '.', '');
        $csum = number_format($total_cdose, 2, '.', '');
        $dsum = number_format($total_ddose, 2, '.', '');
        $esum = number_format($total_edose, 2, '.', '');
        
        $qsum = number_format($total_qdose, 2, '.', '');
        $ssum = number_format($total_sdose, 2, '.', '');
        
        $sum_alldose = number_format($total_rdose + $total_cdose 
                + $total_ddose 
                + $total_edose
                + $total_qdose
                + $total_sdose
                , 2, '.', '');
        $sum_rowsmarkup = "\n<tr>"
                . "<td>$rsum</td>"
                . "<td>$csum</td>"
                . "<td>$dsum</td>"
                . "<td>$esum</td>"
                . "<td>$qsum</td>"
                . "<td>$ssum</td>"
                . "<td>$sum_alldose</td>"
                . "</tr>";
        
    //error_log('radiation tab LOOK>>>>>'.print_r($avgvalue_rows,TRUE));    

        $form["data_entry_area1"]['table_container']['dosetotals'] = array('#type' => 'item',
         '#markup' => '<h3>Accumulated Total Radiation Dose Information</h3>'
            . '<p>The simple cummulative radiation dose exposure of this patient for values found in the system.</p>'
            . '<table id="my-raptor-radiationrawtotal-table" class="non-search-table">'
            . '<thead>'
            . '<th>Radionuclide Dose (mGy)</th>'
            . '<th>Device CTDIvol (mGy)</th>'
            . '<th>Device DLP (mGycm)</th>'
            . '<th>Device Dose (mGy)</th>'

            . '<th>Air Kerma</th>'
            . '<th>DAP</th>'
            
            . '<th>Total Dose (mGy)</th>'
            . '</tr>'
            . '</thead>'
            . '<tbody>'
            . $sum_rowsmarkup
            . '</tbody>'
            . '</table>'
            );
        
        $form["data_entry_area1"]['table_container']['doseaverages'] = array('#type' => 'item',
         '#markup' => '<h3>Rolling Average Dose History</h3>'
            . '<p>The cummulative radiation dose averages for values found in the system for this patient.</p>'
            . '<table id="my-raptor-radiationavg-table" class="dataTable">'
            . '<thead>'
            . '<tr><th>Date</th>'
            . '<th>Radionuclide Average Dose (mGy)</th>'
            . '<th>Device CTDIvol Average (mGy)</th>'
            . '<th>Device DLP Average (mGycm)</th>'
            . '<th>Device Dose Average (mGy)</th>'
            . '<th>Device Air Kerma Avg</th>'
            . '<th>Device DAP Avg</th>'
            . '<th>Total Dose Average (mGy)</th>'
            . '</tr>'
            . '</thead>'
            . '<tbody>'
            . $avgrowsmarkup
            . '</tbody>'
            . '</table>'
            );


        $form["data_entry_area1"]['table_container']['dosedetails'] = array('#type' => 'item',
                 '#markup' => '<h3>Dose History Detail</h3>'
                            . '<p>The radiation dose details for values found in the system for this patient.</p>'
                            . '<table id="my-raptor-radiationdetail-table" class="dataTable">'
                            . '<thead>'
                            . '<tr>'
                            . '<th>Action</th>'
                            . '<th>Exam Date</th>'
                            . '<th>Procedure Type</th>'
                            //. '<th>Height</th>'
                            //. '<th>Weight</th>'
                            //. '<th>BMI</th>'
                            . '<th>Radionuclide Dose (mGy)</th>'
                            . '<th>Device CTDIvol (mGy)</th>'
                            . '<th>Device DLP (mGycm)</th>'
                            . '<th>Device Dose (mGy)</th>'
                            . '<th>Dose Type</th>'
                            . '<th>Entry State</th>'
                            . '</tr>'
                            . '</thead>'
                            . '<tbody>'
                            . $rowsmarkup
                            .  '</tbody>'
                            . '</table>');
        
        $form["data_entry_area1"]['actionarea'] = array(
            '#type'     => 'fieldset',
            '#attributes' => array(
                'class' => array(
                    'data-entry-area'
                )
             ),
            '#disabled' => $disabled,
        );
        
        $key = '';
        $linkmarkup = '<a class="raptor-dose-xs-crud" href="'.$base_url.'/raptor/addradiationdosehxentry">Create New Entry</a>';
        $form["data_entry_area1"]['actionarea']['add'] = array(
            '#markup'=>$linkmarkup,
        );
        
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
