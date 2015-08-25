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

require_once 'PageNavigation.php';

/**
 * An edit page that is a child of a parent page.
 *
 * @author Frank Font of SAN Business Consultants
 */
abstract class ChildEditBasePage implements \raptor\PageNavigation
{
    protected $m_gobackurl = NULL;
    protected $m_gobackurlparams_arr = NULL;

    public function getGobacktoFullURL() 
    {
        if(!is_array($this->m_gobackurlparams_arr))
        {
            return $this->m_gobackurl;
        }
        $concat = '';
        $params = '';
        foreach($this->m_gobackurlparams_arr as $k=>$v)
        {
            if($k == '')
            {
                $params .= $concat.$v;
            } else {
                $params .= $concat.$k.'='.$v;
            }
            $concat = '&';
        }
        return $this->m_gobackurl.'?'.$params;
    }
    
    public function getGobacktoURL()
    {
        return $this->m_gobackurl;
    }
    
    public function getGobacktoURLParams()
    {
        if($this->m_gobackurlparams_arr == NULL)
        {
            return array();
        }
        return $this->m_gobackurlparams_arr;
    }
    
    public function setGobacktoURL($url,$gobackurlparams_arr=NULL) 
    {
        $this->m_gobackurl = $url;
        $this->m_gobackurlparams_arr = $gobackurlparams_arr;
    }
    
    /**
     * @return a myvalues array
     */
    function getFieldValues()
    {
        throw new \Exception('Not implemented');
    }
    
    /**
     * Use form state to validate the form.
     * Use this if you have to alter the form state!
     */
    function looksValidFormState($form, &$form_state)
    {
        $myvalues = $form_state['values'];
        return $this->looksValid($form, $myvalues);
    }    
    
    /**
     * Some checks to validate the data before we try to save it.
     */
    function looksValid($form, $myvalues)
    {
        throw new \Exception('Not implemented');
    }    
    
    /**
     * Write the values into the database.
     */
    function updateDatabase($form, $myvalues)
    {
        throw new \Exception('Not implemented');
    }

    /**
     * @return array of all option values for the form
     */
    function getAllOptions()
    {
        throw new \Exception('Not implemented');
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    abstract function getForm($form, &$form_state, $disabled, $myvalues_override);

    function getExitButtonMarkup($goback='',$label='Exit')
    {
        if($goback == '')
        {
            $markup = array('#type' => 'item'
                    , '#markup' => '<a class="admin-cancel-button" href="#">'.$label.'</a>');
        } else {
            $markup = array('#type' => 'item'
                    , '#markup' => '<a class="admin-cancel-button" href="'.$goback.'">'.$label.'</a>');
        }
        return $markup;
    }
}
