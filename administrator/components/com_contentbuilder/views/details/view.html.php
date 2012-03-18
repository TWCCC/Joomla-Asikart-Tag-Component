<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        http://www.crosstec.de
 * @license     GNU/GPL
*/

// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');
require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'classes' . DS . 'contentbuilder.php');

class ContentbuilderViewDetails extends JView
{
    function display($tpl = null)
    {
        // Get data from the model
        $subject = $this->get('Data');
        
        if(!class_exists('cbFeMarker')){
            echo '
            <style type="text/css">
            .icon-48-logo_left { background-image: url(../administrator/components/com_contentbuilder/views/logo_left.png); }
            </style>
            ';
            JToolBarHelper::title(   '<img src="components/com_contentbuilder/views/logo_right.png" alt="" align="top" /> <span style="display:inline-block; vertical-align:middle"> :: ' . $subject->page_title . '</span>', 'logo_left.png' );
        }
        
        $event = new stdClass();
        
        JFactory::getDBO()->setQuery("Select articles.`article_id` From #__contentbuilder_articles As articles, #__content As content Where content.id = articles.article_id And (content.state = 1 Or content.state = 0) And articles.form_id = " . intval($subject->form_id) . " And articles.record_id = " . JFactory::getDBO()->Quote($subject->record_id));
        $article = JFactory::getDBO()->loadResult();

        $table = JTable::getInstance('content');

        jimport('joomla.version');
        $version = new JVersion();
        
        // required for pagebreak plugin
        JRequest::setVar('view', 'article');
        
        if(version_compare($version->getShortVersion(), '1.6', '>=')){

            require_once(JPATH_SITE . DS . 'components' . DS . 'com_content' . DS . 'helpers' . DS . 'route.php');
            
            $isNew = true;
            if ($article > 0) {
                $table->load($article);
                $isNew = false;
            }

            $table->cbrecord = $subject;
            $table->text = $table->cbrecord->template;
            
            $alias = $table->alias ? contentbuilder::stringURLUnicodeSlug($table->alias) : contentbuilder::stringURLUnicodeSlug($subject->page_title);
            if(trim(str_replace('-','',$alias)) == '') {
                    $datenow = JFactory::getDate();
                    $alias = $datenow->toFormat("%Y-%m-%d-%H-%M-%S");
            }
            
            // we pass the slug with a flag in the end, and see in the end if the slug has been used in the output
            $table->slug = ($article > 0 ? $article : 0) . ':' . $alias . ':contentbuilder_slug_used';
            
            $registry = new JRegistry;
            $registry->loadJSON($table->attribs);

            JPluginHelper::importPlugin('content');
            $dispatcher = JDispatcher::getInstance();
            
            // seems to be a joomla bug. if sef urls is enabled, "start" is used for paging in articles, else "limitstart" will be used
            $limitstart = JRequest::getVar('limitstart', 0, '', 'int');
            $start      = JRequest::getVar('start', 0, '', 'int');
            
            $dispatcher->trigger('onContentPrepare', array ('com_content.article', &$table, &$registry, $limitstart ? $limitstart : $start));
            $subject->template = $table->text;

            $results = $dispatcher->trigger('onContentAfterTitle', array('com_content.article', &$table, &$registry, $limitstart ? $limitstart : $start));
            $event->afterDisplayTitle = trim(implode("\n", $results));

            $results = $dispatcher->trigger('onContentBeforeDisplay', array('com_content.article', &$table, &$registry, $limitstart ? $limitstart : $start));
            $event->beforeDisplayContent = trim(implode("\n", $results));

            $results = $dispatcher->trigger('onContentAfterDisplay', array('com_content.article', &$table, &$registry, $limitstart ? $limitstart : $start));
            $event->afterDisplayContent = trim(implode("\n", $results));

            // if the slug has been used, we would like to stay in com_contentbuilder, so we re-arrange the resulting url a little
            if(strstr($subject->template, 'contentbuilder_slug_used') !== false ){
                
                $matches = array(array(),array());
                preg_match_all("/\\\"([^\"]*contentbuilder_slug_used[^\"]*)\\\"/i", $subject->template, $matches);
                
                foreach($matches[1] As $match){
                    $sub = '';
                    $parameters = explode('?', $match);
                    if(count($parameters) == 2){
                        $parameters[1] = str_replace('&amp;','&',$parameters[1]);
                        $parameter = explode('&', $parameters[1]);
                        foreach($parameter As $par){
                            $keyval = explode('=',$par);
                            if($keyval[0] != '' && $keyval[0] != 'option' && $keyval[0] != 'id' && $keyval[0] != 'record_id' && $keyval[0] != 'view' && $keyval[0] != 'catid' && $keyval[0] != 'Itemid' && $keyval[0] != 'lang'){
                                $sub .= '&'.$keyval[0].'='.(isset($keyval[1]) ? $keyval[1] : '');
                            }
                        }
                    }
                    $subject->template = str_replace($match, JRoute::_('index.php?option=com_contentbuilder&controller=details&id='.JRequest::getInt('id').'&record_id='.JRequest::getCmd('record_id','').'&Itemid='.JRequest::getInt('Itemid', 0) . $sub ), $subject->template);
                }
            }
            
            // the same for the case a toc has been created
            if(isset($table->toc) && strstr($table->toc, 'contentbuilder_slug_used') !== false ){
                
                preg_match_all("/\\\"([^\"]*contentbuilder_slug_used[^\"]*)\\\"/i", $table->toc, $matches);
                
                foreach($matches[1] As $match){
                    $sub = '';
                    $parameters = explode('?', $match);
                    if(count($parameters) == 2){
                        $parameters[1] = str_replace('&amp;','&',$parameters[1]);
                        $parameter = explode('&', $parameters[1]);
                        foreach($parameter As $par){
                            $keyval = explode('=',$par);
                            if($keyval[0] != '' && $keyval[0] != 'option' && $keyval[0] != 'id' && $keyval[0] != 'record_id' && $keyval[0] != 'view' && $keyval[0] != 'catid' && $keyval[0] != 'Itemid'  && $keyval[0] != 'lang'){
                                $sub .= '&'.$keyval[0].'='.(isset($keyval[1]) ? $keyval[1] : '');
                            }
                        }
                    }
                    $table->toc = str_replace($match, JRoute::_('index.php?option=com_contentbuilder&controller=details&id='.JRequest::getInt('id').'&record_id='.JRequest::getCmd('record_id','').'&Itemid='.JRequest::getInt('Itemid', 0) . $sub ), $table->toc);
                }
            }
            
        }else{

            $params =& JComponentHelper::getParams('com_content');

            $isNew = true;
            if ($article > 0) {
                $table->load($article);
                $isNew = false;
            }
            
            $params->merge($table->attribs);

            $table->cbrecord = $subject;
            $table->text = $table->cbrecord->template;

            JPluginHelper::importPlugin('content');
            $dispatcher = JDispatcher::getInstance();
            $dispatcher->trigger('onPrepareContent', array (& $table, & $params, JRequest::getVar('limitstart', 0, '', 'int')));
            $subject->template = $table->text;
            
            $results = $dispatcher->trigger('onAfterDisplayTitle', array (&$table, &$params, JRequest::getVar('limitstart', 0, '', 'int')));
            $event->afterDisplayTitle = trim(implode("\n", $results));

            $results = $dispatcher->trigger('onBeforeDisplayContent', array (&$table, &$params, JRequest::getVar('limitstart', 0, '', 'int')));
            $event->beforeDisplayContent = trim(implode("\n", $results));

            $results = $dispatcher->trigger('onAfterDisplayContent', array (&$table, &$params, JRequest::getVar('limitstart', 0, '', 'int')));
            $event->afterDisplayContent = trim(implode("\n", $results));

        }
        
        if(!isset($table->toc)){
            $table->toc = '';
        }
        
        $pattern = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
	$subject->template = preg_replace($pattern, '', $subject->template);
        
        JPluginHelper::importPlugin('contentbuilder_themes', $subject->theme_plugin);
        $dispatcher = JDispatcher::getInstance();
        $results = $dispatcher->trigger('getContentTemplateCss', array());
        $this->assignRef( 'theme_css', implode('', $results));
        
        JPluginHelper::importPlugin('contentbuilder_themes', $subject->theme_plugin);
        $dispatcher = JDispatcher::getInstance();
        $results = $dispatcher->trigger('getContentTemplateJavascript', array());
        $this->assignRef( 'theme_js', implode('', $results));
        
        $this->assignRef( 'toc', $table->toc );
        $this->assignRef( 'event', $event );
        
        $this->assignRef( 'show_page_heading', $subject->show_page_heading );
        $this->assignRef( 'tpl', $subject->template );
        $this->assignRef( 'page_title', $subject->page_title );
        $this->assignRef( 'created', $subject->created );
        $this->assignRef( 'created_by', $subject->created_by );
        $this->assignRef( 'modified', $subject->modified );
        $this->assignRef( 'modified_by', $subject->modified_by );
        
        $this->assignRef( 'metadesc', $subject->metadesc );
        $this->assignRef( 'metakey', $subject->metakey );
        $this->assignRef( 'author', $subject->author );
        $this->assignRef( 'rights', $subject->rights );
        $this->assignRef( 'robots', $subject->robots );
        $this->assignRef( 'xreference', $subject->xreference );
        
        $this->assignRef( 'print_button', $subject->print_button );
        $this->assignRef( 'show_back_button', $subject->show_back_button );
        parent::display($tpl);
    }
}
