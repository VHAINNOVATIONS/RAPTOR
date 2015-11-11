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
 * Copyright 2015 SAN Business Consultants, a Maryland USA company (sanbusinessconsultants.com)
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ------------------------------------------------------------------------------------
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