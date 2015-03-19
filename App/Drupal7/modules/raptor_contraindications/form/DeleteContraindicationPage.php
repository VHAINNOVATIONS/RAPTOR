<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 */

namespace raptor;

module_load_include('php','simplerulesengine_ui','form/DeleteRulePage');
module_load_include('inc','raptor_contraindications','core/ContraIndEngine');


/**
 * This class returns the Admin Information input content
 *
 * @author Frank Font
 */
class DeleteContraindicationPage extends \simplerulesengine\DeleteRulePage
{
    public function __construct($rule_nm)
    {
        parent::__construct(
                    $rule_nm
                ,   new \raptor\ContraIndEngine(NULL)
                ,   array('return'=>NULL)
                );
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues, $aHtmlClassnameOverrides=NULL)
    {
        $form = parent::getForm($form, $form_state, $disabled, $myvalues, $aHtmlClassnameOverrides);
        global $base_url;
        $form["data_entry_area1"]['cancel'] = array(
                '#markup' => '<input class="admin-cancel-button" '
                . ' type="button" '
                . ' value="Cancel" '
                . ' data-redirect="'.$base_url.'/worklist?dialog=manageContraindications">');
        return $form;
    }
}
