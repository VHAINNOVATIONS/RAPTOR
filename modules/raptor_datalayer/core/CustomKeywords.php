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
