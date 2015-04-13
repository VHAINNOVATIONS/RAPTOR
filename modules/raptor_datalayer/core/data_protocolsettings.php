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

require_once ("data_utility.php");

/**
 * Returns information about the protocol settings.
 *
 * @author Frank Font of SAN Business Consultants
 */
class ProtocolSettings
{
    public function getDefaultValuesRawResult($protocol_shortname)
    {
        if($protocol_shortname == null)
        {
            die('The protocol_shortname value cannot be null for template query!');
        }
        $result = NULL;
        try{
            $result = db_select('raptor_protocol_template','p')
                    ->fields('p')
                    ->condition('protocol_shortname', $protocol_shortname)
                    ->execute();
            if($result->rowCount() !== 1)
            {
                $msg = 'Trouble getting 1 protocol tempate for ['.$protocol_shortname.'] because found '.$result->rowCount().' instead!';
                error_log($msg);
                //drupal_set_message('Contact support because '.$msg,'error');
            }
        } catch (\Exception $ex) {
            $msg = 'Trouble getting protocol settings for ['.$protocol_shortname.'] because '.$ex->getMessage();
            error_log($msg);
            //drupal_set_message('Contact support because '.$msg,'error');
        }
        return $result;
    }

    /**
     * This returns data used by the AJAX process to populate default values
     * @param type $protocol_shortname
     * @return array of keys and values
     */
    public function getDefaultValuesStructured($protocol_shortname)
    {
        $result = $this->getDefaultValuesRawResult($protocol_shortname);
        if( $result->rowCount() == 0 )
        {
            //TODO -- Throw a fatal error here
            //throw new /Exception
            //Return empty result for now --- in production this will be a fatal error!!!
            drupal_set_message('No template found for ['.$protocol_shortname.'] (normal condition during development of the system)','warning'); 
            $value = array(
                'hydration' => -1,
                'sedation' => -1,
                'contrast' => -1,
                'radioisotope' => -1,
                'consentreq' => -1,
                'protocolnotes' => -1,
                'examnotes' => -1,
            );
        } else {
            $record = $result->fetchObject();
            $hydration = array();
            if($record->hydration_oral_tx != null)
            {
                $hydration['oral'] = $record->hydration_oral_tx;
                $hydration['iv'] = -1;
            } else if($record->hydration_iv_tx != null) {
                $hydration['oral'] = -1;
                $hydration['iv'] = $record->hydration_iv_tx;
            } else {
                $hydration = -1;
            }
            $sedation = array();
            if($record->sedation_oral_tx != null)
            {
                $sedation['oral'] = $record->sedation_oral_tx;
                $sedation['iv'] = -1;
            } else if($record->sedation_iv_tx != null) {
                $sedation['oral'] = -1;
                $sedation['iv'] = $record->sedation_iv_tx;  //Fixed 20140830
            } else {
                $sedation = -1;
            }

            $contrast = array();
            $contrast['enteric'] = -1;
            $contrast['iv'] = -1;
            if($record->contrast_enteric_tx != null)
            {
                $contrast['enteric'] = $record->contrast_enteric_tx;
            }
            if($record->contrast_iv_tx != null) 
            {
                $contrast['iv'] = $record->contrast_iv_tx;
            } 
            if(count($contrast) == 0)
            {
                $contrast = -1;
            }
            
            $radioisotope = array();
            $radioisotope['iv'] = -1;
            $radioisotope['enteric'] = -1;
            if($record->radioisotope_enteric_tx != null)
            {
                $radioisotope['enteric'] = $record->radioisotope_enteric_tx;
            }
            if($record->radioisotope_iv_tx != null) 
            {
                $radioisotope['iv'] = $record->radioisotope_iv_tx;
            } 
            if(count($radioisotope) == 0)
            {
                $radioisotope = -1;
            }
            
            if($record->consent_req_kw != null)
            {
                $consentreq = $record->consent_req_kw;
            } else {
                $consentreq = -1;
            }
            
            if($record->protocolnotes_tx != null)
            {
                $protocolnotes['text'] =  $record->protocolnotes_tx;
            } else {
                $protocolnotes = -1;
            }
            if($record->examnotes_tx != null)
            {
                $examnotes['text'] =  $record->examnotes_tx;
            } else {
                $examnotes = -1;
            }
            $value = array(
                'hydration' => $hydration,
                'sedation' => $sedation,
                'contrast' => $contrast,
                'radioisotope' => $radioisotope,
                'consentreq' => $consentreq,
                'protocolnotes' => $protocolnotes,
                'examnotes' => $examnotes,
            );
        }
        return $value;
    }
}
