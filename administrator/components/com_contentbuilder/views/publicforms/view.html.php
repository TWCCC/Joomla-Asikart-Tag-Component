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

class ContentbuilderViewPublicforms extends JView
{
    function display($tpl = null)
    {
        // Get data from the model
        $items = $this->get( 'Data');
        $perms = $this->get( 'Permissions');
        $page_heading = $this->get( 'ShowPageHeading');
        $introtext = $this->get( 'ShowIntrotext');
        $show_tags = $this->get( 'ShowTags');
        $show_id = $this->get( 'ShowId');
        $show_permissions = $this->get( 'ShowPermissions');
        $show_permissions_new = $this->get( 'ShowPermissionsNew');
        $show_permissions_edit = $this->get( 'ShowPermissionsEdit');
        $pagination = $this->get('Pagination');
        $tags = $this->get( 'Tags');
        
        $state = $this->get( 'state' );
        
        $lists['order_Dir'] = $state->get( 'forms_filter_order_Dir' );
        $lists['order'] = $state->get( 'forms_filter_order' );
        $lists['state']	= JHTML::_('grid.state', $state->get( 'forms_filter_state' ) );
        $lists['limitstart'] = $state->get( 'limitstart' );
        $lists['filter_tag'] = $state->get( 'forms_filter_tag' );
        
        $ordering = ($lists['order'] == 'ordering');

        $this->assignRef('show_permissions', $show_permissions);
        $this->assignRef('show_permissions_new', $show_permissions_new);
        $this->assignRef('show_permissions_edit', $show_permissions_edit);
        $this->assignRef('page_heading', $page_heading);
        $this->assignRef('show_tags', $show_tags);
        $this->assignRef('show_id', $show_id);
        $this->assignRef('introtext', $introtext);
        $this->assignRef('perms', $perms);
        $this->assignRef('ordering', $ordering);
        $this->assignRef( 'tags', $tags );
        $this->assignRef( 'lists', $lists );

        $this->assignRef( 'items', $items );
        $this->assignRef( 'pagination', $pagination );
        parent::display($tpl);
    }
}
