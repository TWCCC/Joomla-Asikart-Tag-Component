<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        http://www.crosstec.de
 * @license     GNU/GPL
*/
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

function cbgetPlugins(){
    $plugins = array();
    $plugins['contentbuilder_verify'] = array();
    $plugins['contentbuilder_verify'][] = 'paypal';
    $plugins['contentbuilder_verify'][] = 'passthrough';
    $plugins['contentbuilder_validation'] = array();
    $plugins['contentbuilder_validation'][] = 'notempty';
    $plugins['contentbuilder_validation'][] = 'equal';
    $plugins['contentbuilder_validation'][] = 'email';
    $plugins['contentbuilder_validation'][] = 'date_not_before';
    $plugins['contentbuilder_validation'][] = 'date_is_valid';
    $plugins['contentbuilder_themes'] = array();
    $plugins['contentbuilder_themes'][] = 'khepri';
    $plugins['contentbuilder_themes'][] = 'blank';
    $plugins['system'] = array();
    $plugins['system'][] = 'contentbuilder_system';
    $plugins['contentbuilder_submit'] = array();
    $plugins['contentbuilder_submit'][] = 'submit_sample';
    $plugins['contentbuilder_listaction'] = array();
    $plugins['contentbuilder_listaction'][] = 'trash';
    $plugins['contentbuilder_listaction'][] = 'untrash';
    $plugins['content'] = array();
    $plugins['content'][] = 'contentbuilder_verify';
    $plugins['content'][] = 'contentbuilder_permission_observer';
    $plugins['content'][] = 'contentbuilder_image_scale';
    $plugins['content'][] = 'contentbuilder_download';
    $plugins['content'][] = 'contentbuilder_rating';
    return $plugins;
}

function com_uninstall(){
    
    jimport('joomla.version');
    $version = new JVersion();

    if(version_compare($version->getShortVersion(), '1.6', '<')){
        $db = JFactory::getDBO();
        $db->setQuery("Delete From #__components Where `admin_menu_link` Like 'option=com_contentbuilder%'");
        $db->query();
        
        $plugins = cbgetPlugins();

        $installer = new JInstaller();

        foreach($plugins As $folder => $subplugs){
            foreach($subplugs As $plugin){
                
                $db->setQuery('SELECT `id` FROM #__plugins WHERE `element` = "'.$plugin.'" AND `folder` = "'.$folder.'"');
                
                $id = $db->loadResult();

                if($id)
                {
                    $installer->uninstall('plugin',$id,1);
                } 
            }
        }
    }
}