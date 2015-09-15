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

namespace raptor_formulas;

module_load_include('php', 'raptor_glue', 'core/Config');
module_load_include('php', 'raptor_datalayer', 'core/UserInfo');

/**
 * Logic for scoring order match
 *
 * @author Frank Font of SAN Business Consultants
 */
class MatchOrderToUser 
{
    private $m_userinfo;
    
    public function __construct($oUser) 
    {
        $this->m_userinfo = $oUser;
    }
    
    /**
     * Higher score for higher relevance to the user on interval.
     * @return array (score, comment)
     */
    public function getTicketRelevance($aTicket)
    {

        $oUser = $this->m_userinfo;
         
        $nNow = time();
        $aProcName=explode(' ',strtoupper($aTicket[\raptor\WorklistColumnMap::WLIDX_STUDY]));
        $score=0;
        $addscore=0;
        $comment=array();
        
        //Add 100pts if the ticket is assigned to the $oUser (always factor this in)
        $a = $aTicket[\raptor\WorklistColumnMap::WLIDX_ASSIGNEDUSER];
        if(is_array($a))
        {
            if($oUser->getUserID() == $a['uid'])
            {
                $score += WLSCORE_ASSIGNED;    
                $comment['assigned'] = WLSCORE_ASSIGNED;
            }
        }

        //Score depends on rights and workflow mode.
        $bFullScoring = FALSE;
        if($aTicket[\raptor\WorklistColumnMap::WLIDX_WORKFLOWSTATUS] == 'AC' 
                || $aTicket[\raptor\WorklistColumnMap::WLIDX_WORKFLOWSTATUS] == 'CO' 
                || $aTicket[\raptor\WorklistColumnMap::WLIDX_WORKFLOWSTATUS] == 'RV' )
        {
            if($oUser->getPrivilegeSetting('PWI1') == 1)    //Can protocol an order
            {
                $bFullScoring = TRUE;
                if($aTicket[\raptor\WorklistColumnMap::WLIDX_WORKFLOWSTATUS] == 'RV')
                {
                    //Ready for review means something to this user.    20140811
                    $score += WLSCORE_REVIEW;
                    $comment['review'] = WLSCORE_REVIEW;
                }
            }
        } else if($aTicket[\raptor\WorklistColumnMap::WLIDX_WORKFLOWSTATUS] == 'AP' 
                || $aTicket[\raptor\WorklistColumnMap::WLIDX_WORKFLOWSTATUS] == 'PA' ) {
            if($oUser->getPrivilegeSetting('CE1') == 1)     //Can complete an examination
            {
                $bFullScoring = TRUE;
            }
        }
             
        if($bFullScoring)
        {
            //Score the urgency.
            if($aTicket[\raptor\WorklistColumnMap::WLIDX_URGENCY] == 'STAT')
            {
                $score += WLSCORE_URGENCY_STAT;
                $comment['STAT'] = WLSCORE_URGENCY_STAT;
            } 
            else if($aTicket[\raptor\WorklistColumnMap::WLIDX_URGENCY] == 'URGENT') 
            {
                $score += WLSCORE_URGENCY_URGENT;
                $comment['URGENT'] = WLSCORE_URGENCY_URGENT;
            }    

            //Factor in the modality too
            if($oUser->getModalityPreferencesOverrides())
            {
                $aModality=$oUser->getModalityPreferencesOverrides();
            } else {
                $aModality=$oUser->getModalityPreferences();
            }
            if(in_array($aTicket[\raptor\WorklistColumnMap::WLIDX_MODALITY], $aModality))
            {
                //Position does not matter for modality
                $score += WLSCORE_MODALITY;
                $comment['modality'] = WLSCORE_MODALITY;
            }

            //Factor in the anatomy keywords
            if($oUser->hasWeightedAnatomyPreferencesOverrides())
            {
                $aAnatomy=$oUser->getWeightedAnatomyPreferencesOverrides();
            } else {
                $aAnatomy=$oUser->getWeightedAnatomyPreferences();
            }
            $aAG = $aAnatomy[0];
            $nKWMatches = 0;
            foreach($aAG as $sKeyword)
            {
                if(in_array($sKeyword,$aProcName))
                {
                    $nKWMatches++;
                }
            }
            $addscore = WLSCORE_KWM_MOST * $nKWMatches;
            $score += $addscore;
            if($nKWMatches > 0)
            {
                $comment['kwg1'] = $addscore;
            }
            $nKWMatches = 0;
            $aAG = $aAnatomy[1];
            foreach($aAG as $sKeyword)
            {
                if(in_array($sKeyword,$aProcName))
                {
                    $nKWMatches++;
                }
            }
            $addscore = WLSCORE_KWM_MODERATE * $nKWMatches;
            $score += $addscore;
            if($nKWMatches > 0)
            {
                $comment['kwg2'] = $addscore;
            }
            $nKWMatches = 0;
            $aAG = $aAnatomy[2];
            foreach($aAG as $sKeyword)
            {
                if(in_array($sKeyword,$aProcName))
                {
                    $nKWMatches++;
                }
            }
            $addscore = WLSCORE_KWM_LEAST * $nKWMatches;
            $score += $addscore;
            if($nKWMatches > 0)
            {
                $comment['kwg3'] = $addscore;
            }

            //See if there is scheduled time to consider.
            $aSchedInfo = $aTicket[\raptor\WorklistColumnMap::WLIDX_SCHEDINFO];
            if(isset($aSchedInfo['EventDT']) && !isset($aSchedInfo['CanceledDT']))
            {
                $nEventSched = strtotime($aSchedInfo['EventDT']);
                if($nEventSched<$nNow)
                {
                    //Event date has already passed!
                    $addscore = WLSCORE_MISSED_SCHED_DT;
                    $score += $addscore;
                    $comment['sedt'] = $addscore;
                } else {
                    $nDeltaDO=abs($nEventSched-$nNow);
                    $n1Days=86400;
                    if($nDeltaDO < $n1Days)
                    {
                        //Less than 24 hours away.
                        $addscore = WLSCORE_SCHED_IN24HR;
                        $score += $addscore;
                        $comment['sedt'] = $addscore;
                    } else {
                        $nDaysUntil=ceil($nDeltaDO/$n1Days);
                        if($nDaysUntil <= 7)
                        {
                            $addscore = WLSCORE_SCHED_DAYSAWAYIN7 / $nDaysUntil;
                            $score += $addscore;
                            $comment['sedt'] = $addscore;
                        }
                    }
                }
                //drupal_set_message('Found schedule date <br>event=' . date(DATE_ISO8601, $nEventSched) . '<br>now=' . date(DATE_ISO8601,$nNow) . '<br>diff=' . ($nEventSched-$nNow) . '<br>' . print_r($comment, TRUE) . '<br>'  . print_r($aTicket[\raptor\WorklistColumnMap::WLIDX_SCHEDINFO],TRUE));
            }

            if(DISABLE_TICKET_AGE1_SCORING)
            {
                //This has been disabled, generally done because our test data is YEARS OLD thus all tickets get HUGE scores if not disabled!
                $comment['#disabled_age1'] = 'age1 criteria was ignored!';
            } else {
                //Factor in the age of the ticket too, older ticket scores slightly higher.
                if(isset($aTicket[\raptor\WorklistColumnMap::WLIDX_DATETIMEDESIRED]))
                {
                    $aDO = explode('@',$aTicket[\raptor\WorklistColumnMap::WLIDX_DATETIMEDESIRED]);
                    $aDO = date_parse($aDO[0]);
                    $nDO = mktime(0,0,0,$aDO['month'],$aDO['day'],$aDO['year']);
                } else {
                    $nDO=$nNow;
                }
                $nDeltaDO=($nNow-$nDO); //20150914
                if($nDeltaDO > 0)
                {
                    $n1Days=86400; //60*60*24
                    $n7Days=604800;
                    $nWeeksOld=ceil($nDeltaDO/$n7Days);
                    if($nWeeksOld > 0)
                    {
                        $addscore = (WLSCORE_AGE_WEEKS_FACTOR * $nWeeksOld);
                        $comment['age1'] = $addscore;
                        $score+=$addscore;
                    }
                }
            }

            if(DISABLE_TICKET_AGE2_SCORING)
            {
                //This has been disabled, generally done because our test data is YEARS OLD thus all tickets get HUGE scores if not disabled!
                $comment['#disabled_age2'] = 'age2 criteria was ignored!';
            } else {
                if(isset($aTicket[\raptor\WorklistColumnMap::WLIDX_DATEORDERED]))
                {
                    $aDO = explode('@',$aTicket[\raptor\WorklistColumnMap::WLIDX_DATEORDERED]);
                    $aDO = date_parse($aDO[0]);
                    $nDO = mktime(0,0,0,$aDO['month'],$aDO['day'],$aDO['year']);
                } else {
                    $nDO=$nNow;
                }
                $nDeltaDO=($nNow-$nDO); //20150914
                if($nDeltaDO > 0)
                {
                    $n1Days=86400; //60*60*24
                    $n7Days=604800;
                    $nDaysOld=ceil($nDeltaDO/$n1Days);
                    if($nDaysOld > 0)
                    {
                        $addscore = (WLSCORE_AGE_DAYS_FACTOR * $nDaysOld);
                        $comment['age2'] = $addscore;
                        $score+=$addscore;
                    }
                }
            }
        }

        return array(ceil($score),$comment);         
     }
}
