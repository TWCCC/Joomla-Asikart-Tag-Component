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

class ContentbuilderControllerElementoptions extends JController
{
    function __construct()
    {
        jimport('joomla.html.pane');
        parent::__construct();
    }

    function display()
    {
        JRequest::setVar('tmpl', JRequest::getWord('tmpl',null));
        JRequest::setVar('layout', JRequest::getWord('layout',null));
        JRequest::setVar('view', 'elementoptions');

        parent::display();
    }
    
    function save()
    {
        $model = $this->getModel('elementoptions');
        $id = $model->store();
        
        if ($id) {
            $msg = JText::_( 'COM_CONTENTBUILDER_SAVED' );
        } else {
            $msg = JText::_( 'COM_CONTENTBUILDER_ERROR' );
        }

        
        $type_change_url = '';
        $type_change = JRequest::getInt('type_change',0);
        if($type_change){
            $type_change_url = '&type_change=1&type_selection='.JRequest::getCmd('type_selection','');
        }
        
        // Check the table in so it can be edited.... we are done with it anyway
        $link = JRoute::_('index.php?option=com_contentbuilder&controller=elementoptions&tabStartOffset='.JRequest::getInt('tabStartOffset',0).'&tmpl=component&element_id='.JRequest::getInt('element_id',  0).'&id='.JRequest::getInt('id',  0).$type_change_url, false);
        $this->setRedirect($link, $msg);
    }
}
