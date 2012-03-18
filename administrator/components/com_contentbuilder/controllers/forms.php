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

class ContentbuilderControllerForms extends JController
{
    function __construct()
    {
        parent::__construct();
        JHtml::_('behavior.modal');
        if(JRequest::getInt('email_users',-1) != -1){
            JFactory::getSession()->set('email_users', JRequest::getVar('email_users','none'), 'com_contentbuilder');
        }
        if(JRequest::getInt('email_admins',-1) != -1){
            JFactory::getSession()->set('email_admins', JRequest::getVar('email_admins',''), 'com_contentbuilder');
        }
        if(JRequest::getInt('slideStartOffset',-1) != -1){
            JFactory::getSession()->set('slideStartOffset', JRequest::getInt('slideStartOffset',1));
        }
        if(JRequest::getInt('tabStartOffset',-1) != -1){
            JFactory::getSession()->set('tabStartOffset', JRequest::getInt('tabStartOffset',0));
        }
        // Register Extra tasks
        $this->registerTask( 'add', 'edit' );
    }

    function copy()
    {
        $cid = JRequest::getVar('cid', array(), '', 'array');

        if(count($cid) > 0)
        {
            $model = $this->getModel( 'forms' );
            $model->copy();
        }

        $this->setRedirect( 'index.php?option=com_contentbuilder&controller=forms&limitstart='.JRequest::getInt('limitstart'), JText::_('COM_CONTENTBUILDER_COPIED') );
    }
    
    function orderup() {
        $model = $this->getModel( 'form' );
        $model->move(-1);
        $this->setRedirect( JRoute::_('index.php?option=com_contentbuilder&controller=forms&limitstart='.JRequest::getInt('limitstart'), false) );
    }

    function listorderup() {
        $model = $this->getModel( 'form' );
        $model->listMove(-1);
        JRequest::setVar( 'view', 'form' );
        JRequest::setVar( 'layout', 'form'  );
        JRequest::setVar( 'hidemainmenu', 1 );
        JRequest::setVar( 'filter_order', 'ordering' );
        JRequest::setVar( 'filter_order_Dir', 'asc' );
        JRequest::setVar( 'limitstart', JRequest::getInt('limitstart') );
        parent::display();
    }

    function orderdown() {
        $model = $this->getModel( 'form' );
        $model->move(1);
        $this->setRedirect( JRoute::_('index.php?option=com_contentbuilder&controller=forms&limitstart='.JRequest::getInt('limitstart'), false) );
    }

    function listorderdown() {
        $model = $this->getModel( 'form' );
        $model->listMove(1);
        JRequest::setVar( 'view', 'form' );
        JRequest::setVar( 'layout', 'form'  );
        JRequest::setVar( 'hidemainmenu', 1 );
        JRequest::setVar( 'filter_order', 'ordering' );
        JRequest::setVar( 'filter_order_Dir', 'asc' );
        JRequest::setVar( 'limitstart', JRequest::getInt('limitstart') );
        parent::display();
    }

    function saveorder(){
        $model = $this->getModel( 'forms' );
        $model->saveOrder();
        $this->setRedirect( JRoute::_('index.php?option=com_contentbuilder&controller=forms&limitstart='.JRequest::getInt('limitstart'), false) );
    }


    function listsaveorder(){
        $model = $this->getModel( 'form' );
        $model->listSaveOrder();
        JRequest::setVar( 'view', 'form' );
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
            $model = $this->getModel( 'form' );
            $model->setPublished();
        }
        else if(count($cid) > 1)
        {
            $model = $this->getModel( 'form' );
            $model->setPublished();
        }

        $this->setRedirect( JRoute::_('index.php?option=com_contentbuilder&controller=forms&limitstart='.JRequest::getInt('limitstart'), false), JText::_('COM_CONTENTBUILDER_PUBLISHED') );
    }

    function listpublish() {
        $cid = JRequest::getVar('cid', array(), '', 'array');

        $model = $this->getModel( 'form' );
        $model->setListPublished();

        JRequest::setVar( 'view', 'form' );
        JRequest::setVar( 'layout', 'form'  );
        JRequest::setVar( 'hidemainmenu', 1 );
        JRequest::setVar( 'filter_order', 'ordering' );
        JRequest::setVar( 'filter_order_Dir', 'asc' );
        JRequest::setVar( 'limitstart', JRequest::getInt('limitstart') );

        parent::display();
    }

    function linkable() {
        $cid = JRequest::getVar('cid', array(), '', 'array');

        $model = $this->getModel( 'form' );
        $model->setListLinkable();

        JRequest::setVar( 'view', 'form' );
        JRequest::setVar( 'layout', 'form'  );
        JRequest::setVar( 'hidemainmenu', 1 );
        JRequest::setVar( 'filter_order', 'ordering' );
        JRequest::setVar( 'filter_order_Dir', 'asc' );
        JRequest::setVar( 'limitstart', JRequest::getInt('limitstart') );

        parent::display();
    }

    function editable() {
        $cid = JRequest::getVar('cid', array(), '', 'array');

        $model = $this->getModel( 'form' );
        $model->setListEditable();

        JRequest::setVar( 'view', 'form' );
        JRequest::setVar( 'layout', 'form'  );
        JRequest::setVar( 'hidemainmenu', 1 );
        JRequest::setVar( 'filter_order', 'ordering' );
        JRequest::setVar( 'filter_order_Dir', 'asc' );
        JRequest::setVar( 'limitstart', JRequest::getInt('limitstart') );

        parent::display();
    }
    
    function editable_include() {
        $this->editable();
    }

    function list_include() {
        $cid = JRequest::getVar('cid', array(), '', 'array');

        $model = $this->getModel( 'form' );
        $model->setListListInclude();

        JRequest::setVar( 'view', 'form' );
        JRequest::setVar( 'layout', 'form'  );
        JRequest::setVar( 'hidemainmenu', 1 );
        JRequest::setVar( 'filter_order', 'ordering' );
        JRequest::setVar( 'filter_order_Dir', 'asc' );
        JRequest::setVar( 'limitstart', JRequest::getInt('limitstart') );

        parent::display();
    }

    function search_include() {
        $cid = JRequest::getVar('cid', array(), '', 'array');

        $model = $this->getModel( 'form' );
        $model->setListSearchInclude();

        JRequest::setVar( 'view', 'form' );
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
            $model = $this->getModel( 'form' );
            $model->setUnpublished();
        }
        else if(count($cid) > 1)
        {
            $model = $this->getModel( 'form' );
            $model->setUnpublished();
        }

        $this->setRedirect( JRoute::_('index.php?option=com_contentbuilder&controller=forms&limitstart='.JRequest::getInt('limitstart'), false), JText::_('COM_CONTENTBUILDER_UNPUBLISHED') );
    }

    function listunpublish() {
        $cid = JRequest::getVar('cid', array(), '', 'array');

        $model = $this->getModel( 'form' );
        $model->setListUnpublished();

        JRequest::setVar( 'view', 'form' );
        JRequest::setVar( 'layout', 'form'  );
        JRequest::setVar( 'hidemainmenu', 1 );
        JRequest::setVar( 'filter_order', 'ordering' );
        JRequest::setVar( 'filter_order_Dir', 'asc' );
        JRequest::setVar( 'limitstart', JRequest::getInt('limitstart') );

        parent::display();
    }

    function not_linkable() {
        $cid = JRequest::getVar('cid', array(), '', 'array');

        $model = $this->getModel( 'form' );
        $model->setListNotLinkable();

        JRequest::setVar( 'view', 'form' );
        JRequest::setVar( 'layout', 'form'  );
        JRequest::setVar( 'hidemainmenu', 1 );
        JRequest::setVar( 'filter_order', 'ordering' );
        JRequest::setVar( 'filter_order_Dir', 'asc' );
        JRequest::setVar( 'limitstart', JRequest::getInt('limitstart') );

        parent::display();
    }

    function not_editable() {
        $cid = JRequest::getVar('cid', array(), '', 'array');

        $model = $this->getModel( 'form' );
        $model->setListNotEditable();

        JRequest::setVar( 'view', 'form' );
        JRequest::setVar( 'layout', 'form'  );
        JRequest::setVar( 'hidemainmenu', 1 );
        JRequest::setVar( 'filter_order', 'ordering' );
        JRequest::setVar( 'filter_order_Dir', 'asc' );
        JRequest::setVar( 'limitstart', JRequest::getInt('limitstart') );

        parent::display();
    }

    function no_list_include() {
        $cid = JRequest::getVar('cid', array(), '', 'array');

        $model = $this->getModel( 'form' );
        $model->setListNoListInclude();

        JRequest::setVar( 'view', 'form' );
        JRequest::setVar( 'layout', 'form'  );
        JRequest::setVar( 'hidemainmenu', 1 );
        JRequest::setVar( 'filter_order', 'ordering' );
        JRequest::setVar( 'filter_order_Dir', 'asc' );
        JRequest::setVar( 'limitstart', JRequest::getInt('limitstart') );

        parent::display();
    }

    function no_search_include() {
        $cid = JRequest::getVar('cid', array(), '', 'array');

        $model = $this->getModel( 'form' );
        $model->setListNoSearchInclude();

        JRequest::setVar( 'view', 'form' );
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
        JRequest::setVar( 'view', 'form' );
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
        $model = $this->getModel('form');
        $id = $model->store();
        
        if ($id) {
            $msg = JText::_( 'COM_CONTENTBUILDER_SAVED' );
        } else {
            $msg = JText::_( 'COM_CONTENTBUILDER_ERROR' );
        }

        $limit = 0;
        $additionalParams = '';
        if($keep_task){
            if($id){
                $additionalParams = '&task=edit&cid[]='.$id;
                $limit = JRequest::getInt('limitstart');
            }
        }

        // Check the table in so it can be edited.... we are done with it anyway
        $link = JRoute::_('index.php?option=com_contentbuilder&controller=forms&limitstart='.$limit.$additionalParams, false);
        $this->setRedirect($link, $msg);
    }

    function saveNew()
    {
        $model = $this->getModel('form');

        if ($model->store()) {
            $msg = JText::_( 'COM_CONTENTBUILDER_SAVED' );
        } else {
            $msg = JText::_( 'COM_CONTENTBUILDER_ERROR' );
        }

        // Check the table in so it can be edited.... we are done with it anyway
        $link = JRoute::_('index.php?option=com_contentbuilder&controller=forms&task=edit&limitstart='.JRequest::getInt('limitstart'), false);
        $this->setRedirect($link, $msg);
    }

    function remove()
    {
        $model = $this->getModel('form');
        if(!$model->delete()) {
            $msg = JText::_( 'COM_CONTENTBUILDER_ERROR' );
        } else {
            $msg = JText::_( 'COM_CONTENTBUILDER_DELETED' );
        }

        $this->setRedirect( JRoute::_('index.php?option=com_contentbuilder&controller=forms&limitstart='.JRequest::getInt('limitstart'), false), $msg );
    }

    function listremove()
    {
        $model = $this->getModel('form');
        if(!$model->listDelete()) {
            $msg = JText::_( 'COM_CONTENTBUILDER_ERROR' );
        } else {
            $msg = JText::_( 'COM_CONTENTBUILDER_DELETED' );
        }

        JRequest::setVar( 'view', 'form' );
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
        $this->setRedirect( JRoute::_('index.php?option=com_contentbuilder&controller=forms&limitstart=0', false), $msg );
    }

    function display()
    {
        JRequest::setVar('tmpl', JRequest::getWord('tmpl',null));
        JRequest::setVar('layout', JRequest::getWord('layout',null));
        JRequest::setVar('view', 'forms');

        parent::display();
    }
}
