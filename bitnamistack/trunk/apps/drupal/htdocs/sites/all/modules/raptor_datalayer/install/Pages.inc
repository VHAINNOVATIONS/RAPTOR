<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 * 
 * Updated 20140504
 */

namespace raptor;

/**
 * Initialize key page contexts of the system.
 *
 * @author FrankWin7VM
 */
class Pages 
{
    
    public function init_worklist_page()
    {
        $context = new stdClass();
        $context->disabled = FALSE; /* Edit this to true to make a default context disabled initially */
        $context->api_version = 3;
        $context->name = 'raptor_worklist';
        $context->description = 'RAPTOR Worklist';
        $context->tag = 'raptor_worklist';
        $context->conditions = array(
          'path' => array(
            'values' => array(
              'worklist' => 'worklist',
            ),
          ),
        );
        $context->reactions = array(
          'block' => array(
            'blocks' => array(),
            'layout' => 'worklist',
          ),
        );
        $context->condition_mode = 1;

        // Translatables
        // Included for use with string extractors like potx.
        t('RAPTOR Worklist');
        t('raptor_worklist');
    }
    
    public function init_protocol_page()
    {
        $context = new stdClass();
        $context->disabled = FALSE; /* Edit this to true to make a default context disabled initially */
        $context->api_version = 3;
        $context->name = 'raptor_protocol';
        $context->description = 'RAPTOR Protocol';
        $context->tag = 'raptor_protocol';
        $context->conditions = array(
          'path' => array(
            'values' => array(
              'protocol' => 'protocol',
            ),
          ),
        );
        $context->reactions = array(
          'block' => array(
            'blocks' => array(),
            'layout' => 'protocol',
          ),
          'theme' => array(
            'title' => 'Protocol',
            'subtitle' => 'Main',
          ),
        );
        $context->condition_mode = 1;

        // Translatables
        // Included for use with string extractors like potx.
        t('RAPTOR Protocol');
        t('raptor_protocol');
    }
    
}
