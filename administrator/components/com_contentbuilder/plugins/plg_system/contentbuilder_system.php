<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        http://www.crosstec.de
 * @license     GNU/GPL
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );


class  plgSystemContentbuilder_system extends JPlugin
{
        private $caching = 0;
        
	function __construct( &$subject, $params )
        {
            parent::__construct($subject, $params);
            
        }
        
        function onBeforeRender(){
            $plugin = JPluginHelper::getPlugin('system', 'contentbuilder_system');
            jimport( 'joomla.html.parameter' );
            $pluginParams = new JParameter($plugin->params);

            if($pluginParams->def('nocache', 1)){
                JFactory::getConfig()->setValue('config.caching', $this->caching);
            }
        }
        
        function onAfterDispatch(){
            
            jimport('joomla.filesystem.file');
            jimport('joomla.filesystem.folder');
        
            if(!JFile::exists(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'contentbuilder.php'))
            {
                return;
            }
            
            $db   = JFactory::getDBO();
            $app  = JFactory::getApplication();
            
            if($app->isSite()){
                
                // loading the required themes, if any
                $body = JFactory::getDocument()->getBuffer('component');
                preg_match_all("/<!--\(cbArticleId:(\d{1,})\)-->/si", $body, $matched_ids);
                
                $ids = array();
                if(isset($matched_ids[1]) && is_array($matched_ids[1])){
                    foreach( $matched_ids[1] As $id ){
                        if( !in_array(intval($id), $ids) ){
                            $ids[] = intval($id);
                        }
                    }
                }
                $the_ids = implode(',', $ids);
                
                if($the_ids){
                    JFactory::getDocument()->addScript(JURI::root(true).'/components/com_contentbuilder/assets/js/contentbuilder.js');
                    $db->setQuery("Select Distinct forms.theme_plugin From #__contentbuilder_forms As forms, #__contentbuilder_articles As articles, #__content As content Where forms.id = articles.form_id And articles.article_id In (".$the_ids.") And content.id = articles.article_id And (content.state = 1 Or content.state = 0)");
                    $themes = $db->loadResultArray();
                    foreach($themes As $theme){
                        if($theme){
                            JPluginHelper::importPlugin('contentbuilder_themes', $theme);
                            $dispatcher = JDispatcher::getInstance();
                            $results_css = $dispatcher->trigger('getContentTemplateCss', array());
                            $results_js  = $dispatcher->trigger('getContentTemplateJavascript', array());
                            JFactory::getDocument()->addStyleDeclaration(implode('',$results_css));
                            JFactory::getDocument()->addScriptDeclaration(implode('',$results_js));
                        }
                    }
                }
                // theme loading end
                
                $option = JRequest::getCmd('option', '');
                $view   = JRequest::getCmd('view', '');
                $task   = JRequest::getCmd('task', '');
                $layout = JRequest::getCmd('layout', '');
                $id     = JRequest::getVar('id', 0);
                $id     = explode(':', $id);
                $id     = intval($id[0]);
                $a_id   = JRequest::getVar('a_id', 0);
                $a_id   = explode(':', $a_id);
                $a_id   = intval($a_id[0]);
                
                $plugin = JPluginHelper::getPlugin('system', 'contentbuilder_system');
                jimport( 'joomla.html.parameter' );
                $pluginParams = new JParameter($plugin->params);
                
                // if somebody tries to submit an article through the built-in joomla content submit
                if( $pluginParams->def('disable_new_articles', 0) && trim(JRequest::getCmd('option','')) == 'com_content' && ( trim(JRequest::getCmd('task','')) == 'new' || trim(JRequest::getCmd('task','')) == 'article.add' || ( trim(JRequest::getCmd('view','')) == 'article' && trim(JRequest::getCmd('layout','')) == 'form' ) || ( trim(JRequest::getCmd('view','')) == 'form' && trim(JRequest::getCmd('layout','')) == 'edit' ) && $a_id <= 0 ) ){
                    JFactory::getLanguage()->load('com_contentbuilder');
                    JFactory::getApplication()->redirect('index.php', JText::_('COM_CONTENTBUILDER_PERMISSIONS_NEW_NOT_ALLOWED'), 'error');
                }
                
                // redirect to content edit if there is a record existing for this article
                if( $option == 'com_content' && ( ( $id && $view == 'article' && $task == 'edit' ) || ( $a_id && $view == 'form' && $layout == 'edit' ) ) ){
                    $id = $id ? $id : $a_id;
                    $db->setQuery("Select article.record_id, article.form_id From #__contentbuilder_articles As article, #__content As content Where content.id = " . intval($id) . " And (content.state = 0 Or content.state = 1) And article.article_id = content.id");
                    $article = $db->loadAssoc();
                    if(is_array($article)){
                        JFactory::getApplication()->redirect('index.php?option=com_contentbuilder&controller=edit&id='.$article['form_id']."&record_id=".$article['record_id']."&jsback=1&Itemid=".JRequest::getInt('Itemid',0));
                    }
                }
            }
        }
        
        function onAfterRoute(){
            
            jimport('joomla.filesystem.file');
            jimport('joomla.filesystem.folder');
        
            if(!JFile::exists(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'contentbuilder.php'))
            {
                return;
            }
            
            // register non-existent records
            if( in_array(JRequest::getVar('option', ''), array('com_contentbuilder', 'com_content')) ){
                
                $db = JFactory::getDBO();
                
                require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'contentbuilder.php');
                $db->setQuery("Select `type`, `reference_id` From #__contentbuilder_forms Where published = 1");
                $views = $db->loadAssocList();
                $typeview = array();
                foreach($views As $view){
                    if(!isset($typeview[$view['type'].$view['reference_id']])){
                        $typeview[$view['type'].$view['reference_id']] = true;
                        $form = contentbuilder::getForm($view['type'], $view['reference_id']);
                        if(is_object($form)){
                            $form->synchRecords();
                        }
                    }
                }
            }
            
            if(JRequest::getCmd('option', '') == 'com_content' || JRequest::getCmd('option', '') == 'com_contentbuilder'){
                // managing published states
                
                $db = JFactory::getDBO();
                $date = JFactory::getDate();
                
                $db->setQuery("Update #__contentbuilder_records Set published = 1 Where is_future = 1 And publish_up <> '0000-00-00 00:00:00' And publish_up <= '".$date->toMySQL()."'");
                $db->query();
                
                $db->setQuery("Update #__contentbuilder_records Set published = 0 Where publish_down <> '0000-00-00 00:00:00' And publish_down <= '".$date->toMySQL()."'");
                $db->query();
                
                // published states END
            }
            
            // joomla 1.5 and following obviously has problems when logging out and being in list view and the menu item access being registered.
            // J! is then trying to redirect to com_content (for non-obvious reasons), using the view variable orginally used in contentbuilder and then it will 
            // throw an error 500, view not found
            // this will get rid of the view parameter and pass the rest of the url to the return parameter
            $enc = base64_decode(JRequest::getVar('return', '', 'method', 'base64'));
            if(is_string($enc)){
                $enc = explode('?',$enc);
                count($enc) > 1 ? parse_str($enc[1], $out) : $out = array();
                if(isset($out['option']) && $out['option'] == 'com_contentbuilder'){
                    $i = 0;
                    $length = count($out);
                    $return = '';
                    foreach($out As $key => $value){
                        if(strtolower($key) != 'view'){
                            $return .= $key.'='.$value.($i + 1 < $length ? '&' : '');
                        }
                        $i++;
                    }
                    JRequest::setVar('return', base64_encode('index.php'.($return ? '?' : '').$return));
                }
            }
            
            if( in_array(JRequest::getVar('option'), array('com_content') ) ){
                
                $plugin = JPluginHelper::getPlugin('system', 'contentbuilder_system');
                jimport( 'joomla.html.parameter' );
                $pluginParams = new JParameter($plugin->params);
                
                if($pluginParams->def('nocache', 1)){
                    $this->caching = JFactory::getConfig()->getValue('config.caching');
                    JFactory::getConfig()->setValue('config.caching', 0);
                }
            }
            
            if(JRequest::getVar('option') == 'com_contentbuilder'){
                
                JFactory::getDBO()->setQuery("
                    Update 
                        #__contentbuilder_records As records,
                        #__contentbuilder_forms As forms,
                        #__contentbuilder_registered_users As cbusers,
                        #__users As users
                    Set 
                        records.published = 0
                    Where
                        records.reference_id = forms.reference_id
                    And
                        records.published = 1
                    And
                        records.`type` = forms.`type`
                    And
                        forms.act_as_registration = 1
                    And
                        forms.id = cbusers.form_id
                    And
                        records.record_id = cbusers.record_id
                    And
                      (
                        (
                            users.id = cbusers.user_id
                          And
                            users.block = 1
                        )
                      )
                    ");
                JFactory::getDBO()->query();
                
                JFactory::getDBO()->setQuery("
                    Update 
                        #__contentbuilder_records As records,
                        #__contentbuilder_forms As forms,
                        #__contentbuilder_registered_users As cbusers,
                        #__users As users
                    Set 
                        records.published = forms.auto_publish
                    Where
                        records.reference_id = forms.reference_id
                    And
                        records.published = 0
                    And
                        records.`type` = forms.`type`
                    And
                        forms.act_as_registration = 1
                    And
                        forms.id = cbusers.form_id
                    And
                        records.record_id = cbusers.record_id
                    And
                        users.id = cbusers.user_id
                    And
                        users.block = 0
                    ");
                JFactory::getDBO()->query();
            }
        }
        
        function onAfterInitialise()
	{
            $this->onAfterInitialize();
        }
        
	function onAfterInitialize()
	{
            jimport('joomla.filesystem.file');
            jimport('joomla.filesystem.folder');
        
            if(!JFile::exists(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'contentbuilder.php'))
            {
                return;
            }
            
            $app  = JFactory::getApplication();
            
            if(!$app->isSite()){
               return; 
            }
            
            // synch the records if there are any changes
            if($app->isSite()){

                $db         = JFactory::getDBO();
                $user       = JFactory::getUser();
                
                $db->setQuery("
                    Update
                        #__contentbuilder_articles As articles,
                        #__content As content, 
                        #__contentbuilder_forms As forms,
                        #__contentbuilder_registered_users As cbusers,
                        #__users As users
                    Set 
                        content.state = 0
                    Where 
                        articles.article_id = content.id
                    And
                        content.state = 1
                    And
                        articles.form_id = forms.id
                    And
                        forms.act_as_registration = 1
                    And
                        forms.id = cbusers.form_id
                    And
                        content.created_by = cbusers.user_id
                    And
                      (
                        (
                            users.id = cbusers.user_id
                          And
                            users.block = 1
                        )
                      )
                    ");
                $db->query();
                
                $db->setQuery("
                    Update 
                        #__contentbuilder_articles As articles,
                        #__content As content, 
                        #__contentbuilder_forms As forms,
                        #__contentbuilder_records As records,
                        #__contentbuilder_registered_users As cbusers,
                        #__users As users
                    Set 
                        content.state = forms.auto_publish
                    Where 
                        articles.article_id = content.id
                    And
                        content.state = 0
                    And
                        articles.form_id = forms.id
                    And
                        forms.act_as_registration = 1
                    And
                        forms.id = cbusers.form_id
                    And
                        content.created_by = cbusers.user_id
                    And
                        users.id = cbusers.user_id
                    And
                        records.record_id = cbusers.record_id
                    And
                        records.`type` = forms.`type`
                    And
                        users.block = 0
                    ");
                $db->query();
                
                $plugin = JPluginHelper::getPlugin('system', 'contentbuilder_system');
                jimport( 'joomla.html.parameter' );
                
                $pluginParams = new JParameter($plugin->params);
                
                require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'contentbuilder.php');
                
                $db->setQuery("
                Select 
                    form.id As form_id,
                    form.act_as_registration,
                    form.default_category,
                    form.registration_name_field, 
                    form.registration_username_field, 
                    form.registration_email_field, 
                    form.registration_email_repeat_field, 
                    form.`last_update`,
                    article.`article_id`,
                    form.`title_field`,
                    form.`create_articles`,
                    form.`name`,
                    form.`use_view_name_as_title`,
                    form.`protect_upload_directory`,
                    form.`reference_id`,
                    records.`record_id`,
                    form.`type`,
                    form.`published_only`,
                    form.`own_only`,
                    form.`own_only_fe`,
                    records.`last_update` As record_last_update,
                    article.`last_update` As article_last_update
                From
                    #__contentbuilder_records As records
                    Left Join #__contentbuilder_forms As form On ( form.`type` = records.`type` And form.reference_id = records.reference_id )
                    Left Join #__contentbuilder_articles As article On ( form.`type` = records.`type` And form.reference_id = records.reference_id And article.form_id = form.id And article.record_id = records.record_id )
                    Left Join #__content As content On ( form.`type` = records.`type` And form.reference_id = records.reference_id And article.article_id = content.id And article.form_id = form.id And article.record_id = records.record_id )
                Where 
                    form.`published` = 1
                And
                    form.create_articles = 1
                And
                    form.`type` = records.`type`
                And 
                    form.reference_id = records.reference_id
                And
                   (
                     (
                        article.form_id = form.id 
                      And 
                        article.record_id = records.record_id
                      And 
                        article.article_id = content.id 
                      And 
                        ( content.state = 1 Or content.state = 0 )
                      And
                      (
                        form.`last_update` > article.`last_update`   
                       Or
                        records.`last_update` > article.`last_update`
                      )
                     )
                     Or
                     (
                        form.id Is Not Null And records.id Is Not Null And content.id Is Null And article.id Is Null
                     )
                   )
                Limit " . intval($pluginParams->def('limit_per_turn', 50)));
                $list = $db->loadAssocList();
                
                if(isset($list[0])){
                    $lang = JFactory::getLanguage();
                    $lang->load('com_contentbuilder', JPATH_ADMINISTRATOR);
                }
                
                $jdate = JFactory::getDate();
                $now   = $jdate->toMySQL();
                
                foreach($list As $data){
                
                    if(is_array($data)){

                        $form = contentbuilder::getForm($data['type'], $data['reference_id']);
                        if(!$form || !$form->exists){
                            return;
                        }

                        // creating the article
                        if($data['create_articles']){
                            
                            $data['labels'] = $form->getElementLabels();
                            $ids = array();
                            foreach ($data['labels'] As $reference_id => $label) {
                                $ids[] = $db->Quote($reference_id);
                            }

                            if (count($ids)) {
                                $db->setQuery("Select Distinct `label`, reference_id From #__contentbuilder_elements Where form_id = " . intval($data['form_id']) . " And reference_id In (" . implode(',', $ids) . ") And published = 1 Order By ordering");
                                $rows = $db->loadAssocList();
                                $ids = array();
                                foreach ($rows As $row) {
                                    $ids[] = $row['reference_id'];
                                }
                            }
                            
                            $data['items'] = $form->getRecord($data['record_id'], false, -1, true);

                            $article_id = contentbuilder::createArticle($data['form_id'], $data['record_id'], $data['items'], $ids, $data['title_field'], $form->getRecordMetadata($data['record_id']), array(), false, 1, $data['default_category']);
                
                            if($article_id){
                                $db->setQuery("Update #__contentbuilder_articles Set `last_update`=".$db->Quote($now) . " Where article_id = " . $db->Quote($article_id) . " And record_id = " . $db->Quote($data['record_id']) . " And form_id = " . $db->Quote($data['form_id']));
                                $db->query();
                            }
                        }
                    }
                }
            }
	}
}
