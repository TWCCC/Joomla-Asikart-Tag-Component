<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        http://www.crosstec.de
 * @license     GNU/GPL
*/

// No direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );

class ContentbuilderModelUsers extends JModel
{
    /**
     * Items total
     * @var integer
     */
    private $_total = null;

    /**
     * Pagination object
     * @var object
     */
    private $_pagination = null;

    function  __construct($config) {
        parent::__construct($config);

        $mainframe = JFactory::getApplication();
        $option = 'com_contentbuilder';

        // Get pagination request variables
        $limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
        $limitstart = JRequest::getVar('limitstart', 0, '', 'int');

        // In case limit has been changed, adjust it
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);

        $filter_order     = $mainframe->getUserStateFromRequest(  $option.'users_filter_order', 'filter_order', '`users`.`id`', 'cmd' );
        $filter_order_Dir = $mainframe->getUserStateFromRequest( $option.'users_filter_order_Dir', 'filter_order_Dir', 'desc', 'word' );

        $this->setState('users_filter_order', $filter_order);
        $this->setState('users_filter_order_Dir', $filter_order_Dir);

        $filter_state = $mainframe->getUserStateFromRequest( $option.'users_filter_state', 'filter_state', '', 'word' );
        $this->setState('users_filter_state', $filter_state);
        
        $search = $mainframe->getUserStateFromRequest("$option.users_search", 'users_search', '', 'string');
        $this->setState('users_search', $search);
    }

    /*
     *
     * MAIN LIST AREA
     * 
     */

    private function buildOrderBy() {
        $mainframe = JFactory::getApplication();
        $option = 'com_contentbuilder';

        $orderby = '';
        $filter_order     = $this->getState('users_filter_order');
        $filter_order_Dir = $this->getState('users_filter_order_Dir');

        /* Error handling is never a bad thing*/
        if(!empty($filter_order) && !empty($filter_order_Dir) && $filter_order != 'ordering') {
            $orderby = ' ORDER BY '.$filter_order.' '.$filter_order_Dir;
        }

        return $orderby;
    }


    function setPublished()
    {
        $cids = JRequest::getVar('cid', array(), '', 'array');
        JArrayHelper::toInteger($cids);
        foreach($cids As $cid){
            $this->_db->setQuery("Select id From #__contentbuilder_users Where form_id = ".JRequest::getInt('form_id',0)." And userid = " . $cid);
            if(!$this->_db->loadResult() && JRequest::getInt('form_id',0) && $cid){
                $this->_db->setQuery("Insert Into #__contentbuilder_users (form_id, userid, published) Values (".JRequest::getInt('form_id',0).", $cid, 1)");
                $this->_db->query();
            }
        }
        $this->_db->setQuery( ' Update #__contentbuilder_users '.
                '  Set published = 1 Where form_id = '.JRequest::getInt('form_id',0).' And userid In ( ' . implode(',',$cids)  . ')');
        $this->_db->query();

    }

    function setUnpublished()
    {
        $cids = JRequest::getVar('cid', array(), '', 'array');
        JArrayHelper::toInteger($cids);
        foreach($cids As $cid){
            $this->_db->setQuery("Select id From #__contentbuilder_users Where form_id = ".JRequest::getInt('form_id',0)." And userid = " . $cid);
            if(!$this->_db->loadResult() && JRequest::getInt('form_id',0) && $cid){
                $this->_db->setQuery("Insert Into #__contentbuilder_users (form_id, userid, published) Values (".JRequest::getInt('form_id',0).", $cid, 1)");
                $this->_db->query();
            }
        }
        $this->_db->setQuery( ' Update #__contentbuilder_users '.
                '  Set published = 0 Where form_id = '.JRequest::getInt('form_id',0).' And userid In ( ' . implode(',',$cids)  . ')');
        $this->_db->query();
    }
    
    /**
     * @return string The query
     */
    private function _buildQuery(){
        
        $where = '';
        
        if(trim($this->getState('users_search')) != '' ){
            $where = ' Where users.id = '.$this->_db->Quote(intval($this->getState('users_search'))).' Or users.username Like '.$this->_db->Quote('%'.$this->getState('users_search').'%').' Or users.`name` Like '.$this->_db->Quote('%'.$this->getState('users_search').'%').' ';
        }
        
        return 'Select SQL_CALC_FOUND_ROWS users.*, contentbuilder_users.verified_view, contentbuilder_users.verified_new, contentbuilder_users.verified_edit, contentbuilder_users.records, contentbuilder_users.published From #__users As users Left Join #__contentbuilder_users As contentbuilder_users On ( users.id = contentbuilder_users.userid And contentbuilder_users.form_id = '.JRequest::getInt('form_id',0).' ) ' . $where . $this->buildOrderBy();
    }

    /**
    * Gets the currencies
    * @return array List of products
    */
    function getData()
    {
        // Lets load the data if it doesn't already exist
        if (empty( $this->_data ))
        {
            $query = $this->_buildQuery();
            $this->_data = $this->_getList( $query, $this->getState('limitstart'), $this->getState('limit') );
        echo $this->_db->getErrorMsg();
        }

        return $this->_data;
    }

    function getTotal() {
        // Load the content if it doesn't already exist
        if (empty($this->_total)) {
            $query = $this->_buildQuery();
            $this->_total = $this->_getListCount($query);
        }
        return $this->_total;
    }

    function getPagination() {
        // Load the content if it doesn't already exist
        if (empty($this->_pagination)) {
            jimport('joomla.html.pagination');
            $this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
        }
        return $this->_pagination;
    }

}
