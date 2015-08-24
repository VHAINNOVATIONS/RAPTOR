<?php
/**
 * @file
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase 2
 * Open Source VA Innovation Project 2011-2015
 * VA Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, Alex Podlesny, et al
 * Contacts: acasertano@sanbusinessconsultants.com, ffont@sanbusinessconsultants.com
 * ------------------------------------------------------------------------------------
 * 
 */ 

namespace raptor_ewdvista;

require_once 'EwdUtils.php';

/**
 * Helper for returning notes content
 *
 * @author Frank Font of SAN Business Consultants
 */
class NotesHelper
{
    
    //Declare the field numbers
    private static $FLD_FACILITY = 1;
    private static $FLD_UNKNOWN2 = 2;
    private static $FLD_DATETIMESTR = 3;
    private static $FLD_TITLE = 4;
    private static $FLD_AUTHOR = 5;
    private static $FLD_DETAILS = 6;
    
    
    public function getFormattedNotes($rawresult)
    {
        try
        {
error_log("LOOK notes stuff raw >>>".print_r($rawresult, TRUE));
            if(!is_array($rawresult))
            {
                $errmsg = "Expected an array for notes result but instead got $rawresult";
                error_log("$errmsg >>>".print_r($rawresult, TRUE));
                throw new \Exception($errmsg);
            }
            $formatted = array();
            foreach($rawresult as $onegroup)
            {
error_log("LOOK notes one group stuff >>>".print_r($onegroup, TRUE));
                foreach($onegroup as $blocks)
                {
error_log("LOOK notes blocks >>>".print_r($blocks, TRUE));
                    foreach($blocks as $onenoteitem)
                    {
    error_log("LOOK notes one item >>>".print_r($onenoteitem, TRUE));

                        $localTitle = $onenoteitem[self::$FLD_TITLE];
                        $datetimestr = $onenoteitem[self::$FLD_DATETIMESTR];
                        if(strlen($localTitle) > RAPTOR_DEFAULT_SNIPPET_LEN)
                        {
                            $snippetText = substr($localTitle, 0, RAPTOR_DEFAULT_SNIPPET_LEN).'...';
                        } else {
                            $snippetText = $localTitle;
                        }
                        $authorName = $onenoteitem[self::$FLD_AUTHOR];
                        $facility = $onenoteitem[self::$FLD_FACILITY];
                        $notetext = $onenoteitem[self::$FLD_DETAILS];
                        $formatted[] = array(
                                            "Type"=>$localTitle, 
                                            "Date"=>$datetimestr,
                                            "Snippet" => $snippetText,
                                            "Details" => array('Type of Note'=>$localTitle, 
                                                            'Author'=>$authorName, 
                                                            'Note Text'=>$notetext, 
                                                            'Facility'=>$facility,
                                                )
                            );
                    }
                }
            }
            /*
            $formatted[] = array(
                'Type' => 'RAPTOR SAFETY CHECKLIST',
                'Date' => '07/16/2015 02:51 pm',
                'Snippet' => 'RAPTOR SAFETY CHECKLIST',
                'Details' => array
                    (
                        'Type of Note' => 'RAPTOR SAFETY CHECKLIST',
                        'Author' => NULL,
                        'Note Text' =>  'demo 123 LOCAL TITLE: RAPTOR SAFETY CHECKLIST yadayada',
                    )
                );
             */
            
            return $formatted;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
