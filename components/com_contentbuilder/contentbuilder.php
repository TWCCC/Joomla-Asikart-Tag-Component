<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        http://www.crosstec.de
 * @license     GNU/GPL
*/

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

JHTML::_('behavior.mootools');

class cbFeMarker{}

// Require the base controller

require_once( JPATH_COMPONENT.DS.'controller.php' );

JRequest::setVar('cb_controller', null);
JRequest::setVar('cb_category_id', null);
JRequest::setVar('cb_list_filterhidden', null);
JRequest::setVar('cb_list_orderhidden', null);
JRequest::setVar('cb_show_author',null);
JRequest::setVar('cb_show_top_bar',null);
JRequest::setVar('cb_show_details_top_bar',null);
JRequest::setVar('cb_show_bottom_bar',null);
JRequest::setVar('cb_show_details_bottom_bar',null);
JRequest::setVar('cb_latest',null);
JRequest::setVar('cb_show_details_back_button',null);
JRequest::setVar('cb_list_limit',null);
JRequest::setVar('cb_filter_in_title',null);
JRequest::setVar('cb_prefix_in_title',null);
JRequest::setVar('force_menu_item_id',null);
JRequest::setVar('cb_category_menu_filter',null);


if (JRequest::getInt('Itemid', 0)) {
    
    $option = 'com_contentbuilder';
    
    if( JRequest::getVar('layout',null) !== null ){
        JFactory::getSession()->set('com_contentbuilder.layout.'.JRequest::getInt('Itemid',0).JRequest::getVar('layout',null), JRequest::getVar('layout',null));
    }

    if( JFactory::getSession()->get('com_contentbuilder.layout.'.JRequest::getInt('Itemid',0).JRequest::getVar('layout',null), null) !== null ){
        JRequest::setVar('layout', JFactory::getSession()->get('com_contentbuilder.layout.'.JRequest::getInt('Itemid',0).JRequest::getVar('layout',null), null));
    }
    
    jimport('joomla.version');
    $version = new JVersion();

    if (version_compare($version->getShortVersion(), '1.6', '>=')) {
        $menu = JSite::getMenu();
        $item = $menu->getActive();
        if (is_object($item)) {
            if($item->params->get('form_id', null) !== null){
                JRequest::setVar('id', $item->params->get('form_id', null));
            }
            JRequest::setVar('cb_category_id', $item->params->get('cb_category_id', null));
            JRequest::setVar('cb_controller', $item->params->get('cb_controller', null));
            JRequest::setVar('cb_list_filterhidden', $item->params->get('cb_list_filterhidden', null));
            JRequest::setVar('cb_list_orderhidden', $item->params->get('cb_list_orderhidden', null));
            JRequest::setVar('cb_show_author', $item->params->get('cb_show_author', null));
            JRequest::setVar('cb_show_bottom_bar', $item->params->get('cb_show_bottom_bar', null));
            JRequest::setVar('cb_show_top_bar', $item->params->get('cb_show_top_bar', null));
            JRequest::setVar('cb_show_details_bottom_bar', $item->params->get('cb_show_details_bottom_bar', null));
            JRequest::setVar('cb_show_details_top_bar', $item->params->get('cb_show_details_top_bar', null));
            JRequest::setVar('cb_show_details_back_button', $item->params->get('cb_show_details_back_button', null));
            JRequest::setVar('cb_list_limit', $item->params->get('cb_list_limit', 20));
            JRequest::setVar('cb_filter_in_title', $item->params->get('cb_filter_in_title', 1));
            JRequest::setVar('cb_prefix_in_title', $item->params->get('cb_prefix_in_title', 1));
            JRequest::setVar('force_menu_item_id', $item->params->get('force_menu_item_id', 0));
            JRequest::setVar('cb_category_menu_filter', $item->params->get('cb_category_menu_filter', 0));
            
        } 
        
    } else {
        $params = JComponentHelper::getParams($option);
        if($params->get('form_id', null) !== null){
            JRequest::setVar('id', $params->get('form_id', null));
        }
        JRequest::setVar('cb_category_id', $params->get('cb_category_id', null));
        JRequest::setVar('cb_controller', $params->get('cb_controller', null));
        JRequest::setVar('cb_list_filterhidden', $params->get('cb_list_filterhidden', null));
        JRequest::setVar('cb_list_orderhidden', $params->get('cb_list_orderhidden', null));
        JRequest::setVar('cb_show_author', $params->get('cb_show_author', null));
        JRequest::setVar('cb_show_bottom_bar', $params->get('cb_show_bottom_bar', null));
        JRequest::setVar('cb_show_top_bar', $params->get('cb_show_top_bar', null));
        JRequest::setVar('cb_show_details_bottom_bar', $params->get('cb_show_details_bottom_bar', null));
        JRequest::setVar('cb_show_details_top_bar', $params->get('cb_show_details_top_bar', null));
        JRequest::setVar('cb_show_details_back_button', $params->get('cb_show_details_back_button', null));
        JRequest::setVar('cb_list_limit', $params->get('cb_list_limit', 20));
        JRequest::setVar('cb_filter_in_title', $params->get('cb_filter_in_title', 1));
        JRequest::setVar('cb_prefix_in_title', $params->get('cb_prefix_in_title', 1));
        JRequest::setVar('force_menu_item_id', $params->get('force_menu_item_id', 0));
        JRequest::setVar('cb_category_menu_filter', $params->get('cb_category_menu_filter', 0));
    }
}

// Require specific controller if requested
$controller = trim(JRequest::getWord('controller'));

if(JRequest::getCmd('view','') == 'details' || ( JRequest::getCmd('view','') == 'latest' && JRequest::getCmd('controller', '') == '' ) ){
    $controller = 'details';
}

if(JRequest::getVar('cb_controller') == 'edit'){
    $controller = 'edit';
}
else if(JRequest::getVar('cb_controller') == 'publicforms' && JRequest::getInt('id',0) <= 0){
    $controller = 'publicforms';
}

if(!$controller) {
    
    $controller = 'list';
    
}

$path = JPATH_COMPONENT . DS . 'controllers' . DS . $controller . '.php';
if (file_exists($path)) {
    require_once $path;
} else {
    $controller = '';
}

// Create the controller
$classname    = 'ContentbuilderController'.ucfirst( $controller );
$controller   = new $classname( );

// Perform the Request task
$controller->execute( JRequest::getWord( 'task' ) );

// Redirect if set by the controller
$controller->redirect();
