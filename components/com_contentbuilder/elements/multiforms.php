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

    class JElementMultiforms extends JElement {

        function fetchElement($name, $value, $node, $control_name) {
            $class = $node->attributes('class') ? $node->attributes('class') : "text_area";
            $multiple = 'multiple="multiple" ';
            $db = JFactory::getDBO();
            $db->setQuery("Select * From #__contentbuilder_forms Where published = 1 Order By `ordering`");
            $status = $db->loadAssocList();
            $out = '<script type="text/javascript">
            var contentbuilder_selected = ['.$value.'];
            function contentbuilder_storeSelected(list){
                 document.getElementById("' . $control_name . '_' . $name . '").value = "";
                 for (var x=0;x<list.length;x++)
                 {
                    if (list[x].selected)
                    {
                     document.getElementById("' . $control_name . '_' . $name . '").value += list[x].value;
                     if(x+1 < list.length){
                        document.getElementById("' . $control_name . '_' . $name . '").value += ",";
                     }
                    }
                 }
            }
            </script>';
            $out .= '<input type="hidden" name="' . $control_name . '[' . $name . ']" id="' . $control_name . '_' . $name . '" value=""/>';
            $out .= '<select '.$multiple.'onchange="contentbuilder_storeSelected(this);" name="' . $control_name . '_select[' . $name . ']" id="' . $control_name . 'select' . $name . '" onchange="if(typeof contentbuilder_setFormId != \'undefined\') { contentbuilder_setFormId(this.options[this.selectedIndex].value); }" class="' . $class . '">' . "\n";
            foreach ($status As $stat) {
                $out .= '<option value="' . $stat['id'] . '">' . htmlentities($stat['name'], ENT_QUOTES, 'UTF-8') . '</option>' . "\n";
            }
            $out .= '</select>' . "\n";
            $out .= '<script type="text/javascript">
            var contentbuilder_list = document.getElementById("' . $control_name . 'select' . $name . '");
            for(var i = 0; i < contentbuilder_selected.length; i++){
                for (var x=0;x<contentbuilder_list.length;x++)
                {
                    if( contentbuilder_list[x].value == contentbuilder_selected[i]){
                        contentbuilder_list[x].selected = true;
                    }
                }
            }
            </script>';
            return $out;
        }

    }

} else {

    jimport('joomla.html.html');
    jimport('joomla.form.formfield');

    class JFormFieldMultiforms extends JFormField {

        protected $type = 'Multiforms';

        protected function getInput() {
            $class = $this->element['class'] ? $this->element['class'] : "text_area";
            $multiple = 'multiple="multiple" ';
            $db = JFactory::getDBO();
            $db->setQuery("Select id,`name` From #__contentbuilder_forms Where published = 1 Order By `ordering`");
            $status = $db->loadObjectList();
            return JHTML::_('select.genericlist',  $status, $this->name, $multiple.'style="width: 100%;" onchange="if(typeof contentbuilder_setFormId != \'undefined\') { contentbuilder_setFormId(this.options[this.selectedIndex].value); }" class="' . $this->element['class'] . '"', 'id', 'name', $this->value );
        }
    }
}