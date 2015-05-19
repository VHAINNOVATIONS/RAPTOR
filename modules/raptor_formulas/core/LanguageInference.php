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

namespace raptor_formulas;

/**
 * Logic for infering intent from language.
 *
 * @author Frank Font of SAN Business Consultants
 */
class LanguageInference 
{
    private $m_supported_modalities = "MR CT NM FL US "; //Must include the space after each!
    
    /**
     * Return a string with the two char code, each with a trailing space!
     */
    public function getSupportedModalityCodes()
    {
        return $this->m_supported_modalities;
    }
    
    /**
     * NULL means no opinion
     */
    public function inferModalityFromPhrase($phrase)
    {
        $haystack = strtoupper(trim($phrase));
        $ma = NULL;
        if(strlen($haystack) > 2)
        {
            if(MATCH_MODALITY_ON_PREFIX)
            {
                //Were they nice enough to prefix with the modality?
                if(substr($haystack,0,1) == '*')
                {
                    //Ignore the first character completely.
                    $first3 = substr($haystack,1,3);
                } else {
                    $first3 = substr($haystack,0,3);
                }
                $real_modality_pos = strpos($this->m_supported_modalities, $first3);  
            } else {
                //We ignored the prefix
                $real_modality_pos = FALSE;    
            }
            if($real_modality_pos !== FALSE)
            {
                //Got it, just remove the space.
                $ma = trim($first3);
            } else {
                //Try to figure it out from the content.
                if(MATCH_MODALITY_STANDARD_TERMS)
                {
                    if(strpos($haystack, 'FLUORO') !== FALSE
                            || strpos($haystack, 'ARTHROGRAM') !== FALSE
                            )
                    {
                        $ma = 'FL';
                    } else
                    if(strpos($haystack, 'MRI') !== FALSE 
                            || strpos($haystack, 'MAGNETIC') !== FALSE)
                    {
                        $ma = 'MR';
                    } else
                    if(strpos($haystack, 'CAT SCAN') !== FALSE 
                            || strpos($haystack, 'CATSCAN') !== FALSE)
                    {
                        $ma = 'CT';
                    } else
                    if(strpos($haystack, 'ECHO') !== FALSE 
                            || strpos($haystack, 'ULTRASOUND') !== FALSE)
                    {
                        $ma = 'US';
                    } else
                    if(strpos($haystack, 'NUCLEAR') !== FALSE 
                            || strpos($haystack, 'SCAN') !== FALSE
                            || strpos($haystack, 'BONE') !== FALSE)
                    {
                        $ma = 'NM';
                    }
                }
                //Try the custom matches
                if(CUSTOM_TERMS4MATCH_MR != NULL)
                {
                    $ma = $this->checkModalityMatch($haystack
                            ,explode(',', CUSTOM_TERMS4MATCH_MR)
                            ,'MR');
                }
                if($ma == NULL && CUSTOM_TERMS4MATCH_CT != NULL)
                {
                    $ma = $this->checkModalityMatch($haystack
                            ,explode(',', CUSTOM_TERMS4MATCH_CT)
                            ,'CT');
                }
                if($ma == NULL && CUSTOM_TERMS4MATCH_NM != NULL)
                {
                    $ma = $this->checkModalityMatch($haystack
                            ,explode(',', CUSTOM_TERMS4MATCH_NM)
                            ,'NM');
                }
                if($ma == NULL && CUSTOM_TERMS4MATCH_FL != NULL)
                {
                    $ma = $this->checkModalityMatch($haystack
                            ,explode(',', CUSTOM_TERMS4MATCH_FL)
                            ,'FL');
                }
                if($ma == NULL && CUSTOM_TERMS4MATCH_US != NULL)
                {
                    $ma = $this->checkModalityMatch($haystack
                            ,explode(',', CUSTOM_TERMS4MATCH_US)
                            ,'US');
                }
            }
        }

        //Return the inference.
        return $ma;
    }
    
    private function checkModalityMatch($haystack, $customterms_ar, $return_on_match, $return_on_fail=NULL)
    {
        foreach($customterms_ar as $term)
        {
            if(strpos($haystack, $term) !== FALSE)
            {
                return $return_on_match;
                break;
            }
        }
        return $return_on_fail;
    }
    
    /**
     * TRUE means yes contrast
     * FALSE means no contrast
     * NULL means no opinion
     */
    public function inferContrastFromPhrase($phrase)
    {
        $haystack = strtoupper($phrase);

        //Look for indication of both
        $both_contrast = FALSE; //Assume not both
        //TODO -- pull the content from raptor_list_kw_withandwithout_contrast
        $both_contrast_ind[] = 'W&WO CONT';
        $both_contrast_ind[] = 'W&W/O CONT';
        $both_contrast_ind[] = 'WITH AND WITHOUT CONT';
        foreach($both_contrast_ind as $needle)
        {
            $p = strpos($haystack, $needle);
            if($p !== FALSE)
            {
                $both_contrast = TRUE;
                break;
            }
        }
        if(!$both_contrast)
        {
            //Look for the NO indicators
            $no_contrast = NULL;
            $no_contrast_ind = array();
            //TODO -- pull the content from raptor_list_kw_without_contrast
            $no_contrast_ind[] = 'WO CONT';
            $no_contrast_ind[] = 'W/O CONT';
            $no_contrast_ind[] = 'WN CONT';
            $no_contrast_ind[] = 'W/N CONT';
            $no_contrast_ind[] = 'NO CONT';
            $no_contrast_ind[] = 'WITHOUT CONT';
            $no_contrast_ind[] = 'NON-CONT';
            foreach($no_contrast_ind as $needle)
            {
                $p = strpos($haystack, $needle);
                if($p !== FALSE)
                {
                    $no_contrast = TRUE;
                    break;
                }
            }

            //Look for the YES indicators
            $yes_contrast = NULL;
            $yes_contrast_ind = array();
            //TODO -- pull the content from raptor_list_kw_with_contrast
            $yes_contrast_ind[] = 'W CONT';
            $yes_contrast_ind[] = 'WITH CONT';
            $yes_contrast_ind[] = 'W/IV CONT';
            $yes_contrast_ind[] = 'INCLUDE CONT';
            $yes_contrast_ind[] = 'INC CONT';
            foreach($yes_contrast_ind as $needle)
            {
                $p = strpos($haystack, $needle);
                if($p !== FALSE)
                {
                    $yes_contrast = TRUE;
                    break;
                }
            }

            //Return our analysis result.
            if($no_contrast === TRUE && $yes_contrast === NULL)
            {
                return FALSE;
            }
            if($no_contrast === NULL && $yes_contrast === TRUE)
            {
                return TRUE;
            }
        }
        
        //No clues or confusing indications.
        return NULL;
    }

    /**
     * Return the collection of words keywords to look for in phrases
     */
    public function inferOrderPhraseKeywords($phrase)
    {
        //Terms to ignore in the order name for keyword matching purposes
        $ignorelist = array('CT','MR','FL','NM','W/O'
            ,'W','W&WO','W/WO','INCLUDE','CONT'
            ,'WITH','WITHOUT'
            ,'CONTRAST','W/IV'); 
        return $this->inferKeywords($phrase, $ignorelist);
    }
    
    /**
     * Return words from the phrase after removing those on the ignore list
     */
    public function inferKeywords($phrase, $ignorelist=NULL)
    {
        $keywords = explode(' ', $phrase);
        if($ignorelist !== NULL)
        {
            foreach($keywords as $kw)
            {
                $ignore = FALSE;
                foreach($ignorelist as $ilw)
                {
                    if($kw == $ilw)
                    {
                        $ignore = TRUE;
                        break;
                    }
                }
                if(!$ignore)
                {
                    $keep[] = $kw;
                }
            }
            $keywords = $keep;
        }
        return $keywords;
    }
    
    public function getProtocolMatchCluesMap($phrase, $cpt_codes=NULL)
    {
        $clues = array();
        if($cpt_codes == NULL)
        {
            $cpt_codes = array(); //TODO provide the codes associated with this order
        }
        $clues['cpt_codes'] = $cpt_codes;  
        $clues['keywords'] = $this->inferOrderPhraseKeywords($phrase);
        $clues['modality_abbr'] = $this->inferModalityFromPhrase($phrase);
        $clues['contrast'] = $this->inferContrastFromPhrase($phrase);
        return $clues;
    }
}
