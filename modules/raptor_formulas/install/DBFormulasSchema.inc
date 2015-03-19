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

/**
 * This class is for List database schema content
 *
 * author Frank Font of SAN Business Consultants
 */
class DBFormulasSchema
{
    /**
     * Add all the schema content for the list tables
     */
    public function addToSchema(&$schema)
    {
        $schema['raptor_list_kw_with_contrast'] = array(
          'description' => 'Phrases that indicate an order includes contrast',
          'fields' => array(
            'phrase_tx' => array(
              'type' => 'varchar',
              'length' => 50,
              'not null' => TRUE,
              'description' => 'The exact text to look for',
            ),
          ),
        );
        
        $schema['raptor_list_kw_without_contrast'] = array(
          'description' => 'Phrases that indicate an order is without contrast',
          'fields' => array(
            'phrase_tx' => array(
              'type' => 'varchar',
              'length' => 50,
              'not null' => TRUE,
              'description' => 'The exact text to look for',
            ),
          ),
        );
        
        $schema['raptor_list_kw_withandwithout_contrast'] = array(
          'description' => 'Phrases that indicate an order is with and without contrast',
          'fields' => array(
            'phrase_tx' => array(
              'type' => 'varchar',
              'length' => 50,
              'not null' => TRUE,
              'description' => 'The exact text to look for',
            ),
          ),
        );
    }    
}
    