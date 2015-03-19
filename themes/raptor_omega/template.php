<?php

/**
 * @file
 * Template overrides as well as (pre-)process and alter hooks for the
 * RAPTOR Omega theme.
 */

 function raptor_omega_omega_layout_alter(&$layout) 
 {
    //Make sure we always load the following modules
    module_load_include('php', 'raptor_workflow', 'core/Menus');
    
    //die('look now>>>['.arg(1).']');
    
    //Pick the best layout to use.
    if (arg(0) == 'worklist')
    {
            $layout = 'worklist';
    } else if (arg(0) == 'protocol') {
            $layout = 'protocol';
    } else if (arg(0) == 'user' 
            || (arg(1) == 'kickout_timeout')
            || (arg(1) == 'start')) {
            $layout = 'login';
    } else {
            $layout = 'admin';	//Default layout
    }
 }
 
 