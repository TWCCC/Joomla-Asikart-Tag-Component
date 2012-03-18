<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        http://www.crosstec.de
 * @license     GNU/GPL
*/

// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

class ContentbuilderViewElementoptions extends JView
{
    function display($tpl = null)
    {
        jimport('joomla.version');
        $version = new JVersion();
        if(version_compare($version->getShortVersion(), '1.6', '>=')){
            echo '<link rel="stylesheet" href="'.JURI::root(true).'/administrator/components/com_contentbuilder/views/bluestork.fix.css" type="text/css" />';
        }
        
        // Get data from the model
        $element = $this->get('Data');
        $validations = $this->get('ValidationPlugins');
        $this->assignRef('validations', $validations);
        $this->assignRef('element', $element);
        $this->assignRef('group_definition', $this->get('GroupDefinition'));
        parent::display($tpl);
    }
}
