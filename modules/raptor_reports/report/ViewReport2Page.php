<?php
/**
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

use \DateTime;

require_once 'AReport.php';

/**
 * This class returns the User Activity Analysis Report
 *
 * CLIN2 1.7
 * 
 * @author Frank Font of SAN Business Consultants
 */
class ViewReport2Page extends AReport
{

    public function getName() 
    {
        return 'User Activity Analysis';
    }

    public function getDescription() 
    {
        return 'Shows analysis of user activity in the system';
    }

    public function getRequiredPrivileges() 
    {
        $aRequire['VREP2'] = 1;
        return $aRequire;
    }
    
    public function getMenuKey() 
    {
        return 'raptor/viewrepusract1';
    }
    
    /**
     * Get the values to populate the form.
     * @return type result of the queries as an array
     */
    function getFieldValues()
    {
        $myvalues = array();
        //$myvalues['formmode'] = 'V';
        
		$result = db_query("Call raptor_user_dept_analysis('user')")
				->execute();
		
		$result = db_select('temp4', 't')
				->fields('t')
				->orderBy('modality_abbr', 'DESC')
				->orderBy('_year', 'DESC')
				->orderBy('quarter', 'DESC')
				->orderBy('week', 'DESC')
				->orderBy('day', 'DESC')
				->orderBy('username', 'DESC')
				->execute();
		
		while($res = $result->fetchAssoc())
		{
			$myvalues[] = $res;
		}
        return $myvalues;
    }
	
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues)
    {
		$dtF = new DateTime("@0");
        //$form = $this->m_oPageHelper->getForm($form, $form_state, $disabled, $myvalues, $this->m_aHelpText);

        $form["data_entry_area1"] = array(
            '#prefix' => "\n<section class='user-admin raptor-dialog-table'>\n",
            '#suffix' => "\n</section>\n",
        );
        $form["data_entry_area1"]['table_container'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="raptor-dialog-table-container">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );

        $rows = '';
		foreach($myvalues as $val)
		{
			if(((string)$val['_year']>0) && ((string)$val['quarter']>0) && ((string)$val['week']>0))
			{
				$max_A_S = round((int)$val['Max_Time_A_S']);
				$dtT = new DateTime("@$max_A_S");
				$max_A_S = $dtF->diff($dtT)->format('%a days, %h hours and %i minutes');
				
				$avg_A_S = round((int)$val['Avg_Time_A_S']);
				$dtT = new DateTime("@$avg_A_S");
				$avg_A_S = $dtF->diff($dtT)->format('%a days, %h hours and %i minutes');
				
				$max_A_C = round((int)$val['Max_Time_A_C']);
				$dtT = new DateTime("@$max_A_C");
				$max_A_C = $dtF->diff($dtT)->format('%a days, %h hours and %i minutes');
				
				$avg_A_C = round((int)$val['Avg_Time_A_C']);
				$dtT = new DateTime("@$avg_A_C");
				$avg_A_C = $dtF->diff($dtT)->format('%a days, %h hours and %i minutes');
				
				$max_collab = round((int)$val['Max_Time_Collab']);
				$dtT = new DateTime("@$max_collab");
				$max_collab = $dtF->diff($dtT)->format('%a days, %h hours and %i minutes');
				
				$avg_collab = round((int)$val['Avg_Time_Collab']);
				$dtT = new DateTime("@$avg_collab");
				$avg_collab = $dtF->diff($dtT)->format('%a days, %h hours and %i minutes');
				
				$rows .= '<tr>'
					. '<td>' . $val['modality_abbr'] . '</td>'
					. '<td>' . $val['_year'] . '</td>'
					. '<td>' . $val['quarter'] . '</td>'
					. '<td>' . $val['week'] . '</td>'
					. '<td>' . $val['day'] . '</td>'
					. '<td title="'.$val['uid'].'">' . $val['username'] . '</td>'
					. '<td>' . $val['role_nm'] . '</td>'
					. '<td>' . $val['most_recent_login_dt'] . '</td>'
					. '<td>' . $val['Total_Approved']  . '</td>'
					. '<td>' . $val['Count_Collab']  . '</td>'
					. '<td>' . $val['Total_Acknowledge']  . '</td>'
					. '<td>' . $val['Total_Complete']  . '</td>'
					. '<td>' . $val['Total_Suspend']  . '</td>'
					. '<td>' . $max_A_S . '</td>'
					. '<td>' . $avg_A_S . '</td>'
					. '<td>' . $max_A_C . '</td>'
					. '<td>' . $avg_A_C . '</td>'
					. '<td>' . $max_collab . '</td>'
					. '<td>' . $avg_collab . '</td>'
					. '<td>' . $val['Total_Scheduled'] . '</td>'
					
					. '</tr>';
			}
		}

                
//The modality column value comes from modality_abbr fields in the database                
                
        $form["data_entry_area1"]['table_container']['users'] = array('#type' => 'item',
                 '#markup' => '<table class="raptor-dialog-table">'
                            . '<thead><tr>'
                            . '<th title="The modality abbreviation of this metric" >Modality</th>'
                            . '<th title="The year of this metric" >Year</th>'
                            . '<th title="The quarter number of this metric" >Quarter</th>'
                            . '<th title="The week number of this metric, Jan 1 is week 1" >Week</th>'
                            . '<th title="The day number of this metric" >Day</th>'
                            . '<th title="The name of the user" >User Name</th>'
                            . '<th title="The role of the user in the system" >User Role</th>'
                            . '<th title="The most recent login timestamp" >Most recent login</th>'
                            . '<th title="Total number of tickets moved to Approved state">Total Approved</th>'
                            . '<th title="Total number of tickets moved to Collaboration state">Count Picked for Collab</th>'
                            . '<th title="Total number of tickets moved to Acknowledge state">Total Acknowlege</th>'
                            . '<th title="Total number of tickets moved to Complete state">Total Complete</th>'
                            . '<th title="Total number of tickets moved to Suspend state">Total Suspend</th>'
                            . '<th title="Max time a ticket was in Approved state before it was Scheduled">Max Time between Approved and Sched</th>'
                            . '<th title="Average time tickets were in Approved state before were Scheduled">Avg Time Approved to Sched</th>'
                            . '<th title="Max time a ticket was in Approved state before it moved to Completed state">Max Time Approved to Exam Completed</th>'
                            . '<th title="Average time tickets were in Accepted state moving to Completed state">Avg Time Accepted to Exam Completed</th>'
                            . '<th title="Max time a ticket was in Collaboration state">Max Time Collab</th>'
                            . '<th title="Avg time tickets were in Collaboration state">Avg Time Collab</th>'
                            . '<th title="Total number of tickets scheduled">Total Scheduled</th>'
                            . '</tr></thead>'
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
       
        $form['data_entry_area1']['action_buttons']['refresh'] = array('#type' => 'submit'
                , '#attributes' => array('class' => array('admin-action-button'), 'id' => 'refresh-report')
                , '#value' => t('Refresh Report'));
        
        global $base_url;
        $goback = $base_url . '/raptor/viewReports';
        /*
        $form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => '<input class="admin-cancel-button" type="button"'
                . ' value="Cancel"'
                . ' data-redirect="'.$goback.'">');
         */
        $form['data_entry_area1']['action_buttons']['cancel'] = $this->getExitButtonMarkup($goback);
        return $form;
    }
    
    
}
