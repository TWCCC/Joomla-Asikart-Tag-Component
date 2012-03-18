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

    class JElementCbfilter extends JElement {

        function fetchElement($name, $value, $node, $control_name) {
            $out = '<input type="hidden" name="' . $control_name . '[' . $name . ']" id="'.$control_name . $name.'" value="'.$value.'"/>';
            $out .= '<div id="cbElementsWrapper">';
            $class = $node->attributes('class') ? $node->attributes('class') : "text_area";
            $db = JFactory::getDBO();
            if($value){
                $db->setQuery("Select * From #__contentbuilder_elements Where published = 1 And form_id = " . intval($value));
                $elements = $db->loadAssocList();
                $i = 0;
                
                $out .= '<table border="0" width="100%">';
                foreach($elements As $element){
                    $out .= "<tr><td>";
                    $out .= '<label>'.htmlentities($element['label'], ENT_QUOTES, 'UTF-8').'</label>';
                    $out .= '</td><td>';
                    $out .= '<input value="" type="text" onchange="contentbuilder_addValue(\''.$element['reference_id'].'\',this.value);" name="element_'.$element['reference_id'].'" id="element_'.$element['reference_id'].'"/>';
                    $out .= '</td><td>';
                    $out .= '<input style="width: 25px;" value="" type="text" onchange="contentbuilder_addOrderValue(\''.$element['reference_id'].'\',this.value);" name="element_'.$element['reference_id'].'_order" id="element_'.$element['reference_id'].'_order"/>';
                    $out .= '</td></tr>';
                    $i++;
                }
                $out .= '</table>';
            }
            else
            {
                $out .= '<br/><br/>'.JText::_('COM_CONTENTBUILDER_ADD_LIST_VIEW_SELECT_FORM_FIRST');
            }
            $out .= '</div>';
            $out .= '
                <script type="text/javascript">
                <!--
                var form_id = document.getElementById("paramsform_id").options[document.getElementById("paramsform_id").selectedIndex].value;
                var curr_form_id = document.getElementById("'.$control_name . $name.'").value;
               
                document.getElementById("'.$control_name . $name.'").value = form_id;
                
                if(curr_form_id != form_id){
                    document.getElementById("cbElementsWrapper").innerHTML = "'.addslashes(JText::_('COM_CONTENTBUILDER_ADD_LIST_VIEW_SELECT_FORM_FIRST')).'";
                    document.getElementById("paramscb_list_filterhidden").value = "";
                    document.getElementById("paramscb_list_orderhidden").value = "";
                }

                var currval_splitted = currval.split("\n");
                for(var i = 0; i < currval_splitted.length; i++){
                    if( currval_splitted[i] != "" ){
                        var keyval = currval_splitted[i].split("\t");
                        if( keyval.length == 2 ){
                            cb_value[keyval[0]] = keyval[1];
                            if(document.getElementById("element_"+keyval[0])){
                                document.getElementById("element_"+keyval[0]).value = keyval[1];
                            }
                        }
                    }
                }
                
                var currval_order_splitted = currval_order.split("\n");
                for(var i = 0; i < currval_order_splitted.length; i++){
                    if( currval_order_splitted[i] != "" ){
                        var keyval_order = currval_order_splitted[i].split("\t");
                        if( keyval_order.length == 2 ){
                            cb_value_order[keyval_order[0]] = keyval_order[1];
                            if(document.getElementById("element_"+keyval_order[0]+"_order")){
                                document.getElementById("element_"+keyval_order[0]+"_order").value = keyval_order[1];
                            }
                        }
                    }
                }

                function contentbuilder_setFormId(form_id){
                    document.getElementById("'.$control_name . $name.'").value = form_id;
                    document.getElementById("cbElementsWrapper").innerHTML = "'.addslashes(JText::_('COM_CONTENTBUILDER_ADD_LIST_VIEW_SELECT_FORM_FIRST')).'";
                    document.getElementById("paramscb_list_filterhidden").value = "";
                    document.getElementById("paramscb_list_orderhidden").value = "";
                }
                //-->
                </script>';
            return $out;
        }
    }

} else {

    jimport('joomla.html.html');
    jimport('joomla.form.formfield');

    class JFormFieldCbfilter extends JFormField {

        protected $type = 'Forms';

        protected function getInput() {
            $out = '<input type="hidden" name="'.$this->name.'" id="'.$this->id.'" value="'.$this->value.'"/>';
            $out .= '<div id="cbElementsWrapper">';
            $class = $this->element['class'] ? $this->element['class'] : "text_area";
            $db = JFactory::getDBO();
            if($this->value){
                $db->setQuery("Select * From #__contentbuilder_elements Where published = 1 And form_id = " . intval($this->value));
                $elements = $db->loadAssocList();
                $i = 0;
                
                foreach($elements As $element){
                    $out .= '<label>'.htmlentities($element['label'], ENT_QUOTES, 'UTF-8').'</label><input value="" type="text" onchange="contentbuilder_addValue(\''.$element['reference_id'].'\',this.value);" name="element_'.$element['reference_id'].'" id="element_'.$element['reference_id'].'"/>';
                    $out .= '<input style="width: 25px;" value="" type="text" onchange="contentbuilder_addOrderValue(\''.$element['reference_id'].'\',this.value);" name="element_'.$element['reference_id'].'_order" id="element_'.$element['reference_id'].'_order"/>';
                    
                    $i++;
                }
                
            }
            else
            {
                $out .= '<br/><br/>'.JText::_('COM_CONTENTBUILDER_ADD_LIST_VIEW_SELECT_FORM_FIRST');
            }
            $out .= '</div>';
            $out .= '
                <script type="text/javascript">
                <!--
                var form_id = document.getElementById("jformparamsform_id").options[document.getElementById("jformparamsform_id").selectedIndex].value;
                var curr_form_id = document.getElementById("'.$this->id.'").value;
               
                document.getElementById("'.$this->id.'").value = form_id;
                
                if(curr_form_id != form_id){
                    document.getElementById("cbElementsWrapper").innerHTML = "'.addslashes(JText::_('COM_CONTENTBUILDER_ADD_LIST_VIEW_SELECT_FORM_FIRST')).'";
                    document.getElementById("jform_params_cb_list_filterhidden").value = "";
                    document.getElementById("jform_params_cb_list_orderhidden").value = "";
                }

                var currval_splitted = currval.split("\n");
                for(var i = 0; i < currval_splitted.length; i++){
                    if( currval_splitted[i] != "" ){
                        var keyval = currval_splitted[i].split("\t");
                        if( keyval.length == 2 ){
                            cb_value[keyval[0]] = keyval[1];
                            if(document.getElementById("element_"+keyval[0])){
                                document.getElementById("element_"+keyval[0]).value = keyval[1];
                            }
                        }
                    }
                }
                
                var currval_order_splitted = currval_order.split("\n");
                for(var i = 0; i < currval_order_splitted.length; i++){
                    if( currval_order_splitted[i] != "" ){
                        var keyval_order = currval_order_splitted[i].split("\t");
                        if( keyval_order.length == 2 ){
                            cb_value_order[keyval_order[0]] = keyval_order[1];
                            if(document.getElementById("element_"+keyval_order[0]+"_order")){
                                document.getElementById("element_"+keyval_order[0]+"_order").value = keyval_order[1];
                            }
                        }
                    }
                }

                function contentbuilder_setFormId(form_id){
                    document.getElementById("'.$this->id.'").value = form_id;
                    document.getElementById("cbElementsWrapper").innerHTML = "'.addslashes(JText::_('COM_CONTENTBUILDER_ADD_LIST_VIEW_SELECT_FORM_FIRST')).'";
                    document.getElementById("jform_params_cb_list_filterhidden").value = "";
                    document.getElementById("jform_params_cb_list_orderhidden").value = "";
                }
                //-->
                </script>';
            return $out;
        }
    }
}