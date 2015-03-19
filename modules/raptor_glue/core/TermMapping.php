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
        return array(  
                0  =>  t('Not Evaluated')
               ,1  =>  t('Needs significant improvement')
               ,2  =>  t('Needs improvement')
               ,3  =>  t('Satisfactory')
               ,4  =>  t('Very good')
               ,5  =>  t('Outstanding'));        
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