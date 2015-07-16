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
 * Functions for getting keywords
 *
 * @author Frank Font of SAN
 */
class CustomKeywords
{
    private static function getKeywordsFromTable($sTablename, $sFieldName='keyword')
    {
        $rows = array();
        try
        {
            $sSQL = 'SELECT ' . $sFieldName . ' '
                    . ' FROM `' . $sTablename . '` '
                    . ' ORDER BY '. $sFieldName;
            $result = db_query($sSQL);
            if($result->rowCount()>0)
            {
                foreach($result as $record) 
                {
                    $value = $record->$sFieldName;
                    $rows[$value] = $value;
                }
            }
        } catch (\Exception $ex) {
            throw new \Exception("Failed getKeywordsFromTable($sTablename,$sFieldName) because " 
                    . $ex->getMessage(),99345,$ex);
        }
        return $rows;
    }
    
    public static function getRareContrastKeywords()
    {
        return self::getKeywordsFromTable('raptor_atrisk_rare_contrast');
    }
    
    public static function getRareRadioisotopeKeywords()
    {
        return self::getKeywordsFromTable('raptor_atrisk_rare_radioisotope');
    }
    
    public static function getAllergyContrastKeywords()
    {
        return self::getKeywordsFromTable('raptor_atrisk_allergy_contrast');
    }
    
    public static function getBloodThinnerKeywords()
    {
        return self::getKeywordsFromTable('raptor_atrisk_bloodthinner');
    }
    
}
