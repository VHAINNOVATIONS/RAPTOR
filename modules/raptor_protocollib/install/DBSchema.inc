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

namespace raptor_protocollib;

/**
 * This class is for protocol database schema content
 *
 * author Frank Font of SAN Business Consultants
 */
class DBSchema
{
    /**
     * Add all the schema content for the protocol tables
     */
    public function addToSchema(&$schema)
    {
        $schema['raptor_protocol_lib_uploads'] = array(
          'description' => 'Protocol library upload tracking',
          'fields' => array(
            'protocol_shortname' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Protocol short name which must be unique',
            ),
            'version' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'The version number of this protocol to match the parent record',
            ),
            'filename' => array(
              'type' => 'varchar',
              'length' => 128,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Filename in RAPTOR of the scanned protocol',
            ),
            'original_filename' => array(
              'type' => 'varchar',
              'length' => 128,
              'not null' => FALSE,
              'description' => 'Original filename of uploaded protocol',
            ),
            'filetype' => array(
              'type' => 'varchar',
              'length' => 8,
              'not null' => TRUE,
              'description' => 'The uppercase file extension of the file',
            ),
            'file_blob' => array(
              'type' => 'blob',
              'mysql_type' => 'mediumblob',
              'not null' => TRUE,
              'description' => 'The uploaded image as a blob',
            ),
            'filesize' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Size in bytes',
            ),
            'uploaded_by_uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Who uploaded the file',
            ),
            'comment_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => FALSE,
              'default' => '',
              'description' => 'Comment from uploader',
            ),
            'uploaded_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was last updated',
            ),
          ),
          'primary key' => array('protocol_shortname','version'),
        );
    }    
}
    