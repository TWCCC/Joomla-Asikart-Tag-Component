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

    class JElementForms extends JElement {

        function fetchElement($name, $value, $node, $control_name) {
            $class = $node->attributes('class') ? $node->attributes('class') : "text_area";
            $db = JFactory::getDBO();
            $db->setQuery("Select * From #__contentbuilder_forms Where published = 1 Order By `ordering`");
            $status = $db->loadAssocList();
            $out = '<select name="' . $control_name . '[' . $name . ']" id="' . $control_name . $name . '" onchange="if(typeof contentbuilder_setFormId != \'undefined\') { contentbuilder_setFormId(this.options[this.selectedIndex].value); }" class="' . $class . '">' . "\n";
            foreach ($status As $stat) {
                $out .= '<option value="' . $stat['id'] . '"' . ($stat['id'] == $value ? ' selected="selected"' : '') . '>' . htmlentities($stat['name'], ENT_QUOTES, 'UTF-8') . '</option>' . "\n";
            }
            $out .= '</select>' . "\n";
            return $out;
        }

    }

} else {

    jimport('joomla.html.html');
    jimport('joomla.form.formfield');

    class JFormFieldForms extends JFormField {

        protected $type = 'Forms';

        protected function getInput() {
            $class = $this->element['class'] ? $this->element['class'] : "text_area";
            $db = JFactory::getDBO();
            $db->setQuery("Select id,`name` From #__contentbuilder_forms Where published = 1 Order By `ordering`");
            $status = $db->loadObjectList();
            return JHTML::_('select.genericlist',  $status, $this->name, '" onchange="if(typeof contentbuilder_setFormId != \'undefined\') { contentbuilder_setFormId(this.options[this.selectedIndex].value); }" class="' . $this->element['class'] . '"', 'id', 'name', $this->value );
        }
    }
}