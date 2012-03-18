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
require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'classes' . DS . 'contentbuilder.php');

class ContentbuilderModelUser extends JModel
{
    private $_form_id = 0;

    function  __construct($config)
    {
        parent::__construct();

        $this->setIds(JRequest::getInt('joomla_userid',  0), JRequest::getInt('form_id',  ''));
        
    }

    /*
     * MAIN DETAILS AREA
     */

    /**
     *
     * @param int $id
     */
    function setIds($id, $form_id) {
        // Set id and wipe data
        $this->_id = $id;
        $this->_form_id = $form_id;
        $this->_data = null;
    }

    private function _buildQuery(){
        return 'Select SQL_CALC_FOUND_ROWS users.*, contentbuilder_users.limit_edit, contentbuilder_users.limit_add, contentbuilder_users.id As cb_id, contentbuilder_users.form_id, contentbuilder_users.verification_date_edit, contentbuilder_users.verification_date_new, contentbuilder_users.verification_date_view, contentbuilder_users.verified_view, contentbuilder_users.verified_new, contentbuilder_users.verified_edit, contentbuilder_users.records, contentbuilder_users.published From #__users As users Left Join #__contentbuilder_users As contentbuilder_users On ( users.id = contentbuilder_users.userid And contentbuilder_users.form_id = '.JRequest::getInt('form_id',0).' ) Where users.id = ' . $this->_id;
                
    }
    
    function setListVerifiedView()
    {
        $items	= JRequest::getVar( 'cid', array(), 'post', 'array' );
        JArrayHelper::toInteger($items);
        if (count($items)) {
            $cids = $items;
            foreach($cids As $cid){
                $this->_db->setQuery("Select id From #__contentbuilder_users Where form_id = ".JRequest::getInt('form_id',0)." And userid = " . $cid);
                if(!$this->_db->loadResult() && JRequest::getInt('form_id',0) && $cid){
                    $this->_db->setQuery("Insert Into #__contentbuilder_users (form_id, userid, published) Values (".JRequest::getInt('form_id',0).", $cid, 1)");
                    $this->_db->query();
                }
            }
            
            $this->_db->setQuery( ' Update #__contentbuilder_users '.
                        '  Set verified_view = 1 Where form_id = '.$this->_form_id.' And userid In ( '.implode(',', $items) . ')' );
            $this->_db->query();
        }
    }
    
    function setListNotVerifiedView()
    {
        $items	= JRequest::getVar( 'cid', array(), 'post', 'array' );
        JArrayHelper::toInteger($items);
        if (count($items)) {
            
            $cids = $items;
            foreach($cids As $cid){
                $this->_db->setQuery("Select id From #__contentbuilder_users Where form_id = ".JRequest::getInt('form_id',0)." And userid = " . $cid);
                if(!$this->_db->loadResult() && JRequest::getInt('form_id',0) && $cid){
                    $this->_db->setQuery("Insert Into #__contentbuilder_users (form_id, userid, published) Values (".JRequest::getInt('form_id',0).", $cid, 1)");
                    $this->_db->query();
                }
            }
            
            $this->_db->setQuery( ' Update #__contentbuilder_users '.
                        '  Set verified_view = 0 Where form_id = '.$this->_form_id.' And userid In ( '.implode(',', $items) . ')' );
            $this->_db->query();
        }
    }

    function setListVerifiedNew()
    {
        $items	= JRequest::getVar( 'cid', array(), 'post', 'array' );
        JArrayHelper::toInteger($items);
        if (count($items)) {
            $cids = $items;
            foreach($cids As $cid){
                $this->_db->setQuery("Select id From #__contentbuilder_users Where form_id = ".JRequest::getInt('form_id',0)." And userid = " . $cid);
                if(!$this->_db->loadResult() && JRequest::getInt('form_id',0) && $cid){
                    $this->_db->setQuery("Insert Into #__contentbuilder_users (form_id, userid, published) Values (".JRequest::getInt('form_id',0).", $cid, 1)");
                    $this->_db->query();
                }
            }
            
            $this->_db->setQuery( ' Update #__contentbuilder_users '.
                        '  Set verified_new = 1 Where form_id = '.$this->_form_id.' And userid In ( '.implode(',', $items) . ')' );
            $this->_db->query();
        }
    }
    
    function setListNotVerifiedNew()
    {
        $items	= JRequest::getVar( 'cid', array(), 'post', 'array' );
        JArrayHelper::toInteger($items);
        if (count($items)) {
            
            $cids = $items;
            foreach($cids As $cid){
                $this->_db->setQuery("Select id From #__contentbuilder_users Where form_id = ".JRequest::getInt('form_id',0)." And userid = " . $cid);
                if(!$this->_db->loadResult() && JRequest::getInt('form_id',0) && $cid){
                    $this->_db->setQuery("Insert Into #__contentbuilder_users (form_id, userid, published) Values (".JRequest::getInt('form_id',0).", $cid, 1)");
                    $this->_db->query();
                }
            }
            
            $this->_db->setQuery( ' Update #__contentbuilder_users '.
                        '  Set verified_new = 0 Where form_id = '.$this->_form_id.' And userid In ( '.implode(',', $items) . ')' );
            $this->_db->query();
        }
    }
    
    function setListVerifiedEdit()
    {
        $items	= JRequest::getVar( 'cid', array(), 'post', 'array' );
        JArrayHelper::toInteger($items);
        if (count($items)) {
            $cids = $items;
            foreach($cids As $cid){
                $this->_db->setQuery("Select id From #__contentbuilder_users Where form_id = ".JRequest::getInt('form_id',0)." And userid = " . $cid);
                if(!$this->_db->loadResult() && JRequest::getInt('form_id',0) && $cid){
                    $this->_db->setQuery("Insert Into #__contentbuilder_users (form_id, userid, published) Values (".JRequest::getInt('form_id',0).", $cid, 1)");
                    $this->_db->query();
                }
            }
            
            $this->_db->setQuery( ' Update #__contentbuilder_users '.
                        '  Set verified_edit = 1 Where form_id = '.$this->_form_id.' And userid In ( '.implode(',', $items) . ')' );
            $this->_db->query();
        }
    }
    
    function setListNotVerifiedEdit()
    {
        $items	= JRequest::getVar( 'cid', array(), 'post', 'array' );
        JArrayHelper::toInteger($items);
        if (count($items)) {
            
            $cids = $items;
            foreach($cids As $cid){
                $this->_db->setQuery("Select id From #__contentbuilder_users Where form_id = ".JRequest::getInt('form_id',0)." And userid = " . $cid);
                if(!$this->_db->loadResult() && JRequest::getInt('form_id',0) && $cid){
                    $this->_db->setQuery("Insert Into #__contentbuilder_users (form_id, userid, published) Values (".JRequest::getInt('form_id',0).", $cid, 1)");
                    $this->_db->query();
                }
            }
            
            $this->_db->setQuery( ' Update #__contentbuilder_users '.
                        '  Set verified_edit = 0 Where form_id = '.$this->_form_id.' And userid In ( '.implode(',', $items) . ')' );
            $this->_db->query();
        }
    }
    
    function getData()
    {
        // Lets load the data if it doesn't already exist
        if (empty( $this->_data ))
        {
            $query = $this->_buildQuery();
            $this->_db->setQuery($query);
            $this->_data = $this->_db->loadObject();
            
            if($this->_data->published === null){
                $this->_data->published = 1;
            }
            
            return $this->_data;
        }
        return null;
    }
    
    function store()
    {
        $insert = 0;
        $this->_db->setQuery("Select id From #__contentbuilder_users Where form_id = ".JRequest::getInt('form_id',0)." And userid = " . JRequest::getInt('joomla_userid',0));
        if(!$this->_db->loadResult() && JRequest::getInt('form_id',0) && JRequest::getInt('joomla_userid',0)){
            $this->_db->setQuery("Insert Into #__contentbuilder_users (form_id, userid, published) Values (".JRequest::getInt('form_id',0).", ".JRequest::getInt('joomla_userid',0).", 1)");
            $this->_db->query();
            $insert = $this->_db->insertid();
        }
        
        $data = JRequest::get( 'post' );
        
        if(!$insert){
            $data['id'] = intval($data['cb_id']);
        }else{
            $data['id'] = $insert;
        }
        
        $data['userid'] = $data['joomla_userid'];
        
        
        $data['verified_view'] = JRequest::getInt('verified_view',0);
        $data['verified_new'] = JRequest::getInt('verified_new',0);
        $data['verified_edit'] = JRequest::getInt('verified_edit',0);
        $data['published'] = JRequest::getInt('published',0);
        
        $row = $this->getTable('cbuser');
        
        if (!$row->bind($data)) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        if (!$row->check()) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }
        
        $storeRes = $row->store();

        if (!$storeRes) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }
        
        return true;
    }
}
