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

class ContentbuilderControllerAjax extends JController
{
    function __construct()
    {
        parent::__construct();
        
        contentbuilder::setPermissions(JRequest::getInt('id',0),0, class_exists('cbFeMarker') ? '_fe' : '');
    }

    function display()
    {
        JRequest::setVar('tmpl', JRequest::getWord('tmpl',null));
        JRequest::setVar('layout', JRequest::getWord('layout',null));
        JRequest::setVar('view', 'ajax');
        JRequest::setVar('format', 'raw');
        
        parent::display();
    }
}
