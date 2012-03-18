<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        http://www.crosstec.de
 * @license     GNU/GPL
*/

// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');
require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'classes' . DS . 'contentbuilder.php');

class ContentbuilderControllerList extends JController
{
    function __construct()
    {
        contentbuilder::setPermissions(JRequest::getInt('id',0),0, class_exists('cbFeMarker') ? '_fe' : '' );
        parent::__construct();
    }

    function display()
    {
        contentbuilder::checkPermissions('listaccess', JText::_('COM_CONTENTBUILDER_PERMISSIONS_LISTACCESS_NOT_ALLOWED'), class_exists('cbFeMarker') ? '_fe' : '');
        
        JRequest::setVar('tmpl', JRequest::getWord('tmpl',null));
        JRequest::setVar('layout', JRequest::getWord('layout',null) == 'latest' ? null : JRequest::getWord('layout',null));
        JRequest::setVar('view', 'list');

        parent::display();
    }
}
