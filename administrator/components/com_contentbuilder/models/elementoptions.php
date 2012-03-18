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

jimport('joomla.version');
$version = new JVersion();

if(version_compare($version->getShortVersion(), '1.7', '>=')){
    require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'classes' . DS . 'plugin_helper.php');
} else {
    require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'classes' . DS . 'plugin_helper15.php');
}

class ContentbuilderModelElementoptions extends JModel
{
    private $_element_id = 0;

    function  __construct($config)
    {
        parent::__construct();

        $this->setIds(JRequest::getInt('id',  0), JRequest::getInt('element_id',  ''));
    }

    /*
     * MAIN DETAILS AREA
     */

    /**
     *
     * @param int $id
     */
    function setIds($id, $element_id) {
        // Set id and wipe data
        $this->_id = $id;
        $this->_element_id = $element_id;
        $this->_data = null;
    }

    private function _buildQuery(){
        return 'Select SQL_CALC_FOUND_ROWS * From #__contentbuilder_elements Where id = '.intval($this->_element_id);
    }

    function getData()
    {
        // Lets load the data if it doesn't already exist
        if (empty( $this->_data ))
        {
            $query = $this->_buildQuery();
            $this->_db->setQuery($query);
            $this->_data = $this->_db->loadObject();
            if (is_object($this->_data)) {
                $this->_data->options = $this->_data->options ? unserialize(base64_decode($this->_data->options)) : null;
            }
            return $this->_data;
           
        }
        return null;
    }
    
    function getValidationPlugins(){
        jimport('joomla.version');
        
        $db = JFactory::getDBO();
        
        $version = new JVersion();
        
        if(version_compare($version->getShortVersion(), '1.6', '>=')){
            
            $db->setQuery("Select `element` From #__extensions Where `folder` = 'contentbuilder_validation' And `enabled` = 1");
            $res = $db->loadResultArray();
            return $res;
            
        } else {
            
            $db->setQuery("Select `element` From #__plugins Where `folder` = 'contentbuilder_validation' And `published` = 1");
            $res = $db->loadResultArray();
            return $res;
        }
        
        return array();
    }
    
    function getGroupDefinition(){
        $this->_db->setQuery("Select `type`, `reference_id` From #__contentbuilder_forms Where id = " . intval($this->_id));
        $form = $this->_db->loadAssoc();
        $form = contentbuilder::getForm($form['type'], $form['reference_id']);
        if($form->isGroup($this->_data->reference_id)){
            return $form->getGroupDefinition($this->_data->reference_id);
        }
        return array();
    }
    
    function store()
    {
        if( JRequest::getInt('type_change', 0) ){
            $this->_db->setQuery("Update #__contentbuilder_elements Set `type`=".$this->_db->Quote(JRequest::getCmd('type_selection', ''))." Where id = " . $this->_element_id);
            $this->_db->query();
            return 1;
        }
        $query = '';
        $plugins = contentbuilder::getFormElementsPlugins();
        $type = JRequest::getCmd('field_type','');
        switch($type){
            case in_array(JRequest::getCmd('field_type',''),contentbuilder::getFormElementsPlugins()):
                
                $hint          = JRequest::getVar('hint','', 'POST', 'STRING', JREQUEST_ALLOWHTML);
                
                $removables = array();

                $plgs = CBPluginHelper::importPlugin('contentbuilder_form_elements', JRequest::getCmd('field_type',''));
                $removables = array_merge($removables, $plgs);

                $dispatcher = JDispatcher::getInstance();
                $results = $dispatcher->trigger('onSettingsStore', array());

                if(count($results)){
                    $results = $results[0];
                }
                
                foreach($removables As $removable){
                    $dispatcher->detach($removable);
                }

                $the_item = $results;
                
                $query = " `options`='".base64_encode( serialize( $the_item['options'] ) )."', `type`=".$this->_db->Quote(JRequest::getCmd('field_type','')).", `change_type`=".$this->_db->Quote(JRequest::getCmd('field_type','')).", `hint`=".$this->_db->Quote($hint).", `default_value`=".$this->_db->Quote($the_item['default_value']) . " ";
            
                break;
            case '':
            case 'text':
                $length        = JRequest::getVar('length','');
                $maxlength     = JRequest::getInt('maxlength','');
                $password      = JRequest::getInt('password',0);
                $readonly      = JRequest::getInt('readonly',0);
                $default_value = JRequest::getVar('default_value', '');
                $allow_raw     = JRequest::getInt('allow_encoding',0) == 2 ? true : false; // 0 = filter on, 1 = allow html, 2 = allow raw
                $allow_html    = JRequest::getInt('allow_encoding',0) == 1 ? true : false;
                $hint          = JRequest::getVar('hint','', 'POST', 'STRING', JREQUEST_ALLOWHTML);
                
                $options            = new stdClass();
                $options->length    = $length;
                $options->maxlength = $maxlength;
                $options->password  = $password;
                $options->readonly  = $readonly;
                $options->allow_raw   = $allow_raw;
                $options->allow_html  = $allow_html;
                
                $query = " `options`='".base64_encode( serialize( $options ) )."', `type`='text', `change_type`='text', `hint`=".$this->_db->Quote($hint).", `default_value`=".$this->_db->Quote($default_value) . " ";
            
            break;
            case 'textarea':
                $maxlength     = JRequest::getInt('maxlength','');
                $width         = JRequest::getVar('width','');
                $height        = JRequest::getVar('height','');
                $default_value = JRequest::getVar('default_value', '');
                $readonly      = JRequest::getInt('readonly',0);
                $allow_raw     = JRequest::getInt('allow_encoding',0) == 2 ? true : false; // 0 = filter on, 1 = allow html, 2 = allow raw
                $allow_html    = JRequest::getInt('allow_encoding',0) == 1 ? true : false;
                $hint          = JRequest::getVar('hint','', 'POST', 'STRING', JREQUEST_ALLOWHTML);
                
                $options            = new stdClass();
                $options->maxlength = $maxlength;
                $options->width     = $width;
                $options->height    = $height;
                $options->readonly  = $readonly;
                $options->allow_raw   = $allow_raw;
                $options->allow_html  = $allow_html;
                
                $query = " `options`='".base64_encode( serialize( $options ) )."', `type`='textarea', `change_type`='textarea', `hint`=".$this->_db->Quote($hint).", `default_value`=".$this->_db->Quote($default_value) . " ";            
            break;
            case 'checkboxgroup':
            case 'radiogroup':
            case 'select':
                $seperator     = ','; //JRequest::getVar('seperator',',');
                $default_value = implode($seperator,JRequest::getVar('default_value', array()));
                $allow_raw     = JRequest::getInt('allow_encoding',0) == 2 ? true : false; // 0 = filter on, 1 = allow html, 2 = allow raw
                $allow_html    = JRequest::getInt('allow_encoding',0) == 1 ? true : false;
                $hint          = JRequest::getVar('hint','', 'POST', 'STRING', JREQUEST_ALLOWHTML);
                
                $options            = new stdClass();
                $options->seperator = $seperator;
                $options->allow_raw   = $allow_raw;
                $options->allow_html  = $allow_html;
                
                if($type == 'select'){
                    $multi = JRequest::getInt('multiple',0);
                    $options->multiple = $multi;
                    $options->length   = JRequest::getVar('length','');
                }
                
                if( $type == 'checkboxgroup' || $type == 'radiogroup'){
                    $options->horizontal = JRequest::getBool('horizontal',0);
                    $options->horizontal_length   = JRequest::getVar('horizontal_length','');
                }
                
                $query = " `options`='".base64_encode( serialize( $options ) )."', `type`='".$type."', `change_type`='".$type."', `hint`=".$this->_db->Quote($hint).", `default_value`=".$this->_db->Quote($default_value) . " ";            
            break;
            case 'upload':
                
                jimport('joomla.filesystem.file');
                jimport('joomla.filesystem.folder');
                
                $this->_db->setQuery("Select upload_directory, protect_upload_directory From #__contentbuilder_forms Where id = " . $this->_id);
                $setup = $this->_db->loadAssoc();
                
                // rel check for setup
                
                $tokens = '';
                
                $upl_ex = explode('|',$setup['upload_directory']);
                $setup['upload_directory'] = $upl_ex[0];
                
                $upl_ex2 = explode('|',trim(JRequest::getVar('upload_directory', '')));
                
                JRequest::setVar('upload_directory', $upl_ex2[0]);
                
                $is_relative = strpos(strtolower($setup['upload_directory']), '{cbsite}') === 0;
                $tmp_upload_directory = $setup['upload_directory'];
                $upload_directory = $is_relative ? str_replace(array('{CBSite}','{cbsite}'), JPATH_SITE, $setup['upload_directory']) : $setup['upload_directory'];
                
                // rel check for element options
                $is_opt_relative = strpos(strtolower(trim(JRequest::getVar('upload_directory', ''))), '{cbsite}') === 0;
                $tmp_opt_upload_directory = trim(JRequest::getVar('upload_directory', ''));
                JRequest::setVar('upload_directory', $is_relative ? str_replace(array('{CBSite}','{cbsite}'), JPATH_SITE, trim(JRequest::getVar('upload_directory', ''))) : trim(JRequest::getVar('upload_directory', '')) );
                
                
                $protect = $setup['protect_upload_directory'];
                
                if(!trim(JRequest::getVar('upload_directory', '')) && !JFolder::exists($upload_directory)){
                    
                    if(!JFolder::exists(JPATH_SITE . DS . 'media' . DS . 'contentbuilder')){
                        JFolder::create(JPATH_SITE . DS . 'media' . DS . 'contentbuilder');
                        JFile::write(JPATH_SITE . DS . 'media' . DS . 'contentbuilder' . DS . 'index.html', $def = '');
                    }
                    
                    if(!JFolder::exists(JPATH_SITE . DS . 'media' . DS . 'contentbuilder' . DS . 'upload')){
                        JFolder::create(JPATH_SITE . DS . 'media' . DS . 'contentbuilder' . DS . 'upload');
                        JFile::write(JPATH_SITE . DS . 'media' . DS . 'contentbuilder' . DS . 'upload' . DS . 'index.html', $def = '');
                    }
                    
                    $upload_directory = JPATH_SITE . DS . 'media'.DS.'contentbuilder'.DS.'upload';
                    
                    if($is_opt_relative){
                        $is_relative = 1;
                        $tmp_upload_directory = '{CBSite}' . DS . 'media'.DS.'contentbuilder'.DS.'upload';
                    }
                    
                    if( isset($upl_ex[1]) ){
                        $tokens = '|'.$upl_ex[1];
                    }
                    
                    JFactory::getApplication()->enqueueMessage(JText::_('COM_CONTENTBUILDER_FALLBACK_UPLOAD_CREATED') . ' ('.DS.'media'.DS.'contentbuilder'.DS.'upload'.')', 'warning');
                
                } else if(trim(JRequest::getVar('upload_directory', '')) != '' && !JFolder::exists(contentbuilder::makeSafeFolder(JRequest::getVar('upload_directory', '')))){
                    
                    $upload_directory = contentbuilder::makeSafeFolder(JRequest::getVar('upload_directory', ''));
                    
                    JFolder::create($upload_directory);
                    JFile::write($upload_directory . DS . 'index.html', $def = '');
                        
                    if($is_opt_relative){
                        $is_relative = 1;
                        $tmp_upload_directory = $tmp_opt_upload_directory;
                    }
                    
                    if( isset($upl_ex2[1]) ){
                        $tokens = '|'.$upl_ex2[1];
                    }
                    
                    JFactory::getApplication()->enqueueMessage(JText::_('COM_CONTENTBUILDER_FALLBACK_UPLOAD_CREATED') . ' ('.$upload_directory.')', 'warning');
                
                } else if(trim(JRequest::getVar('upload_directory', '')) != '' && JFolder::exists(contentbuilder::makeSafeFolder(JRequest::getVar('upload_directory', '')))){
                    
                    $upload_directory = contentbuilder::makeSafeFolder(JRequest::getVar('upload_directory', ''));
                    
                    if($is_opt_relative){
                        $is_relative = 1;
                        $tmp_upload_directory = $tmp_opt_upload_directory;
                    }
                    
                    if( isset($upl_ex2[1]) ){
                        $tokens = '|'.$upl_ex2[1];
                    }
                    
                } else {
                   if( isset($upl_ex[1]) ){
                        $tokens = '|'.$upl_ex[1];
                   }
                }
                
                if($protect && JFolder::exists($upload_directory)){
                    
                    JFile::write(contentbuilder::makeSafeFolder($upload_directory) . DS . '.htaccess', $def = 'deny from all');
                
                } else if(!$protect && JFolder::exists($upload_directory)){
                    if(JFile::exists(contentbuilder::makeSafeFolder($upload_directory) . DS . '.htaccess')){
                        JFile::delete(contentbuilder::makeSafeFolder($upload_directory) . DS . '.htaccess');
                    }
                
                }
                
                $default_value = JRequest::getVar('default_value', '');
                $hint          = JRequest::getVar('hint','', 'POST', 'STRING', JREQUEST_ALLOWHTML);
                
                $options = new stdClass();
                $options->upload_directory = JFolder::exists($upload_directory) ? ($is_relative ? $tmp_upload_directory :  $upload_directory) . $tokens : '';
                $options->allowed_file_extensions = JRequest::getVar('allowed_file_extensions','');
                $options->max_filesize = JRequest::getVar('max_filesize','');
                
                $query = " `options`='".base64_encode( serialize( $options ) )."', `type`='".$type."', `change_type`='".$type."', `hint`=".$this->_db->Quote($hint).", `default_value`=".$this->_db->Quote($default_value) . " ";            
            break;
            case 'captcha':
                $default_value = JRequest::getVar('default_value', '');
                $hint          = JRequest::getVar('hint','', 'POST', 'STRING', JREQUEST_ALLOWHTML);
                
                $options = new stdClass();
                
                $query = " `options`='".base64_encode( serialize( $options ) )."', `type`='".$type."', `change_type`='".$type."', `hint`=".$this->_db->Quote($hint).", `default_value`=".$this->_db->Quote($default_value) . " ";            
            break;
            case 'calendar':
                $length        = JRequest::getVar('length','');
                $format        = JRequest::getVar('format','');
                $transfer_format = JRequest::getVar('transfer_format','');
                $maxlength     = JRequest::getInt('maxlength','');
                $readonly      = JRequest::getInt('readonly',0);
                $default_value = JRequest::getVar('default_value', '');
                $hint          = JRequest::getVar('hint','', 'POST', 'STRING', JREQUEST_ALLOWHTML);
                
                $options            = new stdClass();
                $options->length    = $length;
                $options->maxlength = $maxlength;
                $options->readonly  = $readonly;
                $options->format    = $format;
                $options->transfer_format = $transfer_format;
                
                $query = " `options`='".base64_encode( serialize( $options ) )."', `type`='calendar', `change_type`='calendar', `hint`=".$this->_db->Quote($hint).", `default_value`=".$this->_db->Quote($default_value) . " ";
            
            break;
            case 'hidden':
                $allow_raw     = JRequest::getInt('allow_encoding',0) == 2 ? true : false; // 0 = filter on, 1 = allow html, 2 = allow raw
                $allow_html    = JRequest::getInt('allow_encoding',0) == 1 ? true : false;
                $default_value = JRequest::getVar('default_value', '');
                $hint = '';
                
                $options            = new stdClass();
                $options->allow_raw   = $allow_raw;
                $options->allow_html  = $allow_html;
                
                $query = " `options`='".base64_encode( serialize( $options ) )."', `type`='".$type."', `change_type`='".$type."', `hint`=".$this->_db->Quote($hint).", `default_value`=".$this->_db->Quote($default_value) . " ";            
            break;
        }
        if($query){
            
            $custom_init_script       = JRequest::getVar('custom_init_script','', 'POST', 'STRING', JREQUEST_ALLOWRAW);
            $custom_action_script     = JRequest::getVar('custom_action_script','', 'POST', 'STRING', JREQUEST_ALLOWRAW);
            $custom_validation_script = JRequest::getVar('custom_validation_script','', 'POST', 'STRING', JREQUEST_ALLOWRAW);
            $validation_message       = JRequest::getVar('validation_message','');
            $validations              = JRequest::getVar('validations',array());
            
            $other  = " `validations`=".$this->_db->Quote(implode(',',$validations)).", ";
            $other .= " `custom_init_script`=".$this->_db->Quote($custom_init_script).", ";
            $other .= " `custom_action_script`=".$this->_db->Quote($custom_action_script).", ";
            $other .= " `custom_validation_script`=".$this->_db->Quote($custom_validation_script).", ";
            $other .= " `validation_message`=".$this->_db->Quote($validation_message).", ";
            
            $this->_db->setQuery("Update #__contentbuilder_elements Set $other $query Where id = " . $this->_element_id);
            $this->_db->query();
            return true;
        }
        return false;
    }
}
