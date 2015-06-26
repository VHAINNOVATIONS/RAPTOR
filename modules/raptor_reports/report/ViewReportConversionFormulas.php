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

require_once 'AReport.php';
module_load_include('php', 'raptor_formulas', 'core/Conversions');

/**
 * This class returns the Admin Information input content
 *
 * @author Frank Font of SAN Business Consultants
 */
class ViewReportConversionFormulas extends AReport
{
    private static $reqprivs = array();
    private static $menukey = 'raptor/showconversionformulas';
    private static $reportname = 'Conversion Formulas';

    function __construct()
    {
        parent::__construct(self::$reqprivs, self::$menukey, self::$reportname);
    }

    public function getDescription() 
    {
        return 'Shows supported conversion formulas';
    }

    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues)
    {
        $form['data_entry_area1'] = array(
            '#prefix' => "\n<section class='raptor-report'>\n",
            '#suffix' => "\n</section>\n",
        );
        $form['data_entry_area1']['intro'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="raptor-dialog-table-container">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );
        $form['data_entry_area1']['table_container'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="raptor-dialog-table-container">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );

        $intro[] = 'These are the conversion formulas used by RAPTOR to convert values'
                . ' from one unit of measure into another unit of measure.'
                . '  The formulas that convert into "preferred" units of measure '
                . 'are identified as our "Normalizing" formulas in this report.';
        $intro[] = 'The preferred unit of measure for each category is configurable'
                . ' by each site using configuration constants.'
                . '  The constants are shown in the hover text over the preferred units in the report.';
        $form['data_entry_area1']['intro']['main'] = array('#type' => 'item',
                 '#markup' => '<p>'.implode('</p><p>', $intro).'</p>'
            );
                
                     
        $rows = "\n";
        
        $allmaps = \raptor_formulas\Conversions::getAllSupportedConversions();
        $defaultUOM = array();
        $defaultsincat = array();
        $defaultsincat[UOM_NORMALIZED_TEMPERATURE] = 'UOM_NORMALIZED_TEMPERATURE';
        $defaultUOM['temperature'] = $defaultsincat;
        $defaultsincat = array();
        $defaultsincat[UOM_NORMALIZED_LENGTH] = 'UOM_NORMALIZED_LENGTH';
        $defaultUOM['length'] = $defaultsincat;
        $defaultsincat = array();
        $defaultsincat[UOM_NORMALIZED_WEIGHT] = 'UOM_NORMALIZED_WEIGHT';
        $defaultUOM['weight'] = $defaultsincat;
        $defaultsincat = array();
        $defaultsincat[UOM_NORMALIZED_TIME_RADIATION] = 'UOM_NORMALIZED_TIME_RADIATION';
        $defaultUOM['time'] = $defaultsincat;
        $defaultsincat = array();
        $defaultsincat[UOM_NORMALIZED_FREQ_RADIATION] = 'UOM_NORMALIZED_FREQ_RADIATION';
        $defaultUOM['frequency'] = $defaultsincat;
        $defaultsincat = array();
        $defaultsincat[UOM_NORMALIZED_FLUORO_DAP] = 'UOM_NORMALIZED_FLUORO_DAP';
        $defaultsincat[UOM_NORMALIZED_FLUORO_AIRKERMA] = 'UOM_NORMALIZED_FLUORO_AIRKERMA';
        $defaultsincat[UOM_NORMALIZED_DLP_RADIATION] = 'UOM_NORMALIZED_DLP_RADIATION';
        $defaultsincat[UOM_NORMALIZED_CTDIVOL_RADIATION] = 'UOM_NORMALIZED_CTDIVOL_RADIATION';
        $defaultsincat[UOM_NORMALIZED_EQUIPOTHER_RADIATION] = 'UOM_NORMALIZED_EQUIPOTHER_RADIATION';
        $defaultsincat[UOM_NORMALIZED_RADIOISOTOPE_RADIATION] = 'UOM_NORMALIZED_RADIOISOTOPE_RADIATION';
        $defaultUOM['radiation'] = $defaultsincat;
        
        foreach($allmaps as $category=>$catmap) 
        {
            $defaultsincat = $defaultUOM[$category];
            foreach($catmap as $from=>$tomap)
            {
                $fromisdefault = array_key_exists($from,$defaultsincat);
                if($fromisdefault)
                {
                    $title = $defaultsincat[$from];
                    $frommarkup = "<strong><span title='This is the declared $title unit of measure'>$from</span></strong>";
                } else {
                    $frommarkup = $from;
                }
                foreach($tomap as $to=>$formula)
                {
                    $is_also_a_from = (isset($catmap[$to]));
                    $toisdefault = array_key_exists($to,$defaultsincat);
                    if($toisdefault)
                    {
                        $title = $defaultsincat[$to];
                        $tomarkup = "<strong><span title='This is the declared $title unit of measure'>$to</span></strong>";
                        $preferred = "<strong title='Converts into $title'>Yes<strong>";
                        if($is_also_a_from)
                        {
                            $tomarkup = "<strong>"
                                    . "<span title='This is the declared $title unit of measure'>$to</span>"
                                    . "</strong>";
                            $preferred = "<strong title='Converts into $title'>Yes<strong>";
                        } else {
                            $tomarkup = "<strong><i>"
                                    . "<span title='This is the declared $title unit of measure (There is NO conversion formula from this unit of measure)'>"
                                    . "$to"
                                    . "</span>"
                                    . "</i></strong>";
                            $preferred = "<strong title='Converts into $title'>Yes<strong>";
                        }
                    } else {
                        if($is_also_a_from)
                        {
                            $tomarkup = $to;
                            $preferred = 'no';
                        } else {
                            $tomarkup = "<i title='There is NO conversion formula from this unit of measure'>$to</i>";
                            $preferred = 'no';
                        }
                    }
                    try
                    {
                        $exampleoutput = \raptor_formulas\Conversions::convertAnything($from, $to, 1);
                    } catch (\Exception $ex) {
                        $exampleoutput = $ex->getMessage();
                    }
                    $rows   .= "\n".'<tr>'
                            . "<td>$category</td>"
                            . "<td>"
                            . "$preferred"
                            . "</td>"
                            . "<td>$frommarkup</td>"
                            . "<td>$tomarkup</td>"
                            . "<td>$formula</td>"
                            . "<td>$exampleoutput</td>"
                            .'</tr>';
                }
            }
        }

        $form['data_entry_area1']['table_container']['formulas'] = array('#type' => 'item',
                 '#markup' => '<table id="my-raptor-dialog-table" class="raptor-dialog-table dataTable">'
                            . '<thead><tr>'
                            . '<th title="The context in which this formula applies">Category</th>'
                            . '<th title="Identifies formulas that produce preferred unit of measure output">Normalizing</th>'
                            . '<th title="Units the formula converts from">From Units</th>'
                            . '<th title="Units the formula converts into">To Units</th>'
                            . '<th title="The formula that converts from one unit of measure into another">Formula</th>'
                            . '<th title="Example with input value 1">Example Unit Conversion</th>'
                            . '</tr>'
                            . '</thead>'
                            . '<tbody>'
                            . $rows
                            .  '</tbody>'
                            . '</table>');
        
       $form['data_entry_area1']['action_buttons'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="raptor-action-buttons">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );

        $form['data_entry_area1']['action_buttons']['refresh'] = array('#type' => 'submit'
                , '#attributes' => array('class' => array('admin-action-button'), 'id' => 'refresh-report')
                , '#value' => t('Refresh Report'));

        global $base_url;
        $goback = $base_url . '/raptor/viewReports';
        /*
        $form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => '<input class="admin-cancel-button" type="button"'
                . ' value="Cancel"'
                . ' data-redirect="'.$goback.'">');
         */
        $form['data_entry_area1']['action_buttons']['cancel'] = $this->getExitButtonMarkup($goback);
        return $form;
    }
}
