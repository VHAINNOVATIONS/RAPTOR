<?php

/**
 * @file
 * Template overrides as well as (pre-)process and alter hooks for the
 * RAPTOR Omega theme.
 */

 function raptor_omega_omega_layout_alter(&$layout) 
 {
	if (arg(0) == 'worklist')
	{
		$layout = 'worklist';
	} else if (arg(0) == 'protocol') {
		$layout = 'protocol';
	} else {
		$layout = 'admin';	//Default layout
	}
 }
 
 