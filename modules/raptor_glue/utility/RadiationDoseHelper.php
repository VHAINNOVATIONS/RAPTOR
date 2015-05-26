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
 * This file contains radiation dose helper methods
 *
 * @author Frank Font of SAN Business Consultants
 */
class RadiationDoseHelper
{
    private static $dose_source_cd_term_map = array(
          'R'=>'Radioisotope'
        , 'E'=>'Equipment Other'
        , 'C'=>'CTDIvol'
        , 'D'=>'DLP'
        , 'Q'=>'Fluoro Air Kerma'
        , 'S'=>'Fluoro DAP'
        , 'T'=>'Fluoro Time'
        , 'H'=>'Fluoro Frame Rate'
        );

    private static $dose_source_cd_uom_map = array(
          'R'=>'mGy'
        , 'E'=>'mCi'
        , 'C'=>'mGy'
        , 'D'=>'mGycm'
        , 'Q'=>'mGy/min'
        , 'S'=>'mGy*cm^2'
        , 'T'=>'min'
        , 'H'=>'Hz'
        );
    
    public static function getDoseSourceTermMap()
    {
        return self::$dose_source_cd_term_map;
    }

    public static function getDoseSourceDefaultUOMMap($source_cd)
    {
        return self::$dose_source_cd_uom_map;
    }
    
    public static function getDefaultTermForDoseSource($source_cd)
    {
        return self::$dose_source_cd_term_map[$source_cd];
    }
    
    public static function getDefaultUOMForDoseSource($source_cd)
    {
        return self::$dose_source_cd_uom_map[$source_cd];
    }
}