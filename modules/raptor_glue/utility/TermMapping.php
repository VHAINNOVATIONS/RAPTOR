<?php
/**
 * @file
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

/**
 * This file contains important terms used globally in RAPTOR
 *
 * @author Frank Font of SAN Business Consultants
 */
class TermMapping
{
    public static function getQAScoreLanguageMapping()
    {
        module_load_include('inc', 'raptor_glue', 'core/QualityAssuranceDefs');
        return array(  
                0  =>  t(QA_SCORE_TERM_0)
               ,1  =>  t(QA_SCORE_TERM_1)
               ,2  =>  t(QA_SCORE_TERM_2)
               ,3  =>  t(QA_SCORE_TERM_3)
               ,4  =>  t(QA_SCORE_TERM_4)
               ,5  =>  t(QA_SCORE_TERM_5)
            );
    }
    
    public static function getQAScoreLanguage($score)
    {
        $terms = TermMapping::getQAScoreLanguageMapping();
        $language = $terms[$score];
        if($language <= '')
        {
            throw new \Exception("There is NO QA term for score='$score'");
        }
        return $language;
    }
}