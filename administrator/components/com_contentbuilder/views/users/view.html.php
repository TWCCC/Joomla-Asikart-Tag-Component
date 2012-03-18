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

require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'classes' . DS . 'contentbuilder.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'classes' . DS . 'contentbuilder_helpers.php');

class ContentbuilderViewUsers extends JView
{
    function display($tpl = null)
    {
        echo '
        <style type="text/css">
        .icon-48-logo_left { background-image: url(../administrator/components/com_contentbuilder/views/logo_left.png); }
        </style>
        ';
        jimport('joomla.version');
        $version = new JVersion();
        if(version_compare($version->getShortVersion(), '1.6', '>=')){
            echo '<link rel="stylesheet" href="'.JURI::root(true).'/administrator/components/com_contentbuilder/views/bluestork.fix.css" type="text/css" />';
        }
        JToolBarHelper::title(   '<img src="components/com_contentbuilder/views/logo_right.png" alt="" align="top" /> <span style="display:inline-block; vertical-align:middle"> :: ' . JText::_( 'COM_CONTENTBUILDER_FORMS' ) . '</span>', 'logo_left.png' );
        JToolBarHelper::editListX();

        // Get data from the model
        $items = $this->get( 'Data');
        $pagination = $this->get('Pagination');

        $state = $this->get( 'state' );
        $lists['users_search'] = $state->get( 'users_search' );
        $lists['order_Dir'] = $state->get( 'users_filter_order_Dir' );
        $lists['order'] = $state->get( 'users_filter_order' );
        $lists['state']	= JHTML::_('grid.state', $state->get( 'users_filter_state' ) );
        $lists['limitstart'] = $state->get( 'limitstart' );
        
        $ordering = ($lists['order'] == 'ordering');

        $this->assignRef('ordering', $ordering);
        $this->assignRef( 'lists', $lists );

        $this->assignRef( 'items', $items );
        $this->assignRef( 'pagination', $pagination );
        parent::display($tpl);
    }
}
