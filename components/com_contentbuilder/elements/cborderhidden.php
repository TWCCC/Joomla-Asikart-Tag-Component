<?php

/**
 * @package     BreezingCommerce
 * @author      Markus Bopp
 * @link        http://www.crosstec.de
 * @license     GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.version');
$version = new JVersion();

if (version_compare($version->getShortVersion(), '1.6', '<')) {

    jimport( 'joomla.html.parameter.element' );

    class JElementCborderhidden extends JElement {

        function fetchElement($name, $value, $node, $control_name) {
            $class = $node->attributes('class') ? $node->attributes('class') : "text_area";
            $db = JFactory::getDBO();
            $out = '<input type="hidden" name="' . $control_name . '[' . $name . ']" id="'.$control_name . $name.'" value="'.$value.'"/>'."\n";
            $out .= '
                <script type="text/javascript">
                <!--
                document.getElementById("' . $control_name . $name .'-lbl").style.display = "none";
                var cb_value_order = {};
                var currval_order = "'.str_replace(array("\n","\r"),array("\\n",""),addslashes($value)).'";
                
                function contentbuilder_addOrderValue(element_id, value){
                    cb_value_order[element_id] = value;
                    var contents = "";
                    for(var x in cb_value_order){
                        contents += x + "\t" + cb_value_order[x] + "\n";
                    }
                    document.getElementById("'.$control_name . $name.'").value = contents;
                }
                //-->
                </script>';
            return $out;
        }

    }

} else {

    jimport('joomla.html.html');
    jimport('joomla.form.formfield');

    class JFormFieldCborderhidden extends JFormField {

        protected $type = 'Forms';

        protected function getInput() {
            $class = $this->element['class'] ? $this->element['class'] : "text_area";
            $db = JFactory::getDBO();
            $out = '<input type="hidden" name="'.$this->name.'" id="'.$this->id.'" value="'.$this->value.'"/>'."\n";
            $out .= '
                <script type="text/javascript">
                <!--
                var cb_value_order = {};
                var currval_order = "'.str_replace(array("\n","\r"),array("\\n",""),addslashes($this->value)).'";
                
                function contentbuilder_addOrderValue(element_id, value){
                    cb_value_order[element_id] = value;
                    var contents = "";
                    for(var x in cb_value_order){
                        contents += x + "\t" + cb_value_order[x] + "\n";
                    }
                    document.getElementById("'.$this->id.'").value = contents;
                }
                //-->
                </script>';
            return $out;
        }
    }
}