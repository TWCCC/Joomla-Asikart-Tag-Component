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

jimport('joomla.version');
$version = new JVersion();

if(version_compare($version->getShortVersion(), '1.6', '>=')){

    if( ( JRequest::getCmd('controller','') == 'elementoptions' || JRequest::getCmd('controller','') == 'storages' || JRequest::getCmd('controller','') == 'forms' || JRequest::getCmd('controller','') == 'users' ) && !JFactory::getUser()->authorise('contentbuilder.manage', 'com_contentbuilder') ){

            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));

    }

    if ( !( JRequest::getCmd('controller','') == 'elementoptions' || JRequest::getCmd('controller','') == 'storages' || JRequest::getCmd('controller','') == 'forms' || JRequest::getCmd('controller','') == 'users' ) && !JFactory::getUser()->authorise('contentbuilder.admin', 'com_contentbuilder')) 
    {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
    }
    
    $db = JFactory::getDBO();
    $db->setQuery("Select id From `#__menu` Where `alias` = 'root'");
    if(!$db->loadResult()){
        $db->setQuery("INSERT INTO `#__menu` VALUES(1, '', 'Menu_Item_Root', 'root', '', '', '', '', 1, 0, 0, 0, 0, 0, '0000-00-00 00:00:00', 0, 0, '', 0, '', 0, 67, 0, '*', 0)");
        $db->query();
    }

}

require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'contentbuilder.php');

$db = JFactory::getDBO();
$db->setQuery("Select `id`,`name` From #__contentbuilder_forms Where display_in In (1,2) And published = 1");
$forms = $db->loadAssocList();

foreach($forms As $form){
    contentbuilder::createBackendMenuItem($form['id'], $form['name'], true);
}


// Require the base controller
require_once( JPATH_COMPONENT_ADMINISTRATOR.DS.'controller.php' );

if(version_compare($version->getShortVersion(), '1.6', '>=')){

JSubMenuHelper::addEntry(
                        JText::_('COM_CONTENTBUILDER_STORAGES'),
                        'index.php?option=com_contentbuilder&controller=storages', JRequest::getVar('controller','') == 'storages');

    
JSubMenuHelper::addEntry(
                        JText::_('COM_CONTENTBUILDER_LIST'),
                        'index.php?option=com_contentbuilder&controller=forms', JRequest::getVar('controller','') == 'forms');

JSubMenuHelper::addEntry(
                        'Try BreezingForms!',
                        'index.php?option=com_contentbuilder&view=contentbuilder&market=true', JRequest::getVar('view','') == 'contentbuilder');


JSubMenuHelper::addEntry(
                        JText::_('COM_CONTENTBUILDER_ABOUT'),
                        'index.php?option=com_contentbuilder&view=contentbuilder', JRequest::getVar('view','') == 'contentbuilder');
}

// Require specific controller if requested
$controller = trim(JRequest::getWord('controller'));
if($controller) {
    $path = JPATH_COMPONENT_ADMINISTRATOR.DS.'controllers'.DS.$controller.'.php';
    if (file_exists($path)) {
        require_once $path;
    } else {
        $controller = '';
    }
}

// Create the controller
$classname    = 'ContentbuilderController'.ucfirst( $controller );
$controller   = new $classname( );

// Perform the Request task
$controller->execute( JRequest::getWord( 'task' ) );

// Redirect if set by the controller
$controller->redirect();
