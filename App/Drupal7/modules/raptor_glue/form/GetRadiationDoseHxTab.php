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
    
    private function getRowMarkup($linkmarkup,$showprocdate,$procname,$rdose,$cdose,$ddose,$edose,$etype,$validationstatus)
    {
        $rdosemarkup = '<ol>'.implode('<li>', $rdose).'</ol>';
        $cdosemarkup = '<ol>'.implode('<li>', $cdose).'</ol>';
        $ddosemarkup = '<ol>'.implode('<li>', $ddose).'</ol>';
        $edosemarkup = '<ol>'.implode('<li>', $edose).'</ol>';
        
        $onerow = '<td>'.$linkmarkup.'</td>'
              . '<td>'.$showprocdate.'</td>'
              . '<td>'.$procname.'</td>'
              . '<td>'.$rdosemarkup.'</td>'
              . '<td>'.$cdosemarkup.'</td>'
              . '<td>'.$ddosemarkup.'</td>'
              . '<td>'.$edosemarkup.'</td>'
              . '<td>'.$etype.'</td>'
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
        global $base_url;

        $protocoldashboard = $this->m_oDD->getDashboardDetails();
        $patientid=$protocoldashboard['PatientID'];
        $result = $this->getQueryResult($patientid, $myvalues);
        
        $trackingid = '';
        $groupingkey='';
        $showprocdate = '';
        $procname = '';
        $etype = '';
        $validationstatus = 'Unvalidated';  //TODO
        $ddose = array();
        $rdose = array();
        foreach($result as $item) 
        {
            $prevshowprocdate = $showprocdate;
            $prevprocname = $procname;
            $prevetype = $etype;
            $prevgroupingkey = $groupingkey;
            $siteid = isset($item->siteid) ? $item->siteid : '';
            $IEN = isset($item->IEN) ? $item->IEN : '';
            $trackingid = $siteid.'-'.$IEN;
            $etype = ($item->dose_type_cd == 'A') ? 'Actual' : 'Estimated';
            $procname = $item->name;
            $groupingkey = $item->dose_dt . '_' . $item->patientid . '_' . $trackingid . '_' . $item->dose_type_cd;
            if($prevshowprocdate > '' && $prevgroupingkey != $groupingkey)
            {
                $key = urlencode($groupingkey);
                $linkmarkup = '<a href="'.$base_url.'/raptor/editradiationdosehxentry?key='.$key.'">edit</a>';
                $rows[] = $this->getRowMarkup($linkmarkup,$prevshowprocdate,$prevprocname,$rdose,$cdose,$ddose,$edose,$prevetype,$validationstatus);

                //Initialize the value accumulators
                $rdose = array();
                $cdose = array();
                $ddose = array();
                $edose = array();
            }
            $showprocdate = date('Y-m-d',strtotime($item->dose_dt));
            //Add this stuff into our accumulators
            if($item->dose_source_cd == 'R')
            {
                //Radioisotope
                $rdose[] = $item->dose;
            } else if($item->dose_source_cd == 'C'){
                //device CTDIvol
                $cdose[] = $item->dose;
            } else if($item->dose_source_cd == 'D'){
                //device DLP
                $ddose[] = $item->dose;
            } else if($item->dose_source_cd == 'E'){
                //device Other
                $edose[] = $item->dose;
            } else {
                //Stop all processing if we are here.
                throw new \Exception('Did NOT recognize dose_source_cd value as ['.$item->dose.']');
            }
        }
        if(count($rdose) > 0 || count($cdose) > 0 || count($ddose) > 0 || count($edose) > 0 )
        {
            $key = urlencode($groupingkey);
            $linkmarkup = '<a href="'.$base_url.'/raptor/editradiationdosehxentry?key='.$key.'">edit</a>';
            $rows[] = $this->getRowMarkup($linkmarkup,$showprocdate,$procname,$rdose,$cdose,$ddose,$edose,$etype,$validationstatus);
            $rowsmarkup = "\n<tr>".implode("</tr>\n<tr>",$rows).'</tr>';
        } else {
            $rowsmarkup = '<!-- NOTHING -->';
        }
        

        $form["data_entry_area1"]['table_container']['protocols'] = array('#type' => 'item',
                 '#markup' => '<table id="my-raptor-dialog-table" class="dataTable">'
                            . '<thead>'
                            . '<tr>'
                            . '<th>Action</th>'
                            . '<th>Exam Date</th>'
                            . '<th>Procedure Type</th>'
                            //. '<th>Height</th>'
                            //. '<th>Weight</th>'
                            //. '<th>BMI</th>'
                            . '<th>Radioisotope Dose (mGy)</th>'
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
        
        $key = 
        $linkmarkup = '<a href="'.$base_url.'/raptor/addradiationdosehxentry">Create New Entry</a>';
        $form["data_entry_area1"]['actionarea']['add'] = array(
            '#markup'=>$linkmarkup,
        );
        
        return $form;
    }
}
