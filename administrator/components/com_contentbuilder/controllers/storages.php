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

class ContentbuilderControllerStorages extends JController
{
    function __construct()
    {
        parent::__construct();

        // Register Extra tasks
        $this->registerTask( 'add', 'edit' );
    }

    function orderup() {
        $model = $this->getModel( 'storage' );
        $model->move(-1);
        $this->setRedirect( JRoute::_('index.php?option=com_contentbuilder&controller=storages&limitstart='.JRequest::getInt('limitstart'), false) );
    }

    function listdelete(){
        $model = $this->getModel( 'storage' );
        $model->listDelete();
        JFactory::getApplication()->enqueueMessage(JText::_('COM_CONTENTBUILDER_DELETED'));
        JRequest::setVar( 'view', 'storage' );
        JRequest::setVar( 'layout', 'form'  );
        JRequest::setVar( 'hidemainmenu', 1 );
        JRequest::setVar( 'filter_order', 'ordering' );
        JRequest::setVar( 'filter_order_Dir', 'asc' );
        JRequest::setVar( 'limitstart', JRequest::getInt('limitstart') );
        parent::display();
    }
    
    function listorderup() {
        $model = $this->getModel( 'storage' );
        $model->listMove(-1);
        JRequest::setVar( 'view', 'storage' );
        JRequest::setVar( 'layout', 'form'  );
        JRequest::setVar( 'hidemainmenu', 1 );
        JRequest::setVar( 'filter_order', 'ordering' );
        JRequest::setVar( 'filter_order_Dir', 'asc' );
        JRequest::setVar( 'limitstart', JRequest::getInt('limitstart') );
        parent::display();
    }

    function orderdown() {
        $model = $this->getModel( 'storage' );
        $model->move(1);
        $this->setRedirect( JRoute::_('index.php?option=com_contentbuilder&controller=storages&limitstart='.JRequest::getInt('limitstart'), false) );
    }

    function listorderdown() {
        $model = $this->getModel( 'storage' );
        $model->listMove(1);
        JRequest::setVar( 'view', 'storage' );
        JRequest::setVar( 'layout', 'form'  );
        JRequest::setVar( 'hidemainmenu', 1 );
        JRequest::setVar( 'filter_order', 'ordering' );
        JRequest::setVar( 'filter_order_Dir', 'asc' );
        JRequest::setVar( 'limitstart', JRequest::getInt('limitstart') );
        parent::display();
    }

    function saveorder(){
        $model = $this->getModel( 'storages' );
        $model->saveOrder();
        $this->setRedirect( JRoute::_('index.php?option=com_contentbuilder&controller=storages&limitstart='.JRequest::getInt('limitstart'), false) );
    }


    function listsaveorder(){
        $model = $this->getModel( 'storage' );
        $model->listSaveOrder();
        JRequest::setVar( 'view', 'storage' );
        JRequest::setVar( 'layout', 'form'  );
        JRequest::setVar( 'hidemainmenu', 1 );
        JRequest::setVar( 'filter_order', 'ordering' );
        JRequest::setVar( 'filter_order_Dir', 'asc' );
        JRequest::setVar( 'limitstart', JRequest::getInt('limitstart') );

        parent::display();
    }

    function publish() {
        $cid = JRequest::getVar('cid', array(), '', 'array');

        if(count($cid) == 1)
        {
            $model = $this->getModel( 'storage' );
            $model->setPublished();
        }
        else if(count($cid) > 1)
        {
            $model = $this->getModel( 'storage' );
            $model->setPublished();
        }

        $this->setRedirect( JRoute::_('index.php?option=com_contentbuilder&controller=storages&limitstart='.JRequest::getInt('limitstart'), false), JText::_('COM_CONTENTBUILDER_PUBLISHED') );
    }

    function listpublish() {
        $cid = JRequest::getVar('cid', array(), '', 'array');

        $model = $this->getModel( 'storage' );
        $model->setListPublished();

        JRequest::setVar( 'view', 'storage' );
        JRequest::setVar( 'layout', 'form'  );
        JRequest::setVar( 'hidemainmenu', 1 );
        JRequest::setVar( 'filter_order', 'ordering' );
        JRequest::setVar( 'filter_order_Dir', 'asc' );
        JRequest::setVar( 'limitstart', JRequest::getInt('limitstart') );

        parent::display();
    }

    function unpublish() {
        $cid = JRequest::getVar('cid', array(), '', 'array');

        if(count($cid) == 1)
        {
            $model = $this->getModel( 'storage' );
            $model->setUnpublished();
        }
        else if(count($cid) > 1)
        {
            $model = $this->getModel( 'storage' );
            $model->setUnpublished();
        }

        $this->setRedirect( JRoute::_('index.php?option=com_contentbuilder&controller=storages&limitstart='.JRequest::getInt('limitstart'), false), JText::_('COM_CONTENTBUILDER_UNPUBLISHED') );
    }

    function listunpublish() {
        $cid = JRequest::getVar('cid', array(), '', 'array');

        $model = $this->getModel( 'storage' );
        $model->setListUnpublished();

        JRequest::setVar( 'view', 'storage' );
        JRequest::setVar( 'layout', 'form'  );
        JRequest::setVar( 'hidemainmenu', 1 );
        JRequest::setVar( 'filter_order', 'ordering' );
        JRequest::setVar( 'filter_order_Dir', 'asc' );
        JRequest::setVar( 'limitstart', JRequest::getInt('limitstart') );

        parent::display();
    }


    /**
     * display the edit form
     * @return void
     */
    function edit()
    {
        JRequest::setVar( 'view', 'storage' );
        JRequest::setVar( 'layout', 'form'  );
        JRequest::setVar( 'hidemainmenu', 1 );
        JRequest::setVar( 'filter_order', 'ordering' );
        JRequest::setVar( 'filter_order_Dir', 'asc' );
        parent::display();
    }

    function apply()
    {
        $this->save(true);
    }

    function save($keep_task = false)
    {
        $model = $this->getModel('storage');
        
        $file = JRequest::getVar('csv_file', null, 'files', 'array');
        
        if( trim(JFile::makeSafe($file['name'])) == '' || $file['size'] <= 0 ){
            $id = $model->store();
        }else{
            $id = $model->storeCsv($file);
        }
        
        if (is_numeric($id) && $id) {
            $msg = JText::_( 'COM_CONTENTBUILDER_SAVED' );
        } else if(!is_numeric($id) && !is_bool($id) && is_string($id)) {
            $msg = $id;
        } else {
            $msg = JText::_( 'COM_CONTENTBUILDER_ERROR' );
        }

        $limit = 0;
        $additionalParams = '';
        if($keep_task){
            if(!is_string($id) && $id){
                $additionalParams = '&task=edit&cid[]='.$id;
                $limit = JRequest::getInt('limitstart');
            }
        }

        // Check the table in so it can be edited.... we are done with it anyway
        $link = JRoute::_('index.php?option=com_contentbuilder&controller=storages&limitstart='.$limit.$additionalParams, false);
        $this->setRedirect($link, $msg);
    }

    function saveNew()
    {
        $model = $this->getModel('storage');

        if ($model->store()) {
            $msg = JText::_( 'COM_CONTENTBUILDER_SAVED' );
        } else {
            $msg = JText::_( 'COM_CONTENTBUILDER_ERROR' );
        }

        // Check the table in so it can be edited.... we are done with it anyway
        $link = JRoute::_('index.php?option=com_contentbuilder&controller=storages&task=edit&limitstart='.JRequest::getInt('limitstart'), false);
        $this->setRedirect($link, $msg);
    }

    function remove()
    {
        $model = $this->getModel('storage');
        if(!$model->delete()) {
            $msg = JText::_( 'COM_CONTENTBUILDER_ERROR' );
        } else {
            $msg = JText::_( 'COM_CONTENTBUILDER_DELETED' );
        }

        $this->setRedirect( JRoute::_('index.php?option=com_contentbuilder&controller=storages&limitstart='.JRequest::getInt('limitstart'), false), $msg );
    }

    function listremove()
    {
        $model = $this->getModel('storage');
        if(!$model->listDelete()) {
            $msg = JText::_( 'COM_CONTENTBUILDER_ERROR' );
        } else {
            $msg = JText::_( 'COM_CONTENTBUILDER_DELETED' );
        }

        JRequest::setVar( 'view', 'storage' );
        JRequest::setVar( 'layout', 'form'  );
        JRequest::setVar( 'hidemainmenu', 1 );
        JRequest::setVar( 'filter_order', 'ordering' );
        JRequest::setVar( 'filter_order_Dir', 'asc' );
        JRequest::setVar( 'limitstart', JRequest::getInt('limitstart', 0) );

        parent::display();
    }

    function cancel()
    {
        $msg = JText::_( 'COM_CONTENTBUILDER_CANCELLED' );
        $this->setRedirect( JRoute::_('index.php?option=com_contentbuilder&controller=storages&limitstart=0', false), $msg );
    }

    function display()
    {
        JRequest::setVar('tmpl', JRequest::getWord('tmpl',null));
        JRequest::setVar('layout', JRequest::getWord('layout',null));
        JRequest::setVar('view', 'storages');

        parent::display();
    }
}
