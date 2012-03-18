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

class ContentbuilderControllerUsers extends JController
{
    function __construct()
    {
        parent::__construct();
        
        // Register Extra tasks
        $this->registerTask( 'add', 'edit' );
    }
    
    function verified_view() {
        $cid = JRequest::getVar('cid', array(), '', 'array');

        $model = $this->getModel( 'user' );
        $model->setListVerifiedView();

        JRequest::setVar( 'view', 'users' );
        JRequest::setVar( 'layout', 'default'  );
        JRequest::setVar( 'hidemainmenu', 1 );
        JRequest::setVar( 'filter_order', 'ordering' );
        JRequest::setVar( 'filter_order_Dir', 'asc' );
        JRequest::setVar( 'limitstart', JRequest::getInt('limitstart') );

        parent::display();
    }
    
    function not_verified_view() {
        $cid = JRequest::getVar('cid', array(), '', 'array');

        $model = $this->getModel( 'user' );
        $model->setListNotVerifiedView();

        JRequest::setVar( 'view', 'users' );
        JRequest::setVar( 'layout', 'default'  );
        JRequest::setVar( 'hidemainmenu', 1 );
        JRequest::setVar( 'filter_order', 'ordering' );
        JRequest::setVar( 'filter_order_Dir', 'asc' );
        JRequest::setVar( 'limitstart', JRequest::getInt('limitstart') );

        parent::display();
    }
    
    function verified_new() {
        $cid = JRequest::getVar('cid', array(), '', 'array');

        $model = $this->getModel( 'user' );
        $model->setListVerifiedNew();

        JRequest::setVar( 'view', 'users' );
        JRequest::setVar( 'layout', 'default'  );
        JRequest::setVar( 'hidemainmenu', 1 );
        JRequest::setVar( 'filter_order', 'ordering' );
        JRequest::setVar( 'filter_order_Dir', 'asc' );
        JRequest::setVar( 'limitstart', JRequest::getInt('limitstart') );

        parent::display();
    }
    
    function not_verified_new() {
        $cid = JRequest::getVar('cid', array(), '', 'array');

        $model = $this->getModel( 'user' );
        $model->setListNotVerifiedNew();

        JRequest::setVar( 'view', 'users' );
        JRequest::setVar( 'layout', 'default'  );
        JRequest::setVar( 'hidemainmenu', 1 );
        JRequest::setVar( 'filter_order', 'ordering' );
        JRequest::setVar( 'filter_order_Dir', 'asc' );
        JRequest::setVar( 'limitstart', JRequest::getInt('limitstart') );

        parent::display();
    }
    
    function verified_edit() {
        $cid = JRequest::getVar('cid', array(), '', 'array');

        $model = $this->getModel( 'user' );
        $model->setListVerifiedEdit();

        JRequest::setVar( 'view', 'users' );
        JRequest::setVar( 'layout', 'default'  );
        JRequest::setVar( 'hidemainmenu', 1 );
        JRequest::setVar( 'filter_order', 'ordering' );
        JRequest::setVar( 'filter_order_Dir', 'asc' );
        JRequest::setVar( 'limitstart', JRequest::getInt('limitstart') );

        parent::display();
    }
    
    function not_verified_edit() {
        $cid = JRequest::getVar('cid', array(), '', 'array');

        $model = $this->getModel( 'user' );
        $model->setListNotVerifiedEdit();

        JRequest::setVar( 'view', 'users' );
        JRequest::setVar( 'layout', 'default'  );
        JRequest::setVar( 'hidemainmenu', 1 );
        JRequest::setVar( 'filter_order', 'ordering' );
        JRequest::setVar( 'filter_order_Dir', 'asc' );
        JRequest::setVar( 'limitstart', JRequest::getInt('limitstart') );

        parent::display();
    }
    
    function edit()
    {
        JRequest::setVar( 'view', 'user' );
        JRequest::setVar( 'layout', 'default'  );
        JRequest::setVar( 'hidemainmenu', 1 );
        JRequest::setVar( 'filter_order', 'ordering' );
        JRequest::setVar( 'filter_order_Dir', 'asc' );
        parent::display();
    }

    function apply()
    {
        $this->save(true);
    }

    function publish() {
        $cid = JRequest::getVar('cid', array(), '', 'array');

        if(count($cid) == 1)
        {
            $model = $this->getModel( 'users' );
            $model->setPublished();
        }
        else if(count($cid) > 1)
        {
            $model = $this->getModel( 'users' );
            $model->setPublished();
        }

        $this->setRedirect( JRoute::_('index.php?option=com_contentbuilder&controller=users&form_id='.JRequest::getInt('form_id',0).'&tmpl='.JRequest::getCmd('tmpl','').'&limitstart='.JRequest::getInt('limitstart'), false), JText::_('COM_CONTENTBUILDER_PUBLISHED') );
    }
    
    function unpublish() {
        $cid = JRequest::getVar('cid', array(), '', 'array');

        if(count($cid) == 1)
        {
            $model = $this->getModel( 'users' );
            $model->setUnpublished();
        }
        else if(count($cid) > 1)
        {
            $model = $this->getModel( 'users' );
            $model->setUnpublished();
        }

        $this->setRedirect( JRoute::_('index.php?option=com_contentbuilder&controller=users&form_id='.JRequest::getInt('form_id',0).'&tmpl='.JRequest::getCmd('tmpl','').'&limitstart='.JRequest::getInt('limitstart'), false), JText::_('COM_CONTENTBUILDER_UNPUBLISHED') );
    }
    
    function save($keep_task = false)
    {
        $model = $this->getModel('user');
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
                $additionalParams = '&task=edit&joomla_userid='.$id;
                $limit = JRequest::getInt('limitstart');
            }
        }

        // Check the table in so it can be edited.... we are done with it anyway
        $link = 'index.php?option=com_contentbuilder&controller=users&form_id='.JRequest::getInt('form_id',0).'&tmpl='.JRequest::getCmd('tmpl','').'&limitstart='.$limit.$additionalParams;
        $this->setRedirect(JRoute::_($link, false), $msg);
    }

    function cancel()
    {
        $msg = JText::_( 'COM_CONTENTBUILDER_CANCELLED' );
        $this->setRedirect( JRoute::_('index.php?option=com_contentbuilder&controller=users&form_id='.JRequest::getInt('form_id',0).'&tmpl='.JRequest::getCmd('tmpl','').'&limitstart=0', false), $msg );
    }

    function display()
    {
        JRequest::setVar('tmpl', JRequest::getWord('tmpl',null));
        JRequest::setVar('layout', JRequest::getWord('layout',null));
        JRequest::setVar('view', 'users');

        parent::display();
    }
}
