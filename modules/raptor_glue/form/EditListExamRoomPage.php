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

require_once 'EditListsBasePage.php';

/**
 * This class returns the Admin Information input content
 *
 * @author Frank Font of SAN Business Consultants
 */
class EditListExamRoomPage extends EditListsBasePage
{

    function __construct()
    {
        parent::__construct('raptor_schedule_location'
            ,array('location_tx','description_tx')
            ,array(true,       true)
            ,array('t',        't')
            ,array(16,         100)
            ,array('Location','Description Text')
            ,array('location_tx')
            );
        
        global $base_url;
        
        $url = $base_url.'/raptor/editexamroomoptions';
        $name = 'Edit Examination Room Options';
        $description = 'Exam room options are selectable during the scheduling process.';
        $listname = 'Exam Rooms';
        $reqprivs = array('EERL1'=>1);

        $this->setName($name);
        $this->setListName($listname);
        $this->setDescription($description);
        $this->setURL($url);
        $this->setRequiredPrivs($reqprivs);
    }
}
