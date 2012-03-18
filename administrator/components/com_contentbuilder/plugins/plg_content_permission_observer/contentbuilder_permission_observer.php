<?php
/**
* @version 1.0
* @package ContentBuilder Image Scale
* @copyright (C) 2011 by Markus Bopp
* @license Released under the terms of the GNU General Public License
**/

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

jimport( 'joomla.plugin.plugin' );

class plgContentContentbuilder_permission_observer extends JPlugin {

    function __construct( &$subject, $params )
    {
        parent::__construct($subject, $params);
    }

    /**
     * Joomla 1.5 compatibility
     */
    function onPrepareContent(&$article, &$params, $limitstart = 0 )
    {
        $this->onContentPrepare('', $article, $params, $limitstart);
    }

    function onContentPrepare($context, &$article, &$params, $limitstart = 0) {
        
        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.folder');

        if(!JFile::exists(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'contentbuilder.php'))
        {
            return true;
        }
        
        if (isset($article->id) && $article->id) {
            
            $frontend = true;
            if (JFactory::getApplication()->isAdmin()) {
                $frontend = false;
            }
            
            $db = JFactory::getDBO();
            $db->setQuery("Select form.`reference_id`,article.`record_id`,article.`form_id`,form.`type`,form.`published_only`,form.`own_only`,form.`own_only_fe` From #__contentbuilder_articles As article, #__contentbuilder_forms As form Where form.`published` = 1 And form.id = article.`form_id` And article.`article_id` = " . $article->id);
            $data = $db->loadAssoc();

            require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'contentbuilder.php');
            $form = contentbuilder::getForm($data['type'], $data['reference_id']);
            
            if(!$form || !$form->exists){
                return true;
            }
            
            if ($form && !( JRequest::getVar('option','') == 'com_contentbuilder' && JRequest::getVar('controller','') == 'edit' )) {
                
                JFactory::getLanguage()->load('com_contentbuilder');
                contentbuilder::setPermissions($data['form_id'], $data['record_id'], $frontend ? '_fe' : '');
                
                if(JRequest::getCmd('view') == 'article'){
                   contentbuilder::checkPermissions('view', JText::_('COM_CONTENTBUILDER_PERMISSIONS_VIEW_NOT_ALLOWED'), $frontend ? '_fe' : '');
                }else{
                    if($frontend){
                        if(!contentbuilder::authorizeFe('view')){
                            $article->text = JText::_('COM_CONTENTBUILDER_PERMISSIONS_VIEW_NOT_ALLOWED');
                        }
                    }else{
                        if(!contentbuilder::authorize('view')){
                            $article->text = JText::_('COM_CONTENTBUILDER_PERMISSIONS_VIEW_NOT_ALLOWED');
                        }
                    }
                }
            }
        }
	return true;
    }
}