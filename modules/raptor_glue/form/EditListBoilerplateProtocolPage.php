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
class EditListBoilerplateProtocolPage extends EditListsBasePage
{

    function __construct()
    {
        parent::__construct('raptor_boilerplate_protocol'
            ,array('category_tx','title_tx','content_tx')
            ,array(TRUE, TRUE, TRUE)
            ,array('t','t','t')
            ,array(50,40,250)
            ,array('Category','Title','Snippet')
            ,array('category_tx','title_tx')
            );

        global $base_url;
        $url = $base_url.'/raptor/editboilerplateprotocoloptions';
        $name = 'Edit Protocol Boilerplate Text Options';
        $description = 'These are snippets of boilerplate text that can be selected by button click during protocol workflow phase.';
        $listname = 'Protocol Boilerplate Text';
        $reqprivs = array('EBO1'=>1);

        $this->setName($name);
        $this->setListName($listname);
        $this->setDescription($description);
        $this->setURL($url);
        $this->setRequiredPrivs($reqprivs);
    }
}
