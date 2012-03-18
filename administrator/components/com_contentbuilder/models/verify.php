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

class ContentbuilderModelVerify extends JModel
{

    private $frontend = false;
    
    function  __construct($config) {
        parent::__construct($config);

        $this->frontend = JFactory::getApplication()->isSite();
        
        $mainframe = JFactory::getApplication();
        $option = 'com_contentbuilder';

        $plugin = JRequest::getVar('plugin','');
        $verification_name = JRequest::getVar('verification_name','');
        
        $verification_id = JRequest::getVar('verification_id', '');
        $setup = '';
        $user_id = 0;
        
        if( !$verification_id ){
            $user_id = JFactory::getUser()->get('id', 0);
            $setup = JFactory::getSession()->get($plugin.$verification_name, '', 'com_contentbuilder.verify.'.$plugin.$verification_name);
        }
        else
        {
            $this->_db->setQuery("Select `setup`,`user_id` From #__contentbuilder_verifications Where `verification_hash` = " . $this->_db->Quote($verification_id));
            $setup = $this->_db->loadAssoc();
            if(is_array($setup)){
                $user_id = $setup['user_id'];
                $setup = $setup['setup'];
            }
        }

        $out = array();
        
        if($setup){
            parse_str($setup, $out);
        }
        
        if( isset($out['plugin']) && $out['plugin'] && isset($out['verification_name']) && $out['verification_name'] && isset($out['verify_view']) && $out['verify_view'] ){
           // alright 
        } else {
            JFactory::getApplication()->redirect('index.php', 'Spoofed data or invalid verification id', 'error');
        }
         
        if( isset( $out['plugin_options'] ) ){
            $options = base64_decode($out['plugin_options']);
            parse_str($options, $opts);
            $out['plugin_options'] = $opts;
            if(!count($out['plugin_options'])){
               $out['plugin_options'] = array();
            }
        } else {
            $out['plugin_options'] = array();
        }
        
        $_now = JFactory::getDate();
        
        //$this->_db->setQuery("Select count(id) From #__contentbuilder_verifications Where Timestampdiff(Second, `start_date`, '".strtotime($_now->toMySQL())."') < 1 And ip = " . $this->_db->Quote($_SERVER['REMOTE_ADDR']));
        //$ver = $this->_db->loadResult();
        
        //if($ver >= 5){
        //    $this->_db->setQuery("Delete From #__contentbuilder_verifications Where `verification_date` = '0000-00-00 00:00:00' And ip = " . $this->_db->Quote($_SERVER['REMOTE_ADDR']));
        //    $this->_db->query();
        //    JError::raiseError(500, 'Penetration Denied');
        //}
        
        //$this->_db->setQuery("Delete From #__contentbuilder_verifications Where Timestampdiff(Second, `start_date`, '".strtotime($_now->toMySQL())."') > 86400 And `verification_date` = '0000-00-00 00:00:00'");
        //$this->_db->query();
        
        $rec = null;
        $redirect_view = '';
        
        if( isset($out['require_view']) && is_numeric($out['require_view']) && intval($out['require_view']) > 0 ){
               
            if( JFactory::getSession()->get('cb_last_record_user_id', 0, 'com_contentbuilder') ){
                $user_id = JFactory::getSession()->get('cb_last_record_user_id', 0, 'com_contentbuilder') ;
                JFactory::getSession()->clear('cb_last_record_user_id', 'com_contentbuilder');
            }
            
            $id = intval($out['require_view']);
            
            $this->_db->setQuery("Select `type`, `reference_id`, `show_all_languages_fe` From #__contentbuilder_forms Where published = 1 And id = " . $id);
            $formsettings = $this->_db->loadAssoc();
            
            if(!is_array($formsettings)){
                JError::raiseError(500, 'Verification Setup failed. Reason: View id ' . $out['require_view'] . ' has been requested but is not available (not existent or unpublished). Please update your content template or publish the view.');
            }
            
            $form = contentbuilder::getForm($formsettings['type'], $formsettings['reference_id']);
            $labels = $form->getElementLabels();
            
            $ids = array();
            
            foreach($labels As $reference_id => $label){
                $ids[] = $reference_id;
            }
            
            $rec = $form->getListRecords($ids, '', array(), 0, 1, '', array(), 'desc', 0, false, $user_id, 0, -1, -1, -1, -1, array(), true, null);
            
            if(count($rec) > 0){
                $rec = $rec[0];
                $rec = $form->getRecord($rec->colRecord, false, -1, true );
            }
            
            if(!$form->getListRecordsTotal($ids)){
                JFactory::getApplication()->redirect('index.php?option=com_contentbuilder&lang='.JRequest::getCmd('lang','').'&return='.  base64_encode(JURI::getInstance()->toString()).'&controller=edit&record_id=&id='.$id.'&rand='.rand(0,  getrandmax()));
            }
        }
        
        // clearing session after possible required view to make re-visits possible
        JFactory::getSession()->clear($plugin.$verification_name, 'com_contentbuilder.verify.'.$plugin.$verification_name);
       
        $verification_data = '';
        if(is_array($rec) && count($rec)){
            foreach($rec As $value){
                $verification_data .= urlencode(str_replace(array("\r","\n"), '', $value->recTitle)) ."=". urlencode(str_replace(array("\r","\n"), '', $value->recValue))."&";
            }
            $verification_data = rtrim($verification_data,'&');
        } 
           
        if( !JRequest::getBool('verify', 0) && !JRequest::getVar('token','') ){
            $verification_id = md5(uniqid(null,true) . mt_rand(0, mt_getrandmax()) . $user_id);
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
                    ".$this->_db->Quote('type=normal&'.$verification_data).",
                    ".$user_id.",
                    ".$this->_db->Quote($plugin).",
                    ".$this->_db->Quote($_SERVER['REMOTE_ADDR']).",
                    ".$this->_db->Quote($setup).",
                    ".intval($out['client'])."
                    )
            ");
            $this->_db->query();
        }
        
        /*
         if(intval($out['client']) && !JFactory::getApplication()->isAdmin()){
            parse_str(JURI::getInstance()->getQuery(), $data1);
            $this_page = JURI::getInstance()->base() . 'administrator/index.php?'.http_build_query($data1, '', '&');
        }else{
            parse_str(JURI::getInstance()->getQuery(), $data1);
            $urlex = explode('?', JURI::getInstance()->toString());
            $this_page = $urlex[0] . '?' . http_build_query($data1, '', '&');
        }
         */
        if(intval($out['client']) && !JFactory::getApplication()->isAdmin()){
            $this_page = JURI::getInstance()->base() . 'administrator/index.php?'.JURI::getInstance()->getQuery();
        }else{
            $this_page = JURI::getInstance()->toString();
        }
        
        JPluginHelper::importPlugin('contentbuilder_verify', $plugin);
        $dispatcher = JDispatcher::getInstance();
        $setup_result = $dispatcher->trigger('onSetup', array($this_page, $out));
                    
        if(!implode('', $setup_result)){
           
            if( !JRequest::getBool('verify', 0) ){
                
                if(JFactory::getApplication()->isAdmin()){
                    $local = explode('/', JURI::getInstance()->base());
                    unset($local[count($local)-1]);
                    unset($local[count($local)-1]);
                    parse_str(JURI::getInstance()->getQuery(), $data);
                    $this_page = implode('/', $local).'/index.php?'. http_build_query($data, '', '&') . '&verify=1&verification_id='.$verification_id;
                }else{
                    parse_str(JURI::getInstance()->getQuery(), $data);
                    $urlex = explode('?', JURI::getInstance()->toString());
                    $this_page = $urlex[0] . '?' . http_build_query($data, '', '&') . '&verify=1&verification_id='.$verification_id;
                }
                
                $forward_result = $dispatcher->trigger('onForward', array($this_page, $out));
                $forward = implode('',$forward_result);
                
                if($forward){
                    JFactory::getApplication()->redirect($forward);
                }
            }
            else
            {
                
                if($verification_id){
                            
                    $msg = '';

                    $verify_result = $dispatcher->trigger('onVerify', array($this_page, $out));

                    if(count($verify_result)){

                        if($verify_result[0] === false){

                            $msg = JText::_('COM_CONTENTBUILDER_VERIFICATION_FAILED');

                        }else{

                            if(isset($verify_result[0]['msg']) && $verify_result[0]['msg']){

                                $msg = $verify_result[0]['msg'];
                            }
                            else
                            {
                                if(isset($out['verification_msg']) && $out['verification_msg'])
                                {
                                    $msg = urldecode($out['verification_msg']);
                                }
                                else
                                {
                                    $msg = JText::_('COM_CONTENTBUILDER_VERIFICATION_SUCCESS');
                                }
                            }

                            if( ( !$out['client'] && ( !isset($out['return-site']) || !$out['return-site'] ) ) || ( $out['client'] && ( !isset($out['return-admin']) || !$out['return-admin'] ) ) ){
                                if(intval($out['client']) && !JFactory::getApplication()->isAdmin()){
                                    $redirect_view = JURI::getInstance()->base() . 'administrator/index.php?option=com_contentbuilder&controller=list&lang='.JRequest::getCmd('lang','').'&id='.$out['verify_view'];
                                }else{
                                    $redirect_view = 'index.php?option=com_contentbuilder&controller=list&lang='.JRequest::getCmd('lang','').'&id='.$out['verify_view'];
                                }
                            }

                            $this->_db->setQuery("Select id From #__contentbuilder_users Where userid = " . $this->_db->Quote($user_id) . " And form_id = " . intval($out['verify_view']));
                            $usertableid = $this->_db->loadResult();

                            $levels = explode(',',$out['verify_levels']);

                            if($usertableid){
                                $this->_db->setQuery("Update #__contentbuilder_users
                                Set
                                ".(in_array('view', $levels) ? ' verified_view=1, verification_date_view='.$this->_db->Quote($_now->toMySQL()).", " : '')."
                                ".(in_array('new', $levels) ? ' verified_new=1, verification_date_new='.$this->_db->Quote($_now->toMySQL()).", " : '')."
                                ".(in_array('edit', $levels) ? ' verified_edit=1, verification_date_edit='.$this->_db->Quote($_now->toMySQL()).", " : '')."
                                published = 1
                                Where id = $usertableid
                                ");
                                $this->_db->query();
                            }else{
                                $this->_db->setQuery("
                                Insert Into #__contentbuilder_users
                                (
                                ".(in_array('view', $levels) ? 'verified_view, verification_date_view,' : '')."
                                ".(in_array('new', $levels) ? 'verified_new, verification_date_new,' : '')."
                                ".(in_array('edit', $levels) ? 'verified_edit, verification_date_edit,' : '')."
                                published,
                                userid,
                                form_id
                                )
                                Values
                                (
                                ".(in_array('view', $levels) ? '1, '.$this->_db->Quote($_now->toMySQL()).',' : '')."
                                ".(in_array('new', $levels) ? '1, '.$this->_db->Quote($_now->toMySQL()).',' : '')."
                                ".(in_array('edit', $levels) ? '1, '.$this->_db->Quote($_now->toMySQL()).',' : '')."
                                1,
                                ".$this->_db->Quote($user_id).",
                                ".intval($out['verify_view'])."
                                )
                                ");
                                $this->_db->query();
                            }
                            
                            $verification_data = ($verification_data ? '&' : '').'';
                            if(isset($verify_result[0]['data']) && is_array($verify_result[0]['data']) && count($verify_result[0]['data'])){
                                foreach( $verify_result[0]['data'] As $key => $value ){
                                    $verification_data .= urlencode(str_replace(array("\r","\n"), '', $key)) ."=". urlencode(str_replace(array("\r","\n"), '', $value))."&";
                                }
                                $verification_data = rtrim($verification_data,'&');
                            }
                            
                            $this->_db->setQuery("
                                Update #__contentbuilder_verifications
                                Set
                                `verification_hash` = '',
                                `is_test` = ".(isset($verify_result[0]['is_test']) ? intval(isset($verify_result[0]['is_test'])) : 0).",
                                `verification_date` = ".$this->_db->Quote($_now->toMySQL())." 
                                ".($verification_data ? ',verification_data = concat(verification_data, '.$this->_db->Quote($verification_data).') ' : '')."
                                Where
                                verification_hash = ".$this->_db->Quote($verification_id)."
                                And
                                verification_hash <> ''
                                And
                                `verification_date` = '0000-00-00 00:00:00'
                                
                            ");
                            $this->_db->query();
                            
                            // token check if given
                            if( JRequest::getVar('token','') ){

                                jimport('joomla.version');
                                $version = new JVersion();

                                if(version_compare($version->getShortVersion(), '1.6', '>=')){

                                    $this->activate(JRequest::getVar('token',''));

                                } else {

                                    $this->activate15(JRequest::getVar('token',''));
                                }
                            }
                            
                            // exit if requested
                            if(count($verify_result) && isset($verify_result[0]['exit']) && $verify_result[0]['exit']){
                    
                                @ob_end_clean();
                                
                                if(isset($verify_result[0]['header']) && $verify_result[0]['header']){
                                    header($verify_result[0]['header']); 
                                }

                                exit;
                            }
                        }
                    }
                }
                else
                {
                    $msg = JText::_('COM_CONTENTBUILDER_VERIFICATION_NOT_EXECUTED');
                }
                
                if(!$out['client']){
                    JFactory::getApplication()->redirect( $redirect_view ? $redirect_view : ( !$out['client'] && isset($out['return-site']) && $out['return-site'] ? base64_decode($out['return-site']) : 'index.php' ), $msg );
                }else{
                    JFactory::getApplication()->redirect( $redirect_view ? $redirect_view : ( $out['client'] && isset($out['return-admin']) && $out['return-admin'] ? base64_decode($out['return-admin']) : 'index.php' ), $msg );
                }
            }
        }
        else
        {
            JError::raiseError(500, 'Verification Setup failed. Reason: ' . implode('',$setup_result));
        }
    }
    
    public function activate($token)
    {
        JFactory::getLanguage()->load('com_users', JPATH_SITE );
        
        $config	= JFactory::getConfig();
        $userParams	= JComponentHelper::getParams('com_users');
        $db		= $this->getDbo();

        // Get the user id based on the token.
        $db->setQuery(
                'SELECT `id` FROM `#__users`' .
                ' WHERE `activation` = '.$db->Quote($token) .
                ' AND `block` = 1' .
                ' AND `lastvisitDate` = '.$db->Quote($db->getNullDate())
        );
        $userId = (int) $db->loadResult();

        // Check for a valid user id.
        if (!$userId) {
            JError::raiseError(500, JText::_('COM_USERS_ACTIVATION_TOKEN_NOT_FOUND'));
        }

        // Load the users plugin group.
        JPluginHelper::importPlugin('user');

        // Activate the user.
        $user = JFactory::getUser($userId);
        $user->set('activation', '');
        $user->set('block', '0');

        // Store the user object.
        if (!$user->save()) {
                JError::raiseError(500, JText::sprintf('COM_USERS_REGISTRATION_ACTIVATION_SAVE_FAILED', $user->getError()));
        }

        return true;
    }
    
    function activate15($activation)
    {
        JFactory::getLanguage()->load('com_user', JPATH_SITE );
        
        //Initialize some variables
        $db = & JFactory::getDBO();

        // Lets get the id of the user we want to activate
        $query = 'SELECT id'
        . ' FROM #__users'
        . ' WHERE activation = '.$db->Quote($activation)
        . ' AND block = 1'
        . ' AND lastvisitDate = '.$db->Quote('0000-00-00 00:00:00');
        ;
        $db->setQuery( $query );
        $id = intval( $db->loadResult() );

        // Is it a valid user to activate?
        if ($id)
        {
                $user =& JUser::getInstance( (int) $id );

                $user->set('block', '0');
                $user->set('activation', '');

                // Time to take care of business.... store the user.
                if (!$user->save())
                {
                        JError::raiseError(500, "SOME_ERROR_CODE");
                        return false;
                }
        }
        else
        {
                JError::raiseError(500, JText::_('UNABLE TO FIND A USER WITH GIVEN ACTIVATION STRING') );
                return false;
        }

        return true;
    }
}
