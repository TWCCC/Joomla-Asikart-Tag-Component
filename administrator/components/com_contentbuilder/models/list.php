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
require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'classes' . DS . 'contentbuilder_helpers.php');

class ContentbuilderModelList extends JModel
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

    private $_menu_item = false;
    
    private $frontend = false;
    
    private $_menu_filter = array();
    
    private $_menu_filter_order = array();
    
    private $_show_page_heading = true;
    
    private $_page_class = '';
    
    function  __construct($config) {
        parent::__construct($config);

        $mainframe = JFactory::getApplication();
        $option = 'com_contentbuilder';

        $this->frontend = class_exists('cbFeMarker');

        if($this->frontend){
            JFactory::getDocument()->addStyleSheet(JURI::root(true).'/components/com_contentbuilder/assets/css/system.css');
        }
        
        if(JRequest::getInt('Itemid',0)){
            $this->_menu_item = true;
        }
        
        $this->setId(JRequest::getInt('id',0));

        // Get pagination request variables
        $limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', JRequest::getInt('cb_list_limit', 0) > 0 ? JRequest::getInt('cb_list_limit', 0) : $mainframe->getCfg('list_limit'), 'int');
        $limitstart = JRequest::getVar('limitstart', 0, '', 'int');

        // In case limit has been changed, adjust it
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);

        if(JFactory::getSession()->get($option.'formsd_id', 0) == 0 || JFactory::getSession()->get($option.'formsd_id', 0) == $this->_id ){
            $filter_order     = $mainframe->getUserStateFromRequest(  $option.'formsd_filter_order', 'filter_order', '', 'cmd' );
            $filter_order_Dir = $mainframe->getUserStateFromRequest( $option.'formsd_filter_order_Dir', 'filter_order_Dir', '', 'cmd' );
            $filter           = $mainframe->getUserStateFromRequest(  $option.'formsd_filter', 'filter', '', 'string' );
            $filter_state     = $mainframe->getUserStateFromRequest(  $option.'formsd_filter_state', 'list_state_filter', 0, 'int' );
            $filter_publish   = $mainframe->getUserStateFromRequest(  $option.'formsd_filter_publish', 'list_publish_filter', -1, 'int' );
            $filter_language  = $mainframe->getUserStateFromRequest(  $option.'formsd_filter_language', 'list_language_filter', '', 'cmd' );
        }else{
            $mainframe->setUserState($option.'formsd_filter_order', JRequest::getCmd('filter_order',''));
            $mainframe->setUserState($option.'formsd_filter_order_Dir', JRequest::getCmd('filter_order_Dir',''));
            $mainframe->setUserState($option.'formsd_filter', JRequest::getVar('filter',''));
            $mainframe->setUserState($option.'formsd_filter_state', JRequest::getInt('list_state_filter',0));
            $mainframe->setUserState($option.'formsd_filter_publish', JRequest::getInt('list_publish_filter',-1));
            $mainframe->setUserState($option.'formsd_filter_language', JRequest::getCmd('list_language_filter',''));
            $filter_order     = JRequest::getCmd('filter_order','');
            $filter_order_Dir = JRequest::getCmd('filter_order_Dir','');
            $filter           = JRequest::getVar('filter','');
            $filter_state     = JRequest::getInt('list_state_filter',0);
            $filter_publish   = JRequest::getInt('list_publish_filter',-1);
            $filter_language  = JRequest::getCmd('list_language_filter','');
        }
        
        $this->setState('formsd_filter_state', $filter_state);
        $this->setState('formsd_filter_publish', $filter_publish);
        $this->setState('formsd_filter_language', empty($filter_language) ? null : $filter_language);
        $this->setState('formsd_filter', $filter);
        $this->setState('formsd_filter_order', $filter_order);
        $this->setState('formsd_filter_order_Dir', $filter_order_Dir);

        if($this->frontend && JRequest::getInt('Itemid',0)){
            
            // try menu item
            jimport('joomla.version');
            $version = new JVersion();

            if(version_compare($version->getShortVersion(), '1.6', '>=')){
                $menu = JSite::getMenu();
                $item = $menu->getActive();
                if (is_object($item)) {
                    if($item->params->get('show_page_heading', null) !== null){
                        $this->_show_page_heading = $item->params->get('show_page_heading', null);
                    }
                    if($item->params->get('pageclass_sfx', null) !== null){
                        $this->_page_class = $item->params->get('pageclass_sfx', null);
                    }
                }

            }else{
                $params = JComponentHelper::getParams( $option );
                if($params->get('show_page_heading', null) !== null){
                    $this->_show_page_heading = $params->get('show_page_heading', null);
                }
                if($params->get('pageclass_sfx', null) !== null){
                    $this->_page_class = $params->get('pageclass_sfx', null);
                }
            }
        }
        
        $menu_filter = JRequest::getVar('cb_list_filterhidden', null);
        
        if($menu_filter !== null){
            $lines  = explode("\n", $menu_filter);
            foreach($lines As $line){
                $keyval = explode("\t", $line);
                if(count($keyval) == 2){
                    $keyval[1] = str_replace( array("\n","\r"), "", $keyval[1] );
                    $keyval[1] = contentbuilder::execPhpValue($keyval[1]);
                    if($keyval[1] != ''){
                        $this->_menu_filter[$keyval[0]] = explode('|',$keyval[1]);
                    }
                }
            }
        }
        
        $menu_filter_order = JRequest::getVar('cb_list_orderhidden', null);
        
        if($menu_filter_order !== null){
            $lines  = explode("\n", $menu_filter_order);
            foreach($lines As $line){
                $keyval = explode("\t", $line);
                if(count($keyval) == 2){
                    $keyval[1] = str_replace( array("\n","\r"), "", $keyval[1] );
                    if($keyval[1] != ''){
                        $this->_menu_filter_order[$keyval[0]] = intval($keyval[1]);
                    }
                }
            }
        }
        
        @natsort($this->_menu_filter_order);
        
        JFactory::getSession()->set($option.'formsd_id', $this->_id);
    }

    function setId($id) {
        // Set id and wipe data
        $this->_id      = $id;
        $this->_data    = null;
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
        $filter_order     = $this->getState('formsd_filter_order');
        $filter_order_Dir = $this->getState('formsd_filter_order_Dir') ? $this->getState('formsd_filter_order_Dir') : 'desc';

        /* Error handling is never a bad thing*/
        if(!empty($filter_order) && !empty($filter_order_Dir) ) {
            $orderby = ' ORDER BY '.$filter_order.' '.$filter_order_Dir;
        }

        return $orderby;
    }


    /**
     * @return string The query
     */
    private function _buildQuery(){
        return 'Select SQL_CALC_FOUND_ROWS * From #__contentbuilder_forms Where id = '.intval($this->_id).' And published = 1';
    }

    /**
    * Gets the currencies
    * @return array List of products
    */
    function getData()
    {
        $mainframe = JFactory::getApplication();
        $option = 'com_contentbuilder';
        
        // Lets load the data if it doesn't already exist
        if (empty( $this->_data ))
        {
            $query = $this->_buildQuery();
            $this->_data = $this->_getList($query, 0, 1);

            if(!count($this->_data)){
                JError::raiseError(404, JText::_('COM_CONTENTBUILDER_FORM_NOT_FOUND'));
            }

            foreach($this->_data As $data){
                if(!$this->frontend && $data->display_in == 0){
                    JError::raiseError(404, JText::_('COM_CONTENTBUILDER_FORM_NOT_FOUND'));
                }else if($this->frontend && $data->display_in == 1){
                    JError::raiseError(404, JText::_('COM_CONTENTBUILDER_FORM_NOT_FOUND'));
                }
                
                // filter by category if requested by menu item
                if( JRequest::getVar('cb_category_menu_filter', null) !== null && JRequest::getVar('cb_category_menu_filter', 0) == 1 && JRequest::getVar('cb_category_id', null) !== null ){
                    if(JRequest::getInt('cb_category_id', -1 ) > -2){
                        $this->setState('article_category_filter', JRequest::getInt('cb_category_id', -1 ));
                    } else {
                        $this->setState('article_category_filter', $data->default_category);
                    }
                }
                
                $data->show_page_heading = $this->_show_page_heading;
                $data->page_class = $this->_page_class;
                $data->form_id = $this->_id;
                if($data->type && $data->reference_id){
                    $data->form = contentbuilder::getForm($data->type, $data->reference_id);
                    if(!$data->form->exists){
                        JError::raiseError(404, JText::_('COM_CONTENTBUILDER_FORM_NOT_FOUND'));
                    }
                    $data->page_title = '';
                    if(JRequest::getInt('cb_prefix_in_title', 1)){
                        if(!$this->_menu_item){
                            $data->page_title = $data->use_view_name_as_title ? $data->name : $data->form->getPageTitle();
                        }else{
                            $data->page_title = $data->use_view_name_as_title ? $data->name : JFactory::getDocument()->getTitle();
                        }
                    }
                    
                    // enables the record randomizer
                    $now = JFactory::getDate();
                    $data->rand_update = intval($data->rand_update);
                    if($data->rand_update < 1){
                        $data->rand_update = 86400;
                    }
                    if( $data->initial_sort_order == 'Rand' && ( $data->rand_date_update == '0000-00-00 00:00:00' || $now->toUnix() - strtotime($data->rand_date_update) >= $data->rand_update ) ){
                        $this->_db->setQuery("UPDATE #__contentbuilder_records SET rand_date = '".$now->toMySQL()."' + interval rand()*10000 day Where `type` = ".$this->_db->Quote($data->type)." And reference_id = " . $this->_db->Quote($data->reference_id));
                        $this->_db->query();
                        $this->_db->setQuery("Update #__contentbuilder_forms Set rand_date_update = '" . $now->toMySQL() . "'");
                        $this->_db->query();
                    }
                    
                    $data->labels = $data->form->getElementLabels();
                    
                    if( JRequest::getBool('filter_reset', false)  ){
                        
                        JFactory::getSession()->clear('com_contentbuilder.filter_signal.'.$this->_id);
                        JFactory::getSession()->clear('com_contentbuilder.filter.'.$this->_id);
                        JFactory::getSession()->clear('com_contentbuilder.calendar_filter_from.'.$this->_id);
                        JFactory::getSession()->clear('com_contentbuilder.calendar_filter_to.'.$this->_id);
                        JFactory::getSession()->clear('com_contentbuilder.calendar_formats.'.$this->_id);
                        JFactory::getSession()->clear('com_contentbuilder.filter_keywords.'.$this->_id);
                        JFactory::getSession()->clear('com_contentbuilder.filter_article_categories.'.$this->_id);
                            
                    }
                    else if(
                            ( 
                                JFactory::getSession()->get('com_contentbuilder.filter_signal.'.$this->_id, false) 
                                || 
                                JRequest::getBool('contentbuilder_filter_signal', false) 
                            )
                            && $data->allow_external_filter){
                        
                        $orders = array();
                        $filters = array();
                        $filters_from = array();
                        $filters_to = array();
                        $calendar_formats = array();
                        
                        // renew on request
                        if( JRequest::getBool('contentbuilder_filter_signal', false) ){
                            
                            if( JRequest::getVar('cbListFilterKeywords','') ){
                                $this->setState('formsd_filter', JRequest::getVar('cbListFilterKeywords',''));
                            }
                            
                            if( JRequest::getVar('cbListFilterArticleCategories',-1) > -1 ){
                                $this->setState('article_category_filter', JRequest::getInt('cbListFilterArticleCategories',-1));
                            }
                            
                            $filters = JRequest::getVar( 'cb_filter', array(), 'POST', 'array' );
                            $filters_from = JRequest::getVar( 'cbListFilterCalendarFrom', array(), 'POST', 'array' );
                            $filters_to = JRequest::getVar( 'cbListFilterCalendarTo', array(), 'POST', 'array' );
                            $calendar_formats = JRequest::getVar( 'cb_filter_calendar_format', array(), 'POST', 'array' );
                        
                            JFactory::getSession()->set('com_contentbuilder.filter_signal.'.$this->_id, true);
                            JFactory::getSession()->set('com_contentbuilder.filter.'.$this->_id, $filters);
                            JFactory::getSession()->set('com_contentbuilder.filter_keywords.'.$this->_id, JRequest::getVar('cbListFilterKeywords',''));
                            JFactory::getSession()->set('com_contentbuilder.filter_article_categories.'.$this->_id, JRequest::getInt('cbListFilterArticleCategories',-1));
                            JFactory::getSession()->set('com_contentbuilder.calendar_filter_from.'.$this->_id, $filters_from);
                            JFactory::getSession()->set('com_contentbuilder.calendar_filter_to.'.$this->_id, $filters_to);
                            JFactory::getSession()->set('com_contentbuilder.calendar_formats.'.$this->_id, $calendar_formats);
                        
                        // else pick from session
                        } else if(JFactory::getSession()->get('com_contentbuilder.filter_signal.'.$this->_id, false)){
                            
                            $filters = JFactory::getSession()->get('com_contentbuilder.filter.'.$this->_id, array());
                            $filters_from = JFactory::getSession()->get('com_contentbuilder.calendar_filter_from.'.$this->_id, array());
                            $filters_to = JFactory::getSession()->get('com_contentbuilder.calendar_filter_to.'.$this->_id, array());
                            $calendar_formats = JFactory::getSession()->get('com_contentbuilder.calendar_formats.'.$this->_id, array());
                            $filter_keywords = JFactory::getSession()->get('com_contentbuilder.filter_keywords.'.$this->_id, '');
                            $filter_cats = JFactory::getSession()->get('com_contentbuilder.filter_article_categories.'.$this->_id, -1);
                            
                            if($filter_keywords != ''){
                                $this->setState('formsd_filter', $filter_keywords);
                            }
                            
                            if($filter_cats != -1){
                                $this->setState('article_category_filter', $filter_cats);
                            }
                        }
                        
                        foreach($calendar_formats As $col => $calendar_format){
                            if(isset($filters[$col])){
                                $filter_exploded = explode('/', $filters[$col]);
                                if(isset($filter_exploded[2])){
                                    $to_exploded = explode('to',$filter_exploded[2]);
                                    switch(count($to_exploded)){
                                        case 2:
                                            if($to_exploded[0] != ''){
                                                $filters[$col] = '@range/date/'.  contentbuilder_convert_date(trim($to_exploded[0]), $calendar_format) . ' to ' . contentbuilder_convert_date(trim($to_exploded[1]), $calendar_format);
                                            }
                                            else
                                            {
                                                $filters[$col] = '@range/date/to ' . contentbuilder_convert_date(trim($to_exploded[1]), $calendar_format);
                                            }
                                            break;
                                        case 1:
                                            $filters[$col] = '@range/date/'.  contentbuilder_convert_date(trim($to_exploded[0]), $calendar_format);
                                            break;
                                    }
                                    if(isset($to_exploded[0]) && isset($to_exploded[1]) && trim($to_exploded[0]) == '' && trim($to_exploded[1]) == ''){
                                       $filters[$col] = ''; 
                                    }
                                    if(isset($to_exploded[0]) && !isset($to_exploded[1]) && trim($to_exploded[0]) == ''){
                                       $filters[$col] = ''; 
                                    }
                                }
                            }
                        }
                        
                        $new_filters = array();
                        $i = 1;
                        foreach($filters As $filter_key => $filter){
                            if($filter != ''){
                                $orders[$filter_key] = $i;
                                $new_filters[$filter_key] = explode('|', $filter);
                            }
                            $i++;
                        }
                        
                        $this->_menu_filter = $new_filters;
                        $this->_menu_filter_order = $orders;
                        
                    }
                    
                    $ordered_extra_title = '';
                    foreach($this->_menu_filter_order As $order_key => $order){
                        if(isset($this->_menu_filter[$order_key])){
                            // range test
                            $is_range = strstr(strtolower(implode(',', $this->_menu_filter[$order_key])), '@range') !== false;
                            $is_match = strstr(strtolower(implode(',', $this->_menu_filter[$order_key])), '@match') !== false;
                            if($is_range){
                               $ex = explode('/', implode(', ', $this->_menu_filter[$order_key]));
                               if(count($ex) == 3){
                                  $ex2 = explode('to', trim($ex[2]));
                                  $out = '';
                                  $val = $ex2[0];
                                  $val2 = '';
                                  if(isset($ex2[1])){
                                    $val2 = $ex2[1];
                                  }
                                  if(strtolower(trim($ex[1])) == 'date'){
                                      $val = JHTML::_('date', $ex2[0], JText::_('DATE_FORMAT_LC3'));
                                      if(isset($ex2[1])){
                                        $val2 = JHTML::_('date', $ex2[1], JText::_('DATE_FORMAT_LC3'));
                                      }
                                  }
                                  if(count($ex2) == 2){
                                      $out = (trim($ex2[0]) ? JText::_('COM_CONTENTBUILDER_FROM') . ' ' . trim($val) : '') . ' '.JText::_('COM_CONTENTBUILDER_TO').' ' . trim($val2);
                                  }else if(count($ex2) > 0){
                                      $out = JText::_('COM_CONTENTBUILDER_FROM2') . ' ' . trim($val);
                                  }
                                  if($out){
                                    $this->_menu_filter[$order_key] = $ex;
                                    $ordered_extra_title .= ' &raquo; ' . htmlentities($data->labels[$order_key], ENT_QUOTES, 'UTF-8'). ': ' . htmlentities($out, ENT_QUOTES, 'UTF-8');
                                  }
                               }
                            }
                            else if($is_match){
                                $ex = explode('/', implode(', ', $this->_menu_filter[$order_key]));
                                if(count($ex) == 2){
                                    $ex2 = explode(';', trim($ex[1]));
                                    $out = '';
                                    $size = count($ex2);
                                    $i = 0;
                                    foreach($ex2 As $val){
                                       if($i + 1 < $size){
                                           $out .= trim($val) . ' ' . JText::_('COM_CONTENTBUILDER_AND') . ' ';
                                       }else{
                                           $out .= trim($val);
                                       }
                                       $i++;
                                    }
                                    if($out){
                                        $this->_menu_filter[$order_key] = $ex;
                                        $ordered_extra_title .= ' &raquo; ' . htmlentities($data->labels[$order_key], ENT_QUOTES, 'UTF-8'). ': ' . htmlentities($out, ENT_QUOTES, 'UTF-8');
                                    }
                                }
                            }
                            else{
                                $ordered_extra_title .= ' &raquo; ' . htmlentities($data->labels[$order_key], ENT_QUOTES, 'UTF-8'). ': ' . htmlentities(implode(', ', $this->_menu_filter[$order_key]), ENT_QUOTES, 'UTF-8');
                            }
                        }
                    }
                    
                    $data->slug = $data->page_title;
                    $data->slug2 = '';
                    
                    if(JRequest::getInt('cb_filter_in_title',1)){
                        $data->slug2 = str_replace(' &raquo; ','',$ordered_extra_title);
                        $data->page_title .= $ordered_extra_title;
                    }
                    
                    if($this->frontend){
                        $document = JFactory::getDocument();
                        $document->setTitle(html_entity_decode ($data->page_title, ENT_QUOTES, 'UTF-8'));
                    }
                    
                    $ids = array();
                    foreach($data->labels As $reference_id => $label){
                        $ids[] = $this->_db->Quote($reference_id);
                    }
                    $searchable_elements = contentbuilder::getListSearchableElements($this->_id);
                    $data->display_filter = count($searchable_elements) && $data->show_filter;
                    $data->linkable_elements = contentbuilder::getListLinkableElements($this->_id);
                    $data->labels = array();
                    $order_types = array();
                    if(count($ids)){
                        $this->_db->setQuery("Select Distinct `id`,`label`, reference_id, `order_type` From #__contentbuilder_elements Where form_id = " . intval($this->_id) . " And reference_id In (" . implode(',', $ids) . ") And published = 1 And list_include = 1 Order By ordering");
                        $rows = $this->_db->loadAssocList();
                        $ids = array();
                        foreach($rows As $row){
                           // cleaned up, in desired order
                           $data->labels[$row['reference_id']] = $row['label'];
                           $ids[] = $row['reference_id'];
                           $order_types['col'.$row['reference_id']] = $row['order_type'];
                        }
                    }
                    
                    $act_as_registration = array();
                    
                    if( $data->act_as_registration &&
                            $data->registration_username_field &&
                            $data->registration_name_field &&
                            $data->registration_email_field &&
                            $data->registration_email_repeat_field &&
                            $data->registration_password_field &&
                            $data->registration_password_repeat_field
                    ){
                        $act_as_registration[$data->registration_username_field] = 'registration_username_field';
                        $act_as_registration[$data->registration_name_field] = 'registration_name_field';
                        $act_as_registration[$data->registration_email_field] = 'registration_email_field';
                    }
                    
                    $data->items = $data->form->getListRecords($ids, $this->getState('formsd_filter'), $searchable_elements, $this->getState('limitstart'), $this->getState('limit'), $this->getState('formsd_filter_order'), $order_types, $this->getState('formsd_filter_order_Dir') ? $this->getState('formsd_filter_order_Dir') : $data->initial_order_dir, 0, $data->published_only, $this->frontend ? ( $data->own_only_fe ? JFactory::getUser()->get('id', 0) : -1 ) : ( $data->own_only ? JFactory::getUser()->get('id', 0) : -1 ), $this->getState('formsd_filter_state'), $this->getState('formsd_filter_publish'), $data->initial_sort_order == -1 ? -1 : 'col'. $data->initial_sort_order, $data->initial_sort_order2 == -1 ? -1 : 'col'. $data->initial_sort_order2, $data->initial_sort_order3 == -1 ? -1 : 'col'. $data->initial_sort_order3, $this->_menu_filter, $this->frontend ? $data->show_all_languages_fe : true, $this->getState('formsd_filter_language'), $act_as_registration, $data, $this->getState('article_category_filter'));
                    
                    if($data->items === null){
                        $mainframe->setUserState($option.'formsd_filter_order', '');
                        JError::raiseError(500, JText::_('Stale list setup detected. Please reload view.'));
                    }
                    $data->items = contentbuilder::applyItemWrappers($this->_id, $data->items, $data);
                    $this->_total = $data->form->getListRecordsTotal($ids, $this->getState('formsd_filter'), $searchable_elements);
                    $data->visible_cols = $ids;
                    
                    $data->states = array();
                    $data->state_colors = array();
                    $data->state_titles = array();
                    $data->published_items = array();
                    $data->states = contentbuilder::getListStates($this->_id);
                    if($data->list_state){
                        $data->state_colors = contentbuilder::getStateColors($data->items, $this->_id);
                        $data->state_titles = contentbuilder::getStateTitles($data->items, $this->_id);
                    }
                    if($data->list_publish){
                        $data->published_items = contentbuilder::getRecordsPublishInfo($data->items, $data->type, $data->reference_id);
                    }
                    $data->lang_codes = array();
                    if($data->list_language){
                        $data->lang_codes = contentbuilder::getRecordsLanguage($data->items, $data->type, $data->reference_id);
                    }
                    $data->languages = contentbuilder::getLanguageCodes();
                    
                    // Search for the {readmore} tag and split the text up accordingly.
                    $pattern = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
                    $tagPos = preg_match($pattern, $data->intro_text);

                    $fulltext = '';
                    
                    if ($tagPos == 0) {
                        $introtext = $data->intro_text;
                    } else {
                        list($introtext, $fulltext) = preg_split($pattern, $data->intro_text, 2);
                    }
                    
                    $data->intro_text = $introtext . ( $fulltext ? '<br/><br/>' . $fulltext : '' );
                }
                
                return $data;
            }
        }
        return null;
    }

    function getTotal() {
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
