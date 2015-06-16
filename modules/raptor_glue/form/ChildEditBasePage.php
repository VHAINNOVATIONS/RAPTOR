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
            /*
            $form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                    , '#markup' => '<input class="admin-cancel-button" type="button"'
                    . ' value="Cancel"'
                    . ' data-redirect="'.$goback.'">');
             */
        }
        return $markup;
    }
}
