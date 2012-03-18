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

class ContentbuilderControllerEdit extends JController
{
    function __construct()
    {
        JRequest::setVar('cbIsNew', 0);
        
        if(JRequest::getCmd('task','') == 'delete' || JRequest::getCmd('task','') == 'publish'){
            $items = JRequest::getVar( 'cid', array(), 'request', 'array' );
            contentbuilder::setPermissions(JRequest::getInt('id',0), $items, class_exists('cbFeMarker') ? '_fe' : '');
        }else{
            if(JRequest::getCmd('record_id','')){
                contentbuilder::setPermissions(JRequest::getInt('id',0), JRequest::getCmd('record_id',''), class_exists('cbFeMarker') ? '_fe' : '');
            } else {
                JRequest::setVar('cbIsNew', 1);
                contentbuilder::setPermissions(JRequest::getInt('id',0),0, class_exists('cbFeMarker') ? '_fe' : '');
            }
        }
        parent::__construct();
    }

    function save($apply=false){
        
        jimport('joomla.version');
        $version = new JVersion();

        if(JFactory::getApplication()->isSite() && JRequest::getInt('Itemid',0)){
            if (version_compare($version->getShortVersion(), '1.6', '>=')) {
                $menu = JSite::getMenu();
                $item = $menu->getActive();
                if (is_object($item)) {
                    JRequest::setVar('cb_controller', $item->params->get('cb_controller', null));
                    JRequest::setVar('cb_category_id', $item->params->get('cb_category_id', null));
                }
            } else {
                $params = JComponentHelper::getParams('com_contentbuilder');
                JRequest::setVar('cb_controller', $params->get('cb_controller', null));
                JRequest::setVar('cb_category_id', $params->get('cb_category_id', null));
            }
        }

        JRequest::setVar('cbIsNew', 0);
        JRequest::setVar('cbInternalCheck', 1);
        
        if(JRequest::getCmd('record_id',  '')){
            contentbuilder::checkPermissions('edit', JText::_('COM_CONTENTBUILDER_PERMISSIONS_EDIT_NOT_ALLOWED'), class_exists('cbFeMarker') ? '_fe' : '');
        }else{
            JRequest::setVar('cbIsNew', 1);
            contentbuilder::checkPermissions('new', JText::_('COM_CONTENTBUILDER_PERMISSIONS_NEW_NOT_ALLOWED'), class_exists('cbFeMarker') ? '_fe' : '');
        }
        
        $model = $this->getModel('edit');
        $id = $model->store();
        
        $submission_failed = JRequest::getBool('cb_submission_failed',false);
        $cb_submit_msg = JRequest::setVar('cb_submit_msg', '');
        
        $type = 'message';
        if ($id && !$submission_failed) {
            
            $msg = JText::_( 'COM_CONTENTBUILDER_SAVED' );
            $return = JRequest::getVar('return','');
            if( $return ){
                $return = base64_decode($return);
                
                if( !JRequest::getBool('cbInternalCheck',1) ){
                    JFactory::getApplication()->redirect($return, $msg);
                }
                if( JURI::isInternal($return) ){
                    JFactory::getApplication()->redirect($return, $msg);
                }
            }
            
        } else {
            $apply = true; // forcing to stay in form on errors
            $type = 'error';
        }
        
        if(JRequest::getVar('cb_controller') == 'edit'){
            $link = JRoute::_('index.php?option=com_contentbuilder&title='.JRequest::getVar('title', '').'&controller=edit&return='.JRequest::getVar('return','').'&Itemid='.JRequest::getInt('Itemid',0), false);
        } else if($apply){
            $link = JRoute::_('index.php?option=com_contentbuilder&title='.JRequest::getVar('title', '').'&controller=edit&return='.JRequest::getVar('return','').'&backtolist='.JRequest::getInt('backtolist',0).'&id='.JRequest::getInt('id', 0).'&record_id='.$id.'&Itemid='.JRequest::getInt('Itemid',0).'&limitstart='.JRequest::getInt('limitstart',0).'&filter_order='.JRequest::getCmd('filter_order'), false);
        }else{
            $link = JRoute::_('index.php?option=com_contentbuilder&title='.JRequest::getVar('title', '').'&controller=list&id='.JRequest::getInt('id', 0).'&limitstart='.JRequest::getInt('limitstart',0).'&filter_order='.JRequest::getCmd('filter_order').'&Itemid='.JRequest::getInt('Itemid',0), false);
        }
        $this->setRedirect($link, $msg, $type);
    }
    
    function apply(){
        $this->save(true);
    }
    
    function delete(){
        
        contentbuilder::checkPermissions('delete', JText::_('COM_CONTENTBUILDER_PERMISSIONS_DELETE_NOT_ALLOWED'), class_exists('cbFeMarker') ? '_fe' : '');

        $model = $this->getModel('edit');
        $id = $model->delete();
        $msg = JText::_('COM_CONTENTBUILDER_ENTRIES_DELETED');
        $link = JRoute::_('index.php?option=com_contentbuilder&controller=list&id='.JRequest::getInt('id', 0).'&limitstart='.JRequest::getInt('limitstart',0).'&filter_order='.JRequest::getCmd('filter_order').'&Itemid='.JRequest::getInt('Itemid',0), false);
        $this->setRedirect($link, $msg, 'message');
    }
    
    function state(){
        
        contentbuilder::checkPermissions('state', JText::_('COM_CONTENTBUILDER_PERMISSIONS_STATE_CHANGE_NOT_ALLOWED'), class_exists('cbFeMarker') ? '_fe' : '');

        $model = $this->getModel('edit');
        $model->change_list_states();
        $msg = JText::_('COM_CONTENTBUILDER_STATES_CHANGED');
        $link = JRoute::_('index.php?option=com_contentbuilder&controller=list&id='.JRequest::getInt('id', 0).'&limitstart='.JRequest::getInt('limitstart',0).'&filter_order='.JRequest::getCmd('filter_order').'&Itemid='.JRequest::getInt('Itemid',0), false);
        $this->setRedirect($link, $msg, 'message');
    }
    
    function publish(){
        
        contentbuilder::checkPermissions('publish', JText::_('COM_CONTENTBUILDER_PERMISSIONS_PUBLISHING_NOT_ALLOWED'), class_exists('cbFeMarker') ? '_fe' : '');

        $model = $this->getModel('edit');
        $model->change_list_publish();
        if(JRequest::getInt('list_publish', 0)){
            $msg = JText::_('PUBLISHED');
        }else{
            $msg = JText::_('UNPUBLISHED');
        }
        $link = JRoute::_('index.php?option=com_contentbuilder&controller=list&id='.JRequest::getInt('id', 0).'&limitstart='.JRequest::getInt('limitstart',0).'&filter_order='.JRequest::getCmd('filter_order').'&Itemid='.JRequest::getInt('Itemid',0), false);
        $this->setRedirect($link, $msg, 'message');
    }
    
    function language(){
        
        contentbuilder::checkPermissions('language', JText::_('COM_CONTENTBUILDER_PERMISSIONS_CHANGE_LANGUAGE_NOT_ALLOWED'), class_exists('cbFeMarker') ? '_fe' : '');

        $model = $this->getModel('edit');
        $model->change_list_language();
        $msg = JText::_('COM_CONTENTBUILDER_LANGUAGE_CHANGED');
        $link = JRoute::_('index.php?option=com_contentbuilder&controller=list&id='.JRequest::getInt('id', 0).'&limitstart='.JRequest::getInt('limitstart',0).'&filter_order='.JRequest::getCmd('filter_order').'&Itemid='.JRequest::getInt('Itemid',0), false);
        $this->setRedirect($link, $msg, 'message');
    }
    
    function display()
    {
        if(JRequest::getCmd('record_id',  '')){
            contentbuilder::checkPermissions('edit', JText::_('COM_CONTENTBUILDER_PERMISSIONS_EDIT_NOT_ALLOWED'), class_exists('cbFeMarker') ? '_fe' : '');
        }else{
            contentbuilder::checkPermissions('new', JText::_('COM_CONTENTBUILDER_PERMISSIONS_NEW_NOT_ALLOWED'), class_exists('cbFeMarker') ? '_fe' : '');
        }

        JRequest::setVar('tmpl', JRequest::getWord('tmpl',null));
        JRequest::setVar('layout', JRequest::getWord('layout',null) == 'latest' ? null : JRequest::getWord('layout',null));
        JRequest::setVar('view', 'edit');

        parent::display();
    }
}
