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

class ContentbuilderControllerPublicforms extends JController
{
    function __construct()
    {
        parent::__construct();
    }

    function display()
    {
        JRequest::setVar('tmpl', JRequest::getWord('tmpl',null));
        JRequest::setVar('layout', JRequest::getWord('layout',null));
        JRequest::setVar('view', 'publicforms');

        parent::display();
    }
}
