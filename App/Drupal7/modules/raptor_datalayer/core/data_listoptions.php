<?php
/**
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
 * This returns values for pick lists.
 *
 * @author SAN
 */
class ListOptions
{
    function getHydrationOptions($type, $modality)
    {
        $sCoreSQL = 'SELECT option_tx FROM raptor_list_hydration';
        return $this->getSimpleResult($type, $modality, $sCoreSQL);
    }
    
    //Changed the name from pharama to isotope on 5/16
    function getRadioisotopeOptions($type, $modality)
    {
        $sCoreSQL = 'SELECT option_tx FROM raptor_list_radioisotope';
        return $this->getSimpleResult($type, $modality, $sCoreSQL);
    }
    
    function getSedationOptions($type, $modality)
    {
        $sCoreSQL = 'SELECT option_tx FROM raptor_list_sedation';
        return $this->getSimpleResult($type, $modality, $sCoreSQL);
    }

    function getContrastOptions($type, $modality)
    {
        $sCoreSQL = 'SELECT option_tx FROM raptor_list_contrast';
        return $this->getSimpleResult($type, $modality, $sCoreSQL);
    }
    
    function getAtRiskMedsKeywords()
    {
        $sSQL = 'SELECT keyword FROM raptor_atrisk_meds ORDER BY keyword';
        $result = db_query($sSQL);
        return $result->fetchCol();
    }
    
    private function getSimpleResult($type, $modality, $sCoreSQL)
    {
        if($type == null)
        {
            die('The type value cannot be null for core sql ' . $sCoreSQL);
        }
        $filter = array(':type_nm' => $type);
        $andWhere = '';
        if($modality == 'CT')
        {
            $filter[':ct_yn'] = 1;
            $andWhere = ' and ct_yn = :ct_yn';
        }
        if($modality == 'MR')
        {
            $filter[':mr_yn'] = 1;
            $andWhere = ' and mr_yn = :mr_yn';
        }
        if($modality == 'NM')
        {
            $filter[':nm_yn'] = 1;
            $andWhere = ' and nm_yn = :nm_yn';
        }
        if($modality == 'FL')
        {
            $filter[':fl_yn'] = 1;
            $andWhere = ' and fl_yn = :fl_yn';
        }
        if($modality == 'US')
        {
            $filter[':us_yn'] = 1;
            $andWhere = ' and us_yn = :us_yn';
        }
        $result = db_query($sCoreSQL . ' WHERE type_nm = :type_nm ' . $andWhere, $filter);
        return $result->fetchCol();
    }
}
