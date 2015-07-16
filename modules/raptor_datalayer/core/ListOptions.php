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

namespace raptor;

/**
 * This returns values for pick lists.
 *
 * @author Frank Font of SAN Business Consultants
 */
class ListOptions
{
    
    static function getRawBoilerplateProtocolOptions()
    {
        try
        {
            $allthevalues = array();
            $sql = 'SELECT category_tx,title_tx,content_tx '
                    . ' FROM raptor_boilerplate_protocol '
                    . ' ORDER BY category_tx,title_tx';
            $result = db_query($sql);
            if($result->rowCount() > 0)
            {
                while($record = $result->fetchAssoc())
                {
                    $title_tx = $record['title_tx'];
                    $content_tx = $record['content_tx'];
                    $category_tx = $record['category_tx'];
                    if(!array_key_exists($category_tx, $allthevalues))
                    {
                        $allthevalues[$category_tx] = array();
                    }
                    $allthevalues[$category_tx][$title_tx] = array(0 => $content_tx);   //Legacy format
                }
            }
            return $allthevalues;
        } catch (\Exception $ex) {
            throw new \Exception('Failed getBoilerplateProtocolOptions because '.$ex->getMessage());
        }
    }

    static function getRawBoilerplateExamOptions()
    {
        try
        {
            $allthevalues = array();
            $sql = 'SELECT category_tx,title_tx,content_tx '
                    . ' FROM raptor_boilerplate_exam '
                    . ' ORDER BY category_tx,title_tx';
            $result = db_query($sql);
            if($result->rowCount() > 0)
            {
                while($record = $result->fetchAssoc())
                {
                    $title_tx = $record['title_tx'];
                    $content_tx = $record['content_tx'];
                    $category_tx = $record['category_tx'];
                    if(!array_key_exists($category_tx, $allthevalues))
                    {
                        $allthevalues[$category_tx] = array();
                    }
                    $allthevalues[$category_tx][$title_tx] = array(0 => $content_tx);   //Legacy format
                }
            }
            return $allthevalues;
        } catch (\Exception $ex) {
            throw new \Exception('Failed getBoilerplateExamOptions because '.$ex->getMessage());
        }
    }
    
    
    function getHydrationOptions($type, $modality_filter)
    {
        $sCoreSQL = 'SELECT option_tx FROM raptor_list_hydration';
        if(is_array($modality_filter))
        {
            return $this->getModalityFilteredResult($type, $modality_filter, $sCoreSQL);
        } else {
            //Assume simple string filter
            return $this->getSimpleResult($type, $modality_filter, $sCoreSQL);
        }
    }
    
    //Changed the name from pharama to isotope on 5/16
    function getRadioisotopeOptions($type, $modality_filter)
    {
        $sCoreSQL = 'SELECT option_tx FROM raptor_list_radioisotope';
        if(is_array($modality_filter))
        {
            return $this->getModalityFilteredResult($type, $modality_filter, $sCoreSQL);
        } else {
            //Assume simple string filter
            return $this->getSimpleResult($type, $modality_filter, $sCoreSQL);
        }
    }
    
    function getSedationOptions($type, $modality_filter)
    {
        $sCoreSQL = 'SELECT option_tx FROM raptor_list_sedation';
        if(is_array($modality_filter))
        {
            return $this->getModalityFilteredResult($type, $modality_filter, $sCoreSQL);
        } else {
            //Assume simple string filter
            return $this->getSimpleResult($type, $modality_filter, $sCoreSQL);
        }
    }

    function getContrastOptions($type, $modality_filter)
    {
        $sCoreSQL = 'SELECT option_tx FROM raptor_list_contrast';
        if(is_array($modality_filter))
        {
            return $this->getModalityFilteredResult($type, $modality_filter, $sCoreSQL);
        } else {
            //Assume simple string filter
            return $this->getSimpleResult($type, $modality_filter, $sCoreSQL);
        }
    }
    
    function getAtRiskMedsKeywords()
    {
        $sSQL = 'SELECT keyword FROM raptor_atrisk_meds ORDER BY keyword';
        $result = db_query($sSQL);
        return $result->fetchCol();
    }
    
    /**
     * Filter for only one modality or none
     */
    private function getSimpleResult($type, $modality, $sCoreSQL)
    {
        if($type == null)
        {
            throw new \Exception('The type value cannot be null for core sql ' . $sCoreSQL);
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
    
    /**
     * Filter for any number of modalities
     */
    private function getModalityFilteredResult($type, $modality_filter, $sCoreSQL)
    {
        if($type == NULL)
        {
            throw new \Exception('The type value cannot be null for core sql ' . $sCoreSQL);
        }
        $filter = array(':type_nm' => $type);
        $andWhere = '';
        if(is_array($modality_filter) && count($modality_filter)>0)
        {
            //Allow for multiple values
            $andWhere = 'and (';
            $foundcount=0;
            if(in_array('CT',$modality_filter))
            {
                $foundcount++;
                $filter[':ct_yn'] = 1;
                if($foundcount > 1)
                {
                    $andWhere .= ' or ';
                }
                $andWhere .= ' ct_yn = :ct_yn';
            }
            if(in_array('MR',$modality_filter))
            {
                $foundcount++;
                $filter[':mr_yn'] = 1;
                 if($foundcount > 1)
                {
                    $andWhere .= ' or ';
                }
                $andWhere .= ' mr_yn = :mr_yn';
            }
            if(in_array('NM',$modality_filter))
            {
                $foundcount++;
                $filter[':nm_yn'] = 1;
                if($foundcount > 1)
                {
                    $andWhere .= ' or ';
                }
                $andWhere .= ' nm_yn = :nm_yn';
            }
            if(in_array('FL',$modality_filter))
            {
                $foundcount++;
                $filter[':fl_yn'] = 1;
                if($foundcount > 1)
                {
                    $andWhere .= ' or ';
                }
                $andWhere .= ' fl_yn = :fl_yn';
            }
            if(in_array('US',$modality_filter))
            {
                $foundcount++;
                $filter[':us_yn'] = 1;
                if($foundcount > 1)
                {
                    $andWhere .= ' or ';
                }
                $andWhere .= ' us_yn = :us_yn';
            }
            if($foundcount != count($modality_filter))
            {
                throw new \Exception('Did not find expected match for one or more modalities in filter list>>>'
                        .print_r($modality_filter,TRUE));
            }
            $andWhere .= ')';
        }
        $runsql = $sCoreSQL . ' WHERE type_nm = :type_nm ' . $andWhere;
        //error_log('getModalityFilteredResult>>>runsql='.$runsql);
        $result = db_query($runsql, $filter);
        return $result->fetchCol();
    }
    
}
