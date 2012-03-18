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

jimport('joomla.version');
$version = new JVersion();

if(version_compare($version->getShortVersion(), '1.7', '>=')){
    require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'classes' . DS . 'plugin_helper.php');
} else {
    require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'classes' . DS . 'plugin_helper15.php');
}

class ContentbuilderModelEdit extends JModel
{
    private $_record_id = 0;

    private $frontend = false;
    
    private $is15 = true;
    
    private $_menu_item = false;
    
    private $_show_back_button = true;
    
    private $_show_page_heading = true;
    
    private $_menu_filter = array();
    
    private $_menu_filter_order = array();
    
    private $_latest = false;
    
    function createPathByTokens($path, array $names){
        
        if( strpos( $path, '|' ) === false ){
            return $path;
        }
        
        $path = str_replace('|', DS, $path);
        
        foreach($names As $id => $name){
            $is_array = 'STRING';
            if(is_array(JRequest::getVar( 'cb_' . $id, ''))){
                $is_array = 'ARRAY';
            }
            $value = JRequest::getVar( 'cb_' . $id, '', 'POST', $is_array, JREQUEST_ALLOWRAW );
            if($is_array == 'ARRAY' && count($value)){
                $arrvals = array();
                foreach($value As $val){
                    if($val != 'cbGroupMark'){
                       $arrvals[] = $val;
                    }
                }
                $value = implode(DS, $arrvals);
            }
            if(trim($value) == ''){
                $value = '_empty_';
            }
            $path = str_replace('{'.strtolower($name).':value}', $value, $path);
        }
        
        $path = str_replace('{userid}', JFactory::getUser()->get('id', 0), $path);
        $path = str_replace('{username}', JFactory::getUser()->get('username', 'anonymous') . '_' . JFactory::getUser()->get('id', 0), $path);
        $path = str_replace('{name}', JFactory::getUser()->get('name', 'Anonymous') . '_' . JFactory::getUser()->get('id', 0), $path);
        
        $_now = JFactory::getDate();
        $path = str_replace('{date}', $_now->toMySQL(), $path);
        $path = str_replace('{time}', $_now->toFormat('%H:%M:%S'), $path);
        $path = str_replace('{date}', $_now->toMySQL(), $path);
        $path = str_replace('{datetime}', $_now->toFormat(), $path);
        
        $endpath = contentbuilder::makeSafeFolder($path);
        $parts = explode(DS, $endpath);
        $inner_path = '';
        foreach( $parts As $part ){
            if( !JFolder::exists( $inner_path.$part ) ) {
                $inner_path .= DS;
            }
            JFolder::create($inner_path.$part);
            $inner_path .= $part;    
        }
        return $endpath;
    }
    
    function  __construct($config)
    {
        parent::__construct($config);

        $version = new JVersion();
        if (version_compare($version->getShortVersion(), '1.6', '>=')) {
           $this->is15 = false; 
        }
        
        JRequest::setVar('cb_category_id',null);
        
        $this->frontend = JFactory::getApplication()->isSite();
        
        if($this->frontend && JRequest::getInt('Itemid',0)){
            $this->_menu_item = true;
            
            // try menu item
            jimport('joomla.version');
            $version = new JVersion();

            if(version_compare($version->getShortVersion(), '1.6', '>=')){
                $menu = JSite::getMenu();
                $item = $menu->getActive();
                if (is_object($item)) {
                    JRequest::setVar('cb_category_id', $item->params->get('cb_category_id', null));
                    if(JRequest::getVar('cb_controller') == 'edit'){
                        $this->_show_back_button = $item->params->get('show_back_button', null);
                    }
                    
                    if($item->params->get('cb_latest', null) !== null){
                        $this->_latest = $item->params->get('cb_latest', null);
                    }
                    
                    if($item->params->get('show_page_heading', null) !== null){
                        $this->_show_page_heading = $item->params->get('show_page_heading', null);
                    }
                }

            }else{
                $params = JComponentHelper::getParams( 'com_contentbuilder' );
                JRequest::setVar('cb_category_id', $params->get('cb_category_id', null));
                if(JRequest::getVar('cb_controller') == 'edit'){
                    $this->_show_back_button = $params->get('show_back_button', null);
                }
                
                if($params->get('cb_latest', null)){
                    $this->_latest = $params->get('cb_latest', null);
                }
                
                if($params->get('show_page_heading', null) !== null){
                    $this->_show_page_heading = $params->get('show_page_heading', null);
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
        
        $this->setIds(JRequest::getInt('id',  0), JRequest::getCmd('record_id',  ''));
        
        if(!$this->frontend){
            JFactory::getLanguage()->load('com_content');
        }else{
            JFactory::getLanguage()->load('com_content', JPATH_SITE . DS . 'administrator');
            JFactory::getLanguage()->load('joomla', JPATH_SITE . DS . 'administrator');
        }
    }

    /*
     * MAIN DETAILS AREA
     */

    /**
     *
     * @param int $id
     */
    function setIds($id, $record_id) {
        // Set id and wipe data
        $this->_id = $id;
        $this->_record_id = $record_id;
        $this->_data = null;
    }

    private function _buildQuery(){
        return 'Select SQL_CALC_FOUND_ROWS * From #__contentbuilder_forms Where id = '.intval($this->_id).' And published = 1';
    }

    /**
    * Gets the currencies
    * @return array List of currencies
    */
    function getData()
    {
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
                    JError::raiseError(404, JText::_('COM_CONTENTBUILDER_RECORD_NOT_FOUND'));
                }else if($this->frontend && $data->display_in == 1){
                    JError::raiseError(404, JText::_('COM_CONTENTBUILDER_RECORD_NOT_FOUND'));
                }
                
                $data->show_page_heading = $this->_show_page_heading;
                $data->limited_options = $this->frontend ? $data->limited_article_options_fe : $data->limited_article_options;
                $data->form_id = $this->_id;
                $data->record_id = $this->_record_id;
                if($data->type && $data->reference_id){
                    
                    // article options
                    $this->_db->setQuery("Select content.id, content.modified_by, content.version, content.hits, content.catid From #__contentbuilder_articles As articles, #__content As content Where (content.state = 1 Or content.state = 0) And content.id = articles.article_id And articles.form_id = " . $this->_id . " And articles.record_id = " . $this->_db->Quote($this->_record_id));
                    $article = $this->_db->loadAssoc();

                    if($data->create_articles){
                        if(!$this->is15){

                            // JOOMLA 1.6 params retrieval
                            jimport('joomla.form.form');
                            JForm::addFormPath(JPATH_SITE . '/administrator/components/com_contentbuilder/models/forms');
                            JForm::addFieldPath(JPATH_SITE . '/administrator/components/com_content/models/fields');
                            $form = JForm::getInstance('com_content.article', 'article', array('control' => 'jform', 'load_data' => true));

                            if(is_array($article)){

                                $table = JTable::getInstance('content');
                                $loaded = $table->load($article['id']);
                                if($loaded){
                                    // Convert to the JObject before adding other data.
                                    $properties = $table->getProperties(1);
                                    $item = JArrayHelper::toObject($properties, 'JObject');

                                    if (property_exists($item, 'params')) {
                                        $registry = new JRegistry;
                                        $registry->loadJSON($item->params);
                                        $item->params = $registry->toArray();
                                    }

                                    // Convert the params field to an array.
                                    $registry = new JRegistry;
                                    $registry->loadJSON($item->attribs);
                                    $item->attribs = $registry->toArray();

                                    // Convert the params field to an array.
                                    $registry = new JRegistry;
                                    $registry->loadJSON($item->metadata);
                                    $item->metadata = $registry->toArray();
                                    $item->articletext = trim($item->fulltext) != '' ? $item->introtext . "<hr id=\"system-readmore\" />" . $item->fulltext : $item->introtext;

                                    // Import the approriate plugin group.
                                    JPluginHelper::importPlugin('content');

                                    // Get the dispatcher.
                                    $dispatcher	= JDispatcher::getInstance();

                                    // Trigger the form preparation event.
                                    $results = $dispatcher->trigger('onContentPrepareForm', array($form, $item));

                                    // Check for errors encountered while preparing the form.
                                    if (count($results) && in_array(false, $results, true)) {
                                            // Get the last error.
                                            $error = $dispatcher->getError();

                                            // Convert to a JException if necessary.
                                            if (!JError::isError($error)) {
                                                    throw new Exception($error);
                                            }
                                    }

                                    $form->bind($item);
                                    
                                    $data->sectioncategories = array();
                                    $data->row = $item;
                                    $data->lists = array();
                                } else {
                                    $data->sectioncategories = array();
                                    $data->row = new stdClass(); $data->row->title = ''; $data->row->alias = ''; // special for 1.5
                                    $data->lists = array('state' => '', 'frontpage' => '', 'sectionid' => '', 'catid' => ''); // special for 1.5
                                }

                                $data->article_settings = new stdClass();
                                $data->article_settings->modified_by = $article['modified_by'];
                                $data->article_settings->version = $article['version'];
                                $data->article_settings->hits = $article['hits'];
                                $data->article_settings->catid = $article['catid'];

                            }else{
                                $data->article_settings = new stdClass();
                                $data->article_settings->modified_by = 0;
                                $data->article_settings->version = 0;
                                $data->article_settings->hits = 0; 
                                $data->article_settings->catid = 0;
                            }

                            $data->article_options = $form;
                           
                            // article options end
                            // Joomla 1.6 params retrieval end
                        } else {
                            
                            // Joomla 1.5 params retrieval
                            if(!$data->limited_options){
                                $form = new JParameter('', JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_content' .DS.'models'.DS.'article.xml');
                            }else{
                                $form = new JParameter('', JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' .DS.'assets'.DS.'article.xml');
                            }
                            
                            $table = JTable::getInstance('content');
                            
                            // sections
                            $javascript = "onchange=\"changeDynaList( 'catid', sectioncategories, document.adminForm.sectionid.options[document.adminForm.sectionid.selectedIndex].value, 0, 0);\"";

                            $query = 'SELECT s.id, s.title' .
                                            ' FROM #__sections AS s' .
                                            ' ORDER BY s.ordering';
                            $this->_db->setQuery($query);

                            $seections = array();
                            $sections[] = JHTML::_('select.option', '-1', '- '.JText::_('Select Section').' -', 'id', 'title');
                            $sections[] = JHTML::_('select.option', '0', JText::_('Uncategorized'), 'id', 'title');
                            $sections = array_merge($sections, $this->_db->loadObjectList());

                            // categories
                            foreach ($sections as $section)
                            {
                                $section_list[] = (int) $section->id;
                            }

                            $sectioncategories = array ();
                            $sectioncategories[-1] = array ();
                            $sectioncategories[-1][] = JHTML::_('select.option', '-1', JText::_( 'Select Category' ), 'id', 'title');
                            $section_list = implode('\', \'', $section_list);

                            $query = 'SELECT id, title, section' .
                                            ' FROM #__categories' .
                                            ' WHERE section IN ( \''.$section_list.'\' )' .
                                            ' ORDER BY ordering';
                            $this->_db->setQuery($query);
                            $cat_list = $this->_db->loadObjectList();

                            // Uncategorized category mapped to uncategorized section
                            $uncat = new stdClass();
                            $uncat->id = 0;
                            $uncat->title = JText::_('Uncategorized');
                            $uncat->section = 0;
                            $cat_list[] = $uncat;
                            foreach ($sections as $section)
                            {
                                    $sectioncategories[$section->id] = array ();
                                    $rows2 = array ();
                                    foreach ($cat_list as $cat)
                                    {
                                            if ($cat->section == $section->id) {
                                                    $rows2[] = $cat;
                                            }
                                    }
                                    foreach ($rows2 as $row2) {
                                            $sectioncategories[$section->id][] = JHTML::_('select.option', $row2->id, $row2->title, 'id', 'title');
                                    }
                            }
                            $sectioncategories['-1'][] = JHTML::_('select.option', '-1', JText::_( 'Select Category' ), 'id', 'title');
                            $categories = array();
                            foreach ($cat_list as $cat) {
                                    $categories[] = $cat;
                            }

                            $categories[] = JHTML::_('select.option', '-1', JText::_( 'Select Category' ), 'id', 'title');

                            if(is_array($article)){
                                
                                $loaded = $table->load($article['id']);
                            
                                if($loaded){
                                    
                                    // Convert to the JObject before adding other data.
                                    $properties = $table->getProperties(1);
                                    $item = JArrayHelper::toObject($properties, 'JObject');
                                    
                                    $form->set('created_by', $item->created_by);
                                    $form->set('access', $item->access);
                                    $form->set('created_by_alias', $item->created_by_alias);

                                    $form->set('created', JHTML::_('date', $item->created, '%Y-%m-%d %H:%M:%S'));
                                    $form->set('publish_up', JHTML::_('date', $item->publish_up, '%Y-%m-%d %H:%M:%S'));
                                    if (JHTML::_('date', $item->publish_down, '%Y') <= 1969 || $item->publish_down == $this->_db->getNullDate()) {
                                            $form->set('publish_down', JText::_('Never'));
                                    } else {
                                            $form->set('publish_down', JHTML::_('date', $item->publish_down, '%Y-%m-%d %H:%M:%S'));
                                    }

                                    // Advanced Group
                                    $form->loadINI($item->attribs);

                                    // Metadata Group
                                    $form->set('description', $item->metadesc);
                                    $form->set('keywords', $item->metakey);
                                    $form->loadINI($item->metadata);
                                    
                                    $lists['state'] = JHTML::_('select.booleanlist', 'state', '', $item->state);
                                    $query = 'SELECT COUNT(content_id)' .
					' FROM #__content_frontpage' .
					' WHERE content_id = '. (int) $item->id;
                                    $this->_db->setQuery($query);
                                    $item->frontpage = $this->_db->loadResult();
                                    if (!$item->frontpage) {
                                            $item->frontpage = 0;
                                    }
                                    $lists['frontpage'] = JHTML::_('select.booleanlist', 'frontpage', '', $item->frontpage);
                                    $lists['sectionid'] = JHTML::_('select.genericlist',  $sections, 'sectionid', 'class="inputbox" size="1" '.$javascript, 'id', 'title', intval($item->sectionid));
                                    $lists['catid'] = JHTML::_('select.genericlist',  $categories, 'catid', 'class="inputbox" size="1"', 'id', 'title', intval($item->catid));

                                    $query = 'SELECT ordering AS value, title AS text FROM #__content WHERE catid = '.(int) $data->default_category.' AND state > ' .(int) "-1" . ' ORDER BY ordering';
                                    $lists['ordering'] = JHTML::_('list.specificordering', $table, $article['id'], $query, 1);
                                    
                                    
                                    $data->sectioncategories = $sectioncategories;
                                    $data->row = $item; // special for 1.5
                                    $data->lists = $lists;
                                } else {
                                    $data->sectioncategories = $sectioncategories;
                                    $data->row = $table; // special for 1.5
                                    $fplist = JHTML::_('select.booleanlist', 'frontpage', '', 0);
                                    $sectionid = JHTML::_('select.genericlist',  $sections, 'sectionid', 'class="inputbox" size="1" '.$javascript, 'id', 'title', intval($item->sectionid));
                                    $catid = JHTML::_('select.genericlist',  $categories, 'catid', 'class="inputbox" size="1"', 'id', 'title', 0);
                                    
                                    // Select List: Category Ordering
                                    $query = 'SELECT ordering AS value, title AS text FROM #__content WHERE catid = '.(int) $data->default_category.' AND state > ' .(int) "-1" . ' ORDER BY ordering';
                                    $olist = JHTML::_('list.specificordering', $table, 0, $query, 1);
                                    
                                    $data->lists = array('ordering' => $olist, 'state' => '', 'frontpage' => $fplist, 'sectionid' => $sectionid, 'catid' => $catid); // special for 1.5
                                }
                                
                                $data->article_settings = new stdClass();
                                $data->article_settings->modified_by = $article['modified_by'];
                                $data->article_settings->version = $article['version'];
                                $data->article_settings->hits = $article['hits'];
                                $data->article_settings->catid = $article['catid'];
                                
                            } else {
                                
                                $data->sectioncategories = $sectioncategories;
                                $data->row = $table; // special for 1.5
                                $fplist = JHTML::_('select.booleanlist', 'frontpage', '', 0);
                                $sectionid = JHTML::_('select.genericlist',  $sections, 'sectionid', 'class="inputbox" size="1" '.$javascript, 'id', 'title', 0);
                                $catid = JHTML::_('select.genericlist',  $categories, 'catid', 'class="inputbox" size="1"', 'id', 'title', 0);
                                
                                // Select List: Category Ordering
                                $query = 'SELECT ordering AS value, title AS text FROM #__content WHERE catid = '.(int) $data->default_category.' AND state > ' .(int) "-1" . ' ORDER BY ordering';
                                $olist = JHTML::_('list.specificordering', $table, 0, $query, 1);

                                $data->lists = array('ordering' => $olist, 'state' => '', 'frontpage' => $fplist, 'sectionid' => $sectionid, 'catid' => $catid); // special for 1.5
                                $data->article_settings = new stdClass();
                                $data->article_settings->modified_by = 0;
                                $data->article_settings->version = 0;
                                $data->article_settings->hits = 0; 
                                $data->article_settings->catid = 0; 
                            }
                            
                            $data->article_options = $form;
                            
                            // Joomla 1.5 params retrieval end
                        }
                    }
                    
                    $data->back_button = JRequest::getBool('latest', 0) && !JRequest::getCmd('record_id','') ? false : $this->_show_back_button;
                    $data->latest = $this->_latest;
                    $data->is15 = $this->is15;
                    $data->frontend = $this->frontend;
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
                    
                    $data->labels = $data->form->getElementLabels();
                    $ids = array();
                    foreach($data->labels As $reference_id => $label){
                        $ids[] = $this->_db->Quote($reference_id);
                    }
                    
                    if(count($ids)){
                        $this->_db->setQuery("Select Distinct `label`, reference_id From #__contentbuilder_elements Where form_id = " . intval($this->_id) . " And reference_id In (" . implode(',', $ids) . ") And published = 1 Order By ordering");
                        $rows = $this->_db->loadAssocList();
                        $ids = array();
                        foreach($rows As $row){
                           $ids[] = $row['reference_id'];
                        }
                    }
                    
                    $data->items = $data->form->getRecord($this->_record_id, $data->published_only, $this->frontend ? ( $data->own_only_fe ? JFactory::getUser()->get('id', 0) : -1 ) : ( $data->own_only ? JFactory::getUser()->get('id', 0) : -1 ), $this->frontend ? $data->show_all_languages_fe : true );
                    
                    if(count($data->items)){
                        
                        $user = null;
                        
                        if($data->act_as_registration){
                            $meta = $data->form->getRecordMetadata($this->_record_id);
                            $this->_db->setQuery("Select * From #__users Where id = " . $meta->created_id);
                            $user = $this->_db->loadObject();
                        }
                        
                        $label = '';
                        foreach($data->items As $rec){
                            
                            if($rec->recElementId == $data->title_field){
                                
                                if($data->act_as_registration && $user !== null){
                                    
                                    if($data->registration_name_field == $rec->recElementId){
                                         $rec->recValue = $user->name;
                                    }
                                    else 
                                    if($data->registration_username_field == $rec->recElementId){
                                        $item->recValue = $user->username;
                                    }
                                    else 
                                    if($data->registration_email_field == $item->recElementId){
                                        $rec->recValue = $user->email;
                                    }
                                    else 
                                    if($data->registration_email_repeat_field == $rec->recElementId){
                                        $rec->recValue = $user->email;
                                    }
                                }
                                $label = cbinternal($rec->recValue);
                                break;
                            }
                        }
                        
                        // trying first element if no title field given
                        if(!$label){
                            $label = cbinternal($data->items[0]->recValue);
                        }
                        
                        $data->page_title .= $label ? (!$data->page_title ? '' : ': ') . $label : '';
                        
                        if($this->frontend){
                            $document = JFactory::getDocument();
                            $document->setTitle(html_entity_decode ($data->page_title, ENT_QUOTES, 'UTF-8'));
                        }

                    }
                    
                    //if(!$data->edit_by_type){
            
                        $i = 0;
                        $api_items = '';
                        $api_names = $data->form->getElementNames();
                        $cntItems = count($api_names);
                        foreach($api_names As $reference_id => $api_name){
                            $api_items .= '"'.addslashes($api_name).'": "'.addslashes($reference_id).'"'.($i + 1 < $cntItems ? ',' : '');
                            $i++;
                        }
                        $items = $api_items;

                        JFactory::getDocument()->addScriptDeclaration(
'
<!--
var contentbuilder = new function(){

   this.items = {'.$items.'};
   var items = this.items;

   this._ = function(name){
     var els = document.getElementsByName("cb_"+items[name]);
     if(els.length == 0){
        els = document.getElementsByName("cb_"+items[name]+"[]");
     }
     return els.length == 1 ? els[0] : els;
   };
   
   var _ = this._;

   this.urldecode = function (str) {
       return decodeURIComponent((str+\'\').replace(/\+/g, \'%20\'));
   };

   this.getQuery = function ( name ){
       name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");  
       var regexS = "[\\?&]"+name+"=([^&#]*)";  
       var regex = new RegExp( regexS );
       var results = regex.exec( window.location.href ); 
       if( results == null ){
           return null;
       } else {
           return this.urldecode(results[1]);
       }
   };

   this.onClick = function(name, func){
        if(typeof func != "function") return;
        var els = document.getElementsByName("cb_"+items[name]);
        if(els.length == 0){
            els = document.getElementsByName("cb_"+items[name]+"[]");
        }
        for(var i = 0; i < els.length; i++){
            els[i].onclick = func;
        }
   };
   this.onFocus = function(name, func){
        if(typeof func != "function") return;
        var els = document.getElementsByName("cb_"+items[name]);
        if(els.length == 0){
            els = document.getElementsByName("cb_"+items[name]+"[]");
        }
        for(var i = 0; i < els.length; i++){
            els[i].onfocus = func;
        }
   };
   this.onBlur = function(name, func){
        if(typeof func != "function") return;
        var els = document.getElementsByName("cb_"+items[name]);
        if(els.length == 0){
            els = document.getElementsByName("cb_"+items[name]+"[]");
        }
        for(var i = 0; i < els.length; i++){
            els[i].onblur = func;
        }
   };
   this.onChange = function(name, func){
        if(typeof func != "function") return;
        var els = document.getElementsByName("cb_"+items[name]);
        if(els.length == 0){
            els = document.getElementsByName("cb_"+items[name]+"[]");
        }
        for(var i = 0; i < els.length; i++){
            els[i].onchange = func;
        }
   };
   this.onSelect = function(name, func){
        if(typeof func != "function") return;
        var els = document.getElementsByName("cb_"+items[name]);
        if(els.length == 0){
            els = document.getElementsByName("cb_"+items[name]+"[]");
        }
        for(var i = 0; i < els.length; i++){
            els[i].onselect = func;
        }
   };
   
   this.submitReady = function(){ return true; };
   var _submitReady = this.submitReady;
   this.onSubmit = function(){ if(arguments.length > 0 && typeof arguments[0] == "function") { _submitReady = arguments[0]; return; } if(typeof _submitReady == "function" && _submitReady()) { document.adminForm.submit(); } };
}
//-->
'        
                        );
                    //}
                    
                    $data->template = contentbuilder::getEditableTemplate($this->_id, $this->_record_id, $data->items, $ids, !$data->edit_by_type);
                    $metadata = $data->form->getRecordMetadata($this->_record_id);
                    if($metadata instanceof stdClass && $data->metadata){
                        $data->created = $metadata->created ? $metadata->created : '';
                        $data->created_by = $metadata->created_by ? $metadata->created_by : '';
                        $data->modified = $metadata->modified ? $metadata->modified : '';
                        $data->modified_by = $metadata->modified_by ? $metadata->modified_by : '';
                    }else{
                        $data->created = '';
                        $data->created_by = '';
                        $data->modified = '';
                        $data->modified_by = '';
                    }
                }
                return $data;
            }
        }
        return null;
    }
    
    public static function customValidate($code, $field, $fields, $record_id, $form, $value){
        $msg = '';
        eval($code);
        return $msg;
    }
    
    function store(){
       
        JRequest::checkToken('default') or jexit(JText::_('JInvalid_Token'));
        
        JPluginHelper::importPlugin('contentbuilder_submit');
        $submit_dispatcher = JDispatcher::getInstance();
        
        JFactory::getSession()->clear('cb_failed_values', 'com_contentbuilder.'.$this->_id);
        JRequest::setVar('cb_submission_failed', 0);
        
        $query = $this->_buildQuery();
        $this->_data = $this->_getList($query, 0, 1);

        if (!count($this->_data)) {
            JError::raiseError(404, JText::_('COM_CONTENTBUILDER_FORM_NOT_FOUND'));
        }

        foreach ($this->_data As $data) {
            
            if (!$this->frontend && $data->display_in == 0) {
                JError::raiseError(404, JText::_('COM_CONTENTBUILDER_RECORD_NOT_FOUND'));
            } else if ($this->frontend && $data->display_in == 1) {
                JError::raiseError(404, JText::_('COM_CONTENTBUILDER_RECORD_NOT_FOUND'));
            }
            
            $data->form_id = $this->_id;
            if ($data->type && $data->reference_id) {
                $values = array();
                $data->form = contentbuilder::getForm($data->type, $data->reference_id);
                $meta = $data->form->getRecordMetadata($this->_record_id);
                if(!$data->edit_by_type){
                
                    $noneditable_fields = contentbuilder::getListNonEditableElements($this->_id);
                    $names = $data->form->getElementNames();

                    $this->_db->setQuery("Select * From #__contentbuilder_elements Where form_id = " . $this->_id . " And published = 1 And editable = 1");
                    $fields = $this->_db->loadAssocList();

                    $the_fields        = array();
                    $the_name_field = null;
                    $the_username_field = null;
                    $the_password_field = null;
                    $the_password_repeat_field = null;
                    $the_email_field = null;
                    $the_email_repeat_field = null;
                    $the_html_fields   = array();
                    $the_upload_fields = array();
                    $the_captcha_field = null;
                    $the_failed_registration_fields = array();
                    
                    jimport('joomla.filesystem.file');
                    jimport('joomla.filesystem.folder');

                    foreach($fields As $special_field){
                        switch($special_field['type']){
                            case 'text':
                            case 'upload':
                            case 'captcha':
                            case 'textarea': 
                                if( $special_field['type'] == 'upload' ){
                                    $options = unserialize(base64_decode($special_field['options']));
                                    $special_field['options'] = $options;
                                    $the_upload_fields[$special_field['reference_id']] = $special_field;
                                }
                                else if( $special_field['type'] == 'captcha' ){
                                    $options = unserialize(base64_decode($special_field['options']));
                                    $special_field['options'] = $options;
                                    $the_captcha_field = $special_field;
                                }
                                else if( $special_field['type'] == 'textarea' ){
                                    $options = unserialize(base64_decode($special_field['options']));
                                    $special_field['options'] = $options;
                                    if(isset($special_field['options']->allow_html) && $special_field['options']->allow_html){
                                        $the_html_fields[$special_field['reference_id']] = $special_field;
                                    }else{
                                        $the_fields[$special_field['reference_id']] = $special_field;
                                    }
                                }
                                else if( $special_field['type'] == 'text' ){
                                    $options = unserialize(base64_decode($special_field['options']));
                                    $special_field['options'] = $options;
                                    if($data->act_as_registration && $data->registration_username_field == $special_field['reference_id'] ){
                                        $the_username_field = $special_field;
                                    } else if($data->act_as_registration && $data->registration_name_field == $special_field['reference_id'] ){
                                        $the_name_field = $special_field;
                                    } else if($data->act_as_registration && $data->registration_password_field == $special_field['reference_id'] ){
                                        $the_password_field = $special_field;
                                    } else if($data->act_as_registration && $data->registration_password_repeat_field == $special_field['reference_id'] ){
                                        $the_password_repeat_field = $special_field;
                                    } else if($data->act_as_registration && $data->registration_email_field == $special_field['reference_id'] ){
                                        $the_email_field = $special_field;
                                    } else if($data->act_as_registration && $data->registration_email_repeat_field == $special_field['reference_id'] ){
                                        $the_email_repeat_field = $special_field;
                                    } else {
                                        $the_fields[$special_field['reference_id']] = $special_field;
                                    }
                                }
                           break;
                           default:
                                $options = unserialize(base64_decode($special_field['options']));
                                $special_field['options'] = $options;
                                $the_fields[$special_field['reference_id']] = $special_field;
                        }
                    }

                    // we have defined a captcha, so let's test it
                    if($the_captcha_field !== null && !in_array($the_captcha_field['reference_id'], $noneditable_fields)){

                        if(!class_exists('Securimage')){
                           require_once(JPATH_SITE . DS . 'components' . DS . 'com_contentbuilder' . DS . 'images' . DS . 'securimage' . DS . 'securimage.php');
                        }

                        $securimage = new Securimage();
/*
                        jimport('joomla.filesystem.file'); 
jimport('joomla.filesystem.folder'); 
ob_start(); 
JFactory::getSession();
print_r($_SESSION); 
$c = ob_get_contents(); 
ob_end_clean(); 
JFile::write(JPATH_SITE.DS.'logs'.DS.'session_edit.txt',$c);*/
                        $cap_value = JRequest::getVar( 'cb_' . $the_captcha_field['reference_id'], null, 'POST' );
                        if ($securimage->check($cap_value) == false) {
                            JRequest::setVar('cb_submission_failed', 1);
                            JFactory::getApplication()->enqueueMessage(JText::_('COM_CONTENTBUILDER_CAPTCHA_FAILED'), 'error');
                        }
                        $values[$the_captcha_field['reference_id']] = $cap_value;
                        $noneditable_fields[] = $the_captcha_field['reference_id'];
                    }
                    
                    // now let us see if we have a registration
                    // make sure to wait for previous errors
                    if( $data->act_as_registration && $the_name_field !== null && $the_email_field !== null && $the_email_repeat_field !== null && $the_password_field !== null && $the_password_repeat_field !== null && $the_username_field !== null ){
                        
                        $pw1 = JRequest::getVar( 'cb_' . $the_password_field['reference_id'], '', 'POST', 'STRING', JREQUEST_NOTRIM | JREQUEST_ALLOWRAW );
                        $pw2 = JRequest::getVar( 'cb_' . $the_password_repeat_field['reference_id'], '', 'POST', 'STRING', JREQUEST_NOTRIM | JREQUEST_ALLOWRAW );
                        $email = JRequest::getVar( 'cb_' . $the_email_field['reference_id'], '', 'POST', 'STRING', JREQUEST_ALLOWRAW );
                        $email2 = JRequest::getVar( 'cb_' . $the_email_repeat_field['reference_id'], '', 'POST', 'STRING', JREQUEST_ALLOWRAW );
                        $name = JRequest::getVar( 'cb_' . $the_name_field['reference_id'], '', 'POST', 'STRING', JREQUEST_ALLOWRAW );
                        $username = JRequest::getVar( 'cb_' . $the_username_field['reference_id'], '', 'POST', 'STRING', JREQUEST_NOTRIM | JREQUEST_ALLOWRAW );
                        
                        if( !JRequest::getVar('cb_submission_failed', 0) ){
                        
                            if( !trim($name) )
                            {
                                JRequest::setVar('cb_submission_failed', 1);
                                JFactory::getApplication()->enqueueMessage(JText::_('COM_CONTENTBUILDER_NAME_EMPTY'), 'error');
                            }
                            
                            if( !trim($username) )
                            {
                                JRequest::setVar('cb_submission_failed', 1);
                                JFactory::getApplication()->enqueueMessage(JText::_('COM_CONTENTBUILDER_USERNAME_EMPTY'), 'error');
                            }
                            else if( preg_match( "#[<>\"'%;()&]#i", $username) || strlen(utf8_decode($username )) < 2 )
                            {
                                JRequest::setVar('cb_submission_failed', 1);
                                JFactory::getApplication()->enqueueMessage(JText::_('COM_CONTENTBUILDER_USERNAME_INVALID'), 'error');
                            }
                            
                            if( !trim($email) )
                            {
                                JRequest::setVar('cb_submission_failed', 1);
                                JFactory::getApplication()->enqueueMessage(JText::_('COM_CONTENTBUILDER_EMAIL_EMPTY'), 'error');
                            }
                            else if( !contentbuilder_is_email($email) )
                            {
                                JRequest::setVar('cb_submission_failed', 1);
                                JFactory::getApplication()->enqueueMessage(JText::_('COM_CONTENTBUILDER_EMAIL_INVALID'), 'error');
                            } 
                            else if( $email != $email2 )
                            {
                                JRequest::setVar('cb_submission_failed', 1);
                                JFactory::getApplication()->enqueueMessage(JText::_('COM_CONTENTBUILDER_EMAIL_MISMATCH'), 'error');
                            }
                            
                            if( !$meta->created_id && !JFactory::getUser()->get('id', 0) ){

                                $this->_db->setQuery("Select count(id) From #__users Where `username` = " . $this->_db->Quote($username));
                                if($this->_db->loadResult()){
                                    JRequest::setVar('cb_submission_failed', 1);
                                    JFactory::getApplication()->enqueueMessage(JText::_('COM_CONTENTBUILDER_USERNAME_NOT_AVAILABLE'), 'error');
                                }

                                $this->_db->setQuery("Select count(id) From #__users Where `email` = " . $this->_db->Quote($email));
                                if($this->_db->loadResult()){
                                    JRequest::setVar('cb_submission_failed', 1);
                                    JFactory::getApplication()->enqueueMessage(JText::_('COM_CONTENTBUILDER_EMAIL_NOT_AVAILABLE'), 'error');
                                }
                                
                                if( $pw1 != $pw2 ){
                                    JRequest::setVar('cb_submission_failed', 1);
                                    JFactory::getApplication()->enqueueMessage(JText::_('COM_CONTENTBUILDER_PASSWORD_MISMATCH'), 'error');
                                
                                    JRequest::setVar( 'cb_' . $the_password_field['reference_id'], '' );
                                    JRequest::setVar( 'cb_' . $the_password_repeat_field['reference_id'], '' );
                        
                                } 
                                else if( !trim($pw1) )
                                {
                                    JRequest::setVar('cb_submission_failed', 1);
                                    JFactory::getApplication()->enqueueMessage(JText::_('COM_CONTENTBUILDER_PASSWORD_EMPTY'), 'error');
                                
                                    JRequest::setVar( 'cb_' . $the_password_field['reference_id'], '' );
                                    JRequest::setVar( 'cb_' . $the_password_repeat_field['reference_id'], '' );
                                }
                            }
                            else
                            {
                                if($meta->created_id && $meta->created_id != JFactory::getUser()->get('id', 0)){
                                    $this->_db->setQuery("Select count(id) From #__users Where id <> ".$this->_db->Quote($meta->created_id)." And `username` = " . $this->_db->Quote($username));
                                    if($this->_db->loadResult()){
                                        JRequest::setVar('cb_submission_failed', 1);
                                        JFactory::getApplication()->enqueueMessage(JText::_('COM_CONTENTBUILDER_USERNAME_NOT_AVAILABLE'), 'error');
                                    }

                                    $this->_db->setQuery("Select count(id) From #__users Where id <> ".$this->_db->Quote($meta->created_id)." And `email` = " . $this->_db->Quote($email));
                                    if($this->_db->loadResult()){
                                        JRequest::setVar('cb_submission_failed', 1);
                                        JFactory::getApplication()->enqueueMessage(JText::_('COM_CONTENTBUILDER_EMAIL_NOT_AVAILABLE'), 'error');
                                    }
                                }
                                else
                                {
                                    $this->_db->setQuery("Select count(id) From #__users Where id <> ".$this->_db->Quote(JFactory::getUser()->get('id', 0))." And `username` = " . $this->_db->Quote($username));
                                    if($this->_db->loadResult()){
                                        JRequest::setVar('cb_submission_failed', 1);
                                        JFactory::getApplication()->enqueueMessage(JText::_('COM_CONTENTBUILDER_USERNAME_NOT_AVAILABLE'), 'error');
                                    }

                                    $this->_db->setQuery("Select count(id) From #__users Where id <> ".$this->_db->Quote(JFactory::getUser()->get('id', 0))." And `email` = " . $this->_db->Quote($email));
                                    if($this->_db->loadResult()){
                                        JRequest::setVar('cb_submission_failed', 1);
                                        JFactory::getApplication()->enqueueMessage(JText::_('COM_CONTENTBUILDER_EMAIL_NOT_AVAILABLE'), 'error');
                                    }
                                }
                                
                                if(trim($pw1) != '' || trim($pw2) != ''){
                                    
                                    if( $pw1 != $pw2 ){
                                        JRequest::setVar('cb_submission_failed', 1);
                                        JFactory::getApplication()->enqueueMessage(JText::_('COM_CONTENTBUILDER_PASSWORD_MISMATCH'), 'error');
                                    
                                        JRequest::setVar( 'cb_' . $the_password_field['reference_id'], '' );
                                        JRequest::setVar( 'cb_' . $the_password_repeat_field['reference_id'], '' );
                                    } 
                                    else if( !trim($pw1) )
                                    {
                                        JRequest::setVar('cb_submission_failed', 1);
                                        JFactory::getApplication()->enqueueMessage(JText::_('COM_CONTENTBUILDER_PASSWORD_EMPTY'), 'error');
                                    
                                        JRequest::setVar( 'cb_' . $the_password_field['reference_id'], '' );
                                        JRequest::setVar( 'cb_' . $the_password_repeat_field['reference_id'], '' );
                                    }
                                }
                            }
                            
                            if(!JRequest::getVar('cb_submission_failed', 0)){
                                
                                //$noneditable_fields[] = $the_name_field['reference_id'];
                                $noneditable_fields[] = $the_password_field['reference_id'];
                                $noneditable_fields[] = $the_password_repeat_field['reference_id'];
                                //$noneditable_fields[] = $the_email_field['reference_id'];
                                $noneditable_fields[] = $the_email_repeat_field['reference_id'];
                                //$noneditable_fields[] = $the_username_field['reference_id'];

                            }else{
                                
                                $the_failed_registration_fields[$the_name_field['reference_id']] = $the_name_field;
                                //$the_failed_registration_fields[$the_password_field['reference_id']] = $the_password_field;
                                //$the_failed_registration_fields[$the_password_repeat_field['reference_id']] = $the_password_repeat_field;
                                $the_failed_registration_fields[$the_email_field['reference_id']] = $the_email_field;
                                $the_failed_registration_fields[$the_email_repeat_field['reference_id']] = $the_email_repeat_field;
                                $the_failed_registration_fields[$the_username_field['reference_id']] = $the_username_field;
                            }
                        }
                        else
                        {
                            $the_failed_registration_fields[$the_name_field['reference_id']] = $the_name_field;
                            //$the_failed_registration_fields[$the_password_field['reference_id']] = $the_password_field;
                            //$the_failed_registration_fields[$the_password_repeat_field['reference_id']] = $the_password_repeat_field;
                            $the_failed_registration_fields[$the_email_field['reference_id']] = $the_email_field;
                            $the_failed_registration_fields[$the_email_repeat_field['reference_id']] = $the_email_repeat_field;
                            $the_failed_registration_fields[$the_username_field['reference_id']] = $the_username_field;
                        }
                    }

                    $form_elements_objects = array();
                    
                    $_items = $data->form->getRecord($this->_record_id, $data->published_only, $this->frontend ? ( $data->own_only_fe ? JFactory::getUser()->get('id', 0) : -1 ) : ( $data->own_only ? JFactory::getUser()->get('id', 0) : -1 ), $this->frontend ? $data->show_all_languages_fe : true );
                    
                    foreach($names As $id => $name){

                        if(!in_array($id, $noneditable_fields)){

                            if(isset($the_html_fields[$id]))
                            {
                                $the_html_fields[$id]['name'] = $name;
                                $the_html_fields[$id]['value'] = '';
                            } 
                            else if(isset($the_failed_registration_fields[$id]))
                            {
                                $the_failed_registration_fields[$id]['name'] = $name;
                                $the_failed_registration_fields[$id]['value'] = '';
                            }
                            else if(isset($the_upload_fields[$id]))
                            {
                                $the_upload_fields[$id]['name'] = $name;
                                $the_upload_fields[$id]['value'] = '';
                                $the_upload_fields[$id]['orig_value'] = '';
                            }
                            else if(isset($the_fields[$id]))
                            {
                                $the_fields[$id]['name'] = $name;
                                $the_fields[$id]['value'] = '';
                            }
                            
                            if(isset($the_upload_fields[$id]) && $id == $the_upload_fields[$id]['reference_id']){

                                // delete if triggered
                                if( JRequest::getInt('cb_delete_'.$id, 0) == 1 && isset($the_upload_fields[$id]['validations']) && $the_upload_fields[$id]['validations'] == '' ){
                                    if(count($_items)){
                                        foreach($_items As $_item){
                                            if($_item->recElementId == $the_upload_fields[$id]['reference_id']){
                                                $_value = $_item->recValue;
                                                $_files = explode("\n", str_replace("\r",'',$_value));
                                                foreach($_files As $_file){
                                                    if(strpos(strtolower($_file), '{cbsite}') === 0){
                                                        $_file = str_replace(array('{cbsite}','{CBSite}'), array(JPATH_SITE, JPATH_SITE), $_file);
                                                    }
                                                    if(JFile::exists($_file)){
                                                        JFile::delete($_file);
                                                    }
                                                    $values[$id] = '';
                                                }
                                            }
                                        }
                                    }
                                }
                                
                                $file = JRequest::getVar('cb_' . $id, null, 'files', 'array');

                                if( trim(JFile::makeSafe($file['name'])) != '' && $file['size'] > 0){

                                    $filename = trim( JFile::makeSafe($file['name']) );
                                    $infile = $filename;

                                    $src = $file['tmp_name'];
                                    $dest = '';
                                    $tmp_dest = '';
                                    $tmp_upload_field_dir = '';
                                    $tmp_upload_dir = '';
                                    
                                    if(isset($the_upload_fields[$id]['options']) && isset($the_upload_fields[$id]['options']->upload_directory) && $the_upload_fields[$id]['options']->upload_directory != ''){
                                        $tmp_upload_field_dir = $the_upload_fields[$id]['options']->upload_directory;
                                        $tmp_dest = $tmp_upload_field_dir;
                                    } else if($data->upload_directory != ''){
                                        $tmp_upload_dir = $data->upload_directory;
                                        $tmp_dest = $tmp_upload_dir;
                                    }
                                    
                                    if(isset($the_upload_fields[$id]['options']) && isset($the_upload_fields[$id]['options']->upload_directory) && $the_upload_fields[$id]['options']->upload_directory != '' && JFolder::exists($tmp_upload_field_dir)){

                                        $dest = str_replace(array('{CBSite}','{cbsite}'), JPATH_SITE, $the_upload_fields[$id]['options']->upload_directory);

                                    } else if($data->upload_directory != '' && JFolder::exists($tmp_upload_dir)){

                                        $dest = str_replace(array('{CBSite}','{cbsite}'), JPATH_SITE, $data->upload_directory);
                                    }
                                    
                                    // create dest path by tokens
                                    $dest = $this->createPathByTokens($dest, $names);

                                    $msg = '';
                                    $uploaded = false;

                                    // FILE SIZE TEST

                                    if($dest != '' && isset($the_upload_fields[$id]['options']) && isset($the_upload_fields[$id]['options']->max_filesize) && $the_upload_fields[$id]['options']->max_filesize > 0){

                                        $val = $the_upload_fields[$id]['options']->max_filesize;
                                        $val = trim($val);
                                        $last = strtolower($val[strlen($val)-1]);
                                        switch($last) {
                                            case 'g':
                                                $val *= 1024;
                                            case 'm':
                                                $val *= 1024;
                                            case 'k':
                                                $val *= 1024;
                                        }

                                        if($file['size'] > $val){
                                            $msg = JText::_('COM_CONTENTBUILDER_FILESIZE_EXCEEDED') . ' ' . $the_upload_fields[$id]['options']->max_filesize . 'b';
                                        }
                                    }

                                    // FILE EXT TEST

                                    if($dest != '' && isset($the_upload_fields[$id]['options']) && isset($the_upload_fields[$id]['options']->allowed_file_extensions) && $the_upload_fields[$id]['options']->allowed_file_extensions != ''){

                                        $allowed = explode(',',str_replace(' ','',strtolower($the_upload_fields[$id]['options']->allowed_file_extensions)));
                                        $ext = strtolower(JFile::getExt($filename));

                                        if(!in_array($ext, $allowed)){
                                            $msg = JText::_('COM_CONTENTBUILDER_FILE_EXTENSION_NOT_ALLOWED');
                                        }
                                    }

                                    // UPLOAD

                                    if($dest != '' && $msg == ''){

                                        // limit file's name size
                                        $ext = strtolower(JFile::getExt($filename));
                                        $stripped = JFile::stripExt($filename);
                                        // in some apache configurations unknown file extensions could lead to security risks
                                        // because it will try to find an executable extensions within the chain of dots. So we simply remove them.
                                        $filename = str_replace(array(' ','.'),'_',$stripped).'.'.$ext;

                                        $maxnamesize = 100;
                                        if(function_exists('mb_strlen')){
                                            if(mb_strlen($filename) > $maxnamesize){
                                                $filename = mb_substr($filename, mb_strlen($filename)-$maxnamesize);
                                            }
                                        }else{
                                            if(strlen($filename) > $maxnamesize){
                                                $filename = substr($filename, strlen($filename)-$maxnamesize);
                                            }
                                        }

                                        // take care of existing filenames
                                        if(JFile::exists($dest . DS . $filename)){
                                            $filename = md5(mt_rand(0, mt_getrandmax()) . time()) . '_' . $filename;
                                        }

                                        // create pseudo security index.html
                                        if(!JFile::exists($dest.DS.'index.html')){
                                            JFile::write($dest.DS.'index.html', $buffer = '');
                                        }
                                        
                                        if(count($_items)){
                                            $files_to_delete = array();
                                            
                                            foreach($_items As $_item){
                                                if($_item->recElementId == $the_upload_fields[$id]['reference_id']){
                                                    $_value = $_item->recValue;
                                                    $_files = explode("\n", str_replace("\r",'',$_value));
                                                    foreach($_files As $_file){
                                                        if(strpos(strtolower($_file), '{cbsite}') === 0){
                                                            $_file = str_replace(array('{cbsite}','{CBSite}'), array(JPATH_SITE, JPATH_SITE), $_file);
                                                        }
                                                        $files_to_delete[] = $_file;
                                                    }
                                                    break;
                                                }
                                            }
                                            foreach( $files_to_delete As $file_to_delete ){
                                                if(JFile::exists($file_to_delete)){
                                                    JFile::delete($file_to_delete);
                                                }
                                            }
                                        }
                                        
                                        // final upload file moving
                                        $uploaded = JFile::upload($src, $dest . DS . $filename);

                                        if(!$uploaded){
                                            $msg = JText::_('COM_CONTENTBUILDER_UPLOAD_FAILED');
                                        }
                                    }

                                    if($dest == '' || $uploaded !== true){
                                        JRequest::setVar('cb_submission_failed', 1);
                                        JFactory::getApplication()->enqueueMessage($msg . ' ('.$infile.')', 'error');
                                        $the_upload_fields[$id]['value'] = '';
                                    }
                                    else
                                    {
                                        if(strpos(strtolower($tmp_dest), '{cbsite}') === 0){
                                            $dest = str_replace(array(JPATH_SITE, JPATH_SITE), array('{cbsite}','{CBSite}'), $dest);
                                        }
                                        $values[$id] = $dest . DS . $filename;
                                        $the_upload_fields[$id]['value'] = $values[$id];
                                    }
                                    
                                    $the_upload_fields[$id]['orig_value'] = JFile::makeSafe($file['name']);
                                }

                                if(trim($the_upload_fields[$id]['custom_validation_script'])){
                                    $msg = self::customValidate(trim($the_upload_fields[$id]['custom_validation_script']), $the_upload_fields[$id], $merged = array_merge($the_upload_fields, $the_fields, $the_html_fields), JRequest::getCmd('record_id',''), $data->form, isset($values[$id]) ? $values[$id] : '');
                                    $msg = trim($msg);
                                    if(!empty($msg)){
                                        JRequest::setVar('cb_submission_failed', 1);
                                        JFactory::getApplication()->enqueueMessage(trim($msg), 'error');
                                    }
                                }

                                $removables = array();
                                $validations = explode(',', $the_upload_fields[$id]['validations']);
                                
                                foreach($validations As $validation){
                                    $plgs = CBPluginHelper::importPlugin('contentbuilder_validation', $validation);
                                    $removables = array_merge($removables, $plgs);
                                }
                                $dispatcher = JDispatcher::getInstance();
                                
                                $results = $dispatcher->trigger('onValidate', array($the_upload_fields[$id], $merged = array_merge($the_upload_fields, $the_fields, $the_html_fields), JRequest::getCmd('record_id',''), $data->form, isset($values[$id]) ? $values[$id] : ''));
                                
                                foreach($removables As $removable){
                                    $dispatcher->detach($removable);
                                }
                                
                                $all_errors = implode('',$results);
                                if(!empty($all_errors)){
                                    if(isset($values[$id]) && JFile::exists($values[$id])){
                                        JFile::delete($values[$id]);
                                    }
                                    JRequest::setVar('cb_submission_failed', 1);
                                    foreach($results As $result){
                                        $result = trim($result);
                                        if(!empty($result)){
                                            JFactory::getApplication()->enqueueMessage(trim($result), 'error');
                                        }
                                    }
                                }
                            } 
                            else
                            {
                                $f = null;
                                
                                if(isset($the_html_fields[$id]))
                                {
                                    $value = JRequest::getVar( 'cb_' . $id, '', 'POST', 'STRING', JREQUEST_ALLOWHTML );
                                    $f = $the_html_fields[$id];
                                    $the_html_fields[$id]['value'] = $value;
                                } 
                                
                                if(isset($the_failed_registration_fields[$id]))
                                {
                                    $value = JRequest::getVar( 'cb_' . $id, '', 'POST', 'STRING', JREQUEST_NOTRIM | JREQUEST_ALLOWRAW );
                                    $f = $the_failed_registration_fields[$id];
                                    $the_failed_registration_fields[$id]['value'] = $value;
                                }
                                
                                if(isset($the_fields[$id])) 
                                {
                                    $is_array = 'STRING';
                                    if(is_array(JRequest::getVar( 'cb_' . $id, ''))){
                                        $is_array = 'ARRAY';
                                    }
                                    if( isset($the_fields[$id]['options']->allow_raw) && $the_fields[$id]['options']->allow_raw ){
                                        $value = JRequest::getVar( 'cb_' . $id, '', 'POST', $is_array, JREQUEST_ALLOWRAW );
                                    }
                                    else if( isset($the_fields[$id]['options']->allow_html) && $the_fields[$id]['options']->allow_html ){
                                        $value = JRequest::getVar( 'cb_' . $id, '', 'POST', $is_array, JREQUEST_ALLOWHTML );
                                    }
                                    else{
                                        $value = JRequest::getVar( 'cb_' . $id, '', 'POST', $is_array );
                                    }
                                    if(isset($the_fields[$id]['options']->transfer_format)){
                                        $value = contentbuilder_convert_date($value, $the_fields[$id]['options']->format, $the_fields[$id]['options']->transfer_format);
                                    }
                                    $f = $the_fields[$id];
                                    $the_fields[$id]['value'] = $value;
                                }

                                if($f !== null){
                                    
                                    if(trim($f['custom_validation_script'])){
                                        $msg = self::customValidate(trim($f['custom_validation_script']), $f, $merged = array_merge($the_upload_fields, $the_fields, $the_html_fields), JRequest::getCmd('record_id',''), $data->form, $value);
                                        $msg = trim($msg);
                                        if(!empty($msg)){
                                            JRequest::setVar('cb_submission_failed', 1);
                                            JFactory::getApplication()->enqueueMessage(trim($msg), 'error');
                                        }
                                    }
                                    
                                    $removables = array();
                                    $validations = explode(',', $f['validations']);

                                    foreach($validations As $validation){
                                        $plgs = CBPluginHelper::importPlugin('contentbuilder_validation', $validation);
                                        $removables = array_merge($removables, $plgs);
                                    }

                                    $dispatcher = JDispatcher::getInstance();
                                    $results = $dispatcher->trigger('onValidate', array($f, $merged = array_merge($the_upload_fields, $the_fields, $the_html_fields), JRequest::getCmd('record_id',''), $data->form, $value));

                                    foreach($removables As $removable){
                                        $dispatcher->detach($removable);
                                    }

                                    $all_errors = implode('',$results);
                                    $values[$id] = $value;
                                    if(!empty($all_errors)){
                                        JRequest::setVar('cb_submission_failed', 1);
                                        foreach($results As $result){
                                            $result = trim($result);
                                            if(!empty($result)){
                                                JFactory::getApplication()->enqueueMessage(trim($result), 'error');
                                            }
                                        }
                                    }
                                    else
                                    {
                                        $removables = array();
                           
                                        $plgs = CBPluginHelper::importPlugin('contentbuilder_form_elements', $f['type']);
                                        $removables = array_merge($removables, $plgs);

                                        $dispatcher = JDispatcher::getInstance();
                                        $plugin_validations = $dispatcher->trigger('onAfterValidationSuccess', array($f, $m = array_merge($the_upload_fields, $the_fields, $the_html_fields), JRequest::getCmd('record_id',''), $data->form, $value));

                                        if(count($plugin_validations)){
                                            $form_elements_objects[] = $plugin_validations[0];
                                        }
                                        
                                        foreach($removables As $removable){
                                            $dispatcher->detach($removable);
                                        }
                                    }
                                }
                            }
                        }
                    }
                    
                    $submit_before_result = $submit_dispatcher->trigger('onBeforeSubmit', array(JRequest::getCmd('record_id',''), $data->form, $values));
                            
                    if(JRequest::getVar('cb_submission_failed',0)){
                        JFactory::getSession()->set('cb_failed_values', $values, 'com_contentbuilder.'.$this->_id);
                        return JRequest::getCmd('record_id','');
                    }

                    $record_return = $data->form->saveRecord(JRequest::getCmd('record_id',''), $values);
                    
                    foreach($form_elements_objects As $form_elements_object){
                        if($form_elements_object instanceof CBFormElementAfterValidation){
                            $form_elements_object->onSaveRecord($record_return);
                        }
                    }
                    
                    if($data->act_as_registration && $record_return){
                        
                        $meta = $data->form->getRecordMetadata($record_return);
                        
                        if(!$data->registration_bypass_plugin || $meta->created_id){
                            
                            $user_id = $this->register(
                                '',
                                '',
                                '',
                                $meta->created_id,
                                JRequest::getVar( 'cb_' . $the_name_field['reference_id'], '', 'POST', 'STRING', JREQUEST_ALLOWRAW ), 
                                JRequest::getVar( 'cb_' . $the_username_field['reference_id'], '', 'POST', 'STRING', JREQUEST_NOTRIM | JREQUEST_ALLOWRAW ), 
                                JRequest::getVar( 'cb_' . $the_email_field['reference_id'], '', 'POST', 'STRING', JREQUEST_ALLOWRAW ), 
                                JRequest::getVar( 'cb_' . $the_password_field['reference_id'], '', 'POST', 'STRING', JREQUEST_NOTRIM | JREQUEST_ALLOWRAW )
                            );
                            
                            if( $user_id ){
                            
                                JFactory::getSession()->set('cb_last_record_user_id', $user_id, 'com_contentbuilder');

                                $data->form->saveRecordUserData( 
                                        $record_return,
                                        $user_id,
                                        JRequest::getVar( 'cb_' . $the_name_field['reference_id'], '', 'POST', 'STRING', JREQUEST_ALLOWRAW ), 
                                        JRequest::getVar( 'cb_' . $the_username_field['reference_id'], '', 'POST', 'STRING', JREQUEST_NOTRIM | JREQUEST_ALLOWRAW )
                                );
                            }
                            
                        } else {
                            
                            if( !$meta->created_id ) {
                            
                                $bypass = new stdClass();
                                $verification_name = str_replace(array(';','___','|'), '-', trim($data->registration_bypass_verification_name) ? trim($data->registration_bypass_verification_name) : $data->title);
                                $verify_view = trim($data->registration_bypass_verify_view) ? trim($data->registration_bypass_verify_view) : $data->id;
                                $bypass->text = $orig_text = '{CBVerify plugin: '.$data->registration_bypass_plugin.'; verification-name: '.$verification_name.'; verify-view: '.$verify_view.'; '.str_replace(array("\r", "\n"),'',$data->registration_bypass_plugin_params).'}';
                                $params = new stdClass();

                                JPluginHelper::importPlugin('content', 'contentbuilder_verify');
                                $bypass_dispatcher = JDispatcher::getInstance();
                                $bypass_result = $bypass_dispatcher->trigger('onPrepareContent', array(&$bypass, &$params));

                                $verification_id = '';

                                if($bypass->text != $orig_text){
                                    $verification_id = md5(uniqid(null,true) . mt_rand(0, mt_getrandmax()) . JFactory::getUser()->get('id',0));
                                }

                                $user_id = $this->register(
                                    $data->registration_bypass_plugin,
                                    $verification_name,
                                    $verification_id,
                                    $meta->created_id,
                                    JRequest::getVar( 'cb_' . $the_name_field['reference_id'], '', 'POST', 'STRING', JREQUEST_ALLOWRAW ), 
                                    JRequest::getVar( 'cb_' . $the_username_field['reference_id'], '', 'POST', 'STRING', JREQUEST_NOTRIM | JREQUEST_ALLOWRAW ), 
                                    JRequest::getVar( 'cb_' . $the_email_field['reference_id'], '', 'POST', 'STRING', JREQUEST_ALLOWRAW ), 
                                    JRequest::getVar( 'cb_' . $the_password_field['reference_id'], '', 'POST', 'STRING', JREQUEST_NOTRIM | JREQUEST_ALLOWRAW )
                                );

                                if( $user_id ){

                                    JFactory::getSession()->set('cb_last_record_user_id', $user_id, 'com_contentbuilder');

                                    $data->form->saveRecordUserData( 
                                            $record_return,
                                            $user_id,
                                            JRequest::getVar( 'cb_' . $the_name_field['reference_id'], '', 'POST', 'STRING', JREQUEST_ALLOWRAW ), 
                                            JRequest::getVar( 'cb_' . $the_username_field['reference_id'], '', 'POST', 'STRING', JREQUEST_NOTRIM | JREQUEST_ALLOWRAW )
                                    );
                                }

                                if($bypass->text != $orig_text && $user_id){

                                    $_now = JFactory::getDate();

                                    $setup = JFactory::getSession()->get($data->registration_bypass_plugin.$verification_name, '', 'com_contentbuilder.verify.'.$data->registration_bypass_plugin.$verification_name);
                                    JFactory::getSession()->clear($data->registration_bypass_plugin.$verification_name, 'com_contentbuilder.verify.'.$data->registration_bypass_plugin.$verification_name);

                                    $this->_db->setQuery("
                                            Insert Into #__contentbuilder_verifications
                                            (
                                            `verification_hash`,
                                            `start_date`,
                                            `verification_data`,
                                            `user_id`,
                                            `plugin`,
                                            `ip`,
                                            `setup`,
                                            `client`
                                            )
                                            Values
                                            (
                                            ".$this->_db->Quote($verification_id).",
                                            ".$this->_db->Quote($_now->toMySQL()).",
                                            ".$this->_db->Quote('type=registration&').",
                                            ".$user_id.",
                                            ".$this->_db->Quote($data->registration_bypass_plugin).",
                                            ".$this->_db->Quote($_SERVER['REMOTE_ADDR']).",
                                            ".$this->_db->Quote($setup).",
                                            ".intval(JFactory::getApplication()->isAdmin() ? 1 : 0)."
                                            )
                                    ");
                                    $this->_db->query();
                                }
                            }
                        }
                    }
                    
                    if( $this->frontend && !JRequest::getCmd('record_id','') && $record_return && !JRequest::getVar('return','') ){
                        
                        if( $data->force_login ){
                            if( !JFactory::getUser()->get('id', 0) ){
                                if(!$this->is15){
                                    JRequest::setVar('return', base64_encode(JRoute::_('index.php?option=com_users&view=login&Itemid='.JRequest::getInt('Itemid', 0), false)));
                                }
                                else
                                {
                                    JRequest::setVar('return', base64_encode(JRoute::_('index.php?option=com_user&view=login&Itemid='.JRequest::getInt('Itemid', 0), false)));
                                }
                                
                            }else{
                                
                                if(!$this->is15){
                                    JRequest::setVar('return', base64_encode(JRoute::_('index.php?option=com_users&view=profile&Itemid='.JRequest::getInt('Itemid', 0), false)));
                                }
                                else
                                {
                                    JRequest::setVar('return', base64_encode(JRoute::_('index.php?option=com_user&view=user&Itemid='.JRequest::getInt('Itemid', 0), false)));
                                }
                            }
                        }
                        else if( trim($data->force_url) ){
                           JRequest::setVar('cbInternalCheck', 0);
                           JRequest::setVar('return', base64_encode(trim($data->force_url))); 
                        }
                    }
                    
                    if($record_return){

                        $sef = '';
                        $ignore_lang_code = '*';
                        if($data->default_lang_code_ignore){

                            jimport('joomla.version');
                            $version = new JVersion();

                            if(version_compare($version->getShortVersion(), '1.6', '>=')){

                                $this->_db->setQuery("Select lang_code From #__languages Where published = 1 And sef = " . $this->_db->Quote(trim(JRequest::getCmd('lang',''))));
                                $ignore_lang_code = $this->_db->loadResult();
                                if(!$ignore_lang_code){
                                    $ignore_lang_code = '*';
                                }
                            }
                            else
                            {
                                $codes = contentbuilder::getLanguageCodes();
                                foreach($codes As $code){
                                    if(strstr(strtolower($code), strtolower(trim(JRequest::getCmd('lang','')))) !== false){
                                        $ignore_lang_code = strtolower($code);
                                        break;
                                    }
                                }
                            }

                            $sef = trim(JRequest::getCmd('lang',''));
                            if($ignore_lang_code == '*'){
                                $sef = '';
                            }

                        } else {

                            jimport('joomla.version');
                            $version = new JVersion();

                            if(version_compare($version->getShortVersion(), '1.6', '>=')){

                                $this->_db->setQuery("Select sef From #__languages Where published = 1 And lang_code = " . $this->_db->Quote($data->default_lang_code));
                                $sef = $this->_db->loadResult();

                            } else {

                                $codes = contentbuilder::getLanguageCodes();
                                foreach($codes As $code){
                                    if($code == $data->default_lang_code){
                                        $sef = explode('-', $code);
                                        if(count($sef)){
                                            $sef = strtolower($sef[0]);
                                        }
                                        break;
                                    }
                                }
                            }
                        }

                        $language = $data->default_lang_code_ignore ? $ignore_lang_code : $data->default_lang_code;

                        $this->_db->setQuery("Select id, edited From #__contentbuilder_records Where `type` = ".$this->_db->Quote($data->type)." And `reference_id` = ".$this->_db->Quote($data->form->getReferenceId())." And record_id = " . $this->_db->Quote($record_return));
                        $res = $this->_db->loadAssoc();
                        $last_update = JFactory::getDate();
                        $last_update = $last_update->toMySQL();
                        if(!is_array($res)){

                            $is_future = 0;
                            $created_up = JFactory::getDate();
                            $created_up = $created_up->toMySQL();
                            if(intval($data->default_publish_up_days) != 0){
                                $is_future = 1;
                                $date = JFactory::getDate(strtotime('now +'.intval($data->default_publish_up_days).' days'));
                                $created_up = $date->toMySQL();
                            }
                            $created_down = '0000-00-00 00:00:00';
                            if(intval($data->default_publish_down_days) != 0){
                                $date = JFactory::getDate(strtotime($created_up.' +'.intval($data->default_publish_down_days).' days'));
                                $created_down = $date->toMySQL();
                            }
                            $this->_db->setQuery("Insert Into #__contentbuilder_records (session_id,`type`,last_update,is_future,lang_code, sef, published, record_id, reference_id, publish_up, publish_down) Values ('".JFactory::getSession()->getId()."',".$this->_db->Quote($data->type).",".$this->_db->Quote($last_update).",$is_future,".$this->_db->Quote($language).",".$this->_db->Quote(trim($sef)).",".$this->_db->Quote($data->auto_publish && !$is_future ? 1 : 0).", ".$this->_db->Quote($record_return).", ".$this->_db->Quote($data->form->getReferenceId()).", ".$this->_db->Quote($created_up).", ".$this->_db->Quote($created_down).")");
                            $this->_db->query();
                        }else{
                            $this->_db->setQuery("Update #__contentbuilder_records Set last_update = ".$this->_db->Quote($last_update).",lang_code = ".$this->_db->Quote($language).", sef = ".$this->_db->Quote(trim($sef)).", edited = edited + 1 Where `type` = ".$this->_db->Quote($data->type)." And  `reference_id` = ".$this->_db->Quote($data->form->getReferenceId())." And record_id = " . $this->_db->Quote($record_return));
                            $this->_db->query();
                        }
                    }
                
                }  else {
                    
                    $record_return = JRequest::getCmd('record_id','');
                    
                }
                
                $data->items = $data->form->getRecord($record_return, $data->published_only, $this->frontend ? ( $data->own_only_fe ? JFactory::getUser()->get('id', 0)  : -1 ) : ( $data->own_only ? JFactory::getUser()->get('id', 0)  : -1 ), true );
                
                $data->labels = $data->form->getElementLabels();
                $ids = array();
                foreach ($data->labels As $reference_id => $label) {
                    $ids[] = $this->_db->Quote($reference_id);
                }
                $data->labels = array();
                if (count($ids)) {
                    $this->_db->setQuery("Select Distinct `label`, reference_id From #__contentbuilder_elements Where form_id = " . intval($this->_id) . " And reference_id In (" . implode(',', $ids) . ") And published = 1 Order By ordering");
                    $rows = $this->_db->loadAssocList();
                    $ids = array();
                    foreach ($rows As $row) {
                        $ids[] = $row['reference_id'];
                    }
                }
                
                $article_id = 0;
                
                // creating the article
                if($data->create_articles){
                    
                    $data->page_title = $data->use_view_name_as_title ? $data->name : $data->form->getPageTitle();
                    
                    if(!count($data->items)){
                        JError::raiseError(404, JText::_('COM_CONTENTBUILDER_RECORD_NOT_FOUND'));
                    }
                    
                    $this->_db->setQuery("Select articles.`id` From #__contentbuilder_articles As articles, #__content As content Where content.id = articles.article_id And (content.state = 1 Or content.state = 0) And articles.form_id = " . intval($this->_id) . " And articles.record_id = " . $this->_db->Quote($record_return));
                    $article = $this->_db->loadResult();
                    
                    $config = array();
                    if($article){
                        if(!$this->is15){
                            $config = JRequest::getVar('jform',array());
                        } else {
                            $config = array('ordering' => JRequest::getInt('ordering',0),'sectionid' => JRequest::getInt('sectionid',0), 'catid' => JRequest::getInt('catid',0), 'alias' => JRequest::getVar('alias',''),'frontpage' => JRequest::getInt('frontpage',0), 'state' => JRequest::getInt('state',0),'details' => JRequest::getVar('details',array()), 'params' => JRequest::getVar('params',array()), 'meta' => JRequest::getVar('meta',array()));
                        }
                    }
                    $full = $this->frontend ? contentbuilder::authorizeFe('fullarticle') : contentbuilder::authorize('fullarticle');
                    $article_id = contentbuilder::createArticle($this->_id, $record_return, $data->items, $ids, $data->title_field, $data->form->getRecordMetadata($record_return), $config, $full, $this->frontend ? $data->limited_article_options_fe : $data->limited_article_options, JRequest::getVar('cb_category_id',null));
                
                    if(isset($form_elements_objects)){
                        foreach($form_elements_objects As $form_elements_object){
                            if($form_elements_object instanceof CBFormElementAfterValidation){
                                $form_elements_object->onSaveArticle($article_id);
                            }
                        }
                    }
                }
                
                // required to determine blocked users in system plugin
                if($data->act_as_registration && isset($user_id) && $user_id){
                    $this->_db->setQuery("Insert Into #__contentbuilder_registered_users (user_id, form_id, record_id) Values (".intval($user_id).", ".$this->_id.", ".$this->_db->Quote($record_return).")");
                    $this->_db->query();
                }
                
                if(!$data->edit_by_type){
                    
                    $submit_after_result = $submit_dispatcher->trigger('onAfterSubmit', array($record_return, $article_id, $data->form, $values));
                
                    if( (!JRequest::getCmd('record_id','') && $data->email_notifications) || (JRequest::getCmd('record_id','') && $data->email_update_notifications) ){
                    
                        $from     = JFactory::getConfig()->getValue('config.mailfrom');
                        $fromname = JFactory::getConfig()->getValue('config.fromname');
                        
                        $mailer = JFactory::getMailer();
                        
                        $email_admin_template = '';
                        $email_template       = '';
                        
                        // admin email
                        if( trim($data->email_admin_recipients) ){
                            
                            // sender
                            if( trim($data->email_admin_alternative_from) ){
                                foreach($data->items As $item){
                                    $data->email_admin_alternative_from = str_replace('{'.$item->recName.'}',cbinternal($item->recValue), $data->email_admin_alternative_from);
                                }
                                $from = $data->email_admin_alternative_from;
                            }
                            
                            if( trim($data->email_admin_alternative_fromname) ){
                                foreach($data->items As $item){
                                    $data->email_admin_alternative_fromname = str_replace('{'.$item->recName.'}',cbinternal($item->recValue), $data->email_admin_alternative_fromname);
                                }
                                $fromname = $data->email_admin_alternative_fromname;
                            }
                            
                            $mailer->setSender(array(trim($from), trim($fromname)));
                            
                            // recipients
                            foreach($data->items As $item){
                                $data->email_admin_recipients = str_replace('{'.$item->recName.'}',cbinternal($item->recValue), $data->email_admin_recipients);
                            }
                            
                            $recipients_checked_admin = array();
                            $recipients_admin = explode(';', $data->email_admin_recipients );
                            
                            foreach($recipients_admin As $recipient_admin){
                                if(contentbuilder_is_email(trim($recipient_admin))){
                                    $recipients_checked_admin[] = trim($recipient_admin);
                                }
                            }
                            
                            $mailer->addBCC($recipients_checked_admin);
                            $email_admin_template = contentbuilder::getEmailTemplate($this->_id, $record_return, $data->items, $ids, true);
                        
                            // subject
                            $subject_admin = JText::_('COM_CONTENTBUILDER_EMAIL_RECORD_RECEIVED');
                            if( trim($data->email_admin_subject) ){
                                $subject_admin = $data->email_admin_subject;
                                $subject_admin = str_replace(array('{RECORD_ID}','{record_id}'), $record_return, $subject_admin);
                                $subject_admin = str_replace(array('{USER_ID}','{user_id}'), JFactory::getUser()->get('id'), $subject_admin);
                                $subject_admin = str_replace(array('{USERNAME}','{username}'), JFactory::getUser()->get('username'), $subject_admin);
                                $subject_admin = str_replace(array('{USER_FULL_NAME}','{user_full_name}'), JFactory::getUser()->get('name'), $subject_admin);
                                $subject_admin = str_replace(array('{EMAIL}','{email}'), JFactory::getUser()->get('email'), $subject_admin);
                                $subject_admin = str_replace(array('{VIEW_NAME}','{view_name}'), $data->name, $subject_admin);
                                $subject_admin = str_replace(array('{VIEW_ID}','{view_id}'), $this->_id, $subject_admin);
                                $subject_admin = str_replace(array('{IP}','{ip}'), $_SERVER['REMOTE_ADDR'], $subject_admin);
                                
                                foreach($data->items As $item){
                                    $data->email_admin_subject = str_replace('{'.$item->recName.'}',cbinternal($item->recValue), $data->email_admin_subject);
                                }
                            }
                            
                            $mailer->setSubject($subject_admin);
                            
                            // attachments
                            foreach($data->items As $item){
                                $data->email_admin_recipients_attach_uploads = str_replace('{'.$item->recName.'}',$item->recValue, $data->email_admin_recipients_attach_uploads);
                            }
                            
                            $attachments_admin = explode(';', $data->email_admin_recipients_attach_uploads );
                            
                            $attached_admin = array();
                            foreach($attachments_admin As $attachment_admin){
                                $attachment_admin = explode("\n",str_replace("\r","",trim($attachment_admin)));
                                foreach($attachment_admin As $att_admin){
                                    if(strpos(strtolower($att_admin), '{cbsite}') === 0){
                                        $att_admin = str_replace(array('{cbsite}','{CBSite}'), array(JPATH_SITE, JPATH_SITE), $att_admin);
                                    }
                                    if(JFile::exists(trim($att_admin))){
                                        $attached_admin[] = trim($att_admin);
                                    }
                                }
                            }
                            
                            $mailer->addAttachment($attached_admin);
                            
                            $mailer->isHTML($data->email_admin_html);
                            $mailer->setBody($email_admin_template);
                            
                            if(count($recipients_checked_admin)){
                            
                                $send = $mailer->Send();

                                if ( $send !== true ) {
                                    JFactory::getApplication()->enqueueMessage('Error sending email: ' . $mailer->ErrorInfo, 'error');
                                }
                            }
                            
                            $mailer->ClearAddresses();
                            $mailer->ClearAllRecipients();
                            $mailer->ClearAttachments();                            
                        }
                        
                        // public email
                        if( trim($data->email_recipients) ){
                            
                            // sender
                            if( trim($data->email_alternative_from) ){
                                foreach($data->items As $item){
                                    $data->email_alternative_from = str_replace('{'.$item->recName.'}',cbinternal($item->recValue), $data->email_alternative_from);
                                }
                                $from = $data->email_alternative_from;
                            }
                            
                            if( trim($data->email_alternative_fromname) ){
                                foreach($data->items As $item){
                                    $data->email_alternative_fromname = str_replace('{'.$item->recName.'}',cbinternal($item->recValue), $data->email_alternative_fromname);
                                }
                                $fromname = $data->email_alternative_fromname;
                            }
                            
                            $mailer->setSender(array($from, $fromname));
                            
                            // recipients
                            foreach($data->items As $item){
                                $data->email_recipients = str_replace('{'.$item->recName.'}',cbinternal($item->recValue), $data->email_recipients);
                            }
                            
                            $recipients_checked = array();
                            $recipients = explode(';', $data->email_recipients );
                            
                            foreach($recipients As $recipient){
                                if(contentbuilder_is_email($recipient)){
                                    $recipients_checked[] = $recipient;
                                }
                            }
                            
                            $mailer->addBCC($recipients_checked);
                            
                            $email_template = contentbuilder::getEmailTemplate($this->_id, $record_return, $data->items, $ids, false);
                        
                            // subject
                            $subject = JText::_('COM_CONTENTBUILDER_EMAIL_RECORD_RECEIVED');
                            if( trim($data->email_subject) ){
                                $subject = $data->email_subject;
                                $subject = str_replace(array('{RECORD_ID}','{record_id}'), $record_return, $subject);
                                $subject = str_replace(array('{USER_ID}','{user_id}'), JFactory::getUser()->get('id'), $subject);
                                $subject = str_replace(array('{USERNAME}','{username}'), JFactory::getUser()->get('username'), $subject);
                                $subject = str_replace(array('{EMAIL}','{email}'), JFactory::getUser()->get('email'), $subject);
                                $subject = str_replace(array('{USER_FULL_NAME}','{user_full_name}'), JFactory::getUser()->get('name'), $subject);
                                $subject = str_replace(array('{VIEW_NAME}','{view_name}'), $data->name, $subject);
                                $subject = str_replace(array('{VIEW_ID}','{view_id}'), $this->_id, $subject);
                                $subject = str_replace(array('{IP}','{ip}'), $_SERVER['REMOTE_ADDR'], $subject);
                                foreach($data->items As $item){
                                    $data->email_subject = str_replace('{'.$item->recName.'}',cbinternal($item->recValue), $data->email_subject);
                                }
                            }
                            
                            $mailer->setSubject($subject);
                            
                            // attachments
                            foreach($data->items As $item){
                                $data->email_recipients_attach_uploads = str_replace('{'.$item->recName.'}',$item->recValue, $data->email_recipients_attach_uploads);
                            }
                            
                            $attachments = explode(';', $data->email_recipients_attach_uploads );
                            
                            $attached = array();
                            foreach($attachments As $attachment){
                                $attachment = explode("\n",str_replace("\r","",trim($attachment)));
                                foreach($attachment As $att){
                                    if(strpos(strtolower($att), '{cbsite}') === 0){
                                        $att = str_replace(array('{cbsite}','{CBSite}'), array(JPATH_SITE, JPATH_SITE), $att);
                                    }
                                    if(JFile::exists(trim($att))){
                                        $attached[] = trim($att);
                                    }
                                }
                                
                            }
                            
                            $mailer->addAttachment($attached);
                            
                            $mailer->isHTML($data->email_html);
                            $mailer->setBody($email_template);
                            
                            if(count($recipients_checked)){
                            
                                $send = $mailer->Send();

                                if ( $send !== true ) {
                                    JFactory::getApplication()->enqueueMessage('Error sending email: ' . $mailer->ErrorInfo, 'error');
                                }
                            }
                            
                            $mailer->ClearAddresses();
                            $mailer->ClearAllRecipients();
                            $mailer->ClearAttachments();
                        }
                    }
                }
                
                return $record_return;
            }
        }
        
        if(!$this->is15){
            $cache = JFactory::getCache('com_content');
            $cache->clean();
            $cache = JFactory::getCache('com_contentbuilder');
            $cache->clean();
            
        }else{
            $cache = JFactory::getCache('com_content');
            $cache->clean();
            $cache = JFactory::getCache('com_contentbuilder');
            $cache->clean();
        }
        
        return false;
    }
    
    function register($bypass_plugin, $bypass_verification_name, $verification_id, $user_id, $the_name_field, $the_username_field, $the_email_field, $the_password_field){
        
        if( $the_name_field === null || $the_email_field === null || $the_password_field === null || $the_username_field === null ){
                     
            return 0;
        }
        
        if( $user_id ){
            
            jimport('joomla.user.helper');
            $db = JFactory::getDBO();
            
            $pw = '';
            if(!empty($the_password_field)){
                $salt  = JUserHelper::genRandomPassword(32);
                $crypt = JUserHelper::getCryptedPassword($the_password_field, $salt);

                $pw = $crypt.':'.$salt;
            }
            
            $db->setQuery("Update #__users Set `name` = ".$db->Quote($the_name_field).", `username` = ".$db->Quote($the_username_field).", `email` = ".$db->Quote($the_email_field)." ".(!empty($pw) ? ", `password` = '$pw'" : '')." Where id = " . intval($user_id));
            $db->query();
            
            return $user_id;
        }
        
        // else execute the registration
        
        if($this->is15){
            return $this->register15($bypass_plugin, $bypass_verification_name, $verification_id, $the_name_field, $the_username_field, $the_email_field, $the_password_field);
        }
        
        JFactory::getLanguage()->load('com_users', JPATH_SITE );
        
        $config = JFactory::getConfig();
        $params = JComponentHelper::getParams('com_users');

        // Initialise the table with JUser.
        $user = new JUser;
        $data = array();
        $data['activation'] = '';
        $data['block'] = 0;
        
        // Prepare the data for the user object.
        $data['email']		= $the_email_field;
        $data['password']	= $the_password_field;
        $data['password_clear']	= $the_password_field;
        $data['name']	        = $the_name_field;
        $data['username']	= $the_username_field;
        $data['groups']         = array($params->get('new_usertype'));
        $useractivation = $params->get('useractivation');
        
        // Check if the user needs to activate their account.
        if (($useractivation == 1) || ($useractivation == 2)) {
                jimport('joomla.user.helper');
                $data['activation'] = JUtility::getHash(JUserHelper::genRandomPassword());
                $data['block'] = 1;
        }

        // Bind the data.
        if (!$user->bind($data)) {
                $this->setError(JText::sprintf('COM_USERS_REGISTRATION_BIND_FAILED', $user->getError()));
                return false;
        }

        // Load the users plugin group.
        JPluginHelper::importPlugin('user');

        // Store the data.
        if (!$user->save()) {
                $this->setError(JText::sprintf('COM_USERS_REGISTRATION_SAVE_FAILED', $user->getError()));
                return false;
        }

        // Compile the notification mail values.
        $data = $user->getProperties();
        
        $data['fromname']	= $config->get('fromname');
        $data['mailfrom']	= $config->get('mailfrom');
        $data['sitename']	= $config->get('sitename');
        $data['siteurl']	= JUri::base();

        // Handle account activation/confirmation emails.
        if ($useractivation == 2)
        {
                // Set the link to confirm the user email.
                $uri = JURI::getInstance();
                $base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
                $data['activate'] = $base.JRoute::_('index.php?option=com_users&task=registration.activate&token='.$data['activation'], false);

                $emailSubject	= JText::sprintf(
                        'COM_USERS_EMAIL_ACCOUNT_DETAILS',
                        $data['name'],
                        $data['sitename']
                );

                $siteurl = $data['siteurl'].'index.php?option=com_users&task=registration.activate&token='.$data['activation'];
                if($bypass_plugin){
                    $siteurl = $data['siteurl'].'index.php?option=com_contentbuilder&controller=verify&plugin='.urlencode($bypass_plugin).'&verification_name='.urlencode($bypass_verification_name).'&token='.$data['activation'].'&verification_id='.$verification_id.'&format=raw';
                }
                
                $emailBody = JText::sprintf(
                        'COM_USERS_EMAIL_REGISTERED_WITH_ADMIN_ACTIVATION_BODY',
                        $data['name'],
                        $data['sitename'],
                        $siteurl,
                        $data['siteurl'],
                        $data['username'],
                        $data['password_clear']
                );
        }
        else if ($useractivation == 1)
        {
                // Set the link to activate the user account.
                $uri = JURI::getInstance();
                $base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
                $data['activate'] = $base.JRoute::_('index.php?option=com_users&task=registration.activate&token='.$data['activation'], false);

                $emailSubject	= JText::sprintf(
                        'COM_USERS_EMAIL_ACCOUNT_DETAILS',
                        $data['name'],
                        $data['sitename']
                );

                $siteurl = $data['siteurl'].'index.php?option=com_users&task=registration.activate&token='.$data['activation'];
                if($bypass_plugin){
                    $siteurl = $data['siteurl'].'index.php?option=com_contentbuilder&controller=verify&plugin='.urlencode($bypass_plugin).'&verification_name='.urlencode($bypass_verification_name).'&token='.$data['activation'].'&verification_id='.$verification_id.'&format=raw';
                }
                
                $emailBody = JText::sprintf(
                        'COM_USERS_EMAIL_REGISTERED_WITH_ACTIVATION_BODY',
                        $data['name'],
                        $data['sitename'],
                        $siteurl,
                        $data['siteurl'],
                        $data['username'],
                        $data['password_clear']
                );
        } else {

                $emailSubject	= JText::sprintf(
                        'COM_USERS_EMAIL_ACCOUNT_DETAILS',
                        $data['name'],
                        $data['sitename']
                );

                $emailBody = JText::sprintf(
                        'COM_USERS_EMAIL_REGISTERED_BODY',
                        $data['name'],
                        $data['sitename'],
                        $data['siteurl']
                );
        }

        // Send the registration email.
        $return = JUtility::sendMail($data['mailfrom'], $data['fromname'], $data['email'], $emailSubject, $emailBody);

        // Check for an error.
        if ($return !== true) {
                $this->setError(JText::_('COM_USERS_REGISTRATION_SEND_MAIL_FAILED'));

                // Send a system message to administrators receiving system mails
                $db = JFactory::getDBO();
                $q = "SELECT id
                        FROM #__users
                        WHERE block = 0
                        AND sendEmail = 1";
                $db->setQuery($q);
                $sendEmail = $db->loadResultArray();
                if (count($sendEmail) > 0) {
                        $jdate = new JDate();
                        // Build the query to add the messages
                        $q = "INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `date_time`, `subject`, `message`)
                                VALUES ";
                        $messages = array();
                        foreach ($sendEmail as $userid) {
                                $messages[] = "(".$userid.", ".$userid.", '".$jdate->toMySQL()."', '".JText::_('COM_USERS_MAIL_SEND_FAILURE_SUBJECT')."', '".JText::sprintf('COM_USERS_MAIL_SEND_FAILURE_BODY', $return, $data['username'])."')";
                        }
                        $q .= implode(',', $messages);
                        $db->setQuery($q);
                        $db->query();
                }
                return false;
        }

        return $user->id;
    }
    
    function register15($bypass_plugin, $bypass_verification_name, $verification_id, $the_name_field, $the_username_field, $the_email_field, $the_password_field){
        
        JFactory::getLanguage()->load('com_user', JPATH_SITE );
        
        $mainframe = JFactory::getApplication();

        // Get required system objects
        $user 		= clone(JFactory::getUser());
        $pathway 	=& $mainframe->getPathway();
        $config		=& JFactory::getConfig();
        $authorize	=& JFactory::getACL();
        $document   =& JFactory::getDocument();

        $usersConfig = &JComponentHelper::getParams( 'com_users' );
        
        // Initialize new usertype setting
        $newUsertype = $usersConfig->get( 'new_usertype' );
        if (!$newUsertype) {
                $newUsertype = 'Registered';
        }

        $post = array(
        'name' => $the_name_field, 
        'username' => $the_username_field, 
        'email' => $the_email_field, 
        'password' => $the_password_field, 
        'password2' => $the_password_field, 
        'task' => 'register_save',
         'id' => '0',
         'gid' => '0',
        );

        // Bind the post array to the user object
        if (!$user->bind( $post, 'usertype' )) {
                JError::raiseError( 500, $user->getError());
        }

        // Set some initial user values
        $user->set('id', 0);
        $user->set('usertype', $newUsertype);
        $user->set('gid', $authorize->get_group_id( '', $newUsertype, 'ARO' ));

        $date =& JFactory::getDate();
        $user->set('registerDate', $date->toMySQL());

        // If user activation is turned on, we need to set the activation information
        $useractivation = $usersConfig->get( 'useractivation' );
        if ($useractivation == '1')
        {
                jimport('joomla.user.helper');
                $user->set('activation', JUtility::getHash( JUserHelper::genRandomPassword()) );
                $user->set('block', '1');
        }

        // If there was an error with registration, set the message and display form
        if ( !$user->save() )
        {
                JError::raiseWarning('', JText::_( $user->getError()));
                return false;
        }

        // Send registration confirmation mail
        $password = $the_password_field;
        $password = preg_replace('/[\x00-\x1F\x7F]/', '', $password); //Disallow control chars in the email
        $this->_sendMail($bypass_plugin, $bypass_verification_name, $verification_id, $user, $password);

        return $user->get('id', 0);
    }
    
    function _sendMail($bypass_plugin, $bypass_verification_name, $verification_id, &$user, $password)
    {
            global $mainframe;

            $db		=& JFactory::getDBO();

            $name 		= $user->get('name');
            $email 		= $user->get('email');
            $username 	= $user->get('username');

            $usersConfig 	= &JComponentHelper::getParams( 'com_users' );
            $sitename 		= $mainframe->getCfg( 'sitename' );
            $useractivation = $usersConfig->get( 'useractivation' );
            $mailfrom 		= $mainframe->getCfg( 'mailfrom' );
            $fromname 		= $mainframe->getCfg( 'fromname' );
            $siteURL		= JURI::base();

            $subject 	= sprintf ( JText::_( 'Account details for' ), $name, $sitename);
            $subject 	= html_entity_decode($subject, ENT_QUOTES);

            $siteurl_ = $siteURL."index.php?option=com_user&task=activate&activation=".$user->get('activation');
            if($bypass_plugin){
                $siteurl_ = $siteURL.'index.php?option=com_contentbuilder&controller=verify&plugin='.urlencode($bypass_plugin).'&verification_name='.urlencode($bypass_verification_name).'&token='.$user->get('activation').'&verification_id='.$verification_id.'&format=raw';
            }
            
            if ( $useractivation == 1 ){
                    $message = sprintf ( JText::_( 'SEND_MSG_ACTIVATE' ), $name, $sitename, $siteurl_, $siteURL, $username, $password);
            } else {
                    $message = sprintf ( JText::_( 'SEND_MSG' ), $name, $sitename, $siteURL);
            }

            $message = html_entity_decode($message, ENT_QUOTES);

            //get all super administrator
            $query = 'SELECT name, email, sendEmail' .
                            ' FROM #__users' .
                            ' WHERE LOWER( usertype ) = "super administrator"';
            $db->setQuery( $query );
            $rows = $db->loadObjectList();

            // Send email to user
            if ( ! $mailfrom  || ! $fromname ) {
                    $fromname = $rows[0]->name;
                    $mailfrom = $rows[0]->email;
            }

            JUtility::sendMail($mailfrom, $fromname, $email, $subject, $message);

            // Send notification to all administrators
            $subject2 = sprintf ( JText::_( 'Account details for' ), $name, $sitename);
            $subject2 = html_entity_decode($subject2, ENT_QUOTES);

            // get superadministrators id
            foreach ( $rows as $row )
            {
                    if ($row->sendEmail)
                    {
                            $message2 = sprintf ( JText::_( 'SEND_MSG_ADMIN' ), $row->name, $sitename, $name, $email, $username);
                            $message2 = html_entity_decode($message2, ENT_QUOTES);
                            JUtility::sendMail($mailfrom, $fromname, $row->email, $subject2, $message2);
                    }
            }
    }
                        
    
    function delete(){
        $items	= JRequest::getVar( 'cid', array(), 'request', 'array' );
        if (empty( $this->_data ))
        {
            $query = $this->_buildQuery();
            $this->_data = $this->_getList($query, 0, 1);

            if(!count($this->_data)){
                JError::raiseError(404, JText::_('COM_CONTENTBUILDER_FORM_NOT_FOUND'));
            }

            foreach($this->_data As $data){
                if(!$this->frontend && $data->display_in == 0){
                    JError::raiseError(404, JText::_('COM_CONTENTBUILDER_RECORD_NOT_FOUND'));
                }else if($this->frontend && $data->display_in == 1){
                    JError::raiseError(404, JText::_('COM_CONTENTBUILDER_RECORD_NOT_FOUND'));
                }
                $data->form_id = $this->_id;
                if($data->type && $data->reference_id){
                    $data->form = contentbuilder::getForm($data->type, $data->reference_id);
                    $res = $data->form->delete($items, $data->form_id);
                    $cnt = count($items);
                    $new_items = array();
                    if($res && $cnt){
                        for($i = 0; $i < $cnt; $i++){
                            $new_items[] = $this->_db->Quote($items[$i]);
                        }
                        $new_items = implode(',', $new_items);
                        $this->_db->setQuery("Delete From #__contentbuilder_list_records Where form_id = ".intval($this->_id)." And record_id In ($new_items)");
                        $this->_db->query();
                        $this->_db->setQuery("Delete From #__contentbuilder_records Where `type` = ".$this->_db->Quote($data->type)." And  `reference_id` = ".$this->_db->Quote($data->form->getReferenceId())." And record_id In ($new_items)");
                        $this->_db->query();
                        if($data->delete_articles){
                            $this->_db->setQuery("Select article_id From #__contentbuilder_articles Where `type` = ".$this->_db->Quote($data->type)." And reference_id = ".$this->_db->Quote($data->form->getReferenceId())." And record_id In ($new_items)");
                            $articles = $this->_db->loadResultArray();
                            if( count($articles) ){
                                $article_items = array();
                                foreach($articles As $article){
                                    $article_items[] = $this->_db->Quote('com_content.article.'.$article);
                                    $dispatcher = JDispatcher::getInstance();
                                    $table = JTable::getInstance('content');
                                    // Trigger the onContentBeforeDelete event.
                                    if(!$this->is15 && $table->load($article)){
                                        $dispatcher->trigger('onContentBeforeDelete', array('com_content.article', $table));
                                    }
                                    $this->_db->setQuery("Delete From #__content Where id = ".intval($article));
                                    $this->_db->query();
                                    // Trigger the onContentAfterDelete event.
                                    $table->reset();
                                    if(!$this->is15){
                                        $dispatcher->trigger('onContentAfterDelete', array('com_content.article', $table));
                                    }
                                }
                                if(!$this->is15){
                                    $this->_db->setQuery("Delete From #__assets Where `name` In (".implode(',', $article_items).")");
                                    $this->_db->query();
                                }
                            }
                        }
                        
                        $this->_db->setQuery("Delete From #__contentbuilder_articles Where `type` = ".$this->_db->Quote($data->type)." And reference_id = ".$this->_db->Quote($data->form->getReferenceId())." And record_id In ($new_items)");
                        $this->_db->query();
                    }
                }
            }
        }
        
        if(!$this->is15){
            $cache = JFactory::getCache('com_content');
            $cache->clean();
            $cache = JFactory::getCache('com_contentbuilder');
            $cache->clean();
            
        }else{
            $cache = JFactory::getCache('com_content');
            $cache->clean();
            $cache = JFactory::getCache('com_contentbuilder');
            $cache->clean();
        }
    }
    
    function change_list_states(){
        
        $this->_db->setQuery('Select reference_id From #__contentbuilder_forms Where id = '.intval($this->_id));
        $reference_id = $this->_db->loadResult(); 
        if(!$reference_id){
            return;
        }
        
        // prevent from changing to an unpublished state
        $this->_db->setQuery("Select id, action From #__contentbuilder_list_states Where published = 1 And id = ".JRequest::getInt('list_state', 0)." And form_id = " . $this->_id);
        $res = $this->_db->loadAssoc();
        if(!is_array($res)){
            return;
        }
        
        JPluginHelper::importPlugin('contentbuilder_listaction', $res['action']);
        $dispatcher = JDispatcher::getInstance();
        
        $items	= JRequest::getVar( 'cid', array(), 'request', 'array' );
        
        $result = $dispatcher->trigger('onBeforeAction', array($this->_id, $items));
        $error = implode('',$result);
        
        if($error){
            JFactory::getApplication()->enqueueMessage($error);
        }
        
        foreach($items As $item){
            $this->_db->setQuery("Select id From #__contentbuilder_list_records Where form_id = ".$this->_id." And record_id = " . $this->_db->Quote($item));
            $res = $this->_db->loadResult();
            if(!$res){
                $this->_db->setQuery("Insert Into #__contentbuilder_list_records (state_id, form_id, record_id, reference_id) Values (".JRequest::getInt('list_state', 0).", ".$this->_id.", ".$this->_db->Quote($item).", ".$this->_db->Quote($reference_id).")");
                $this->_db->query();
            }else{
                $this->_db->setQuery("Update #__contentbuilder_list_records Set state_id = ".JRequest::getInt('list_state', 0) . " Where form_id = ".$this->_id." And record_id = " . $this->_db->Quote($item));
                $this->_db->query();
            }
        }
        
        $result = $dispatcher->trigger('onAfterAction', array($this->_id, $items, $error));
        $error = implode('',$result);
        
        if($error){
            JFactory::getApplication()->enqueueMessage($error);
        }
    }
    
    function change_list_language(){
        $this->_db->setQuery('Select reference_id,`type` From #__contentbuilder_forms Where id = '.intval($this->_id));
        $typeref = $this->_db->loadAssoc();
         
        if(!is_array($typeref)){
            return;
        }
        
        $reference_id = $typeref['reference_id'];
        $type = $typeref['type'];
        
        $items	= JRequest::getVar( 'cid', array(), 'request', 'array' );
        
        $sef = '';
        jimport('joomla.version');
        $version = new JVersion();

        if(version_compare($version->getShortVersion(), '1.6', '>=')){

            $this->_db->setQuery("Select sef From #__languages Where published = 1 And lang_code = " . $this->_db->Quote(JRequest::getVar('list_language','*')));
            $sef = $this->_db->loadResult();

        } else {

            $codes = contentbuilder::getLanguageCodes();
            foreach($codes As $code){
                if($code == JRequest::getVar('list_language','*')){
                    $sef = explode('-', $code);
                    if(count($sef)){
                        $sef = strtolower($sef[0]);
                    }
                    break;
                }
            }
        }
        
        foreach($items As $item){
            $this->_db->setQuery("Select id From #__contentbuilder_records Where `type` = ".$this->_db->Quote($type)." And `reference_id` = ".$this->_db->Quote($reference_id)." And record_id = " . $this->_db->Quote($item));
            $res = $this->_db->loadResult();
            if(!$res){
                $this->_db->setQuery("Insert Into #__contentbuilder_records (`type`,lang_code, sef, record_id, reference_id) Values (".$this->_db->Quote($type).",".$this->_db->Quote(JRequest::getVar('list_language','*')).", ".$this->_db->Quote($sef).", ".$this->_db->Quote($item).", ".$this->_db->Quote($reference_id).")");
                $this->_db->query();
            }else{
                $this->_db->setQuery("Update #__contentbuilder_records Set sef = ".$this->_db->Quote($sef).", lang_code = ".$this->_db->Quote(JRequest::getVar('list_language','*')) . " Where `type` = ".$this->_db->Quote($type)." And `reference_id` = ".$this->_db->Quote($reference_id)." And record_id = " . $this->_db->Quote($item));
                $this->_db->query();
            }
            
            if(version_compare($version->getShortVersion(), '1.6', '>=')){
                $this->_db->setQuery("Update #__contentbuilder_articles As articles, #__content As content Set content.language = ".$this->_db->Quote(JRequest::getVar('list_language','*')) . " Where ( content.state = 1 Or content.state = 0 ) And content.id = articles.article_id And articles.`type` = ".intval($type)." And articles.reference_id = ".$this->_db->Quote($reference_id)." And articles.record_id = " . $this->_db->Quote($item));
                $this->_db->query();
            }
            else{
                $this->_db->setQuery("Select attribs From #__contentbuilder_articles As articles, #__content As content Where ( content.state = 1 Or content.state = 0 ) And content.id = articles.article_id And articles.form_id = ".intval($this->_id)." And articles.record_id = " . $this->_db->Quote($item));
                $attribs = $this->_db->loadResult();
                if($attribs){
                    $params = new JParameter($attribs);
                    $params->set('language', JRequest::getVar('list_language','*'));
                    $this->_db->setQuery("Update #__contentbuilder_articles As articles, #__content As content Set attribs = ".$this->_db->Quote($params->toString()) . " Where ( content.state = 1 Or content.state = 0 ) And content.id = articles.article_id And articles.`type` = ".intval($type)." And articles.reference_id = ".$this->_db->Quote($reference_id)." And articles.record_id = " . $this->_db->Quote($item));
                    $this->_db->query();
                }
            }
        }

        $cache = JFactory::getCache('com_content');
        $cache->clean();
        $cache = JFactory::getCache('com_contentbuilder');
        $cache->clean();
    }
    
    function change_list_publish(){
        $this->_db->setQuery('Select reference_id,`type` From #__contentbuilder_forms Where id = '.intval($this->_id));
        $typeref = $this->_db->loadAssoc();
         
        if(!is_array($typeref)){
            return;
        }
        
        $reference_id = $typeref['reference_id'];
        $type = $typeref['type'];
        
        $items	= JRequest::getVar( 'cid', array(), 'request', 'array' );
        
        $this->_db->setQuery("SET @ids := null");
        $this->_db->query();
        
        $created_up = JFactory::getDate();
        $created_up = $created_up->toMySQL();
        
        foreach($items As $item){
            $this->_db->setQuery("Select id, publish_up From #__contentbuilder_records Where `type` = ".$this->_db->Quote($type)." And `reference_id` = ".$this->_db->Quote($reference_id)." And record_id = " . $this->_db->Quote($item));
            $res = $this->_db->loadAssoc();
            
            if(!is_array($res)){
                $this->_db->setQuery("Insert Into #__contentbuilder_records (`type`,published, record_id, reference_id) Values (".$this->_db->Quote($type).",".(JRequest::getInt('list_publish', 0) ? 1 : 0).", ".$this->_db->Quote($item).", ".$this->_db->Quote($reference_id).")");
                $this->_db->query();
            }else{
                $this->_db->setQuery("Update #__contentbuilder_records Set is_future = 0, ".(JRequest::getInt('list_publish', 0) ? "publish_up = '".$created_up."',publish_down = '0000-00-00 00:00:00'," : "publish_up = '0000-00-00 00:00:00',publish_down = '0000-00-00 00:00:00',")." published = ".(JRequest::getInt('list_publish', 0) ? 1 : 0) . " Where `type` = ".$this->_db->Quote($type)." And `reference_id` = ".$this->_db->Quote($reference_id)." And record_id = " . $this->_db->Quote($item));
                $this->_db->query();
            }
            
            $this->_db->setQuery("Update #__contentbuilder_articles As articles, #__content As content Set ".(JRequest::getInt('list_publish', 0) ? "content.publish_up = '".$created_up."',publish_down = '0000-00-00 00:00:00'," : "content.publish_up = '".(is_array($res) ? $res['publish_up'] : $created_up)."',publish_down = '0000-00-00 00:00:00',")." content.state = ".(JRequest::getInt('list_publish', 0) ? 1 : 0) . " Where ( SELECT @ids := CONCAT_WS(',', content.id, @ids) ) And ( content.state = 1 Or content.state = 0 ) And content.id = articles.article_id And articles.`type` = ".intval($type)." And articles.reference_id = ".$this->_db->Quote($reference_id)." And articles.record_id = " . $this->_db->Quote($item));
            $this->_db->query();
        }
        $this->_db->setQuery("SELECT @ids");
        $select_ids = $this->_db->loadResult();
        if( $select_ids ){
            $affected_articles = explode(',',$this->_db->loadResult());
        }
        if(!$this->is15){
            $cache = JFactory::getCache('com_content');
            $cache->clean();
            $cache = JFactory::getCache('com_contentbuilder');
            $cache->clean();
            
            // Trigger the onContentChangeState event.
            $dispatcher = JDispatcher::getInstance();
            $result = $dispatcher->trigger('onContentChangeState', array('com_content.article', $affected_articles, JRequest::getInt('list_publish', 0)));
        
        }else{
            $cache = JFactory::getCache('com_content');
            $cache->clean();
            $cache = JFactory::getCache('com_contentbuilder');
            $cache->clean();
        }
    }
}
