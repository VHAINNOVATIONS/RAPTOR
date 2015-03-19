<?php
/**
 * @file
 * ----------------------------------------------------------------------------
 * Created by SAN Business Consultants
 * Designed and implemented by Frank Font(ffont@sanbusinessconsultants.com)
 * In collaboration with Andrew Casertano(acasertano@sanbusinessconsultants.com)
 * Open source enhancements to this module are welcome!  
 * Contact SAN to share updates.
 *
 * Copyright 2014-2015 SAN Business Consultants
 * , a Maryland USA company (sanbusinessconsultants.com)
 *
 * Licensed under the GNU General Public License, Version 2 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.gnu.org/copyleft/gpl.html
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ----------------------------------------------------------------------------
 *
 * This is a simple decision support engine module for Drupal.
 */

namespace simplerulesengine;

/**
 * The source reason for a rule being triggered.  
 * There can be several of these for any one rule.
 *
 * @author Frank Font of SAN Business Consultants
 */
class SRESourceItem 
{
    private $m_rulename_tx; //Unique name of the rule
    private $m_category_tx; //Category of the rule eg Medicine, Procedure
    private $m_summary_msg; //Summary message to show the user.
    private $m_confirmation_required; //TRUE if user must confirm, else FALSE
    private $m_msg_tx;  //Message to show the user.
    private $m_explanation_tx;  //The rule, if any can be simply articulated
    
    public function __construct($rule_name
            ,$category_tx
            ,$summary_msg
            ,$confirmation_required
            ,$msg_tx,$explanation_tx)
    {
        $this->m_rulename_tx=$rule_name;
        $this->m_category_tx=$category_tx;
        $this->m_summary_msg=$summary_msg;
        $this->m_confirmation_required=$confirmation_required;
        $this->m_msg_tx=$msg_tx;
        $this->m_explanation_tx=$explanation_tx;
    }

    public function getRuleName()
    {
        return $this->m_rulename_tx;
    }
    public function getCategory()
    {
        return $this->m_category_tx;
    }
    public function isConfirmationRequired()
    {
        return $this->m_confirmation_required;
    }
    public function getExplanation()
    {
        return $this->m_explanation_tx;
    }
    public function getMessage()
    {
        return $this->m_msg_tx;
    }    

    public function getSummaryMessage()
    {
        return $this->m_summary_msg;
    }    

    private static function __isMatch($value1,$value2,$null_is_wildcard=TRUE)
    {
        if($null_is_wildcard)
        {
            if($value1 === NULL)
            {
                return TRUE;
            }
        }
        return ($value1 === $value2);
    }

    /**
     * Determine if this instance substantially matches another instance.
     * @param type $match_csi
     * @param type $null_is_wildcard
     * @return boolean TRUE if this is a match.
     */
    public function isMatch($match_csi,$null_is_wildcard=TRUE)
    {
        if($match_csi===null)
        {
            die("Did NOT provide a valid "
                    . "instace of SRESourceItem for isMatch!");
        }
        if(!SRESourceItem::__isMatch($match_csi->getCategory()
                , $this->getCategory(),$null_is_wildcard))
        {
            return FALSE;
        }
        if(!SRESourceItem::__isMatch($match_csi->getRuleName()
                , $this->getRuleName(),$null_is_wildcard))
        {
            return FALSE;
        }
        //We are here because we did not reject the match.
        return TRUE;
    }
}
