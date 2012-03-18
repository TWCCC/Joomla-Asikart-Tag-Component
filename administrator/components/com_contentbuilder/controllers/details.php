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

class ContentbuilderControllerDetails extends JController
{
    function __construct()
    {
        if(class_exists('cbFeMarker') && JRequest::getInt('Itemid',0)){
            
            $option = 'com_contentbuilder';
            
            // try menu item
            jimport('joomla.version');
            $version = new JVersion();

            if(version_compare($version->getShortVersion(), '1.6', '>=')){
                $menu = JSite::getMenu();
                $item = $menu->getActive();
                if (is_object($item)) {
                    if($item->params->get('record_id', null) !== null){
                        JRequest::setVar('record_id', $item->params->get('record_id', null));
                        $this->_show_back_button = $item->params->get('show_back_button', null);
                    }
                    
                    //if($item->params->get('show_page_heading', null) !== null){
                    //    $this->_show_page_heading = $item->params->get('show_page_heading', null);
                    //}
                }

            }else{
                $params = JComponentHelper::getParams( $option );
                if($params->get('record_id', null)){
                    JRequest::setVar('record_id', $params->get('record_id', null));
                    $this->_show_back_button = $params->get('show_back_button', null);
                }
                
                //if($params->get('show_page_heading', null) !== null){
                //    $this->_show_page_heading = $params->get('show_page_heading', null);
                //}
            }
        }
        
        if(JRequest::getWord('view', '') == 'latest'){
            
            $db = JFactory::getDBO();
            
            $db->setQuery('Select `type`, `reference_id` From #__contentbuilder_forms Where id = '.intval(JRequest::getInt('id',0)).' And published = 1');
            $form = $db->loadAssoc();
            $form = contentbuilder::getForm($form['type'], $form['reference_id']);
            
            $labels = $form->getElementLabels();
            $ids = array();
            foreach($labels As $reference_id => $label){
                $ids[] = $db->Quote($reference_id);
            }

            if(count($ids)){
                $db->setQuery("Select Distinct `label`, reference_id From #__contentbuilder_elements Where form_id = " . intval(JRequest::getInt('id',0)) . " And reference_id In (" . implode(',', $ids) . ") And published = 1 Order By ordering");
                $rows = $db->loadAssocList();
                $ids = array();
                foreach($rows As $row){
                   $ids[] = $row['reference_id'];
                }
            }
            
            $rec = $form->getListRecords($ids, '', array(), 0, 1, '', array(), 'desc', 0, false, JFactory::getUser()->get('id', 0), 0, -1, -1, -1, -1, array(), true, null);
                      
            if(count($rec) > 0){

                $rec = $rec[0];
                $rec2 = $form->getRecord($rec->colRecord, false, -1, true );

                $record_id = $rec->colRecord;
                JRequest::setVar('record_id', $record_id);

            }
            
            if( !JRequest::getCmd('record_id', '') ){
                
                JRequest::setVar('cbIsNew', 1);
                contentbuilder::setPermissions(JRequest::getInt('id',0),0, class_exists('cbFeMarker') ? '_fe' : '');
                $auth = class_exists('cbFeMarker') ? contentbuilder::authorizeFe('new') : contentbuilder::authorize('new');

                if($auth){
                    JFactory::getApplication()->redirect(JRoute::_('index.php?option=com_contentbuilder&controller=edit&latest=1&backtolist='.JRequest::getInt('backtolist',0).'&id='.JRequest::getInt('id',0).'&record_id=&limitstart='.JRequest::getInt('limitstart',0).'&filter_order='.JRequest::getVar('filter_order',''), false));
                } else {
                   JFactory::getApplication()->enqueueMessage(JText::_('COM_CONTENTBUILDER_ADD_ENTRY_FIRST'));
                   JFactory::getApplication()->redirect('index.php', false);
                }
            }
        }
        
        contentbuilder::setPermissions(JRequest::getInt('id',0), JRequest::getCmd('record_id',0), class_exists('cbFeMarker') ? '_fe' : '');
        parent::__construct();
    }

    function display()
    {
        contentbuilder::checkPermissions('view', JText::_('COM_CONTENTBUILDER_PERMISSIONS_VIEW_NOT_ALLOWED'), class_exists('cbFeMarker') ? '_fe' : '');
        
        JRequest::setVar('tmpl', JRequest::getWord('tmpl',null));
        JRequest::setVar('layout', JRequest::getWord('layout',null) == 'latest' ? null : JRequest::getWord('layout',null));
        if(JRequest::getWord('view', '') == 'latest'){
            JRequest::setVar('cb_latest', 1);
        }
        JRequest::setVar('view', 'details');

        parent::display();
    }
}
