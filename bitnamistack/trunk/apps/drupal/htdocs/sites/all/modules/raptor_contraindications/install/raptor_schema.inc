<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 * 
 * 20140625
 */

require_once("DBContraIndicationSchema.inc");

/**
 * Create the tables
 */
function raptor_contraindications_update_7101() 
{
  //if (db_table_exists('raptor_contraindication_rule')) 
  //{
  //  return st('Unable to create table because raptor_contraindication_rule exists!');
  //}
    $schema = raptor_contraindications_schema();
    db_create_table('raptor_contraindication_rule', $schema['raptor_contraindication_rule']);
    return st('Created table raptor_contraindication_rule');
}

/**
 * Create all the tables of the database 
 * @return schema array
 */
function raptor_contraindications_schema()
{
    $schema = array();

    $oSH = new \raptor\DBContraIndicationSchema();
    $oSH->addToSchema($schema);
  
    return $schema;
}
    