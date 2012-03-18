<?php
/**
 * @version $Id$
 * Kunena Component
 * @package Kunena
 *
 * @Copyright (C) 2008 - 2011 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 *
 * Based on FireBoard Component
 * @Copyright (C) 2006 - 2007 Best Of Joomla All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.bestofjoomla.com
 *
 * Based on Joomlaboard Component
 * @copyright (C) 2000 - 2004 TSMF / Jan de Graaff / All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @author TSMF & Jan de Graaff
 **/

defined( '_JEXEC' ) or die();

// Access check.
if (version_compare(JVERSION, '1.6', '>')) {
	if (!JFactory::getUser()->authorise('core.manage', 'com_kunena')) {
		return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
	}
}

// Start output buffering to cleanup if redirect
ob_start();

JToolBarHelper::title('&nbsp;', 'kunena.png');

$view = JRequest::getCmd ( 'view' );
$task = JRequest::getCmd ( 'task' );

require_once (JPATH_ADMINISTRATOR . '/components/com_kunena/api.php');
require_once (KUNENA_PATH_LIB . '/kunena.version.php');
kimport('error');
KunenaError::initialize();

$kunena_app = & JFactory::getApplication ();

require_once(KPATH_ADMIN.'/install/version.php');
$kn_version = new KunenaVersion();
if ($view == 'install') {
	require_once (KPATH_ADMIN . '/install/controller.php');
	$controller = new KunenaControllerInstall();
	$controller->execute( $task );
	$controller->redirect();
	return;
}

if (!$kn_version->checkVersion() && $task!='schema' && $task!='schemadiff') {
	require_once(dirname(__FILE__).'/install.script.php');
	Com_KunenaInstallerScript::preflight( null, null );
	Com_KunenaInstallerScript::install ( null );
	while (@ob_end_clean());
	$kunena_app->redirect(JURI::root().'administrator/index.php?option=com_kunena&view=install');
}

require_once(KPATH_SITE.'/lib/kunena.defines.php');
$lang = JFactory::getLanguage();
$lang->load('com_kunena', JPATH_ADMINISTRATOR) || $lang->load('com_kunena', KPATH_ADMIN);
$lang->load('com_kunena', JPATH_SITE) || $lang->load('com_kunena', KPATH_SITE);

jimport( 'joomla.utilities.arrayhelper' );

// Now that we have the global defines we can use shortcut defines
require_once (KUNENA_PATH_LIB . '/kunena.config.class.php');

$kunena_config = KunenaFactory::getConfig ();
$kunena_db = JFactory::getDBO ();

// Class structure should be used after this and all the common task should be moved to this class
require_once (KUNENA_PATH . '/class.kunena.php');
require_once (KUNENA_PATH_ADMIN . '/admin.kunena.html.php');

$cid = JRequest::getVar ( 'cid', array () );

if (! is_array ( $cid )) {
	$cid = array ();
}
$cid0 = isset($cid [0]) ? $cid [0] : 0;

$uid = JRequest::getVar ( 'uid', array (0 ) );

if (! is_array ( $uid )) {
	$uid = array ($uid );
}

$order = JRequest::getVar ( 'order', '' );

// initialise some request directives (specifically for J1.5 compatibility)
$no_html = intval ( JRequest::getVar ( 'no_html', 0 ) );
$id = intval ( JRequest::getVar ( 'id', 0 ) );

$pt_stop = "0";

if (! $no_html) {
	html_Kunena::showFbHeader ();
}

$option = JRequest::getCmd ( 'option' );

switch ($task) {
	case "new" :
		editForum ( 0, $option );

		break;

	case "edit" :
		editForum ( $cid0, $option );

		break;

	case "edit2" :
		editForum ( $uid [0], $option );

		break;

	case "save" :
		saveForum ( $option );

		break;

	case "cancel" :
		cancelForum ( $option );

		break;

	case 'cat_lock_0' :
		setForumVariable($cid, 'locked', 1);

		break;

	case 'cat_lock_1' :
		setForumVariable($cid, 'locked', 0);

		break;

	case 'cat_moderate_0' :
		setForumVariable($cid, 'moderated', 1);

		break;

	case 'cat_moderate_1' :
		setForumVariable($cid, 'moderated', 0);

		break;

	case 'cat_review_0' :
		setForumVariable($cid, 'review', 1);

		break;

	case 'cat_review_1' :
		setForumVariable($cid, 'review', 0);

		break;

	case 'cat_allow_anonymous_0' :
		setForumVariable($cid, 'allow_anonymous', 1);

		break;

	case 'cat_allow_anonymous_1' :
		setForumVariable($cid, 'allow_anonymous', 0);

		break;

	case 'cat_allow_polls_0' :
		setForumVariable($cid, 'allow_polls', 1);

		break;

	case 'cat_allow_polls_1' :
		setForumVariable($cid, 'allow_polls', 0);

		break;

	case "publish" :
		setForumVariable($cid, 'published', 1);

		break;

	case "unpublish" :
		setForumVariable($cid, 'published', 0);

		break;

	case "remove" :
		deleteForum ( $cid, $option );

		break;

	case "orderup" :
		orderForumUpDown ( $cid0, - 1, $option );

		break;

	case "orderdown" :
		orderForumUpDown ( $cid0, 1, $option );

		break;

	case "showconfig" :
		showConfig ( $option );

		break;

	case "saveconfig" :
		saveConfig ( $option );

		break;

	case "defaultconfig" :
		defaultConfig ( $option );

		break;

	case "revertconfig" :
		revertConfig ( $option );

		break;

	case "newmoderator" :
		newModerator ( $option, $id );

		break;

	case "addmoderator" :
		addModerator ( $option, $id, $cid, 1 );

		break;

	case "removemoderator" :
		addModerator ( $option, $id, $cid, 0 );

		break;

	case "showprofiles" :
		showProfiles ( $kunena_db, $option, $order );

		break;

	case "profiles" :
		showProfiles ( $kunena_db, $option, $order );

		break;

	case "logout" :
		logout ( $option, $cid );

		break;

	case "deleteuser" :
		deleteUser ( $option, $cid );

		break;

	case "userprofile" :
		editUserProfile ( $option, $cid );

		break;

	case "userblock" :
		userban ( $option, $cid, 1 );

		break;

	case "userunblock" :
		userban ( $option, $cid, 1 );

		break;

	case "userban" :
		userban ($option, $cid, 0 );
		break;

	case "userunban" :
		userban ( $option, $cid, 0 );
		break;

	case "trashusermessages" :
		trashUserMessages ( $option, $cid );

		break;

	case "moveusermessages" :
		moveUserMessages ( $option, $cid );

		break;

	case "moveusermessagesnow" :
		moveUserMessagesNow ( $option, $cid );

		break;

	case "showCss" :
		showCss ( $option );

		break;

	case "saveeditcss" :
		$file = JRequest::getVar ( 'file', 1 );
		$csscontent = JRequest::getVar ( 'csscontent', 1 );

		saveCss ( $file, $csscontent, $option );

		break;

	case "saveuserprofile" :
		saveUserProfile ( $option );

		break;

	case "pruneforum" :
		pruneforum ( $kunena_db, $option );

		break;

	case "doprune" :
		doprune ( $kunena_db, $option );

		break;

	case "douserssync" :
		douserssync ( $kunena_db, $option );

		break;

	case "syncusers" :
		syncusers ( $kunena_db, $option );

		break;

	case "browseImages" :
		browseUploaded ( $kunena_db, $option, 1 );

		break;

	case "browseFiles" :
		browseUploaded ( $kunena_db, $option, 0 );

		break;

	case "deleteImage" :
		deleteAttachment ( JRequest::getInt ( 'id', 0 ), JURI::base () . "index.php?option=$option&task=browseImages", 'COM_KUNENA_IMGDELETED');

		break;

	case "deleteFile" :
		deleteAttachment ( JRequest::getInt ( 'id', 0 ), JURI::base () . "index.php?option=$option&task=browseFiles", 'COM_KUNENA_FILEDELETED' );

		break;

	case "showAdministration" :
		showAdministration ( $option );

		break;

	case "saveorder" :
		orderForum();

		break;

	case 'recount' :
		CKunenaTools::reCountUserPosts ();
		CKunenaTools::reCountBoards ();
		// Also reset the name info stored with messages
		//CKunenaTools::updateNameInfo();
		while (@ob_end_clean());
		$kunena_app->redirect ( JURI::base () . 'index.php?option=com_kunena', JText::_('COM_KUNENA_RECOUNTFORUMS_DONE') );
		break;

	case "showsmilies" :
		showsmilies ( $option );

		break;

	case "uploadsmilies" :
		uploadsmilies ( $option, $cid0 );

		break;

	case "editsmiley" :
		editsmiley ( $option, $cid0 );

		break;

	case "savesmiley" :
		savesmiley ( $option, $id );

		break;

	case "deletesmiley" :
		deletesmiley ( $option, $cid );

		break;

	case "newsmiley" :
		newsmiley ( $option );

		break;

	case 'ranks' :
		showRanks ( $option );

		break;

	case "uploadranks" :
		uploadranks ( $option, $cid0 );

		break;

	case "editRank" :
		editRank ( $option, $cid0 );

		break;

	case "saveRank" :
		saveRank ( $option, $id );

		break;

	case "deleteRank" :
		deleteRank ( $option, $cid );

		break;

	case "newRank" :
		newRank ( $option );

		break;

	case "showtrashview" :
		showtrashview ( $option );

		break;

	case "showsystemreport" :
		showSystemReport ( $option );

		break;

	case "trashpurge" :
		trashpurge ( $option, $cid );

		break;

	case "deleteitemsnow" :
		deleteitemsnow ( $option, $cid );

		break;

	case "trashrestore" :
		trashrestore ( $option, $cid );

		break;

//###########################################
//			START TEMPLATE MANAGER
//###########################################

	case "showTemplates" :
		showTemplates ( $option );

		break;

	case "publishTemplate" :
		publishTemplate ();

		break;

	case "editKTemplate" :
		editKTemplate ( $option );

		break;

	case "saveTemplate" :
	case "applyTemplate" :
		saveTemplate();

		break;

	case "chooseCSSTemplate" :
		chooseCSSTemplate();

		break;

	case "editTemplateCSS" :
		editTemplateCSS();

		break;

	case "saveTemplateCSS" :
		saveTemplateCSS();

		break;

	case "cancelTemplate" :
		cancelTemplate();

		break;

	/*case "previewTemplate" :
		previewTemplate();

		break;*/

	case "addKTemplate" :
		addKTemplate();

		break;

	case "installTemplate" :
		extractKTemplate ();

		break;

	case "showstats" :
		showStats();

		break;

	case "uninstallKTemplate" :
		uninstallKTemplate();

		break;
//###########################################
//			END TEMPLATE MANAGER
//###########################################

	case "createmenu" :
		$lang = JFactory::getLanguage();
		// Start by loading English strings and override them by current locale
		$lang->load('com_kunena.install',JPATH_ADMINISTRATOR, 'en-GB')
			|| $lang->load('com_kunena.install',KPATH_ADMIN, 'en-GB');
		$lang->load('com_kunena.install',JPATH_ADMINISTRATOR)
			|| $lang->load('com_kunena.install',KPATH_ADMIN);

		require_once(KPATH_ADMIN . '/install/model.php');
		$installer = new KunenaModelInstall();
		$installer->deleteMenu();
		$installer->createMenu();

		$kunena_app->enqueueMessage ( JText::_('COM_KUNENA_MENU_CREATED') );
		// No break! Need to display the control panel
	case 'cpanel' :
	default :
		html_Kunena::controlPanel ();
		break;
}

$kn_version_warning = $kn_version->getVersionWarning('COM_KUNENA_VERSION_INSTALLED');
if (! empty ( $kn_version_warning )) {
	$kunena_app->enqueueMessage ( $kn_version_warning, 'notice' );
}
if (!$kn_version->checkVersion()) {
	$kunena_app->enqueueMessage ( sprintf ( JText::_('COM_KUNENA_ERROR_UPGRADE'), Kunena::version() ), 'notice' );
	$kunena_app->enqueueMessage ( JText::_('COM_KUNENA_ERROR_UPGRADE_WARN') );
	$kunena_app->enqueueMessage ( sprintf ( JText::_('COM_KUNENA_ERROR_UPGRADE_AGAIN'), Kunena::version() ) );
	$kunena_app->enqueueMessage ( JText::_('COM_KUNENA_ERROR_INCOMPLETE_SUPPORT') . ' <a href="http://www.kunena.org">www.kunena.org</a>' );
}

// Detect errors in CB integration
// TODO: do we need to enable this?
/*
if (is_object ( $kunenaProfile )) {
	$kunenaProfile->enqueueErrors ();
}
*/

html_Kunena::showFbFooter ();

//###########################################
//			START TEMPLATE MANAGER
//###########################################

    function addKTemplate()
    {
		html_Kunena::installKTemplate();
	}

	function extractKTemplate()
	{
		$app = JFactory::getApplication ();
		$kunena_app = & JFactory::getApplication ();
		$option		= JRequest::getVar('option', '', '', 'cmd');

		jimport ( 'joomla.filesystem.folder' );
		jimport ( 'joomla.filesystem.file' );
		jimport ( 'joomla.filesystem.archive' );
		$tmp = JPATH_ROOT . '/tmp/kinstall/';
		$dest = KPATH_SITE . '/template/';
		$file = JRequest::getVar ( 'install_package', NULL, 'FILES', 'array' );

		if (!$file || !is_uploaded_file ( $file ['tmp_name'])) {
			$app->enqueueMessage ( JText::sprintf('COM_KUNENA_A_TEMPLATE_MANAGER_INSTALL_EXTRACT_MISSING', $file ['name']), 'notice' );
		}
		else {
			$success = JFile::upload($file ['tmp_name'], $tmp . $file ['name']);
			$success = JArchive::extract ( $tmp . $file ['name'], $tmp );
			if (! $success) {
				$app->enqueueMessage ( JText::sprintf('COM_KUNENA_A_TEMPLATE_MANAGER_INSTALL_EXTRACT_FAILED', $file ['name']), 'notice' );
			}
			// Delete the tmp install directory
			if (JFolder::exists($tmp)) {
				$templates = parseXMLTemplateFiles($tmp);
				if (!empty($templates)) {
					foreach ($templates as $template) {
						// Never overwrite default template
						if ($template->directory == 'default') continue;
						if (is_dir($dest.$template->directory)) {
							if (is_file($dest.$template->directory.'/params.ini')) {
								if (is_file($tmp.$template->directory.'/params.ini')) {
									JFile::delete($tmp.$template->directory.'/params.ini');
								}
								JFile::move($dest.$template->directory.'/params.ini', $tmp.$template->directory.'/params.ini');
							}
							JFolder::delete($dest.$template->directory);
						}
						$error = JFolder::move($tmp.$template->directory, $dest.$template->directory);
						if ($error !== true) $app->enqueueMessage ( JText::_('COM_KUNENA_A_TEMPLATE_MANAGER_TEMPLATE').': ' . $error, 'notice' );
					}
					$retval = JFolder::delete($tmp);
					$app->enqueueMessage ( JText::sprintf('COM_KUNENA_A_TEMPLATE_MANAGER_INSTALL_EXTRACT_SUCCESS', $file ['name']) );
				} else {
					JError::raiseWarning(100, JText::_('COM_KUNENA_A_TEMPLATE_MANAGER_TEMPLATE_MISSING_FILE'));
					$retval = false;
				}
			} else {
				JError::raiseWarning(100, JText::_('COM_KUNENA_A_TEMPLATE_MANAGER_TEMPLATE').' '.JText::_('COM_KUNENA_A_TEMPLATE_MANAGER_UNINSTALL').': '.JText::_('COM_KUNENA_A_TEMPLATE_MANAGER_DIR_NOT_EXIST'));
				$retval = false;
			}
		}
		while (@ob_end_clean());
		$kunena_app->redirect( JURI::base () . 'index.php?option='.$option.'&task=showTemplates');
	}

	function uninstallKTemplate()
	{
		$app = JFactory::getApplication ();
		$kunena_app = & JFactory::getApplication ();
		$cid		= JRequest::getVar('cid', array(), 'method', 'array');
		$cid		= array(JFilterInput::clean(@$cid[0], 'cmd'));
		$template	= $cid[0];
		$option		= JRequest::getVar('option', '', '', 'cmd');
		// Initialize variables
		$retval	= true;
		if (!$cid[0] ) {
			$app->enqueueMessage ( JText::_('COM_KUNENA_A_TEMPLATE_MANAGER_TEMPLATE_NOT_SPECIFIED'), 'error' );
			while (@ob_end_clean());
			$kunena_app->redirect( JURI::base () . 'index.php?option='.$option.'&task=showTemplates');
		}
		if (isTemplateDefault($template) || $cid[0] == 'default') {
			$app->enqueueMessage ( JText::sprintf('COM_KUNENA_A_TEMPLATE_MANAGER_UNINSTALL_CANNOT_DEFAULT', $cid), 'error' );
			while (@ob_end_clean());
			$kunena_app->redirect( JURI::base () . 'index.php?option='.$option.'&task=showTemplates');
			return;
		}
		$tpl = KPATH_SITE . '/template/'.$template;
		// Delete the template directory
		if (JFolder::exists($tpl)) {
			$retval = JFolder::delete($tpl);
			$app->enqueueMessage ( JText::sprintf('COM_KUNENA_A_TEMPLATE_MANAGER_UNINSTALL_SUCCESS', $cid) );
		} else {
			JError::raiseWarning(100, JText::_('COM_KUNENA_A_TEMPLATE_MANAGER_TEMPLATE').' '.JText::_('COM_KUNENA_A_TEMPLATE_MANAGER_UNINSTALL').': '.JText::_('COM_KUNENA_A_TEMPLATE_MANAGER_DIR_NOT_EXIST'));
			$retval = false;
		}
		while (@ob_end_clean());
		$kunena_app->redirect( JURI::base () . 'index.php?option='.$option.'&task=showTemplates');
		return $retval;
	}

	function isTemplateDefault($template)
	{
		$kunena_config = & CKunenaConfig::getInstance ();
		$defaultemplate = $kunena_config->template;
		return $defaultemplate == $template ? 1 : 0;
	}

	function parseXMLTemplateFiles($templateBaseDir)
	{
		// Read the template folder to find templates
		jimport('joomla.filesystem.folder');
		$templateDirs = JFolder::folders($templateBaseDir);
		$rows = array();
		// Check that the directory contains an xml file
		foreach ($templateDirs as $templateDir)
		{
			if(!$data = parseXMLTemplateFile($templateBaseDir, $templateDir)){
				continue;
			} else {
				$rows[] = $data;
			}
		}
		return $rows;
	}

function parseKunenaInstallFile($path) {
	// Read the file to see if it's a valid component XML file
	$xml = JFactory::getXMLParser ( 'Simple' );
	if (! $xml->loadFile ( $path )) {
		unset ( $xml );
		return false;
	}
	if (! is_object ( $xml->document ) || ($xml->document->name () != 'kinstall')) {
		unset ( $xml );
		return false;
	}

	$data = new stdClass ();
	$element = & $xml->document->name [0];
	$data->name = $element ? $element->data () : '';
	$data->type = $element ? $xml->document->attributes ( "type" ) : '';

	$element = & $xml->document->creationDate [0];
	$data->creationdate = $element ? $element->data () : JText::_ ( 'Unknown' );

	$element = & $xml->document->author [0];
	$data->author = $element ? $element->data () : JText::_ ( 'Unknown' );

	$element = & $xml->document->copyright [0];
	$data->copyright = $element ? $element->data () : '';

	$element = & $xml->document->authorEmail [0];
	$data->authorEmail = $element ? $element->data () : '';

	$element = & $xml->document->authorUrl [0];
	$data->authorUrl = $element ? $element->data () : '';

	$element = & $xml->document->version [0];
	$data->version = $element ? $element->data () : '';

	$element = & $xml->document->description [0];
	$data->description = $element ? $element->data () : '';

	$element = & $xml->document->thumbnail [0];
	$data->thumbnail = $element ? $element->data () : '';

	return $data;
}

function parseXMLTemplateFile($templateBaseDir, $templateDir)
	{
		// Check if the xml file exists
		if(!is_file("{$templateBaseDir}/{$templateDir}/template.xml")) {
			return false;
		}
		$data = parseKunenaInstallFile("{$templateBaseDir}/{$templateDir}/template.xml");
		if ($data->type != 'kunena-template') {
			return false;
		}
		$data->directory = basename($templateDir);
		return $data;
	}

	function showTemplates($option)
	{
		$kunena_app = & JFactory::getApplication ();
		$kunena_db = &JFactory::getDBO ();
		$limit = $kunena_app->getUserStateFromRequest ( "global.list.limit", 'limit', $kunena_app->getCfg ( 'list_limit' ), 'int' );
		$limitstart = $kunena_app->getUserStateFromRequest ( "{$option}.limitstart", 'limitstart', 0, 'int' );
		$levellimit = $kunena_app->getUserStateFromRequest ( "{$option}.limit", 'levellimit', 10, 'int' );
		$tBaseDir = KUNENA_PATH_TEMPLATE;
		//get template xml file info
		$rows = array();
		$rows = parseXMLTemplateFiles($tBaseDir);
		// set dynamic template information
		for($i = 0; $i < count($rows); $i++)  {
			$rows[$i]->published = isTemplateDefault($rows[$i]->directory);
		}
		jimport('joomla.html.pagination');
		$page = new JPagination(count($rows), $limitstart, $limit);
		$rows = array_slice($rows, $page->limitstart, $page->limit);
		html_Kunena::showTemplates($rows, $page, $option);
	}

	function editKTemplate($option)
	{
		jimport('joomla.filesystem.path');
		$kunena_db	= & JFactory::getDBO();
		$cid		= JRequest::getVar('cid', array(), 'method', 'array');
		$cid		= array(JFilterInput::clean(@$cid[0], 'cmd'));
		$template	= $cid[0];
		$option		= JRequest::getCmd('option');
		if (!$cid[0]) {
			return JError::raiseWarning( 500, JText::_('COM_KUNENA_A_TEMPLATE_MANAGER_TEMPLATE_NOT_SPECIFIED') );
		}
		$tBaseDir	= JPath::clean(KUNENA_PATH_TEMPLATE);
		if (!is_dir( "{$tBaseDir}/{$template}" )) {
			return JError::raiseWarning( 500, JText::_('COM_KUNENA_A_TEMPLATE_MANAGER_TEMPLATE_NOT_FOUND') );
		}
		$lang = JFactory::getLanguage();
		// Start by loading strings for default template and override with current template
		$lang->load('com_kunena.tpl_default', JPATH_SITE)
			|| $lang->load('com_kunena.tpl_default', KPATH_SITE)
			|| $lang->load('com_kunena.tpl_default', KUNENA_PATH_TEMPLATE.'/default');

		if ($template != 'default') {
			$lang->load('com_kunena.tpl_'.$template, JPATH_SITE)
				|| $lang->load('com_kunena.tpl_'.$template, KPATH_SITE)
				|| $lang->load('com_kunena.tpl_'.$template, KUNENA_PATH_TEMPLATE.'/'.$template);
		}
		$ini	= KUNENA_PATH_TEMPLATE.'/'.$template.'/params.ini';
		$xml	= KUNENA_PATH_TEMPLATE.'/'.$template.'/template.xml';
		$row	= parseXMLTemplateFile($tBaseDir, $template);
		jimport('joomla.filesystem.file');
		// Read the ini file
		if (JFile::exists($ini)) {
			$content = JFile::read($ini);
		} else {
			$content = null;
		}
		$params = new JParameter($content, $xml, 'template');
		$default = isTemplateDefault($row->directory);
		if ($default) {
			$row->pages = 'all';
		} else {
			$row->pages = null;
		}
		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		$ftp =& JClientHelper::setCredentialsFromRequest('ftp');
		html_Kunena::editKTemplate($row, $params, $option, $ftp, $template);
	}

	function saveTemplate()
	{
		$kunena_app = & JFactory::getApplication ();
		$kunena_db	= & JFactory::getDBO();
		$template	= JRequest::getVar('id', '', 'method', 'cmd');
		$option		= JRequest::getVar('option', '', '', 'cmd');
		$menus		= JRequest::getVar('selections', array(), 'post', 'array');
		$params		= JRequest::getVar('params', array(), 'post', 'array');
		$default	= JRequest::getBool('default');
		JArrayHelper::toInteger($menus);
		if (!$template) {
			while (@ob_end_clean());
			$kunena_app->redirect( JURI::base () . 'index.php?option='.$option, JText::_('COM_KUNENA_A_TEMPLATE_MANAGER_OPERATION_FAILED').': '.JText::_('COM_KUNENA_A_TEMPLATE_MANAGER_TEMPLATE_NOT_SPECIFIED'));
		}
		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');
		$ftp = JClientHelper::getCredentials('ftp');
		$file = KUNENA_PATH_TEMPLATE.'/'.$template.'/params.ini';
		jimport('joomla.filesystem.file');
		if (count($params))
		{
			$registry = new JRegistry();
			$registry->loadArray($params);
			$txt = $registry->toString();
			$return = JFile::write($file, $txt);
			if (!$return) {
				while (@ob_end_clean());
				$kunena_app->redirect('index.php?option='.$option, JText::_('COM_KUNENA_A_TEMPLATE_MANAGER_OPERATION_FAILED').': '.JText::sprintf('COM_KUNENA_A_TEMPLATE_MANAGER_FAILED_WRITE_FILE.', $file));
			}
		}
		$task = JRequest::getCmd('task');
		if($task == 'applyTemplate') {
			while (@ob_end_clean());
			$kunena_app->redirect( JURI::base () . 'index.php?option='.$option.'&task=editKTemplate&cid[]='.$template, JText::_('COM_KUNENA_A_TEMPLATE_MANAGER_CONFIGURATION_SAVED'));
		} else {
			while (@ob_end_clean());
			$kunena_app->redirect( JURI::base () . 'index.php?option='.$option.'&task=showTemplates', JText::_('COM_KUNENA_A_TEMPLATE_MANAGER_CONFIGURATION_SAVED'));
		}
	}

	function publishTemplate()
	{
		$kunena_app = & JFactory::getApplication ();
		$kunena_db	= & JFactory::getDBO();
		$kunena_config = KunenaFactory::getConfig();
		$cid	= JRequest::getVar('cid', array(), 'method', 'array');
		$cid	= array(JFilterInput::clean(@$cid[0], 'cmd'));
		$option	= JRequest::getCmd('option');
		if ($cid[0])
		{
			$kunena_config->template = $cid[0];
			$kunena_config->remove ();
			$kunena_config->create ();

			$kunena_db->setQuery ( "UPDATE #__kunena_sessions SET allowed='na'" );
			$kunena_db->query ();
			KunenaError::checkDatabaseError();
		}
		while (@ob_end_clean());
		$kunena_app->redirect( JURI::base () . 'index.php?option='.$option.'&task=showTemplates', JText::_('COM_KUNENA_A_TEMPLATE_MANAGER_DEFAULT_SELECTED'));
	}

	function chooseCSSTemplate()
	{
		$kunena_app = & JFactory::getApplication ();
		$option 	= JRequest::getCmd('option');
		$template	= JRequest::getVar('id', '', 'method', 'cmd');
		// Determine template CSS directory
		$dir = KUNENA_PATH_TEMPLATE.'/'.$template.'/css';
		// List template .css files
		jimport('joomla.filesystem.folder');
		$files = JFolder::files($dir, '\.css$', false, false);
		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');
		html_Kunena::chooseCSSFiles($template, $dir, $files, $option);
	}

	function editTemplateCSS()
	{
		$kunena_app = & JFactory::getApplication ();
		$option		= JRequest::getCmd('option');
		$template	= JRequest::getVar('id', '', 'method', 'cmd');
		$filename	= JRequest::getVar('filename', '', 'method', 'cmd');
		jimport('joomla.filesystem.file');
		if (JFile::getExt($filename) !== 'css') {
			$msg = JText::_('COM_KUNENA_A_TEMPLATE_MANAGER_WRONG_CSS');
			while (@ob_end_clean());
			$kunena_app->redirect( JURI::base () . 'index.php?option='.$option.'&task=chooseCSSTemplate&id='.$template, $msg, 'error');
		}
		$content = JFile::read(KUNENA_PATH_TEMPLATE.'/'.$template.'/css/'.$filename);
		if ($content !== false)
		{
			// Set FTP credentials, if given
			jimport('joomla.client.helper');
			$ftp =& JClientHelper::setCredentialsFromRequest('ftp');
			$content = htmlspecialchars($content, ENT_COMPAT, 'UTF-8');
			html_Kunena::editCSSSource($template, $filename, $content, $option, $ftp);
		}
		else
		{
			$msg = JText::sprintf('COM_KUNENA_A_TEMPLATE_MANAGER_FAILED_COULD_NOT_OPEN'.$filename);
			while (@ob_end_clean());
			$kunena_app->redirect( JURI::base () . 'index.php?option='.$option.$msg);
		}
	}

	function saveTemplateCSS()
	{
		$kunena_app = & JFactory::getApplication ();
		$option			= JRequest::getCmd('option');
		$template		= JRequest::getVar('id', '', 'post', 'cmd');
		$filename		= JRequest::getVar('filename', '', 'post', 'cmd');
		$filecontent	= JRequest::getVar('filecontent', '', 'post', 'string', JREQUEST_ALLOWRAW);
		if (!$template) {
			while (@ob_end_clean());
			$kunena_app->redirect( JURI::base () . 'index.php?option='.$option. JText::_('COM_KUNENA_A_TEMPLATE_MANAGER_OPERATION_FAILED').': '.JText::_('COM_KUNENA_A_TEMPLATE_MANAGER_TEMPLATE_NOT_SPECIFIED.'));
		}
		if (!$filecontent) {
			while (@ob_end_clean());
			$kunena_app->redirect( JURI::base () . 'index.php?option='.$option. JText::_('COM_KUNENA_A_TEMPLATE_MANAGER_OPERATION_FAILED').': '.JText::_('COM_KUNENA_A_TEMPLATE_MANAGER_CONTENT_EMPTY'));
		}
		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');
		$ftp = JClientHelper::getCredentials('ftp');
		$file = KUNENA_PATH_TEMPLATE.'/'.$template.'/css/'.$filename;
		if (!$ftp['enabled'] && JPath::isOwner($file) && !JPath::setPermissions($file, '0755')) {
			JError::raiseNotice('SOME_ERROR_CODE', JText::_('COM_KUNENA_A_TEMPLATE_MANAGER_COULD_NOT_CSS_WRITABLE'));
		}
		jimport('joomla.filesystem.file');
		$return = JFile::write($file, $filecontent);
		if (!$ftp['enabled'] && JPath::isOwner($file) && !JPath::setPermissions($file, '0555')) {
			JError::raiseNotice('SOME_ERROR_CODE', JText::_('COM_KUNENA_A_TEMPLATE_MANAGER_COULD_NOT_CSS_UNWRITABLE'));
		}
		if ($return) {
			while (@ob_end_clean());
			$kunena_app->redirect( JURI::base () . 'index.php?option='.$option.'&task=editKTemplate&cid[]='.$template, JText::_('COM_KUNENA_A_TEMPLATE_MANAGER_FILE_SAVED'));
		} else {
			while (@ob_end_clean());
			$kunena_app->redirect( JURI::base () . 'index.php?option='.$option.'&id='.$template.'&task=chooseCSSTemplate', JText::_('COM_KUNENA_A_TEMPLATE_MANAGER_OPERATION_FAILED').': '.JText::sprintf('COM_KUNENA_A_TEMPLATE_MANAGER_FAILED_OPEN_FILE.', $file));
		}
	}

	function cancelTemplate()
	{
		$kunena_app = & JFactory::getApplication ();;
		$option	= JRequest::getCmd('option');
		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');
		while (@ob_end_clean());
		$kunena_app->redirect( JURI::base () . 'index.php?option='.$option);
	}



//###########################################
//			END TEMPLATE MANAGER
//###########################################

function showAdministration($option) {
	$kunena_app = JFactory::getApplication ();
	$kunena_db = JFactory::getDBO ();
	$kunena_acl = JFactory::getACL ();

	$filter_order = $kunena_app->getUserStateFromRequest( $option.'filter_order', 'filter_order', 'ordering', 'cmd' );
	$filter_order_Dir = $kunena_app->getUserStateFromRequest( $option.'filter_order_Dir', 'filter_order_Dir', 'asc', 'word' );
	if ($filter_order_Dir != 'asc') $filter_order_Dir = 'desc';
	$limit = $kunena_app->getUserStateFromRequest ( "global.list.limit", 'limit', $kunena_app->getCfg ( 'list_limit' ), 'int' );
	$limitstart = $kunena_app->getUserStateFromRequest ( "{$option}.limitstart", 'limitstart', 0, 'int' );
	$levellimit = $kunena_app->getUserStateFromRequest ( "{$option}.limit", 'levellimit', 10, 'int' );

	$search = $kunena_app->getUserStateFromRequest( $option.'search', 'search', '', 'string' );
	$search = JString::strtolower( $search );

	$order = '';

	if ($filter_order == 'ordering') {
		$order = ' ORDER BY a.ordering '. $filter_order_Dir;
	} else if ($filter_order == 'name') {
		$order = ' ORDER BY a.name '. $filter_order_Dir ;
	} else if ($filter_order == 'id') {
		$order = ' ORDER BY a.id '. $filter_order_Dir ;
	}

	$where = '';

	if ($search) {
		$where .= ' WHERE LOWER( a.name ) LIKE '.$kunena_db->Quote( '%'.$kunena_db->getEscaped( $search, true ).'%', false ). ' OR LOWER( a.id ) LIKE '.$kunena_db->Quote( '%'.$kunena_db->getEscaped( $search, true ).'%', false );
	}

	if (KUNENA_JOOMLA_COMPAT == '1.5') {
		// Joomla 1.5
		$query= "SELECT a.*, a.parent>0 AS category, u.name AS editor, g.name AS groupname, g.id AS group_id, h.name AS admingroup, v.name AS viewlevel
			FROM #__kunena_categories AS a
			LEFT JOIN #__users AS u ON u.id = a.checked_out
			LEFT JOIN #__core_acl_aro_groups AS g ON a.accesstype='none' AND  g.id = a.pub_access
			LEFT JOIN #__core_acl_aro_groups AS h ON a.accesstype='none' AND  h.id = a.admin_access
			LEFT JOIN #__groups AS v ON a.accesstype='joomla.level' AND v.id = a.access
			".$where
		 	.$order;
	} else {
		// Joomla 1.6
		$query = "SELECT a.*, a.parent>0 AS category, u.name AS editor, g.title AS groupname, h.title AS admingroup, v.title AS viewlevel
			FROM #__kunena_categories AS a
			LEFT JOIN #__users AS u ON u.id = a.checked_out
			LEFT JOIN #__usergroups AS g ON a.accesstype='none' AND g.id = a.pub_access
			LEFT JOIN #__usergroups AS h ON a.accesstype='none' AND  h.id = a.admin_access
			LEFT JOIN #__viewlevels AS v ON a.accesstype='joomla.level' AND v.id = a.access
			".$where
			.$order;
	}
	$kunena_db->setQuery($query);
	$rows = $kunena_db->loadObjectList ('id');
	KunenaError::checkDatabaseError();

	// establish the hierarchy of the categories
	$children = array (0 => array());

	// first pass - collect children
	foreach ( $rows as $v ) {
		$list = array();
		$vv = $v;
		while ($vv->parent>0 && isset($rows[$vv->parent]) && !in_array($vv->parent, $list)) {
			$list[] = $vv->id;
			$vv = $rows[$vv->parent];
		}
		if ($vv->parent) {
			$v->parent = -1;
			if ( empty($search)) $v->published = 0;

			if ( empty($search))
			$v->name = JText::_('COM_KUNENA_CATEGORY_ORPHAN').' : '.$v->name;
		}
		if ($v->accesstype == 'joomla.level') {
			if (KUNENA_JOOMLA_COMPAT == '1.5') {
				$v->accessname = JText::_('COM_KUNENA_INTEGRATION_JOOMLA_LEVEL').': '.($v->viewlevel ? JText::_($v->viewlevel) : JText::_('COM_KUNENA_NOBODY'));
			} else {
				$v->accessname = JText::_('COM_KUNENA_INTEGRATION_JOOMLA_LEVEL').': '.($v->viewlevel ? $v->viewlevel : JText::_('COM_KUNENA_NOBODY'));
			}
		} elseif ($v->accesstype != 'none') {
			$v->accessname = JText::_('COM_KUNENA_INTEGRATION_'.strtoupper(preg_replace('/[^\w\d]+/', '_', $v->accesstype))).': '.$v->access;
		} elseif (KUNENA_JOOMLA_COMPAT == '1.5') {
			// Joomla 1.5
			if ($v->pub_access == 0) {
				$v->accessname = JText::_('COM_KUNENA_PUBLIC');
			} else if ($v->pub_access == - 1) {
				$v->accessname = JText::_('COM_KUNENA_ALLREGISTERED');
			} else if ($v->pub_access == 1 || !$v->groupname) {
				$v->accessname = JText::_('COM_KUNENA_NOBODY');
			} else {
				$v->accessname = JText::sprintf( $v->pub_recurse ? 'COM_KUNENA_A_GROUP_X_PLUS' : 'COM_KUNENA_A_GROUP_X_ONLY', JText::_( $v->groupname ));
			}
			if ($v->pub_access > 0 && $v->admingroup && $v->pub_access != $v->admin_access) {
				$v->accessname .= ' / '.JText::sprintf( $v->admin_recurse ? 'COM_KUNENA_A_GROUP_X_PLUS' : 'COM_KUNENA_A_GROUP_X_ONLY', JText::_( $v->admingroup ));
			}
		} else {
			// Joomla 1.6+
			$v->accessname = JText::sprintf( $v->pub_recurse ? 'COM_KUNENA_A_GROUP_X_PLUS' : 'COM_KUNENA_A_GROUP_X_ONLY', $v->groupname ? JText::_( $v->groupname ) : JText::_('COM_KUNENA_NOBODY') );
			if ($v->admingroup && $v->pub_access != $v->admin_access) {
				$v->accessname .= ' / '.JText::sprintf( $v->admin_recurse ? 'COM_KUNENA_A_GROUP_X_PLUS' : 'COM_KUNENA_A_GROUP_X_ONLY', JText::_( $v->admingroup ));
			}
		}
		if ($v->checked_out && !JTable::isCheckedOut(0, intval($v->checked_out))) {
			$v->checked_out = 0;
			$v->editor = '';
		}
		$children [$v->parent][] = $v;
		$v->location = count ( $children [$v->parent] )-1;
	}

	if (isset($children [-1])) {
		$children [0] = array_merge($children [-1], $children [0]);
		if ( empty($search))
		$kunena_app->enqueueMessage ( JText::_('COM_KUNENA_CATEGORY_ORPHAN_DESC'), 'notice' );
	}

	// second pass - get an indent list of the items
	$list = fbTreeRecurse ( 0, '', array (), $children, max ( 0, $levellimit - 1 ) );
	$total = count ( $list );
	if ($limitstart >= $total)
		$limitstart = 0;

	jimport ( 'joomla.html.pagination' );
	$pageNav = new JPagination ( $total, $limitstart, $limit );

	$levellist = JHTML::_ ( 'select.integerList', 1, 20, 1, 'levellimit', 'size="1" onchange="document.adminForm.submit();"', $levellimit );
	// slice out elements based on limits
	$list = array_slice ( $list, $pageNav->limitstart, $pageNav->limit );
	/**
	 *@end
	 */

	// table ordering
	$lists['order_Dir']	= $filter_order_Dir;
	$lists['order']		= $filter_order;

	$lists['search']= $search;

	html_Kunena::showAdministration ( $list, $children, $pageNav, $option, $lists );
}

//---------------------------------------
//-E D I T   F O R U M-------------------
//---------------------------------------
function editForum($id, $option) {
	$kunena_app = JFactory::getApplication ();
	$kunena_my = JFactory::getUser ();
	kimport('category');
	$category = KunenaCategory::getInstance ( $id );
	if ($category->isCheckedOut($kunena_my->id)) {
		$kunena_app->enqueueMessage ( JText::sprintf('COM_KUNENA_A_CATEGORY_CHECKED_OUT', $category->id), 'notice' );
		while (@ob_end_clean());
		$kunena_app->redirect ( JURI::base () . "index.php?option=$option&task=showAdministration" );
	}

	$kunena_db = JFactory::getDBO ();
	$kunena_acl = JFactory::getACL ();
	$kunena_config = KunenaFactory::getConfig ();

	if ($category->exists()) {
		$category->checkout ( $kunena_my->id );
	} else {
		// New category is by default child of the first section -- this will help new users to do it right
		$kunena_db->setQuery ( "SELECT a.id, a.name FROM #__kunena_categories AS a WHERE parent='0' AND id!='$category->id' ORDER BY ordering" );
		$sections = $kunena_db->loadObjectList ();
		KunenaError::checkDatabaseError();
		$category->parent = empty($sections) ? 0 : $sections[0]->id;
		$category->published = 0;
		$category->ordering = 9999;
		$category->pub_recurse = 1;
		$category->admin_recurse = 1;
		if (KUNENA_JOOMLA_COMPAT == '1.5') {
			$category->accesstype = 'none';
			$category->access = 0;
			$category->pub_access = 0;
			$category->admin_access = 0;
		} else {
			$category->accesstype = 'joomla.level';
			$category->access = 1;
			$category->pub_access = 1;
			$category->admin_access = 8;
		}
		$category->moderated = 1;
	}

	$catList = array();
	$catList[] = JHTML::_('select.option', 0, JText::_('COM_KUNENA_TOPLEVEL'));
	$categoryList = CKunenaTools::KSelectList('parent', $catList, 'class="inputbox"', true, 'parent', $category->parent);

	// make a standard yes/no list
	$yesno = array ();
	$yesno [] = JHTML::_ ( 'select.option', '0', JText::_('COM_KUNENA_ANN_NO') );
	$yesno [] = JHTML::_ ( 'select.option', '1', JText::_('COM_KUNENA_ANN_YES') );
	//Create all kinds of Lists
	$lists = array ();
	$accessLists = array ();
	//create custom group levels to include into the public group selectList
	if (KUNENA_JOOMLA_COMPAT == '1.5') {
		$pub_groups = array ();
		$pub_groups [] = JHTML::_ ( 'select.option', 1, JText::_('COM_KUNENA_NOBODY') );
		$pub_groups [] = JHTML::_ ( 'select.option', 0, JText::_('COM_KUNENA_PUBLIC') );
		$pub_groups [] = JHTML::_ ( 'select.option', - 1, JText::_('COM_KUNENA_ALLREGISTERED') );
		$adm_groups = array ();
		$adm_groups [] = JHTML::_ ( 'select.option', 0, JText::_('COM_KUNENA_NOBODY') );
		$joomlagroups = $kunena_acl->get_group_children_tree ( null, 'USERS', false );
		foreach ($joomlagroups as &$group) {
			$group->text = preg_replace('/(^&nbsp; |\.&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)/', '- ', $group->text);
		}
		$pub_groups = array_merge ( $pub_groups, $joomlagroups );
		$adm_groups = array_merge ( $adm_groups, $joomlagroups );
		// Create the access control lists for Joomla 1.5
		$accessLists ['pub_access'] = JHTML::_ ( 'select.genericlist', $pub_groups, 'pub_access', 'class="inputbox" size="10"', 'value', 'text', $category->pub_access );
		$accessLists ['admin_access'] = JHTML::_ ( 'select.genericlist', $adm_groups, 'admin_access', 'class="inputbox" size="10"', 'value', 'text', $category->admin_access );
	} else {
		// Create the access control lists for Joomla 1.6
		$accessLists ['pub_access'] = JHTML::_ ( 'access.usergroup', 'pub_access', $category->pub_access, 'class="inputbox" size="10"', false);
		$accessLists ['admin_access'] = JHTML::_ ( 'access.usergroup', 'admin_access', $category->admin_access, 'class="inputbox" size="10"', false);
	}
	// Anonymous posts default
	$post_anonymous = array ();
	$post_anonymous [] = JHTML::_ ( 'select.option', '0', JText::_('COM_KUNENA_CATEGORY_ANONYMOUS_X_REG') );
	$post_anonymous [] = JHTML::_ ( 'select.option', '1', JText::_('COM_KUNENA_CATEGORY_ANONYMOUS_X_ANO') );

	$lists ['accesstypes'] = KunenaFactory::getAccessControl()->getAccessTypesList($category);
	$lists ['accesslevels'] = KunenaFactory::getAccessControl()->getAccessLevelsList($category);
	$lists ['pub_recurse'] = JHTML::_ ( 'select.genericlist', $yesno, 'pub_recurse', 'class="inputbox" size="1"', 'value', 'text', $category->pub_recurse );
	$lists ['admin_recurse'] = JHTML::_ ( 'select.genericlist', $yesno, 'admin_recurse', 'class="inputbox" size="1"', 'value', 'text', $category->admin_recurse );
	$lists ['forumLocked'] = JHTML::_ ( 'select.genericlist', $yesno, 'locked', 'class="inputbox" size="1"', 'value', 'text', $category->locked );
	$lists ['forumModerated'] = JHTML::_ ( 'select.genericlist', $yesno, 'moderated', 'class="inputbox" size="1"', 'value', 'text', $category->moderated );
	$lists ['forumReview'] = JHTML::_ ( 'select.genericlist', $yesno, 'review', 'class="inputbox" size="1"', 'value', 'text', $category->review );
	$lists ['allow_polls'] = JHTML::_ ( 'select.genericlist', $yesno, 'allow_polls', 'class="inputbox" size="1"', 'value', 'text', $category->allow_polls );
	$lists ['allow_anonymous'] = JHTML::_ ( 'select.genericlist', $yesno, 'allow_anonymous', 'class="inputbox" size="1"', 'value', 'text', $category->allow_anonymous );
	$lists ['post_anonymous'] = JHTML::_ ( 'select.genericlist', $post_anonymous, 'post_anonymous', 'class="inputbox" size="1"', 'value', 'text', $category->post_anonymous );
	//get a list of moderators, if forum/category is moderated
	$moderatorList = array ();

	if ($category->moderated == 1 && $category->exists()) {
		$kunena_db->setQuery ( "SELECT * FROM #__kunena_moderation AS a INNER JOIN #__users as u ON a.userid=u.id where a.catid=$category->id" );
		$moderatorList = $kunena_db->loadObjectList ();
		KunenaError::checkDatabaseError();
	}

	html_Kunena::editForum ( $category, $categoryList, $moderatorList, $lists, $accessLists, $option, $kunena_config );
}

function saveForum($option) {
	$kunena_app = JFactory::getApplication ();
	if (!JRequest::checkToken()) {
		$kunena_app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
		while (@ob_end_clean());
		$kunena_app->redirect ( JURI::base () . "index.php?option=$option&task=showAdministration" );
	}

	$kunena_db = JFactory::getDBO ();
	$kunena_my = JFactory::getUser ();
	kimport('tables.kunenacategory');
	$row = new TableKunenaCategory ( $kunena_db );
	$id = JRequest::getInt ( 'id', 0, 'post' );
	if ($id) {
		$row->load ( $id );
	}
	if (! $row->save ( JRequest::get('post', JREQUEST_ALLOWRAW), 'parent' )) {
		$kunena_app->enqueueMessage ( $row->getError (), 'error' );
		while (@ob_end_clean());
		$kunena_app->redirect ( JURI::base () . "index.php?option=$option&task=showAdministration" );
	}
	$row->reorder ();

	$kunena_db->setQuery ( "UPDATE #__kunena_sessions SET allowed='na'" );
	$kunena_db->query ();
	KunenaError::checkDatabaseError();

	while (@ob_end_clean());
	$kunena_app->redirect ( JURI::base () . "index.php?option=$option&task=showAdministration" );
}

function orderForum() {
	$kunena_app = JFactory::getApplication ();
	if (!JRequest::checkToken()) {
		$kunena_app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
		while (@ob_end_clean());
		$kunena_app->redirect ( JURI::base () . "index.php?option=com_kunena&task=showAdministration" );
	}

	$kunena_db = JFactory::getDBO();
	$kunena_my = JFactory::getUser ();

	$rettask	= JRequest::getVar( 'return', '', 'post', 'cmd' );
	$order		= JRequest::getVar( 'order', array (), 'post', 'array' );
	$cid		= JRequest::getVar( 'cid', array(), 'post', 'array' );
	$total		= count($cid);

	kimport('category');
	$categories = KunenaCategory::loadCategories($cid);
	foreach ($categories as $category) {
		if (!isset($order[$category->id]) || $category->get('ordering') == $order[$category->id]) continue;
		if (!$category->isCheckedOut($kunena_my->id)) {
			$category->set('ordering', $order[$category->id]);
			if (!$category->save()) {
				$kunena_app->enqueueMessage ( JText::sprintf('COM_KUNENA_A_CATEGORY_SAVE_FAILED', $category->id, $category->getError()), 'notice' );
			}
		} else {
			$kunena_app->enqueueMessage ( JText::sprintf('COM_KUNENA_A_CATEGORY_CHECKED_OUT', $category->id), 'notice' );
		}
	}

	$msg = JText::_('COM_KUNENA_NEW_ORDERING_SAVED');
	while (@ob_end_clean());
	$kunena_app->redirect('index.php?option=com_kunena&task='.$rettask, $msg);
}

function setForumVariable($cid, $variable, $value) {
	$redirect = JURI::base () . "index.php?option=com_kunena&task=showAdministration";
	$kunena_app = JFactory::getApplication ();
	if (!JRequest::checkToken()) {
		$kunena_app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
		while (@ob_end_clean());
		$kunena_app->redirect ( $redirect );
	}
	if (empty ( $cid )) {
		$kunena_app->enqueueMessage ( JText::_('COM_KUNENA_A_NO_CATEGORIES_SELECTED'), 'notice' );
		while (@ob_end_clean());
		$kunena_app->redirect ( $redirect );
	}

	$kunena_my = JFactory::getUser ();
	kimport('category');
	$categories = KunenaCategory::loadCategories($cid);
	$count = 0;
	foreach ($categories as $category) {
		if ($category->get($variable) == $value) continue;
		if (!$category->isCheckedOut($kunena_my->id)) {
			$category->set($variable, $value);
			if ($category->save()) {
				$count++;
			} else {
				$kunena_app->enqueueMessage ( JText::sprintf('COM_KUNENA_A_CATEGORY_SAVE_FAILED', $category->id, $category->getError()), 'notice' );
			}
		} else {
			$kunena_app->enqueueMessage ( JText::sprintf('COM_KUNENA_A_CATEGORY_CHECKED_OUT', $category->id), 'notice' );
		}
	}

	// we must reset fbSession->allowed, when forum record was changed
	$kunena_db = JFactory::getDBO ();
	$kunena_db->setQuery ( "UPDATE #__kunena_sessions SET allowed='na'" );
	$kunena_db->query ();
	KunenaError::checkDatabaseError();

	if (count($cid) == 1) {
		while (@ob_end_clean());
		$kunena_app->redirect ( $redirect, JText::sprintf('COM_KUNENA_A_CATEGORY_SAVED', kescape($category->name)) );
	}
	if (count($cid) > 1) {
		while (@ob_end_clean());
		$kunena_app->redirect ( $redirect, JText::sprintf('COM_KUNENA_A_CATEGORIES_SAVED', $count) );
	}
}

function deleteForum($cid = null, $option) {
	$redirect = JURI::base () . "index.php?option={$option}&task=showAdministration";
	$kunena_app = JFactory::getApplication ();
	if (!JRequest::checkToken()) {
		$kunena_app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
		while (@ob_end_clean());
		$kunena_app->redirect ( $redirect );
	}
	if (empty ( $cid )) {
		$kunena_app->enqueueMessage ( JText::_('COM_KUNENA_A_NO_CATEGORIES_SELECTED'), 'notice' );
		while (@ob_end_clean());
		$kunena_app->redirect ( $redirect );
	}

	kimport('category');
	$categories = KunenaCategory::loadCategories($cid);
	$kunena_my = JFactory::getUser ();
	foreach ($categories as $category) {
		if (!$category->isCheckedOut($kunena_my->id)) {
			if (!$category->delete()) {
				$kunena_app->enqueueMessage ( JText::sprintf('COM_KUNENA_A_CATEGORY_DELETE_FAILED', $category->id, $category->getError()), 'notice' );
			} else {
				$catid[] = $category->id;
			}
		} else {
			$kunena_app->enqueueMessage ( JText::sprintf('COM_KUNENA_A_CATEGORY_CHECKED_OUT', $category->id), 'notice' );
		}
	}

	while (@ob_end_clean());
	$kunena_app->redirect ( $redirect );
}

function cancelForum($option) {
	$redirect = JURI::base () . "index.php?option={$option}&task=showAdministration";
	$kunena_app = JFactory::getApplication ();
	if (!JRequest::checkToken()) {
		$kunena_app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
		while (@ob_end_clean());
		$kunena_app->redirect ( $redirect );
	}
	$id = JRequest::getInt('id', 0);
	kimport('category');
	$kunena_my = JFactory::getUser ();
	$category = KunenaCategory::getInstance ( $id );
	if (!$category->isCheckedOut($kunena_my->id)) {
		$category->checkin ();
	} else {
		$kunena_app->enqueueMessage ( JText::sprintf('COM_KUNENA_A_CATEGORY_CHECKED_OUT', $category->id), 'notice' );
	}
	while (@ob_end_clean());
	$kunena_app->redirect ( $redirect );
}

function orderForumUpDown($uid, $inc, $option) {
	$redirect = JURI::base () . "index.php?option={$option}&task=showAdministration";
	$kunena_app = JFactory::getApplication ();
	if (!JRequest::checkToken()) {
		$kunena_app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
		while (@ob_end_clean());
		$kunena_app->redirect ( $redirect );
	}

	$kunena_db = JFactory::getDBO ();
	kimport('tables.kunenacategory');
	$row = new TableKunenaCategory ( $kunena_db );
	$row->load ( $uid );

	// Ensure that we have the right ordering
	$where = $kunena_db->nameQuote ( 'parent' ) . '=' . $kunena_db->quote ( $row->parent );
	$row->reorder ( $where );
	$row->load ( $uid );
	$row->move ( $inc, $where );

	while (@ob_end_clean());
	$kunena_app->redirect ( $redirect );
}

//===============================
// Config Functions
//===============================
function showConfig($option) {
	require_once (KUNENA_PATH_LIB.'/kunena.timeformat.class.php');
	$kunena_db = &JFactory::getDBO ();
	$kunena_config = KunenaFactory::getConfig ();

	$lists = array ();

	// RSS
	{
		// options to be used later
		$rss_yesno = array ();
		$rss_yesno [] = JHTML::_ ( 'select.option', '0', JText::_('COM_KUNENA_A_NO') );
		$rss_yesno [] = JHTML::_ ( 'select.option', '1', JText::_('COM_KUNENA_A_YES') );

		// ------

		$rss_type = array ();
		$rss_type [] = JHTML::_ ( 'select.option', 'post', JText::_('COM_KUNENA_A_RSS_TYPE_POST') );
		$rss_type [] = JHTML::_ ( 'select.option', 'topic', JText::_('COM_KUNENA_A_RSS_TYPE_TOPIC') );
		$rss_type [] = JHTML::_ ( 'select.option', 'recent', JText::_('COM_KUNENA_A_RSS_TYPE_RECENT') );

		// build the html select list
		$lists ['rss_type'] = JHTML::_ ( 'select.genericlist', $rss_type, 'cfg_rss_type', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->rss_type );

		// ------

		$rss_timelimit = array ();
		$rss_timelimit [] = JHTML::_ ( 'select.option', 'week', JText::_('COM_KUNENA_A_RSS_TIMELIMIT_WEEK') );
		$rss_timelimit [] = JHTML::_ ( 'select.option', 'month', JText::_('COM_KUNENA_A_RSS_TIMELIMIT_MONTH') );
		$rss_timelimit [] = JHTML::_ ( 'select.option', 'year', JText::_('COM_KUNENA_A_RSS_TIMELIMIT_YEAR') );

		// build the html select list
		$lists ['rss_timelimit'] = JHTML::_ ( 'select.genericlist', $rss_timelimit, 'cfg_rss_timelimit', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->rss_timelimit );

		// ------

		$rss_specification = array ();

		$rss_specification [] = JHTML::_ ( 'select.option', 'rss0.91', 'RSS 0.91');
		$rss_specification [] = JHTML::_ ( 'select.option', 'rss1.0', 'RSS 1.0' );
		$rss_specification [] = JHTML::_ ( 'select.option', 'rss2.0', 'RSS 2.0' );
		$rss_specification [] = JHTML::_ ( 'select.option', 'atom1.0', 'Atom 1.0' );

		// build the html select list
		$lists ['rss_specification'] = JHTML::_ ( 'select.genericlist', $rss_specification, 'cfg_rss_specification', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->rss_specification );

		// ------

		$rss_author_format = array ();
		$rss_author_format [] = JHTML::_ ( 'select.option', 'name', JText::_('COM_KUNENA_A_RSS_AUTHOR_FORMAT_NAME') );
		$rss_author_format [] = JHTML::_ ( 'select.option', 'email', JText::_('COM_KUNENA_A_RSS_AUTHOR_FORMAT_EMAIL') );
		$rss_author_format [] = JHTML::_ ( 'select.option', 'both', JText::_('COM_KUNENA_A_RSS_AUTHOR_FORMAT_BOTH') );

		// build the html select list
		$lists ['rss_author_format'] = JHTML::_ ( 'select.genericlist', $rss_author_format, 'cfg_rss_author_format', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->rss_author_format );

		// ------

		// build the html select list
		$lists ['rss_author_in_title'] = JHTML::_ ( 'select.genericlist', $rss_yesno, 'cfg_rss_author_in_title', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->rss_author_in_title );

		// ------

		$rss_word_count = array ();
		$rss_word_count [] = JHTML::_ ( 'select.option', '0', JText::_('COM_KUNENA_A_RSS_WORD_COUNT_ALL') );
		$rss_word_count [] = JHTML::_ ( 'select.option', '50', '50' );
		$rss_word_count [] = JHTML::_ ( 'select.option', '100', '100' );
		$rss_word_count [] = JHTML::_ ( 'select.option', '250', '250' );
		$rss_word_count [] = JHTML::_ ( 'select.option', '500', '500' );
		$rss_word_count [] = JHTML::_ ( 'select.option', '750', '750' );
		$rss_word_count [] = JHTML::_ ( 'select.option', '1000', '1000' );

		// build the html select list
		$lists ['rss_word_count'] = JHTML::_ ( 'select.genericlist', $rss_word_count, 'cfg_rss_word_count', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->rss_word_count );

		// ------

		// build the html select list
		$lists ['rss_allow_html'] = JHTML::_ ( 'select.genericlist', $rss_yesno, 'cfg_rss_allow_html', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->rss_allow_html );

		// ------

		// build the html select list
		$lists ['rss_old_titles'] = JHTML::_ ( 'select.genericlist', $rss_yesno, 'cfg_rss_old_titles', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->rss_old_titles );

		// ------

		$rss_cache = array ();

		$rss_cache [] = JHTML::_ ( 'select.option', '0', '0' );		// disable
		$rss_cache [] = JHTML::_ ( 'select.option', '60', '1' );
		$rss_cache [] = JHTML::_ ( 'select.option', '300', '5' );
		$rss_cache [] = JHTML::_ ( 'select.option', '900', '15' );
		$rss_cache [] = JHTML::_ ( 'select.option', '1800', '30' );
		$rss_cache [] = JHTML::_ ( 'select.option', '3600', '60' );

		$lists ['rss_cache'] = JHTML::_ ( 'select.genericlist', $rss_cache, 'cfg_rss_cache', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->rss_cache );

		// ------

		// build the html select list - (moved enablerss here, to keep all rss-related features together)
		$lists ['enablerss'] = JHTML::_ ( 'select.genericlist', $rss_yesno, 'cfg_enablerss', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->enablerss );
	}

	// build the html select list
	// make a standard yes/no list
	$yesno = array ();
	$yesno [] = JHTML::_ ( 'select.option', '0', JText::_('COM_KUNENA_A_NO') );
	$yesno [] = JHTML::_ ( 'select.option', '1', JText::_('COM_KUNENA_A_YES') );

	$lists ['jmambot'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_jmambot', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->jmambot );
	$lists ['disemoticons'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_disemoticons', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->disemoticons );
	$lists ['regonly'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_regonly', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->regonly );
	$lists ['board_offline'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_board_offline', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->board_offline );
	$lists ['pubwrite'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_pubwrite', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->pubwrite );
	$lists ['useredit'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_useredit', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->useredit );
	$lists ['showhistory'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_showhistory', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->showhistory );
	$lists ['showannouncement'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_showannouncement', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->showannouncement );
	$lists ['avataroncat'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_avataroncat', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->avataroncat );
	$lists ['showchildcaticon'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_showchildcaticon', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->showchildcaticon );
	$lists ['showuserstats'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_showuserstats', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->showuserstats );
	$lists ['showwhoisonline'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_showwhoisonline', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->showwhoisonline );
	$lists ['showpopsubjectstats'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_showpopsubjectstats', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->showpopsubjectstats );
	$lists ['showgenstats'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_showgenstats', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->showgenstats );
	$lists ['showpopuserstats'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_showpopuserstats', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->showpopuserstats );
	$lists ['subscriptionschecked'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_subscriptionschecked', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->subscriptionschecked );
	$lists ['allowfavorites'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_allowfavorites', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->allowfavorites );
	$lists ['mailmod'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_mailmod', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->mailmod );
	$lists ['mailadmin'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_mailadmin', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->mailadmin );
	$lists ['showemail'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_showemail', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->showemail );
	$lists ['askemail'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_askemail', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->askemail );
	$lists ['changename'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_changename', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->changename );
	$lists ['allowavatarupload'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_allowavatarupload', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->allowavatarupload );
	$lists ['allowavatargallery'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_allowavatargallery', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->allowavatargallery );
	$lists ['showstats'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_showstats', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->showstats );
	$lists ['showranking'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_showranking', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->showranking );
	$lists ['rankimages'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_rankimages', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->rankimages );
	$lists ['username'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_username', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->username );
	$lists ['shownew'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_shownew', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->shownew );
	$lists ['allowimageupload'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_allowimageupload', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->allowimageupload );
	$lists ['allowimageregupload'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_allowimageregupload', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->allowimageregupload );
	$lists ['allowfileupload'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_allowfileupload', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->allowfileupload );
	$lists ['allowfileregupload'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_allowfileregupload', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->allowfileregupload );
	$lists ['editmarkup'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_editmarkup', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->editmarkup );
	$lists ['showkarma'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_showkarma', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->showkarma );
	$lists ['enablepdf'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_enablepdf', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->enablepdf );
	$lists ['enableforumjump'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_enableforumjump', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->enableforumjump );
	$lists ['userlist_online'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_userlist_online', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->userlist_online );
	$lists ['userlist_avatar'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_userlist_avatar', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->userlist_avatar );
	$lists ['userlist_name'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_userlist_name', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->userlist_name );
	$lists ['userlist_username'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_userlist_username', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->userlist_username );
	$lists ['userlist_posts'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_userlist_posts', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->userlist_posts );
	$lists ['userlist_karma'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_userlist_karma', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->userlist_karma );
	$lists ['userlist_email'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_userlist_email', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->userlist_email );
	$lists ['userlist_usertype'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_userlist_usertype', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->userlist_usertype );
	$lists ['userlist_joindate'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_userlist_joindate', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->userlist_joindate );
	$lists ['userlist_lastvisitdate'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_userlist_lastvisitdate', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->userlist_lastvisitdate );
	$lists ['userlist_userhits'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_userlist_userhits', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->userlist_userhits );
	$lists ['usernamechange'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_usernamechange', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->usernamechange );
	$lists ['reportmsg'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_reportmsg', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->reportmsg );
	$lists ['captcha'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_captcha', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->captcha );
	$lists ['mailfull'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_mailfull', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->mailfull );
	// New for 1.0.5
	$lists ['showspoilertag'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_showspoilertag', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->showspoilertag );
	$lists ['showvideotag'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_showvideotag', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->showvideotag );
	$lists ['showebaytag'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_showebaytag', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->showebaytag );
	$lists ['trimlongurls'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_trimlongurls', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->trimlongurls );
	$lists ['autoembedyoutube'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_autoembedyoutube', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->autoembedyoutube );
	$lists ['autoembedebay'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_autoembedebay', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->autoembedebay );
	$lists ['highlightcode'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_highlightcode', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->highlightcode );
	// New for 1.5.8 -> SEF
	$lists ['sef'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_sef', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->sef );
	$lists ['sefcats'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_sefcats', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->sefcats );
	$lists ['sefutf8'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_sefutf8', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->sefutf8 );
	// New for 1.6 -> Hide images and files for guests
	$lists['showimgforguest'] = JHTML::_('select.genericlist', $yesno, 'cfg_showimgforguest', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->showimgforguest);
	$lists['showfileforguest'] = JHTML::_('select.genericlist', $yesno, 'cfg_showfileforguest', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->showfileforguest);
	// New for 1.6 -> Check Image MIME types
	$lists['checkmimetypes'] = JHTML::_('select.genericlist', $yesno, 'cfg_checkmimetypes', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->checkmimetypes);
	//New for 1.6 -> Poll
	$lists['pollallowvoteone'] = JHTML::_('select.genericlist', $yesno, 'cfg_pollallowvoteone', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->pollallowvoteone);
  	$lists['pollenabled'] = JHTML::_('select.genericlist', $yesno, 'cfg_pollenabled', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->pollenabled);
  	$lists['showpoppollstats'] = JHTML::_('select.genericlist', $yesno, 'cfg_showpoppollstats', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->showpoppollstats);
  	$lists['pollresultsuserslist'] = JHTML::_('select.genericlist', $yesno, 'cfg_pollresultsuserslist', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->pollresultsuserslist);
  	//New for 1.6 -> Choose ordering system
  	$ordering_system_list = array ();
  	$ordering_system_list[] = JHTML::_('select.option', 'mesid',JText::_('COM_KUNENA_COM_A_ORDERING_SYSTEM_NEW'));
  	$ordering_system_list[] = JHTML::_('select.option', 'replyid', JText::_('COM_KUNENA_COM_A_ORDERING_SYSTEM_OLD'));
  	$lists['ordering_system'] = JHTML::_('select.genericlist', $ordering_system_list, 'cfg_ordering_system', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->ordering_system);
	// New for 1.6: datetime
	require_once(KUNENA_PATH_LIB . '/kunena.timeformat.class.php');
	$dateformatlist = array ();
	$time = CKunenaTimeformat::internalTime() - 80000;
	$dateformatlist[] = JHTML::_('select.option', 'none', JText::_('COM_KUNENA_OPTION_DATEFORMAT_NONE'));
	$dateformatlist[] = JHTML::_('select.option', 'ago', CKunenaTimeformat::showDate($time, 'ago'));
	$dateformatlist[] = JHTML::_('select.option', 'datetime_today', CKunenaTimeformat::showDate($time, 'datetime_today'));
	$dateformatlist[] = JHTML::_('select.option', 'datetime', CKunenaTimeformat::showDate($time, 'datetime'));
	$lists['post_dateformat'] = JHTML::_('select.genericlist', $dateformatlist, 'cfg_post_dateformat', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->post_dateformat);
	$lists['post_dateformat_hover'] = JHTML::_('select.genericlist', $dateformatlist, 'cfg_post_dateformat_hover', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->post_dateformat_hover);
	// New for 1.6: hide ip
	$lists['hide_ip'] = JHTML::_('select.genericlist', $yesno, 'cfg_hide_ip', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->hide_ip);
	//New for 1.6: choose if you want that ghost message box checked by default
	$lists['boxghostmessage'] = JHTML::_('select.genericlist', $yesno, 'cfg_boxghostmessage', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->boxghostmessage);
	// New for 1.6 -> Thank you button
	$lists ['showthankyou'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_showthankyou', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->showthankyou );

	kimport('integration.integration');
	$lists['integration_access'] = KunenaIntegration::getConfigOptions('access');
	$lists['integration_activity'] = KunenaIntegration::getConfigOptions('activity');
	$lists['integration_avatar'] = KunenaIntegration::getConfigOptions('avatar');
	$lists['integration_login'] = KunenaIntegration::getConfigOptions('login');
	$lists['integration_profile'] = KunenaIntegration::getConfigOptions('profile');
	$lists['integration_private'] = KunenaIntegration::getConfigOptions('private');

	$listUserDeleteMessage = array();
	$listUserDeleteMessage[] = JHTML::_('select.option', '0', JText::_('COM_KUNENA_A_DELETEMESSAGE_NOT_ALLOWED'));
	$listUserDeleteMessage[] = JHTML::_('select.option', '1', JText::_('COM_KUNENA_A_DELETEMESSAGE_ALLOWED_IF_REPLIES'));
	$listUserDeleteMessage[] = JHTML::_('select.option', '2', JText::_('COM_KUNENA_A_DELETEMESSAGE_ALWAYS_ALLOWED'));
	$lists['userdeletetmessage'] = JHTML::_('select.genericlist', $listUserDeleteMessage, 'cfg_userdeletetmessage', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->userdeletetmessage);

	$latestCategoryIn = array();
	$latestCategoryIn[] = JHTML::_('select.option', '0', JText::_('COM_KUNENA_COM_A_LATESTCATEGORY_IN_HIDE'));
	$latestCategoryIn[] = JHTML::_('select.option', '1', JText::_('COM_KUNENA_COM_A_LATESTCATEGORY_IN_SHOW'));
	$lists['latestcategory_in'] = JHTML::_('select.genericlist', $latestCategoryIn, 'cfg_latestcategory_in', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->latestcategory_in);

	$optionsShowHide = array();
	$optionsShowHide[] = JHTML::_('select.option', 0, JText::_('COM_KUNENA_COM_A_LATESTCATEGORY_SHOWALL'));
	$lists['latestcategory'] = CKunenaTools::KSelectList('cfg_latestcategory[]', $optionsShowHide, 'class="inputbox" multiple="multiple"', false, 'latestcategory', explode(',',$kunena_config->latestcategory));

	$lists['topicicons'] = JHTML::_('select.genericlist', $yesno, 'cfg_topicicons', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->topicicons);

	$lists['onlineusers'] = JHTML::_('select.genericlist', $yesno, 'cfg_onlineusers', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->onlineusers);

	$lists['debug'] = JHTML::_('select.genericlist', $yesno, 'cfg_debug', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->debug);

	$lists['showbannedreason'] = JHTML::_('select.genericlist', $yesno, 'cfg_showbannedreason', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->showbannedreason);

	$lists['version_check'] = JHTML::_('select.genericlist', $yesno, 'cfg_version_check', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->version_check);

	$lists['showpopthankyoustats'] = JHTML::_('select.genericlist', $yesno, 'cfg_showpopthankyoustats', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->showpopthankyoustats);

	$seerestoredeleted = array();
	$seerestoredeleted[] =JHTML::_('select.option', 2, JText::_('COM_KUNENA_A_SEE_RESTORE_DELETED_NOBODY'));
	$seerestoredeleted[] =JHTML::_('select.option', 0, JText::_('COM_KUNENA_A_SEE_RESTORE_DELETED_ADMINS'));
	$seerestoredeleted[] =JHTML::_('select.option', 1, JText::_('COM_KUNENA_A_SEE_RESTORE_DELETED_ADMINSMODS'));
	$lists ['mod_see_deleted'] = JHTML::_('select.genericlist', $seerestoredeleted, 'cfg_mod_see_deleted', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->mod_see_deleted);


	$listBbcodeImgSecure = array();
	$listBbcodeImgSecure[] = JHTML::_('select.option', 'text', JText::_('COM_KUNENA_COM_A_BBCODE_IMG_SECURE_OPTION_TEXT'));
	$listBbcodeImgSecure[] = JHTML::_('select.option', 'link', JText::_('COM_KUNENA_COM_A_BBCODE_IMG_SECURE_OPTION_LINK'));
	$listBbcodeImgSecure[] = JHTML::_('select.option', 'image', JText::_('COM_KUNENA_COM_A_BBCODE_IMG_SECURE_OPTION_IMAGE'));
	$lists ['bbcode_img_secure'] = JHTML::_('select.genericlist', $listBbcodeImgSecure, 'cfg_bbcode_img_secure', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->bbcode_img_secure);

	$lists ['listcat_show_moderators'] = JHTML::_('select.genericlist', $yesno, 'cfg_listcat_show_moderators', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->listcat_show_moderators);

	$showlightbox = $yesno;
	$showlightbox[] = JHTML::_('select.option', 2, JText::_('COM_KUNENA_A_LIGHTBOX_NO_JS'));
	$lists ['lightbox'] = JHTML::_('select.genericlist', $showlightbox, 'cfg_lightbox', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->lightbox);

	$timesel[] = JHTML::_('select.option', 0, JText::_('COM_KUNENA_SHOW_LASTVISIT'));
	$timesel[] = JHTML::_('select.option', 4, JText::_('COM_KUNENA_SHOW_4_HOURS'));
	$timesel[] = JHTML::_('select.option', 8, JText::_('COM_KUNENA_SHOW_8_HOURS'));
	$timesel[] = JHTML::_('select.option', 12, JText::_('COM_KUNENA_SHOW_12_HOURS'));
	$timesel[] = JHTML::_('select.option', 24, JText::_('COM_KUNENA_SHOW_24_HOURS'));
	$timesel[] = JHTML::_('select.option', 48, JText::_('COM_KUNENA_SHOW_48_HOURS'));
	$timesel[] = JHTML::_('select.option', 168, JText::_('COM_KUNENA_SHOW_WEEK'));
	$timesel[] = JHTML::_('select.option', 720, JText::_('COM_KUNENA_SHOW_MONTH'));
	$timesel[] = JHTML::_('select.option', 8760, JText::_('COM_KUNENA_SHOW_YEAR'));
	// build the html select list
	$lists ['show_list_time'] = JHTML::_('select.genericlist', $timesel, 'cfg_show_list_time', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->show_list_time);

	$sessiontimetype[] = JHTML::_('select.option', 0, JText::_('COM_KUNENA_SHOW_SESSION_TYPE_ALL'));
	$sessiontimetype[] = JHTML::_('select.option', 1, JText::_('COM_KUNENA_SHOW_SESSION_TYPE_VALID'));
	$sessiontimetype[] = JHTML::_('select.option', 2, JText::_('COM_KUNENA_SHOW_SESSION_TYPE_TIME'));

	$lists ['show_session_type'] = JHTML::_('select.genericlist', $sessiontimetype, 'cfg_show_session_type', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->show_session_type);

	$userlist_allowed = array ();
	$userlist_allowed [] = JHTML::_ ( 'select.option', '1', JText::_('COM_KUNENA_A_NO') );
	$userlist_allowed [] = JHTML::_ ( 'select.option', '0', JText::_('COM_KUNENA_A_YES') );
	$lists ['userlist_allowed'] = JHTML::_('select.genericlist', $userlist_allowed, 'cfg_userlist_allowed', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->userlist_allowed);
	$lists ['pubprofile'] = JHTML::_('select.genericlist', $yesno, 'cfg_pubprofile', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->pubprofile);

	$userlist_count_users[] = JHTML::_('select.option', 0, JText::_('COM_KUNENA_SHOW_USERLIST_COUNTUNSERS_ALL'));
	$userlist_count_users[] = JHTML::_('select.option', 1, JText::_('COM_KUNENA_SHOW_USERLIST_COUNTUNSERS_ACTIVATED_ACCOUNT'));
	$userlist_count_users[] = JHTML::_('select.option', 2, JText::_('COM_KUNENA_SHOW_USERLIST_COUNTUNSERS_ACTIVE'));
	$userlist_count_users[] = JHTML::_('select.option', 3, JText::_('COM_KUNENA_SHOW_USERLIST_COUNTUNSERS_NON_BLOCKED_USERS'));
	$lists ['userlist_count_users'] = JHTML::_('select.genericlist', $userlist_count_users, 'cfg_userlist_count_users', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->userlist_count_users);

	// Added new options into K1.6.4
	$lists ['allowsubscriptions'] = JHTML::_ ( 'select.genericlist', $yesno, 'cfg_allowsubscriptions', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->allowsubscriptions );

	$category_subscriptions = array();
	$category_subscriptions[] = JHTML::_('select.option', 'disabled', JText::_('COM_KUNENA_OPTION_CATEGORY_SUBSCRIPTIONS_DISABLED'));
	$category_subscriptions[] = JHTML::_('select.option', 'topic', JText::_('COM_KUNENA_OPTION_CATEGORY_SUBSCRIPTIONS_TOPIC'));
	$category_subscriptions[] = JHTML::_('select.option', 'post', JText::_('COM_KUNENA_OPTION_CATEGORY_SUBSCRIPTIONS_POST'));
	$lists ['category_subscriptions'] = JHTML::_ ( 'select.genericlist', $category_subscriptions, 'cfg_category_subscriptions', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->category_subscriptions );

	$topic_subscriptions = array();
	$topic_subscriptions[] = JHTML::_('select.option', 'disabled', JText::_('COM_KUNENA_OPTION_TOPIC_SUBSCRIPTIONS_DISABLED'));
	$topic_subscriptions[] = JHTML::_('select.option', 'first', JText::_('COM_KUNENA_OPTION_TOPIC_SUBSCRIPTIONS_FIRST'));
	$topic_subscriptions[] = JHTML::_('select.option', 'every', JText::_('COM_KUNENA_OPTION_TOPIC_SUBSCRIPTIONS_EVERY'));
	$lists ['topic_subscriptions'] = JHTML::_ ( 'select.genericlist', $topic_subscriptions, 'cfg_topic_subscriptions', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->topic_subscriptions );

	// Added new options into K1.6.6
	$email_recipient_privacy = array();
	$email_recipient_privacy[] = JHTML::_('select.option', 'to', JText::_('COM_KUNENA_A_SUBSCRIPTIONS_EMAIL_RECIPIENT_PRIVACY_OPTION_TO'));
	$email_recipient_privacy[] = JHTML::_('select.option', 'cc', JText::_('COM_KUNENA_A_SUBSCRIPTIONS_EMAIL_RECIPIENT_PRIVACY_OPTION_CC'));
	$email_recipient_privacy[] = JHTML::_('select.option', 'bcc', JText::_('COM_KUNENA_A_SUBSCRIPTIONS_EMAIL_RECIPIENT_PRIVACY_OPTION_BCC'));
	$lists ['email_recipient_privacy'] = JHTML::_ ( 'select.genericlist', $email_recipient_privacy, 'cfg_email_recipient_privacy', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->email_recipient_privacy );

	$recaptcha_theme = array();
	$recaptcha_theme[] = JHTML::_('select.option', 'red', JText::_('COM_KUNENA_A_RECAPTCHA_THEME_OPTION_RED'));
	$recaptcha_theme[] = JHTML::_('select.option', 'white', JText::_('COM_KUNENA_A_RECAPTCHA_THEME_OPTION_WHITE'));
	$recaptcha_theme[] = JHTML::_('select.option', 'blackglass', JText::_('COM_KUNENA_A_RECAPTCHA_THEME_OPTION_BLACK'));
	$recaptcha_theme[] = JHTML::_('select.option', 'clean', JText::_('COM_KUNENA_A_RECAPTCHA_THEME_OPTION_CLEAN'));
	$lists ['recaptcha_theme'] = JHTML::_ ( 'select.genericlist', $recaptcha_theme, 'cfg_recaptcha_theme', 'class="inputbox" size="1"', 'value', 'text', $kunena_config->recaptcha_theme );

	html_Kunena::showConfig($kunena_config, $lists, $option);
}

function defaultConfig($option) {
	$kunena_app = JFactory::getApplication ();
	$kunena_config = KunenaFactory::getConfig ();
	$kunena_config->backup ();
	$kunena_config->remove ();
	$kunena_config = new CKunenaConfig();
	$kunena_config->create();

	$kunena_db = &JFactory::getDBO ();
	$kunena_db->setQuery ( "UPDATE #__kunena_sessions SET allowed='na'" );
	$kunena_db->query ();
	KunenaError::checkDatabaseError();

	while (@ob_end_clean());
	$kunena_app->redirect ( JURI::base () . "index.php?option=$option&task=showconfig", JText::_('COM_KUNENA_CONFIG_DEFAULT') );
}

function revertConfig($option) {
	$kunena_app = JFactory::getApplication ();
	$kunena_db = &JFactory::getDBO ();

	$isExistTableConfigBackup = $kunena_db->getTableFields('#__kunena_config_backup');
	if ( $isExistTableConfigBackup ) {
		$kunena_config = KunenaFactory::getConfig ();
		$kunena_config->remove ();

		$kunena_db->setQuery ( "ALTER TABLE #__kunena_config_backup RENAME #__kunena_config" );
		$kunena_db->query ();
		KunenaError::checkDatabaseError();

		while (@ob_end_clean());
		$kunena_app->redirect ( JURI::base () . "index.php?option=$option&task=showconfig", JText::_('COM_KUNENA_CONFIG_REVERT_CONFIG_DONE') );
	} else {
		while (@ob_end_clean());
		$kunena_app->redirect ( JURI::base () . "index.php?option=$option&task=showconfig", JText::_('COM_KUNENA_CONFIG_REVERT_CONFIG_CANNOT') );
	}
}

function saveConfig($option) {
	$kunena_app = & JFactory::getApplication ();
	$kunena_config = KunenaFactory::getConfig ();
	$kunena_db = &JFactory::getDBO ();

	foreach ( JRequest::get('post', JREQUEST_ALLOWHTML) as $postsetting => $postvalue ) {
		if (JString::strpos ( $postsetting, 'cfg_' ) === 0) {
			//remove cfg_ and force lower case
			if ( is_array($postvalue) ) {
				$postvalue = implode(',',$postvalue);
			}
			$postname = JString::strtolower ( JString::substr ( $postsetting, 4 ) );

			// No matter what got posted, we only store config parameters defined
			// in the config class. Anything else posted gets ignored.
			if (array_key_exists ( $postname, $kunena_config->GetClassVars () )) {
				if (is_numeric ( $postvalue )) {
					eval ( "\$kunena_config->" . $postname . " = " . $postvalue . ";" );
				} else {
					// Rest is treaded as strings
					eval ( "\$kunena_config->" . $postname . " = '" . $postvalue . "';" );
				}
			} else {
				// This really should not happen if assertions are enable
				// fail it and display the current scope of variables for debugging.
				trigger_error ( 'Unknown configuration variable posted.' );
				assert ( 0 );
			}
		}
	}

	$kunena_config->backup ();
	$kunena_config->remove ();
	$kunena_config->create ();

	$kunena_db->setQuery ( "UPDATE #__kunena_sessions SET allowed='na'" );
	$kunena_db->query ();
	KunenaError::checkDatabaseError();

	while (@ob_end_clean());
	$kunena_app->redirect ( JURI::base () . "index.php?option=$option&task=showconfig", JText::_('COM_KUNENA_CONFIGSAVED') );
}

//===============================
// CSS functions
//===============================
function showCss($option) {
	require_once (KUNENA_PATH_LIB . '/kunena.file.class.php');

	$kunena_config = KunenaFactory::getConfig ();
	$file = KUNENA_PATH_TEMPLATE . '/' . $kunena_config->template . '/css/kunena.forum.css';
	$permission = CKunenaPath::isWritable ( $file );

	if (! $permission) {
		echo "<center><h1><font color=red>" . JText::_('COM_KUNENA_WARNING') . "</font></h1><br />";
		echo "<b>" . JText::_('COM_KUNENA_CFC_FILENAME') . ": " . $file . "</b><br />";
		echo "<b>" . JText::_('COM_KUNENA_CHMOD1') . "</b></center><br /><br />";
	}

	html_Kunena::showCss ( $file, $option );
}

function saveCss($file, $csscontent, $option) {
	require_once (KUNENA_PATH_LIB . '/kunena.file.class.php');

	$kunena_app = & JFactory::getApplication ();
	$tmpstr = JText::_('COM_KUNENA_CSS_SAVE');
	$tmpstr = str_replace ( "%file%", $file, $tmpstr );
	echo $tmpstr;

	if (CKunenaFile::write ( $file, $csscontent )) {
		while (@ob_end_clean());
		$kunena_app->redirect ( JURI::base () . "index.php?option=$option&task=showCss", JText::_('COM_KUNENA_CFC_SAVED') );
	} else {
		while (@ob_end_clean());
		$kunena_app->redirect ( JURI::base () . "index.php?option=$option&task=showCss", JText::_('COM_KUNENA_CFC_NOTSAVED') );
	}
}

//===============================
// Moderator Functions
//===============================
function newModerator($option, $id = null) {
	$kunena_app = & JFactory::getApplication ();
	$kunena_db = &JFactory::getDBO ();
	//die ("New Moderator");
	//$limit = intval(JRequest::getVar( 'limit', 10));
	//$limitstart = intval(JRequest::getVar( 'limitstart', 0));
	$limit = $kunena_app->getUserStateFromRequest ( "global.list.limit", 'limit', $kunena_app->getCfg ( 'list_limit' ), 'int' );
	$limitstart = $kunena_app->getUserStateFromRequest ( "{$option}.limitstart", 'limitstart', 0, 'int' );
	$kunena_db->setQuery ( "SELECT COUNT(*) FROM #__users AS a LEFT JOIN #__kunena_users AS b ON a.id=b.userid WHERE b.moderator=1" );
	$total = $kunena_db->loadResult ();
	if (KunenaError::checkDatabaseError()) return;

	if ($limitstart >= $total)
		$limitstart = 0;
	if ($limit == 0 || $limit > 100)
		$limit = 100;

	$kunena_db->setQuery ( "SELECT * FROM #__users AS a LEFT JOIN #__kunena_users AS b ON a.id=b.userid WHERE b.moderator=1", $limitstart, $limit );
	$userList = $kunena_db->loadObjectList ();
	if (KunenaError::checkDatabaseError()) return;
	$countUL = count ( $userList );

	jimport ( 'joomla.html.pagination' );
	$pageNav = new JPagination ( $total, $limitstart, $limit );
	//$id = intval( JRequest::getVar('id') );
	//get forum name
	$forumName = '';
	$kunena_db->setQuery ( "SELECT name FROM #__kunena_categories WHERE id=$id" );
	$forumName = $kunena_db->loadResult ();
	if (KunenaError::checkDatabaseError()) return;

	//get forum moderators
	$kunena_db->setQuery ( "SELECT userid FROM #__kunena_moderation WHERE catid=$id" );
	$moderatorList = $kunena_db->loadObjectList ();
	if (KunenaError::checkDatabaseError()) return;
	$moderators = 0;
	$modIDs [] = array ();

	if (count ( $moderatorList ) > 0) {
		foreach ( $moderatorList as $ml ) {
			$modIDs [] = $ml->userid;
		}

		$moderators = 1;
	} else {
		$moderators = 0;
	}

	html_Kunena::newModerator ( $option, $id, $moderators, $modIDs, $forumName, $userList, $countUL, $pageNav );
}

function addModerator($option, $id, $cid = null, $publish = 1) {
	$kunena_app = & JFactory::getApplication ();
	$kunena_db = &JFactory::getDBO ();
	$kunena_my = &JFactory::getUser ();

	$numcid = count ( $cid );
	$action = "";

	if ($publish == 1) {
		$action = 'add';
	} else {
		$action = 'remove';
	}

	JArrayHelper::toInteger($cid);

	if (! is_array ( $cid ) || count ( $cid ) < 1) {
		echo "<script> alert('" . JText::_('COM_KUNENA_SELECTMODTO') . " $action'); window.history.go(-1);</script>\n";
		exit ();
	}

	if ($action == 'add') {
		for($i = 0, $n = count ( $cid ); $i < $n; $i ++) {
			$kunena_db->setQuery ( "INSERT INTO #__kunena_moderation SET catid='$id', userid='$cid[$i]'" );
			$kunena_db->query ();
			if (KunenaError::checkDatabaseError()) return;
		}
	} else {
		for($i = 0, $n = count ( $cid ); $i < $n; $i ++) {
			$kunena_db->setQuery ( "DELETE FROM #__kunena_moderation WHERE catid='$id' AND userid='$cid[$i]'" );
			$kunena_db->query ();
			if (KunenaError::checkDatabaseError()) return;
		}
	}

	kimport('tables.kunenacategory');
	$row = new TableKunenaCategory ( $kunena_db );
	$row->checkin ( $id );

	$kunena_db->setQuery ( "UPDATE #__kunena_sessions SET allowed='na'" );
	$kunena_db->query ();
	KunenaError::checkDatabaseError();

	while (@ob_end_clean());
	$kunena_app->redirect ( JURI::base () . "index.php?option=$option&task=edit2&uid=" . $id );
}

//===============================
//   User Profile functions
//===============================
function showProfiles($kunena_db, $option, $order) {
	$kunena_app = JFactory::getApplication ();
	$kunena_db = JFactory::getDBO ();

	$filter_order = $kunena_app->getUserStateFromRequest( $option.'user_filter_order', 'filter_order', 'id', 'cmd' );
	$filter_order_Dir = $kunena_app->getUserStateFromRequest( $option.'user_filter_order_Dir', 'filter_order_Dir', 'asc', 'word' );
	if ($filter_order_Dir != 'asc') $filter_order_Dir = 'desc';
	$limit = $kunena_app->getUserStateFromRequest ( "global.list.limit", 'limit', $kunena_app->getCfg ( 'list_limit' ), 'int' );
	$limitstart = $kunena_app->getUserStateFromRequest ( "{$option}.user_limitstart", 'limitstart', 0, 'int' );

	$search = $kunena_app->getUserStateFromRequest( $option.'user_search', 'search', '', 'string' );

	$order = '';
	if ($filter_order == 'id') {
		$order = ' ORDER BY u.id '. $filter_order_Dir;
	} else if ($filter_order == 'username') {
		$order = ' ORDER BY u.username '. $filter_order_Dir ;
	} else if ($filter_order == 'name') {
		$order = ' ORDER BY u.name '. $filter_order_Dir ;
	} else if ($filter_order == 'moderator') {
		$order = ' ORDER BY ku.moderator '. $filter_order_Dir ;
	}

	$where = array ();
	if (isset ( $search ) && $search != "") {
		$searchstr = $kunena_db->getEscaped ( JString::trim ( JString::strtolower ( $search ) ) );
		$whereid = '';
		if (intval($searchstr)>0) $whereid = 'OR u.id='.intval($searchstr);
		$where [] = "(u.username LIKE '%$searchstr%' OR u.email LIKE '%$searchstr%' OR u.name LIKE '%$searchstr%' $whereid)";
	}
	$where = count ($where) ? implode ( ' AND ', $where ) : '1';

	$kunena_db->setQuery ( "SELECT COUNT(*) FROM #__kunena_users AS ku
		INNER JOIN #__users AS u ON ku.userid=u.id
		WHERE {$where}");
	$total = $kunena_db->loadResult ();
	KunenaError::checkDatabaseError();

	if ($limitstart >= $total)
		$limitstart = 0;
	if ($limit == 0 || $limit > 100)
		$limit = 100;

	$kunena_db->setQuery ( "SELECT u.id, u.username, u.name, ku.moderator
		FROM #__kunena_users AS ku
		INNER JOIN #__users AS u ON ku.userid=u.id
		WHERE {$where}
		{$order}", $limitstart, $limit );

	$users = $kunena_db->loadObjectList ();
	if (KunenaError::checkDatabaseError()) return;

	// table ordering
	$lists['order_Dir']	= $filter_order_Dir;
	$lists['order']		= $filter_order;

	$lists['search']= $search;

	jimport ( 'joomla.html.pagination' );
	$pageNav = new JPagination ( $total, $limitstart, $limit );
	html_Kunena::showProfiles ( $option, $users, $pageNav, $order, $lists );
}

function editUserProfile($option, $uid) {
	if (empty ( $uid [0] )) {
		echo JText::_('COM_KUNENA_PROFILE_NO_USER');
		return;
	}

	$kunena_db = &JFactory::getDBO ();
	$kunena_acl = &JFactory::getACL ();

	$kunena_db->setQuery ( "SELECT * FROM #__kunena_users LEFT JOIN #__users on #__users.id=#__kunena_users.userid WHERE userid=$uid[0]" );
	$userDetails = $kunena_db->loadObjectList ();
	if (KunenaError::checkDatabaseError()) return;
	$user = $userDetails [0];

	//Mambo userids are unique, so we don't worry about that
	$prefview = $user->view;
	$ordering = $user->ordering;
	$moderator = $user->moderator;
	$userRank = $user->rank;

	//grab all special ranks
	$kunena_db->setQuery ( "SELECT * FROM #__kunena_ranks WHERE rank_special = '1'" );
	$specialRanks = $kunena_db->loadObjectList ();
	if (KunenaError::checkDatabaseError()) return;

	//build select list options
	$yesnoRank [] = JHTML::_ ( 'select.option', '0', JText::_('COM_KUNENA_RANK_NO_ASSIGNED') );
	foreach ( $specialRanks as $ranks ) {
		$yesnoRank [] = JHTML::_ ( 'select.option', $ranks->rank_id, $ranks->rank_title );
	}
	//build special ranks select list
	$selectRank = JHTML::_ ( 'select.genericlist', $yesnoRank, 'newrank', 'class="inputbox" size="5"', 'value', 'text', $userRank );

	// make the select list for the view type
	$yesno [] = JHTML::_ ( 'select.option', 'flat', JText::_('COM_KUNENA_A_FLAT') );
	$yesno [] = JHTML::_ ( 'select.option', 'threaded', JText::_('COM_KUNENA_A_THREADED') );
	// build the html select list
	$selectPref = JHTML::_ ( 'select.genericlist', $yesno, 'newview', 'class="inputbox" size="2"', 'value', 'text', $prefview );
	// make the select list for the moderator flag
	$yesnoMod [] = JHTML::_ ( 'select.option', '1', JText::_('COM_KUNENA_ANN_YES') );
	$yesnoMod [] = JHTML::_ ( 'select.option', '0', JText::_('COM_KUNENA_ANN_NO') );
	// build the html select list
	$selectMod = JHTML::_ ( 'select.genericlist', $yesnoMod, 'moderator', 'class="inputbox" size="2"', 'value', 'text', $moderator );
	// make the select list for the moderator flag
	$yesnoOrder [] = JHTML::_ ( 'select.option', '0', JText::_('COM_KUNENA_USER_ORDER_ASC') );
	$yesnoOrder [] = JHTML::_ ( 'select.option', '1', JText::_('COM_KUNENA_USER_ORDER_DESC') );
	// build the html select list
	$selectOrder = JHTML::_ ( 'select.genericlist', $yesnoOrder, 'neworder', 'class="inputbox" size="2"', 'value', 'text', $ordering );

	//get all subscriptions for this user
	$kunena_db->setQuery ( "select thread from #__kunena_subscriptions where userid=$uid[0]" );
	$subslist = $kunena_db->loadObjectList ();
	if (KunenaError::checkDatabaseError()) return;

	//get all categories subscriptions for this user
	$kunena_db->setQuery ( "select catid from #__kunena_subscriptions_categories where userid=$uid[0]" );
	$subscatslist = $kunena_db->loadObjectList ();
	if (KunenaError::checkDatabaseError()) return;

	//get all moderation category ids for this user
	$kunena_db->setQuery ( "select catid from #__kunena_moderation where userid=" . $uid [0] );
	$modCatList = $kunena_db->loadResultArray ();
	if (KunenaError::checkDatabaseError()) return;
	if ($moderator && empty($modCatList)) $modCatList[] = 0;

	$categoryList = array();
	$categoryList[] = JHTML::_('select.option', 0, JText::_('COM_KUNENA_GLOBAL_MODERATOR'));
	$modCats = CKunenaTools::KSelectList('catid[]', $categoryList, 'class="inputbox" multiple="multiple"', false, 'kforums', $modCatList);

	//get all IPs used by this user
	$kunena_db->setQuery ( "SELECT ip FROM #__kunena_messages WHERE userid=$uid[0] GROUP BY ip" );
	$iplist = implode("','", $kunena_db->loadResultArray ());
	if (KunenaError::checkDatabaseError()) return;

	$list = array();
	if ($iplist) {
		$iplist = "'{$iplist}'";
		$kunena_db->setQuery ( "SELECT m.ip,m.userid,u.username,COUNT(*) as mescnt FROM #__kunena_messages AS m INNER JOIN #__users AS u ON m.userid=u.id WHERE m.ip IN ({$iplist}) GROUP BY m.userid,m.ip" );
		$list = $kunena_db->loadObjectlist ();
		if (KunenaError::checkDatabaseError()) return;
	}
	$useridslist = array();
	foreach ($list as $item) {
		$useridslist[$item->ip][] = $item;
	}

	html_Kunena::editUserProfile ( $option, $user, $subslist, $subscatslist, $selectRank, $selectPref, $selectMod, $selectOrder, $uid [0], $modCats, $useridslist );
}

function saveUserProfile($option) {
	$kunena_app = & JFactory::getApplication ();
	$kunena_db = &JFactory::getDBO ();

	if (!JRequest::checkToken()) {
		$kunena_app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
		while (@ob_end_clean());
		$kunena_app->redirect ( JURI::base () . "index.php?option=$option&task=showprofiles" );
	}

	$newview = JRequest::getVar ( 'newview' );
	$newrank = JRequest::getVar ( 'newrank' );
	$signature = JRequest::getVar ( 'message' );
	$deleteSig = JRequest::getVar ( 'deleteSig' );
	$moderator = JRequest::getInt ( 'moderator' );
	$uid = JRequest::getInt ( 'uid' );
	$avatar = JRequest::getVar ( 'avatar' );
	$deleteAvatar = JRequest::getVar ( 'deleteAvatar' );
	$neworder = JRequest::getInt ( 'neworder' );
	$modCatids = JRequest::getVar ( 'catid', array () );

	if ($deleteSig == 1) {
		$signature = "";
	}
	$avatar = '';
	if ($deleteAvatar == 1) {
		$avatar = ",avatar=''";
	}

	$kunena_db->setQuery ( "UPDATE #__kunena_users SET signature={$kunena_db->quote($signature)}, view='$newview',moderator='$moderator', ordering='$neworder', rank='$newrank' $avatar where userid='$uid'" );
	$kunena_db->query ();
	if (KunenaError::checkDatabaseError()) return;

	//delete all moderator traces before anyway
	$kunena_db->setQuery ( "DELETE FROM #__kunena_moderation WHERE userid='$uid'" );
	$kunena_db->query ();
	if (KunenaError::checkDatabaseError()) return;

	//if there are moderatored forums, add them all
	if ($moderator == 1) {
		if (!empty ( $modCatids ) && !in_array(0, $modCatids)) {
			foreach ( $modCatids as $c ) {
				$kunena_db->setQuery ( "INSERT INTO #__kunena_moderation SET catid='$c', userid='$uid'" );
				$kunena_db->query ();
				if (KunenaError::checkDatabaseError()) return;
			}
		}
	}

	$kunena_db->setQuery ( "UPDATE #__kunena_sessions SET allowed='na' WHERE userid='$uid'" );
	$kunena_db->query ();
	KunenaError::checkDatabaseError();

	while (@ob_end_clean());
	$kunena_app->redirect ( JURI::base () . "index.php?option=com_kunena&task=showprofiles" );
}

function trashUserMessages ( $option, $uid ) {
	$kunena_db = &JFactory::getDBO ();
	$kunena_app = & JFactory::getApplication ();

	if (!JRequest::checkToken()) {
		$kunena_app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
		while (@ob_end_clean());
		$kunena_app->redirect ( JURI::base () . "index.php?option=$option&task=profiles" );
	}

	$path = KUNENA_PATH_LIB.'/kunena.moderation.class.php';
	require_once ($path);
	$kunena_mod = CKunenaModeration::getInstance();

	JArrayHelper::toInteger($uid);
	$uids = implode ( ',', $uid );
	if ($uids) {
		//select only the messages which aren't already in the trash
		$kunena_db->setQuery ( "SELECT id FROM #__kunena_messages WHERE hold!=2 AND userid IN ({$uids})" );
		$idusermessages = $kunena_db->loadObjectList ();
		if (KunenaError::checkDatabaseError()) return;
		foreach ($idusermessages as $messageID) {
			$kunena_mod->deleteMessage($messageID->id, false);
		}
	}
	while (@ob_end_clean());
	$kunena_app->redirect ( JURI::base () . "index.php?option=com_kunena&task=profiles" , JText::_('COM_KUNENA_A_USERMES_TRASHED_DONE'));
}

function moveUserMessages ( $option, $uid ){
	$kunena_db = &JFactory::getDBO ();
	$return = JRequest::getCmd( 'return', 'edituserprofile', 'post' );

	JArrayHelper::toInteger($uid);
	$userid = implode(',', $uid);
	$kunena_db->setQuery ( "SELECT id,username FROM #__users WHERE id IN(".$userid.")" );
	$userids = $kunena_db->loadObjectList ();

	$kunena_db->setQuery ( "SELECT id,parent,name FROM #__kunena_categories" );
	$catsList = $kunena_db->loadObjectList ();
	if (KunenaError::checkDatabaseError()) return;

	foreach ($catsList as $cat) {
		if ($cat->parent) {
			$category[] = JHTML::_('select.option', $cat->id, '...'.$cat->name);
		} else {
			$category[] = JHTML::_('select.option', $cat->id, $cat->name);
		}
	}
	$lists = JHTML::_('select.genericlist', $category, 'cid[]', 'class="inputbox" multiple="multiple" size="5"', 'value', 'text');

	html_Kunena::moveUserMessages ( $option, $return, $uid, $lists, $userids );
}

function moveUserMessagesNow ( $option, $cid ) {
	$kunena_db = &JFactory::getDBO ();
	$kunena_app = & JFactory::getApplication ();

	if (!JRequest::checkToken()) {
		$kunena_app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
		while (@ob_end_clean());
		$kunena_app->redirect ( JURI::base () . "index.php?option=$option&task=profiles" );
	}

	$path = KUNENA_PATH_LIB  .'/kunena.moderation.class.php';
	require_once ($path);
	$kunena_mod = CKunenaModeration::getInstance();

	$uid = JRequest::getVar( 'uid', '', 'post' );
	if ($uid) {
		$query = "SELECT id,thread FROM #__kunena_messages WHERE hold=0 AND userid IN ({$uid[0]})";
		$kunena_db->setQuery ( $query );
		$idusermessages = $kunena_db->loadObjectList ();
		if (KunenaError::checkDatabaseError()) return;
		if ( !empty($idusermessages) ) {
			foreach ($idusermessages as $id) {
				$kunena_mod->moveMessage($id->id, $cid[0], '', 0);
			}
		}
	}
	while (@ob_end_clean());
	$kunena_app->redirect ( JURI::base () . "index.php?option=com_kunena&task=profiles", JText::_('COM_A_KUNENA_USERMES_MOVED_DONE') );
}

function logout ( $option, $userid ) {
	$app = JFactory::getApplication ();
	if (!JRequest::checkToken()) {
		$app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
		while (@ob_end_clean());
		$app->redirect ( JURI::base () . "index.php?option=$option&task=showprofiles" );
	}
	$options = array();
	$options['clientid'][] = 0; // site
	$app->logout( (int) $userid[0], $options);

	while (@ob_end_clean());
	$app->redirect ( JURI::base () . "index.php?option=com_kunena&task=profiles", JText::_('COM_A_KUNENA_USER_LOGOUT_DONE') );
}

function deleteUser ( $option, $uid ) {
	$kunena_app = & JFactory::getApplication ();
	if (!JRequest::checkToken()) {
		$kunena_app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
		while (@ob_end_clean());
		$kunena_app->redirect ( JURI::base () . "index.php?option=$option&task=showprofiles" );
	}
	$path = KUNENA_PATH_LIB  .'/kunena.moderation.tools.class.php';
	require_once ($path);
	$user_mod = new CKunenaModerationTools();

	JArrayHelper::toInteger($uid);

	foreach ($uid as $id) {
		$deleteuser = $user_mod->deleteUser($id);
		if (!$deleteuser) {
			$message = $user_mod->getErrorMessage();
		} else {
			$message = JText::_('COM_A_KUNENA_USER_DELETE_DONE');
		}
	}

	while (@ob_end_clean());
	$kunena_app->redirect ( JURI::base () . "index.php?option=com_kunena&task=profiles", $message );
}

function userban($option, $userid, $block = 0) {
	$kunena_app = & JFactory::getApplication ();
	if (!JRequest::checkToken()) {
		$kunena_app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
		while (@ob_end_clean());
		$kunena_app->redirect ( JURI::base () . "index.php?option=$option&task=showprofiles" );
	}

	kimport ( 'userban' );
	$userid = (int) array_shift($userid);
	$ban = KunenaUserBan::getInstanceByUserid ( $userid, true );
	if (! $ban->id) {
		$ban->ban ( $userid, null, $block );
		$success = $ban->save ();
	} else {
		jimport ('joomla.utilities.date');
		$now = new JDate();
		$ban->setExpiration ( $now );
		$success = $ban->save ();
	}

	if ($block) {
		if ($ban->isEnabled ())
			$message = JText::_ ( 'COM_KUNENA_USER_BLOCKED_DONE' );
		else
			$message = JText::_ ( 'COM_KUNENA_USER_UNBLOCK_DONE' );
	} else {
		if ($ban->isEnabled ())
			$message = JText::_ ( 'COM_KUNENA_USER_BANNED_DONE' );
		else
			$message = JText::_ ( 'COM_KUNENA_USER_UNBAN_DONE' );
	}

	$kunena_app = JFactory::getApplication ();
	if (! $success) {
		$kunena_app->enqueueMessage ( $ban->getError (), 'error' );
	} else {
		$kunena_app->enqueueMessage ( $message );
	}
	while (@ob_end_clean());
	$kunena_app->redirect ( JURI::base () . "index.php?option=com_kunena&task=profiles" );
}

//===============================
// Prune Forum functions
//===============================
function pruneforum($kunena_db, $option) {
	$forums_list = array ();
	//get forum list; locked forums are excluded from pruning
	$kunena_db->setQuery ( "SELECT a.id as value, a.name as text" . "\nFROM #__kunena_categories AS a" . "\nWHERE a.parent != '0'" . "\nAND a.locked != '1'" . "\nORDER BY parent, ordering" );
	//get all subscriptions for this user
	$forums_list = $kunena_db->loadObjectList ();
	if (KunenaError::checkDatabaseError()) return;
	$forumList ['forum'] = JHTML::_ ( 'select.genericlist', $forums_list, 'prune_forum', 'class="inputbox" size="4"', 'value', 'text', '' );
	html_Kunena::pruneforum ( $option, $forumList );
}

function doprune($kunena_db, $option) {
	require_once (KUNENA_PATH_LIB.'/kunena.timeformat.class.php');
	$kunena_app = & JFactory::getApplication ();
	if (!JRequest::checkToken()) {
		$kunena_app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
		while (@ob_end_clean());
		$kunena_app->redirect ( JURI::base () . "index.php?option=$option&task=pruneforum" );
		return;
	}

	$catid = JRequest::getInt ( 'prune_forum', - 1 );
	$deleted = 0;

	if ($catid == - 1) {
		echo "<script> alert('" . JText::_('COM_KUNENA_CHOOSEFORUMTOPRUNE') . "'); window.history.go(-1); </script>\n";
		$kunena_app->close ();
	}

	// Convert days to seconds for timestamp functions...
	$prune_days = intval ( JRequest::getVar ( 'prune_days', 36500 ) );
	$prune_date = CKunenaTimeformat::internalTime () - ($prune_days * 86400);

	//get the thread list for this forum
	$kunena_db->setQuery ( "SELECT t.thread, MAX(m.time) AS lasttime
		FROM #__kunena_messages AS m
		LEFT JOIN #__kunena_messages AS t ON m.thread=t.thread AND t.parent=0
		WHERE m.catid={$catid} AND t.ordering = 0
		GROUP BY thread
		HAVING lasttime < {$prune_date}" );
	$threadlist = $kunena_db->loadResultArray ();
	if (KunenaError::checkDatabaseError()) return;

	require_once(KUNENA_PATH_LIB.'/kunena.attachments.class.php');
	foreach ( $threadlist as $thread ) {
		//get the id's for all posts belonging to this thread
		$kunena_db->setQuery ( "SELECT id FROM #__kunena_messages WHERE thread={$thread}" );
		$idlist = $kunena_db->loadResultArray ();
		if (KunenaError::checkDatabaseError()) return;

		if (count ( $idlist ) > 0) {
			//prune all messages belonging to the thread
			$deleted += count ($idlist);
			$idlist = implode(',', $idlist);
			$attachments = CKunenaAttachments::getInstance();
			$attachments->deleteMessage($idlist);

			$kunena_db->setQuery ( "DELETE m, t FROM #__kunena_messages AS m INNER JOIN #__kunena_messages_text AS t ON m.id=t.mesid WHERE m.thread={$thread}" );
			$kunena_db->query ();
			if (KunenaError::checkDatabaseError()) return;
		}
		unset ($idlist);
	}
	if (!empty($threadlist)) {
		$threadlist = implode(',', $threadlist);
		//clean all subscriptions to these deleted threads
		$kunena_db->setQuery ( "DELETE FROM #__kunena_subscriptions WHERE thread IN ({$threadlist})" );
		$kunena_db->query ();
		if (KunenaError::checkDatabaseError()) return;

		//clean all favorites to these deleted threads
		$kunena_db->setQuery ( "DELETE FROM #__kunena_favorites WHERE thread IN ({$threadlist})" );
		$kunena_db->query ();
		if (KunenaError::checkDatabaseError()) return;
	}
	while (@ob_end_clean());
	$kunena_app->redirect ( JURI::base () . "index.php?option=$option&task=pruneforum", "" . JText::_('COM_KUNENA_FORUMPRUNEDFOR') . " " . $prune_days . " " . JText::_('COM_KUNENA_PRUNEDAYS') . "; " . JText::_('COM_KUNENA_PRUNEDELETED') . $deleted . " " . JText::_('COM_KUNENA_PRUNETHREADS') );
}

//===============================
// Sync users
//===============================
function syncusers($kunena_db, $option) {
	html_Kunena::syncusers ( $option );
}

function douserssync($kunena_db, $option) {
	$usercache = JRequest::getBool ( 'usercache', 0 );
	$useradd = JRequest::getBool ( 'useradd', 0 );
	$userdel = JRequest::getBool ( 'userdel', 0 );
	$userrename = JRequest::getBool ( 'userrename', 0 );

	$kunena_app = & JFactory::getApplication ();
	$kunena_db = &JFactory::getDBO ();
	if (!JRequest::checkToken()) {
		$kunena_app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
		while (@ob_end_clean());
		$kunena_app->redirect ( JURI::base () . "index.php?option=$option&task=syncusers" );
		return;
	}

	if ($usercache) {
		//reset access rights
		$kunena_db->setQuery ( "UPDATE #__kunena_sessions SET allowed='na'" );
		$kunena_db->query ();
		if (KunenaError::checkDatabaseError()) return;
		$kunena_app->enqueueMessage ( JText::_('COM_KUNENA_SYNC_USERS_DO_CACHE') );
	}
	if ($useradd) {
		$kunena_db->setQuery ( "INSERT INTO #__kunena_users (userid) SELECT a.id FROM #__users AS a LEFT JOIN #__kunena_users AS b ON b.userid=a.id WHERE b.userid IS NULL" );
		$kunena_db->query ();
		if (KunenaError::checkDatabaseError()) return;
		$kunena_app->enqueueMessage ( JText::_('COM_KUNENA_SYNC_USERS_DO_ADD') . ' ' . $kunena_db->getAffectedRows () );
	}
	if ($userdel) {
		$kunena_db->setQuery ( "DELETE a FROM #__kunena_users AS a LEFT JOIN #__users AS b ON a.userid=b.id WHERE b.username IS NULL" );
		$kunena_db->query ();
		if (KunenaError::checkDatabaseError()) return;
		$kunena_app->enqueueMessage ( JText::_('COM_KUNENA_SYNC_USERS_DO_DEL') . ' ' . $kunena_db->getAffectedRows () );
	}
	if ($userrename) {
		$cnt = CKunenaTools::updateNameInfo ();
		$kunena_app->enqueueMessage ( JText::_('COM_KUNENA_SYNC_USERS_DO_RENAME') . " $cnt" );
	}

	while (@ob_end_clean());
	$kunena_app->redirect ( JURI::base () . "index.php?option=$option&task=syncusers" );
}

//===============================
// Uploaded Images browser
//===============================
function browseUploaded($kunena_db, $option, $type) {
	$kunena_db = &JFactory::getDBO ();
	$kunena_config = KunenaFactory::getConfig ();

	if ($type) {
		$extensionsAllowed = explode(',',$kunena_config->imagetypes);
	} else {
		$extensionsAllowed = explode(',',$kunena_config->filetypes);
	}

	// type = 1 -> images ; type = 0 -> files

	$image_types =	explode(',',$kunena_config->imagemimetypes);
	$imageTypes = array();
	foreach ($image_types as $images ) {
		$imageTypes[] = "'".trim($images)."'";
	}
	$imageTypes= implode(',',$imageTypes);
	if ($type) {
		$where = ' WHERE filetype IN ('.$imageTypes.')';
	} else {
		$where = ' WHERE filetype NOT IN ('.$imageTypes.')';
	}

	$query = "SELECT a.*, b.catid, b.thread FROM #__kunena_attachments AS a LEFT JOIN #__kunena_messages AS b ON a.mesid=b.id $where";
	$kunena_db->setQuery ( $query );
	$uploaded = $kunena_db->loadObjectlist();
	if (KunenaError::checkDatabaseError()) return;

	html_Kunena::browseUploaded ( $option, $uploaded, $type );
}

function deleteAttachment($id, $redirect, $message) {
	$kunena_app = & JFactory::getApplication ();
	$kunena_db = &JFactory::getDBO ();
	if (! $id) {
		while (@ob_end_clean());
		$kunena_app->redirect ( $redirect );
		return;
	}

	require_once (KUNENA_PATH_LIB.'/kunena.attachments.class.php');
	$attachments = CKunenaAttachments::getInstance();
	$attachments->deleteAttachment($id);

	$kunena_app->enqueueMessage ( JText::_($message) );
	while (@ob_end_clean());
	$kunena_app->redirect ( $redirect );
}

//===============================
//   smiley functions
//===============================
//
// Read a listing of uploaded smilies for use in the add or edit smiley code...
//
function collect_smilies_ranks($path) {
  $smiley_rank_images = (array)JFolder::Files($path,false,false,false,array('index.php','index.html'));
  return $smiley_rank_images;
}

function showsmilies($option) {
	$kunena_db = &JFactory::getDBO ();
	$kunena_app = & JFactory::getApplication ();

	$limit = $kunena_app->getUserStateFromRequest ( "global.list.limit", 'limit', $kunena_app->getCfg ( 'list_limit' ), 'int' );
	$limitstart = $kunena_app->getUserStateFromRequest ( "{$option}.limitstart", 'limitstart', 0, 'int' );
	$kunena_db->setQuery ( "SELECT COUNT(*) FROM #__kunena_smileys" );
	$total = $kunena_db->loadResult ();
	if (KunenaError::checkDatabaseError()) return;
	if ($limitstart >= $total)
		$limitstart = 0;
	if ($limit == 0 || $limit > 100)
		$limit = 100;

	$kunena_db->setQuery ( "SELECT * FROM #__kunena_smileys", $limitstart, $limit );
	$smileytmp = $kunena_db->loadObjectList ();
	if (KunenaError::checkDatabaseError()) return;

	$smileypath = smileypath ();

	jimport ( 'joomla.html.pagination' );
	$pageNavSP = new JPagination ( $total, $limitstart, $limit );
	html_Kunena::showsmilies ( $option, $smileytmp, $pageNavSP, $smileypath );

}


	/* *
	 * upload smilies
	 */
	function uploadsmilies()
	{
		$kunena_config = KunenaFactory::getConfig ();
		$kunena_app = & JFactory::getApplication ();
		// load language fo component media
		JPlugin::loadLanguage( 'com_media' );
		$params =& JComponentHelper::getParams('com_media');
		require_once( JPATH_ADMINISTRATOR.'/components/com_media/helpers/media.php' );
		define('COM_KUNENA_MEDIA_BASE', JPATH_ROOT.'/components/com_kunena/template/'.$kunena_config->template.'/images');
		// Check for request forgeries
		JRequest::checkToken( 'request' ) or jexit( 'Invalid Token' );

		$file 			= JRequest::getVar( 'Filedata', '', 'files', 'array' );
		$foldersmiley	= JRequest::getVar( 'foldersmiley', 'emoticons', '', 'path' );
		$format			= JRequest::getVar( 'format', 'html', '', 'cmd');
		$return			= JRequest::getVar( 'return-url', null, 'post', 'base64' );
		$err			= null;

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');

		// Make the filename safe
		jimport('joomla.filesystem.file');
		$file['name']	= JFile::makeSafe($file['name']);

		if (isset($file['name'])) {
			$filepathsmiley = JPath::clean(COM_KUNENA_MEDIA_BASE.'/'.$foldersmiley.'/'.strtolower($file['name']));

			if (!MediaHelper::canUpload( $file, $err )) {
				if ($format == 'json') {
					jimport('joomla.error.log');
					$log = &JLog::getInstance('upload.error.php');
					$log->addEntry(array('comment' => 'Invalid: '.$filepathsmiley.': '.$err));
					header('HTTP/1.0 415 Unsupported Media Type');
					jexit('Error. Unsupported Media Type!');
				} else {
					JError::raiseNotice(100, JText::_($err));
					// REDIRECT
					if ($return) {
						while (@ob_end_clean());
						$kunena_app->redirect(base64_decode($return));
					}
					return;
				}
			}

			if (JFile::exists($filepathsmiley)) {
				if ($format == 'json') {
					jimport('joomla.error.log');
					$log = &JLog::getInstance('upload.error.php');
					$log->addEntry(array('comment' => 'File already exists: '.$filepathsmiley));
					header('HTTP/1.0 409 Conflict');
					jexit('Error. File already exists');
				} else {
					JError::raiseNotice(100, JText::_('COM_KUNENA_A_EMOTICONS_UPLOAD_ERROR_EXIST'));
					// REDIRECT
					if ($return) {
						while (@ob_end_clean());
						$kunena_app->redirect(base64_decode($return));
					}
					return;
				}
			}

			if (!JFile::upload($file['tmp_name'], $filepathsmiley)) {
				if ($format == 'json') {
					jimport('joomla.error.log');
					$log = &JLog::getInstance('upload.error.php');
					$log->addEntry(array('comment' => 'Cannot upload: '.$filepathsmiley));
					header('HTTP/1.0 400 Bad Request');
					jexit('Error. Unable to upload file');
				} else {
					JError::raiseWarning(100, JText::_('COM_KUNENA_A_EMOTICONS_UPLOAD_ERROR_UNABLE'));
					// REDIRECT
					if ($return) {
						while (@ob_end_clean());
						$kunena_app->redirect(base64_decode($return));
					}
					return;
				}
			} else {
				if ($format == 'json') {
					jimport('joomla.error.log');
					$log = &JLog::getInstance();
					$log->addEntry(array('comment' => $foldersmiley));
					jexit('Upload complete');
				} else {
					$kunena_app->enqueueMessage(JText::_('COM_KUNENA_A_EMOTICONS_UPLOAD_SUCCESS'));
					// REDIRECT
					if ($return) {
						while (@ob_end_clean());
						$kunena_app->redirect(base64_decode($return));
					}
					return;
				}
			}
		} else {
			while (@ob_end_clean());
			$kunena_app->redirect('index.php', 'Invalid Request', 'error');
		}
	}

function editsmiley($option, $id) {
	$kunena_db = &JFactory::getDBO ();
	$kunena_db->setQuery ( "SELECT * FROM #__kunena_smileys WHERE id = $id" );

	$smileytmp = $kunena_db->loadAssocList ();
	if (KunenaError::checkDatabaseError()) return;
	$smileycfg = $smileytmp [0];

	$template = KunenaFactory::getTemplate();
	$smileypath = $template->getSmileyPath();
	$smiley_images = collect_smilies_ranks(KPATH_SITE.'/'.$smileypath);

	$smiley_edit_img = '';

	$filename_list = "";
	for($i = 0; $i < count ( $smiley_images ); $i ++) {
		if ($smiley_images [$i] == $smileycfg ['location']) {
			$smiley_selected = "selected=\"selected\"";
			$smiley_edit_img = $template->getSmileyPath($smiley_images [$i]);
		} else {
			$smiley_selected = "";
		}

		$filename_list .= '<option value="' . $smiley_images [$i] . '"' . $smiley_selected . '>' . $smiley_images [$i] . '</option>' . "\n";
	}
	html_Kunena::editsmiley ( $option, $smiley_edit_img, $filename_list, $smileypath, $smileycfg );
}

function newsmiley($option) {
	$template = KunenaFactory::getTemplate();
	$smileypath = $template->getSmileyPath();
	$smiley_images = collect_smilies_ranks(KPATH_SITE.'/'.$smileypath);

	$filename_list = "";
	for($i = 0; $i < count ( $smiley_images ); $i ++) {
		$filename_list .= '<option value="' . $smiley_images [$i] . '">' . $smiley_images [$i] . '</option>' . "\n";
	}

	html_Kunena::newsmiley ( $option, $filename_list, $smileypath );
}

function savesmiley($option, $id = NULL) {
	$kunena_app = & JFactory::getApplication ();
	$kunena_db = &JFactory::getDBO ();
	if (!JRequest::checkToken()) {
		$kunena_app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
		while (@ob_end_clean());
		$kunena_app->redirect ( JURI::base () . "index.php?option=$option&task=showsmilies" );
		return;
	}

	$smiley_code = JRequest::getVar ( 'smiley_code' );
	$smiley_location = JRequest::getVar ( 'smiley_url' );
	$smiley_emoticonbar = (JRequest::getVar ( 'smiley_emoticonbar' )) ? JRequest::getVar ( 'smiley_emoticonbar' ) : 0;

	if (empty ( $smiley_code ) || empty ( $smiley_location )) {
		$task = ($id == NULL) ? 'newsmiley' : 'editsmiley&id=' . $id;
		while (@ob_end_clean());
		$kunena_app->redirect ( JURI::base () . "index.php?option=$option&task=" . $task, JText::_('COM_KUNENA_MISSING_PARAMETER') );
		$kunena_app->close ();
	}

	$kunena_db->setQuery ( "SELECT * FROM #__kunena_smileys" );

	$smilies = $kunena_db->loadAssocList ();
	if (KunenaError::checkDatabaseError()) return;
	foreach ( $smilies as $value ) {
		if (in_array ( $smiley_code, $value ) && ! ($value ['id'] == $id)) {
			$task = ($id == NULL) ? 'newsmiley' : 'editsmiley&id=' . $id;
			while (@ob_end_clean());
			$kunena_app->redirect ( JURI::base () . "index.php?option=$option&task=" . $task, JText::_('COM_KUNENA_CODE_ALLREADY_EXITS') );
			$kunena_app->close ();
		}

	}

	if ($id == NULL) {
		$kunena_db->setQuery ( "INSERT INTO #__kunena_smileys SET code = '$smiley_code', location = '$smiley_location', emoticonbar = '$smiley_emoticonbar'" );
	} else {
		$kunena_db->setQuery ( "UPDATE #__kunena_smileys SET code = '$smiley_code', location = '$smiley_location', emoticonbar = '$smiley_emoticonbar' WHERE id = '$id'" );
	}

	$kunena_db->query ();
	if (KunenaError::checkDatabaseError()) return;

	while (@ob_end_clean());
	$kunena_app->redirect ( JURI::base () . "index.php?option=$option&task=showsmilies", JText::_('COM_KUNENA_SMILEY_SAVED') );
}

function deletesmiley($option, $cid) {
	$kunena_app = & JFactory::getApplication ();
	$kunena_db = &JFactory::getDBO ();
	if (!JRequest::checkToken()) {
		$kunena_app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
		while (@ob_end_clean());
		$kunena_app->redirect ( JURI::base () . "index.php?option=$option&task=showsmilies" );
		return;
	}

	JArrayHelper::toInteger($cid);
	$cids = implode ( ',', $cid );

	if ($cids) {
		$kunena_db->setQuery ( "DELETE FROM #__kunena_smileys WHERE id IN ($cids)" );
		$kunena_db->query ();
		if (KunenaError::checkDatabaseError()) return;
	}

	while (@ob_end_clean());
	$kunena_app->redirect ( JURI::base () . "index.php?option=$option&task=showsmilies", JText::_('COM_KUNENA_SMILEY_DELETED') );
}

function smileypath() {
	$kunena_config = KunenaFactory::getConfig ();
	// FIXME: deprecated, do not exist anymore
	$smiley_live_path = KUNENA_URLEMOTIONSPATH;
	$smiley_abs_path = KUNENA_ABSEMOTIONSPATH;

	$smileypath ['live'] = $smiley_live_path;
	$smileypath ['abs'] = $smiley_abs_path;

	return $smileypath;
}
//===============================
//  FINISH smiley functions
//===============================


//===============================
// Rank Administration
//===============================
//Dan Syme/IGD - Ranks Management


function showRanks($option) {
	$kunena_app = & JFactory::getApplication ();
	$kunena_db = &JFactory::getDBO ();

	$order = JRequest::getVar ( 'order', '' );
	$limit = $kunena_app->getUserStateFromRequest ( "global.list.limit", 'limit', $kunena_app->getCfg ( 'list_limit' ), 'int' );
	$limitstart = $kunena_app->getUserStateFromRequest ( "{$option}.limitstart", 'limitstart', 0, 'int' );
	$kunena_db->setQuery ( "SELECT COUNT(*) FROM #__kunena_ranks" );
	$total = $kunena_db->loadResult ();
	if (KunenaError::checkDatabaseError()) return;
	if ($limitstart >= $total)
		$limitstart = 0;
	if ($limit == 0 || $limit > 100)
		$limit = 100;

	$kunena_db->setQuery ( "SELECT * FROM #__kunena_ranks", $limitstart, $limit );
	$ranks = $kunena_db->loadObjectList ();
	if (KunenaError::checkDatabaseError()) return;

	jimport ( 'joomla.html.pagination' );
	$pageNavSP = new JPagination ( $total, $limitstart, $limit );
	html_Kunena::showRanks ( $option, $ranks, $pageNavSP, $order );

}


	/* *
	 * upload ranks
	 */
	function uploadranks()
	{
		$kunena_config = KunenaFactory::getConfig ();
		$kunena_app = & JFactory::getApplication ();
		// load language fo component media
		JPlugin::loadLanguage( 'com_media' );
		$params =& JComponentHelper::getParams('com_media');
		require_once( JPATH_ADMINISTRATOR.'/components/com_media/helpers/media.php' );
		define('COM_KUNENA_MEDIA_BASE', JPATH_ROOT.'/components/com_kunena/template/'.$kunena_config->template.'/images');
		// Check for request forgeries
		JRequest::checkToken( 'request' ) or jexit( 'Invalid Token' );

		$file 			= JRequest::getVar( 'Filedata', '', 'files', 'array' );
		$folderranks	= JRequest::getVar( 'folderranks', 'ranks', '', 'path' );
		$format			= JRequest::getVar( 'format', 'html', '', 'cmd');
		$return			= JRequest::getVar( 'return-url', null, 'post', 'base64' );
		$err			= null;

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');

		// Make the filename safe
		jimport('joomla.filesystem.file');
		$file['name']	= JFile::makeSafe($file['name']);

		if (isset($file['name'])) {
			$filepathranks = JPath::clean(COM_KUNENA_MEDIA_BASE.'/'.$folderranks.'/'.strtolower($file['name']));

			if (!MediaHelper::canUpload( $file, $err )) {
				if ($format == 'json') {
					jimport('joomla.error.log');
					$log = &JLog::getInstance('upload.error.php');
					$log->addEntry(array('comment' => 'Invalid: '.$filepathranks.': '.$err));
					header('HTTP/1.0 415 Unsupported Media Type');
					jexit('Error. Unsupported Media Type!');
				} else {
					JError::raiseNotice(100, JText::_($err));
					// REDIRECT
					if ($return) {
						while (@ob_end_clean());
						$kunena_app->redirect(base64_decode($return));
					}
					return;
				}
			}

			if (JFile::exists($filepathranks)) {
				if ($format == 'json') {
					jimport('joomla.error.log');
					$log = &JLog::getInstance('upload.error.php');
					$log->addEntry(array('comment' => 'File already exists: '.$filepathranks));
					header('HTTP/1.0 409 Conflict');
					jexit('Error. File already exists');
				} else {
					JError::raiseNotice(100, JText::_('COM_KUNENA_A_RANKS_UPLOAD_ERROR_EXIST'));
					// REDIRECT
					if ($return) {
						while (@ob_end_clean());
						$kunena_app->redirect(base64_decode($return));
					}
					return;
				}
			}

			if (!JFile::upload($file['tmp_name'], $filepathranks)) {
				if ($format == 'json') {
					jimport('joomla.error.log');
					$log = &JLog::getInstance('upload.error.php');
					$log->addEntry(array('comment' => 'Cannot upload: '.$filepathranks));
					header('HTTP/1.0 400 Bad Request');
					jexit('Error. Unable to upload file');
				} else {
					JError::raiseWarning(100, JText::_('COM_KUNENA_A_RANKS_UPLOAD_ERROR_UNABLE'));
					// REDIRECT
					if ($return) {
						while (@ob_end_clean());
						$kunena_app->redirect(base64_decode($return));
					}
					return;
				}
			} else {
				if ($format == 'json') {
					jimport('joomla.error.log');
					$log = &JLog::getInstance();
					$log->addEntry(array('comment' => $filepathranks));
					jexit('Upload complete');
				} else {
					$kunena_app->enqueueMessage(JText::_('COM_KUNENA_A_RANKS_UPLOAD_SUCCESS'));
					// REDIRECT
					if ($return) {
						while (@ob_end_clean());
						$kunena_app->redirect(base64_decode($return));
					}
					return;
				}
			}
		} else {
			while (@ob_end_clean());
			$kunena_app->redirect('index.php', 'Invalid Request', 'error');
		}
	}


function rankpath() {

	// FIXME: deprecated, do not exist anymore
	$rankpath ['live'] = KUNENA_URLRANKSPATH;
	$rankpath ['abs'] = KUNENA_ABSRANKSPATH;

	return $rankpath;

}

function newRank($option) {
	$kunena_db = &JFactory::getDBO ();

	$template = KunenaFactory::getTemplate();
	$rankpath = $template->getRankPath();
	$rank_images = collect_smilies_ranks(KPATH_SITE.'/'.$rankpath);

	$filename_list = "";
	$i = 0;
	foreach ( $rank_images as $id => $row ) {
		$filename_list .= '<option value="' . $rank_images [$id] . '">' . $rank_images [$id] . '</option>' . "\n";
	}

	html_Kunena::newRank ( $option, $filename_list, $rankpath );
}

function deleteRank($option, $cid = null) {
	$kunena_db = &JFactory::getDBO ();
	$kunena_app = & JFactory::getApplication ();
	if (!JRequest::checkToken()) {
		$kunena_app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
		while (@ob_end_clean());
		$kunena_app->redirect ( JURI::base () . "index.php?option=$option&task=ranks" );
		return;
	}

	JArrayHelper::toInteger($cid);
	$cids = implode ( ',', $cid );
	if ($cids) {
		$kunena_db->setQuery ( "DELETE FROM #__kunena_ranks WHERE rank_id IN ($cids)" );
		$kunena_db->query ();
		if (KunenaError::checkDatabaseError()) return;
	}

	while (@ob_end_clean());
	$kunena_app->redirect ( JURI::base () . "index.php?option=$option&task=ranks", JText::_('COM_KUNENA_RANK_DELETED') );
}

function saveRank($option, $id = NULL) {
	$kunena_app = & JFactory::getApplication ();
	$kunena_db = &JFactory::getDBO ();

	if (!JRequest::checkToken()) {
		$kunena_app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
		while (@ob_end_clean());
		$kunena_app->redirect ( JURI::base () . "index.php?option=$option&task=ranks" );
		return;
	}

	$rank_title = JRequest::getVar ( 'rank_title' );
	$rank_image = JRequest::getVar ( 'rank_image' );
	$rank_special = JRequest::getVar ( 'rank_special' );
	$rank_min = JRequest::getVar ( 'rank_min' );

	if (empty ( $rank_title ) || empty ( $rank_image )) {
		$task = ($id == NULL) ? 'newRank' : 'editRank&id=' . $id;
		while (@ob_end_clean());
		$kunena_app->redirect ( JURI::base () . "index.php?option=$option&task=" . $task, JText::_('COM_KUNENA_MISSING_PARAMETER') );
		$kunena_app->close ();
	}

	$kunena_db->setQuery ( "SELECT * FROM #__kunena_ranks" );
	$ranks = $kunena_db->loadAssocList ();
	if (KunenaError::checkDatabaseError()) return;
	foreach ( $ranks as $value ) {
		if (in_array ( $rank_title, $value ) && ! ($value ['rank_id'] == $id)) {
			$task = ($id == NULL) ? 'newRank' : 'editRank&id=' . $id;
			while (@ob_end_clean());
			$kunena_app->redirect ( JURI::base () . "index.php?option=$option&task=" . $task, JText::_('COM_KUNENA_RANK_ALLREADY_EXITS') );
			$kunena_app->close ();
		}
	}

	if ($id == NULL) {
		$kunena_db->setQuery ( "INSERT INTO #__kunena_ranks SET rank_title = '$rank_title', rank_image = '$rank_image', rank_special = '$rank_special', rank_min = '$rank_min'" );
	} else {
		$kunena_db->setQuery ( "UPDATE #__kunena_ranks SET rank_title = '$rank_title', rank_image = '$rank_image', rank_special = '$rank_special', rank_min = '$rank_min' WHERE rank_id = $id" );
	}
	$kunena_db->query ();
	if (KunenaError::checkDatabaseError()) return;

	while (@ob_end_clean());
	$kunena_app->redirect ( JURI::base () . "index.php?option=$option&task=ranks", JText::_('COM_KUNENA_RANK_SAVED') );
}

function editRank($option, $id) {
	$kunena_db = &JFactory::getDBO ();

	$kunena_db->setQuery ( "SELECT * FROM #__kunena_ranks WHERE rank_id = '$id'" );
	$ranks = $kunena_db->loadObjectList ();

	if (KunenaError::checkDatabaseError()) return;

	$template = KunenaFactory::getTemplate();
	$rankpath = $template->getRankPath();
	$rank_images = collect_smilies_ranks(KPATH_SITE.'/'.$rankpath);

	$edit_img = $filename_list = '';

	foreach ( $ranks as $row ) {
		foreach ( $rank_images as $img ) {
			if ($img == $row->rank_image) {
				$selected = ' selected="selected"';
				$edit_img = $template->getRankPath($img);
			} else {
				$selected = '';
			}

			if (JString::strlen ( $img ) > 255) {
				continue;
			}

			$filename_list .= '<option value="' . kunena_htmlspecialchars ( $img ) . '"' . $selected . '>' . $img . '</option>';
		}
	}

	html_Kunena::editRank ( $option, $edit_img, $filename_list, $rankpath, $row );
}

//===============================
//  FINISH rank functions
//===============================
// Dan Syme/IGD - Ranks Management

//===============================
// Trash management
//===============================
function showtrashview($option) {
	$kunena_app = & JFactory::getApplication ();
	$kunena_db = &JFactory::getDBO ();
	$filter_order		= $kunena_app->getUserStateFromRequest( $option.'filter_order',		'filter_order',		'subject', 'cmd' );
	$filter_order_Dir	= $kunena_app->getUserStateFromRequest( $option.'filter_order_Dir',	'filter_order_Dir',	'asc',			'word' );
	$search				= $kunena_app->getUserStateFromRequest( $option.'search',						'search', 			'',			'string' );
	$search				= JString::strtolower( $search );

	$order = JRequest::getVar ( 'order', '' );
	$limit = $kunena_app->getUserStateFromRequest ( "global.list.limit", 'limit', $kunena_app->getCfg ( 'list_limit' ), 'int' );
	$limitstart = $kunena_app->getUserStateFromRequest ( "{$option}.limitstart", 'limitstart', 0, 'int' );
	$kunena_db->setQuery ( "SELECT COUNT(*) FROM #__kunena_messages WHERE hold=2" );
	$total = $kunena_db->loadResult ();
	if (KunenaError::checkDatabaseError()) return;
	if ($limitstart >= $total)
		$limitstart = 0;
	if ($limit == 0 || $limit > 100)
		$limit = 100;

	$where 	= ' WHERE hold=2 ';

	if ($search) {
		$where .= ' AND LOWER( a.subject ) LIKE '.$kunena_db->Quote( '%'.$kunena_db->getEscaped( $search, true ).'%', false ).' OR LOWER( c.username )LIKE '.$kunena_db->Quote( '%'.$kunena_db->getEscaped( $search, true ).'%', false ).' OR  a.thread LIKE '.$kunena_db->Quote( '%'.$kunena_db->getEscaped( $search, true ).'%', false );
	}

	$orderby = ' ORDER BY '. $filter_order .' '. $filter_order_Dir;

	$query = 'SELECT a.*, b.name AS cats_name, c.username FROM #__kunena_messages AS a
	INNER JOIN #__kunena_categories AS b ON a.catid=b.id
	LEFT JOIN #__users AS c ON a.userid=c.id'
	.$where
	.$orderby;
	$kunena_db->setQuery ( $query, $limitstart, $limit );
	$trashitems = $kunena_db->loadObjectList ();
	if (KunenaError::checkDatabaseError()) return;

	// table ordering
	$lists['order_Dir']	= $filter_order_Dir;
	$lists['order']		= $filter_order;

	jimport ( 'joomla.html.pagination' );
	$pageNavSP = new JPagination ( $total, $limitstart, $limit );

	$lists['search']= $search;

	html_Kunena::showtrashview ( $option, $trashitems, $pageNavSP, $lists );
}

function trashpurge($option, $cid) {
	$kunena_db = &JFactory::getDBO ();
	$return = JRequest::getCmd( 'return', 'showtrashview', 'post' );

	JArrayHelper::toInteger($cid);
	$cids = implode ( ',', $cid );
	if ($cids) {
		$kunena_db->setQuery ( "SELECT * FROM #__kunena_messages WHERE hold=2 AND id IN ($cids)");
		$items = $kunena_db->loadObjectList ();
		if (KunenaError::checkDatabaseError()) return;
	}

	html_Kunena::trashpurge ( $option, $return, $cid, $items );
}

function deleteitemsnow ( $option, $cid ) {
	$kunena_app = & JFactory::getApplication ();
	$kunena_db = &JFactory::getDBO ();
	$path = KUNENA_PATH_LIB  .'/kunena.moderation.class.php';
	require_once ($path);
	$kunena_mod = CKunenaModeration::getInstance();

	JArrayHelper::toInteger($cid);
	$cids = implode ( ',', $cid );
	if ($cids) {
		foreach ($cid as $id ) {
			$kunena_db->setQuery ( "SELECT a.parent, a.id, b.threadid FROM #__kunena_messages AS a INNER JOIN #__kunena_polls AS b ON b.threadid=a.id WHERE threadid='{$id}'" );
			$mes = $kunena_db->loadObjectList ();
			if (KunenaError::checkDatabaseError()) return;
			if( !empty($mes[0])) {
				if ($mes[0]->parent == '0' && !empty($mes[0]->threadid) ) {
					//remove of poll
					require_once (KUNENA_PATH_LIB .'/'. 'kunena.poll.class.php');
					$poll = CKunenaPolls::getInstance();
					$poll->delete_poll($mes[0]->threadid);
				}
			}
		}

		$kunena_db->setQuery ( 'SELECT userid FROM #__kunena_messages WHERE id IN (' . $cids. ')' );
		$userids = $kunena_db->loadObjectList ();
		if (KunenaError::checkDatabaseError()) return;

		$kunena_db->setQuery ( 'DELETE FROM #__kunena_messages WHERE id IN (' .$cids. ')' );
		$kunena_db->query ();
		if (KunenaError::checkDatabaseError()) return;

		$kunena_db->setQuery ( 'DELETE FROM #__kunena_messages_text WHERE mesid IN (' . $cids. ')' );
		$kunena_db->query ();
		if (KunenaError::checkDatabaseError()) return;
		foreach ( $userids as $line ) {
			if ($line->userid > 0) {
				$userid_array [] = $line->userid;
			}
		}

		JArrayHelper::toInteger($userid_array);
		$userids = implode ( ',', $userid_array );

		if (count ( $userid_array ) > 0) {
			$kunena_db->setQuery ( 'UPDATE #__kunena_users SET posts=posts-1 WHERE userid IN (' . $userids . ')' );
			$kunena_db->query ();
			if (KunenaError::checkDatabaseError()) return;
		}

		foreach ($cid as $MessageID) {
			$kunena_mod->deleteAttachments($MessageID);
		}
	}

	while (@ob_end_clean());
	$kunena_app->redirect ( JURI::base () . "index.php?option=$option&task=showtrashview", JText::_('COM_KUNENA_TRASH_DELETE_DONE') );
}

function trashrestore($option, $cid) {
	$kunena_app = & JFactory::getApplication ();
	$kunena_db = &JFactory::getDBO ();

	JArrayHelper::toInteger($cid);
	if ($cid) {
		foreach ( $cid as $id ) {
			$kunena_db->setQuery ( "SELECT * FROM #__kunena_messages WHERE id=$id AND hold=2" );
			$mes = $kunena_db->loadObject ();
			if (KunenaError::checkDatabaseError()) return;

			$kunena_db->setQuery ( "UPDATE #__kunena_messages SET hold=0 WHERE hold IN (2,3) AND thread=$mes->thread " );
			$kunena_db->query ();
			if (KunenaError::checkDatabaseError()) return;

			CKunenaTools::reCountUserPosts ();
			CKunenaTools::reCountBoards ();
		}
	}

	while (@ob_end_clean());
	$kunena_app->redirect ( JURI::base () . "index.php?option=$option&task=showtrashview", JText::_('COM_KUNENA_TRASH_RESTORE_DONE') );
}
//===============================
// FINISH trash management
//===============================

//===============================
// Report System
//===============================
function showSystemReport ( $option ) {
	$kunena_app = & JFactory::getApplication ();
	$return = JRequest::getCmd( 'return', 'showsystemreport', 'post' );
	$report = generateSystemReport ();
	html_Kunena::showSystemReport ( $option, $report );
}

function generateSystemReport () {
	jimport('joomla.filesystem.file');
	$kunena_config = KunenaFactory::getConfig ();
	$kunena_app = JFactory::getApplication ();
	$kunena_db = JFactory::getDBO ();
	$JVersion = new JVersion();
	$jversion = $JVersion->PRODUCT .' '. $JVersion->RELEASE .'.'. $JVersion->DEV_LEVEL .' '. $JVersion->DEV_STATUS.' [ '.$JVersion->CODENAME .' ] '. $JVersion->RELDATE;

	if($kunena_app->getCfg('legacy' )) {
		$jconfig_legacy = '[color=#FF0000]Enabled[/color]';
	} else {
		$jconfig_legacy = 'Disabled';
	}
	if(!$kunena_app->getCfg('smtpuser' )) {
		$jconfig_smtpuser = 'Empty';
	} else {
		$jconfig_smtpuser = $kunena_app->getCfg('smtpuser' );
	}
	if($kunena_app->getCfg('ftp_enable' )) {
		$jconfig_ftp = 'Enabled';
	} else {
		$jconfig_ftp = 'Disabled';
	}
	if($kunena_app->getCfg('sef' )) {
		$jconfig_sef = 'Enabled';
	} else {
		$jconfig_sef = 'Disabled';
	}
	if($kunena_app->getCfg('sef_rewrite' )) {
		$jconfig_sef_rewrite = 'Enabled';
	} else {
		$jconfig_sef_rewrite = 'Disabled';
	}

	if (file_exists(JPATH_ROOT. '/.htaccess')) {
		$htaccess = 'Exists';
	} else {
		$htaccess = 'Missing';
	}

	if(ini_get('register_globals')) {
		$register_globals = '[u]register_globals:[/u] [color=#FF0000]On[/color]';
	} else {
		$register_globals = '[u]register_globals:[/u] Off';
	}
	if(ini_get('safe_mode')) {
		$safe_mode = '[u]safe_mode:[/u] [color=#FF0000]On[/color]';
	} else {
		$safe_mode = '[u]safe_mode:[/u] Off';
	}
	if(extension_loaded('mbstring')) {
		$mbstring = '[u]mbstring:[/u] Enabled';
	} else {
		$mbstring = '[u]mbstring:[/u] [color=#FF0000]Not installed[/color]';
	}
	if(extension_loaded('gd')) {
		$gd_info = gd_info ();
		$gd_support = '[u]GD:[/u] '.$gd_info['GD Version'] ;
	} else {
		$gd_support = '[u]GD:[/u] [color=#FF0000]Not installed[/color]';
	}
	$maxExecTime = ini_get('max_execution_time');
	$maxExecMem = ini_get('memory_limit');
	$fileuploads = ini_get('upload_max_filesize');
	$kunenaVersionInfo = CKunenaVersion::versionArray ();

	//get all the config settings for Kunena
	$kunena_db->setQuery ( "SHOW TABLES LIKE '" . $kunena_db->getPrefix () ."kunena_config'" );
	$table_config = $kunena_db->loadResult ();
	if (KunenaError::checkDatabaseError()) return;

	if ($table_config) {
		$kunena_db->setQuery("SELECT * FROM #__kunena_config");
		$kconfig = (object)$kunena_db->loadObject ();
    	if (KunenaError::checkDatabaseError()) return;

    	$kconfigsettings = '[table]';
    	$kconfigsettings .= '[th]Kunena config settings:[/th]';
    	foreach ($kconfig as $key => $value ) {
    		if ($key != 'id' && $key != 'board_title' && $key != 'email' && $key != 'offline_message'
    			&& $key != 'recaptcha_publickey' && $key != 'recaptcha_privatekey' && $key != 'email_visible_addres'
    			&& $key != 'recaptcha_theme') {
				$kconfigsettings .= '[tr][td]'.$key.'[/td][td]'.$value.'[/td][/tr]';
    		}
    	}
		$kconfigsettings .= '[/table]';
	} else {
		$kconfigsettings = 'Your configuration settings aren\'t yet recorded in the database';
	}

	// Get Kunena default template
	$ktemplate = KunenaFactory::getTemplate();
	$ktempaltedetails = $ktemplate->getTemplateDetails();

	// Get database collation
	$collation = getTablesCollation();

	// Get Joomla! template details
	$templatedetails = getJoomlaTemplate();

	// Get Joomla! menu details
	$joomlamenudetails = getJoomlaMenuDetails();

	// Check if Mootools plugins and others kunena plugins are enabled, and get the version of this modules
	jimport( 'joomla.plugin.helper' );
	jimport( 'joomla.application.module.helper' );
	jimport( 'joomla.application.component.helper' );

	$plg = array();

	if ( JPluginHelper::isEnabled('system', 'mtupgrade') ) 	$plg['mtupgrade'] = '[u]System - Mootools Upgrade:[/u] Enabled';
	else $plg['mtupgrade'] = '[u]System - Mootools Upgrade:[/u] Disabled';

	if ( JPluginHelper::isEnabled('system', 'mootools12') ) $plg['mt12'] = '[u]System - Mootools12:[/u] Enabled';
	else $plg['mt12'] = '[u]System - Mootools12:[/u] Disabled';

	$plg['jfirephp'] = checkThirdPartyVersion('jfirephp', 'jfirephp', 'JFirePHP', 'plugins/system', 'system', 0, 0, 1);
	$plg['ksearch'] = checkThirdPartyVersion('kunenasearch', 'kunenasearch', 'Kunena Search', 'plugins/search', 'search', 0, 0, 1);
	$plg['kdiscuss'] = checkThirdPartyVersion('kunenadiscuss', 'kunenadiscuss', 'Kunena Discuss', 'plugins/content', 'content', 0, 0, 1);
	$plg['jxfinderkunena'] = checkThirdPartyVersion('plg_jxfinder_kunena', 'plg_jxfinder_kunena', 'Finder Kunena Posts', 'plugins/finder', 'finder', 0, 0, 1);
	$plg['kjomsocialmenu'] = checkThirdPartyVersion('kunenamenu', 'kunenamenu', 'My Kunena Forum Menu', 'plugins/community', 'community', 0, 0, 1);
	$plg['kjomsocialmykunena'] = checkThirdPartyVersion('mykunena', 'mykunena', 'My Kunena Forum Posts', 'plugins/community', 'community', 0, 0, 1);
	$plg['kjomsocialgroups'] = checkThirdPartyVersion('kunenagroups', 'kunenagroups', 'Kunena Groups', 'plugins/community', 'community', 0, 0, 1);
	foreach ($plg as $id=>$item) {
		if (empty($item)) unset ($plg[$id]);
	}
	if (!empty($plg)) $plgtext = '[quote][b]Plugins:[/b] ' . implode(' | ', $plg) . ' [/quote]';
	else $plgtext = '[quote][b]Plugins:[/b] None [/quote]';

	$mod = array();
	$mod['kunenalatest'] = checkThirdPartyVersion('mod_kunenalatest', 'mod_kunenalatest', 'Kunena Latest', 'modules/mod_kunenalatest', null, 0, 1, 0);
	$mod['kunenastats'] = checkThirdPartyVersion('mod_kunenastats', 'mod_kunenastats', 'Kunena Stats', 'modules/mod_kunenastats', null, 0, 1, 0);
	$mod['kunenalogin'] = checkThirdPartyVersion('mod_kunenalogin', 'mod_kunenalogin', 'Kunena Login', 'modules/mod_kunenalogin', null, 0, 1, 0);
	$mod['kunenasearch'] = checkThirdPartyVersion('mod_kunenasearch', 'mod_kunenasearch', 'Kunena Search', 'modules/mod_kunenasearch', null, 0, 1, 0);
	foreach ($mod as $id=>$item) {
		if (empty($item)) unset ($mod[$id]);
	}
	if (!empty($mod)) $modtext = '[quote][b]Modules:[/b] ' . implode(' | ', $mod) . ' [/quote]';
	else $modtext = '[quote][b]Modules:[/b] None [/quote]';

	$thirdparty = array();
	if ( JFile::exists(JPATH_SITE . '/components/com_alphauserpoints/helper.php') ) {
		require_once(JPATH_SITE . '/components/com_alphauserpoints/helper.php');
		if ( class_exists('AlphaUserPointsHelper') && method_exists('AlphaUserPointsHelper', 'getAupVersion') ) {
			$aup = new AlphaUserPointsHelper ();
			$thirdparty['aup'] = '[u]AlphaUserPoints[/u] '.$aup->getAupVersion();
		} else {
			$thirdparty['aup'] = checkThirdPartyVersion('alphauserpoints', array('manifest','alphauserpoints'), 'AlphaUserPoints', 'components/com_alphauserpoints', null, 1, 0, 0);
		}
	} else {
		$thirdparty['aup'] = checkThirdPartyVersion('alphauserpoints', array('manifest','alphauserpoints'), 'AlphaUserPoints', 'components/com_alphauserpoints', null, 1, 0, 0);
	}

	$thirdparty['cb'] = checkThirdPartyVersion('comprofiler', array('comprofilej','comprofileg') , 'CommunityBuilder', 'components/com_comprofiler', null, 1, 0, 0);

	$thirdparty['jomsocial'] = checkThirdPartyVersion('community', array('community'), 'Jomsocial', 'components/com_community', null, 1, 0, 0);
	if (JFile::exists(JPATH_SITE.'/components/com_uddeim/uddeim.api.php')) {
		require_once(JPATH_SITE.'/components/com_uddeim/uddeim.api.php');
		$uddeim = new uddeIMAPI();
		$api_version = $uddeim->version();
		if ($api_version >= '3') {
			$uddeim_version = $uddeim->mainVersion();
			$thirdparty['uddeim'] = '[u]UddeIm[/u] '.$uddeim_version['version'];
		} else {
			$thirdparty['uddeim'] = checkThirdPartyVersion('uddeim', array('uddeim.j15','uddeim'), 'UddeIm', 'components/com_uddeim', null, 1, 0, 0);
		}
	} else {
		$thirdparty['uddeim'] = checkThirdPartyVersion('uddeim', array('uddeim.j15','uddeim'), 'UddeIm', 'components/com_uddeim', null, 1, 0, 0);
	}
	foreach ($thirdparty as $id=>$item) {
		if (empty($item)) unset ($thirdparty[$id]);
	}
	if (!empty($thirdparty)) $thirdpartytext = '[quote][b]Third-party components:[/b] ' . implode(' | ', $thirdparty) . ' [/quote]';
	else $thirdpartytext = '[quote][b]Third-party components:[/b] None [/quote]';

	$sef = array();
	$sef['sh404sef'] = checkThirdPartyVersion('sh404sef', 'sh404sef', 'sh404sef', 'components/com_sh404sef', null, 1, 0, 0);
	$sef['joomsef'] = checkThirdPartyVersion('joomsef', 'sef', 'ARTIO JoomSEF', 'components/com_sef', null, 1, 0, 0);
	$sef['acesef'] = checkThirdPartyVersion('acesef', 'acesef', 'AceSEF', 'components/com_acesef', null, 1, 0, 0);
	foreach ($sef as $id=>$item) {
		if (empty($item)) unset ($sef[$id]);
	}
	if (!empty($sef)) $seftext = '[quote][b]Third-party SEF components:[/b] ' . implode(' | ', $sef) . ' [/quote]';
	else $seftext = '[quote][b]Third-party SEF components:[/b] None [/quote]';

	$report = '[confidential][b]Joomla! version:[/b] '.$jversion.' [b]Platform:[/b] '.$_SERVER['SERVER_SOFTWARE'].' ('
	    .$_SERVER['SERVER_NAME'].') [b]PHP version:[/b] '.phpversion().' | '.$safe_mode.' | '.$register_globals.' | '.$mbstring
	    .' | '.$gd_support.' | [b]MySQL version:[/b] '.$kunena_db->getVersion().' | [b]Base URL:[/b]' .JURI::root(). '[/confidential][quote][b]Database collation check:[/b] '.$collation.'
		[/quote][quote][b]Legacy mode:[/b] '.$jconfig_legacy.' | [b]Joomla! SEF:[/b] '.$jconfig_sef.' | [b]Joomla! SEF rewrite:[/b] '
	    .$jconfig_sef_rewrite.' | [b]FTP layer:[/b] '.$jconfig_ftp.' |[confidential][b]Mailer:[/b] '.$kunena_app->getCfg('mailer' ).' | [b]From name:[/b] '.$kunena_app->getCfg('fromname' ).' | [b]SMTP Secure:[/b] '.$kunena_app->getCfg('smtpsecure' ).' | [b]SMTP Port:[/b] '.$kunena_app->getCfg('smtpport' ).' | [b]SMTP User:[/b] '.$jconfig_smtpuser.' | [b]SMTP Host:[/b] '.$kunena_app->getCfg('smtphost' ).' [/confidential] [b]htaccess:[/b] '.$htaccess
	    .' | [b]PHP environment:[/b] [u]Max execution time:[/u] '.$maxExecTime.' seconds | [u]Max execution memory:[/u] '
	    .$maxExecMem.' | [u]Max file upload:[/u] '.$fileuploads.' [/quote][b]Kunena menu details[/b]:[spoiler] '.$joomlamenudetails.'[/spoiler][quote][b]Joomla default template details :[/b] '.$templatedetails->name.' | [u]author:[/u] '.$templatedetails->author.' | [u]version:[/u] '.$templatedetails->version.' | [u]creationdate:[/u] '.$templatedetails->creationdate.' [/quote][quote][b]Kunena default template details :[/b] '.$ktempaltedetails->name.' | [u]author:[/u] '.$ktempaltedetails->author.' | [u]version:[/u] '.$ktempaltedetails->version.' | [u]creationdate:[/u] '.$ktempaltedetails->creationDate.' [/quote][quote] [b]Kunena version detailled:[/b] [u]Installed version:[/u] '.$kunenaVersionInfo->version.' | [u]Build:[/u] '
	    .$kunenaVersionInfo->build.' | [u]Version name:[/u] '.$kunenaVersionInfo->name.' | [u]Kunena detailled configuration:[/u] [spoiler] '.$kconfigsettings.'[/spoiler][/quote]'.$thirdpartytext.' '.$seftext.' '.$plgtext.' '.$modtext;

	return $report;
}

function getJoomlaTemplate() {
	$kunena_db = JFactory::getDBO ();
	if (KUNENA_JOOMLA_COMPAT == '1.5') {
		$templatedetails = new stdClass();
		// Get Joomla! frontend assigned template for Joomla! 1.5

		$query = ' SELECT template '
				.' FROM #__templates_menu '
				.' WHERE client_id = 0 AND menuid = 0 ';
		$kunena_db->setQuery($query);
		$jdefaultemplate = $kunena_db->loadResult();

		$templatedetails->name = $jdefaultemplate;

		$xml_tmpl = JFactory::getXMLparser('Simple');
		$xml_tmpl->loadFile(JPATH_SITE.'/templates/'.$jdefaultemplate.'/templateDetails.xml');
		$templatecreationdate= $xml_tmpl->document->creationDate[0];
		$templatedetails->creationdate = $templatecreationdate->data();
		$templateauthor= $xml_tmpl->document->author[0];
		$templatedetails->author = $templateauthor->data();
		$templateversion = $xml_tmpl->document->version[0];
		$templatedetails->version = $templateversion->data();
	} else {
		$templatedetails = new stdClass();
		// Get Joomla! frontend assigned template for Joomla! 1.6
		$query = " SELECT template,title "
				." FROM #__template_styles "
				." WHERE client_id = '0' AND home = '1'";
		$kunena_db->setQuery($query);
		$jdefaultemplate = $kunena_db->loadObject();

		$templatedetails->name = $jdefaultemplate->template;

		$xml_tmpl = JFactory::getXMLparser('Simple');
		$xml_tmpl->loadFile(JPATH_SITE.'/templates/'.$jdefaultemplate->template.'/templateDetails.xml');
		$templatecreationdate= $xml_tmpl->document->creationDate[0];
		$templatedetails->creationdate = $templatecreationdate->data();
		$templateauthor= $xml_tmpl->document->author[0];
		$templatedetails->author = $templateauthor->data();
		$templateversion = $xml_tmpl->document->version[0];
		$templatedetails->version = $templateversion->data();
	}

	return $templatedetails;
}

function getJoomlaMenuDetails() {
	$kunena_db = JFactory::getDBO ();
	if (KUNENA_JOOMLA_COMPAT == '1.5') {
		// Get Kunena aliases
		$query = "SELECT m.id, m.menutype, m.name, m.alias, m.link, m.parent
			FROM #__menu AS m
			INNER JOIN #__menu AS mm ON m.link LIKE CONCAT( '%Itemid=', mm.id )
			WHERE m.published=1 AND m.type = 'menulink' AND mm.link LIKE '%com_kunena%'
			ORDER BY m.menutype, m.parent, m.ordering ASC";
		$kunena_db->setQuery($query);
		$kmenustype = (array) $kunena_db->loadObjectlist('id');
		// Get Kunena menu items
		$query = "SELECT id, menutype, name, alias, link, parent
			FROM #__menu
			WHERE published=1 AND link LIKE '%com_kunena%' ORDER BY menutype, parent, ordering ASC";
		$kunena_db->setQuery($query);
		$kmenustype += (array) $kunena_db->loadObjectlist('id');

		$joomlamenudetails = '[table][tr][td][u] ID [/u][/td][td][u] Name [/u][/td][td][u] Alias [/u][/td][td][u] Menutype [/u][/td][td][u] Link [/u][/td][td][u] ParentID [/u][/td][/tr] ';
		foreach($kmenustype as $item) {
			$joomlamenudetails .= '[tr][td]'.$item->id.' [/td][td] '.$item->name.' [/td][td] '.$item->alias.' [/td][td] '.$item->menutype.' [/td][td] '.$item->link.' [/td][td] '.$item->parent.'[/td][/tr] ';
		}
	} else {
		// Get Kunena extension id
		$query = "SELECT extension_id "
				." FROM #__extensions "
				." WHERE name='com_kunena' AND type='component'";
		$kunena_db->setQuery($query);
		$kextensionid = $kunena_db->loadResult();
		if (KunenaError::checkDatabaseError()) return;

		// Get Kunena menu items
		$query = "SELECT id "
				." FROM #__menu "
				." WHERE component_id='$kextensionid' AND published='1' AND parent_id='1' AND level='1' ORDER BY id ASC";
		$kunena_db->setQuery($query);
		$kmenuparentid = $kunena_db->loadResult();
		if (KunenaError::checkDatabaseError()) return;

		$query = "SELECT id, menutype, title, alias, link, path "
				." FROM #__menu "
				." WHERE parent_id={$kunena_db->Quote($kmenuparentid)} AND type='component' OR title='Kunena Forum' OR title='Kunena' ORDER BY id ASC";
		$kunena_db->setQuery($query);
		$kmenustype = $kunena_db->loadObjectlist();
		if (KunenaError::checkDatabaseError()) return;

		$joomlamenudetails = '[table][tr][td][u] ID [/u][/td][td][u] Name [/u][/td][td][u] Alias [/u][/td][td][u] Menutype [/u][/td][td][u] Link [/u][/td][td][u] Path [/u][/td][/tr] ';
		foreach($kmenustype as $item) {
			$joomlamenudetails .= '[tr][td]'.$item->id.' [/td][td] '.$item->title.' [/td][td] '.$item->alias.' [/td][td] '.$item->menutype.' [/td][td] '.$item->link.' [/td][td] '.$item->path.'[/td][/tr] ';
		}
	}
	$joomlamenudetails .='[/table]';

	return $joomlamenudetails;

}

function getTablesCollation() {
	$kunena_db = JFactory::getDBO ();

	// Check each table in the database if the collation is on utf8
	$tableslist = $kunena_db->getTableList();
	$collation = '';
	foreach($tableslist as $table) {
		if (preg_match('`_kunena_`',$table)) {
			$kunena_db->setQuery("SHOW FULL FIELDS FROM " .$table. "");
			$fullfields = $kunena_db->loadObjectList ();
			if (KunenaError::checkDatabaseError()) return;

			$fieldTypes = array('tinytext','text','char','varchar');

			foreach ($fullfields as $row) {
				$tmp = strpos ( $row->Type , '(' );

				if ($tmp) {
					if ( in_array(substr($row->Type,0,$tmp),$fieldTypes) ) {
						if(!empty($row->Collation) && !preg_match('`utf8`',$row->Collation)) {
							$collation .= $table.' [color=#FF0000]have wrong collation of type '.$row->Collation.' [/color] on field '.$row->Field.'  ';
						}
					}
				} else {
					if ( in_array($row->Type,$fieldTypes) ) {
						if(!empty($row->Collation) && !preg_match('`utf8`',$row->Collation)) {
							$collation .= $table.' [color=#FF0000]have wrong collation of type '.$row->Collation.' [/color] on field '.$row->Field.'  ';
						}
					}
				}
			}
		}
	}
	if(empty($collation)) {
		$collation = 'The collation of your table fields are correct';
	}

	return $collation;
}

function checkThirdPartyVersion($namephp, $namexml, $namedetailled, $path, $plggroup=null, $components=0, $module=0, $plugin=0) {
	jimport('joomla.filesystem.file');
	if ($components) {
		if ( JFile::exists(JPATH_SITE.'/'.$path.'/'.$namephp.'.php') ) {
			$check = false;
			foreach($namexml as $filexml) {
				if ( JFile::exists(JPATH_ADMINISTRATOR.'/'.$path.'/'.$filexml.'.xml') ) {
					$xml_com = JFactory::getXMLparser('Simple');
					$xml_com->loadFile(JPATH_ADMINISTRATOR.'/'.$path.'/'.$filexml.'.xml');
					$com_version = $xml_com->document->version[0];
					$com_version = '[u]'.$namedetailled.'[/u] '.$com_version->data();
					$check = true;
				}
			}

			if(!$check){
				$com_version = '[u]'.$namedetailled.':[/u] The file doesn\'t exist '.$namexml.'.xml !';
			}
		} else {
			$com_version = '';
		}
		return $com_version;
	} elseif ($module) {
		if ( JModuleHelper::isEnabled($namephp) && JFile::exists(JPATH_SITE.'/'.$path.'/'.$namephp.'.php') ) {
			if ( JFile::exists(JPATH_SITE.'/'.$path.'/'.$namexml.'.xml') ) {
				$xml_mod = JFactory::getXMLparser('Simple');
				$xml_mod->loadFile(JPATH_SITE.'/'.$path.'/'.$namexml.'.xml');
				$mod_version = $xml_mod->document->version[0];
				$mod_version = '[u]'.$namedetailled.'[/u] '.$mod_version->data();
			} else {
				$mod_version = '[u]'.$namedetailled.':[/u] The file doesn\'t exist '.$namexml.'.xml !';
			}
		} else {
			$mod_version = '';
		}
		return $mod_version;
	} elseif ($plugin) {

		if (KUNENA_JOOMLA_COMPAT == '1.5') {
			$pathphp = JPATH_SITE.'/'.$path.'/'.$namephp;
			$pathxml = JPATH_SITE.'/'.$path.'/'.$namexml;
		} else {
			$pathphp = JPATH_SITE.'/'.$path.'/'.$namephp.'/'.$namephp;
			$pathxml =JPATH_SITE.'/'.$path.'/'.$namephp.'/'.$namexml;
		}
		if ( JPluginHelper::isEnabled($plggroup, $namephp) && JFile::exists($pathphp.'.php') ) {
			if ( JFile::exists($pathxml.'.xml') ) {
				$xml_plg = JFactory::getXMLparser('Simple');
				$xml_plg->loadFile($pathxml.'.xml');
				$plg_version = $xml_plg->document->version[0];
				$plg_version = '[u]'.$namedetailled.'[/u] '.$plg_version->data();
			}	else {
				$plg_version = '[u]'.$namedetailled.':[/u] The file doesn\'t exist '.$namexml.'.xml !';
			}
		} else {
			$plg_version = '';
		}
		return $plg_version;
	}
}

//===============================
// FINISH report system
//===============================

function showStats() {
	kimport ( 'thankyou' );
	include_once( KPATH_ADMIN . '/html/stats.php' );
}

/* Get latest kunena version
 *
 * Code originally taken from AlphaUserPoints
 * copyright Copyright (C) 2008-2010 Bernard Gilly
 * license : GNU/GPL
 * Website : http://www.alphaplug.com
 */
function getLatestKunenaVersion() {
	$kunena_app = & JFactory::getApplication ();

	$url = 'http://update.kunena.org/kunena_update.xml';
	$data = '';
	$check = array();
	$check['connect'] = 0;

	$data = $kunena_app->getUserState('com_kunena.version_check', null);
	if ( empty($data) ) {
		//try to connect via cURL
		if(function_exists('curl_init') && function_exists('curl_exec')) {
			$ch = @curl_init();

			@curl_setopt($ch, CURLOPT_URL, $url);
			@curl_setopt($ch, CURLOPT_HEADER, 0);
			//http code is greater than or equal to 300 ->fail
			@curl_setopt($ch, CURLOPT_FAILONERROR, 1);
			@curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			//timeout of 5s just in case
			@curl_setopt($ch, CURLOPT_TIMEOUT, 5);
			$data = @curl_exec($ch);
			@curl_close($ch);
		}

		//try to connect via fsockopen
		if(function_exists('fsockopen') && $data == '') {

			$errno = 0;
			$errstr = '';

			//timeout handling: 5s for the socket and 5s for the stream = 10s
			$fsock = @fsockopen("update.kunena.org", 80, $errno, $errstr, 5);

			if ($fsock) {
				@fputs($fsock, "GET /kunena_update.xml HTTP/1.1\r\n");
				@fputs($fsock, "HOST: update.kunena.org\r\n");
				@fputs($fsock, "Connection: close\r\n\r\n");

				//force stream timeout...
				@stream_set_blocking($fsock, 1);
				@stream_set_timeout($fsock, 5);

				$get_info = false;
				while (!@feof($fsock)) {
					if ($get_info) {
						$data .= @fread($fsock, 1024);
					} else {
						if (@fgets($fsock, 1024) == "\r\n") {
							$get_info = true;
						}
					}
				}
				@fclose($fsock);

				//need to check data cause http error codes aren't supported here
				if(!strstr($data, '<?xml version="1.0" encoding="utf-8"?><update>')) {
					$data = '';
				}
			}
		}

		//try to connect via fopen
		if (function_exists('fopen') && ini_get('allow_url_fopen') && $data == '') {

			//set socket timeout
			ini_set('default_socket_timeout', 5);

			$handle = @fopen ($url, 'r');

			//set stream timeout
			@stream_set_blocking($handle, 1);
			@stream_set_timeout($handle, 5);

			$data	= @fread($handle, 1000);

			@fclose($handle);
		}

		$kunena_app->setUserState('com_kunena.version_check',$data);

	}

	if( !empty($data) && strstr($data, '<?xml version="1.0" encoding="utf-8"?>') ) {
		$xml = & JFactory::getXMLparser('Simple');
		$xml->loadString($data);
		$version 				= & $xml->document->version[0];
		$check['latest_version'] = & $version->data();
		$released 				= & $xml->document->released[0];
		$check['released'] 		= & $released->data();
		$check['connect'] 		= 1;
		$check['enabled'] 		= 1;
	}

	return $check;
}

function checkLatestVersion() {
	$latestVersion = getLatestKunenaVersion();

	if ( $latestVersion['connect'] ) {
		if ( version_compare($latestVersion['latest_version'], Kunena::version(), '<=') ) {
			$needUpgrade = JText::sprintf('COM_KUNENA_COM_A_CHECK_VERSION_CORRECT', Kunena::version());
		} else {
			$needUpgrade = JText::sprintf('COM_KUNENA_COM_A_CHECK_VERSION_NEED_UPGRADE',$latestVersion['latest_version'],$latestVersion['released']);
		}
	} else {
		$needUpgrade = JText::_('COM_KUNENA_COM_A_CHECK_VERSION_CANNOT_CONNECT');
	}


	return $needUpgrade;
}

// Grabs gd version


function KUNENA_gdVersion() {
	// Simplified GD Version check
	if (! extension_loaded ( 'gd' )) {
		return;
	}

	if (function_exists ( 'gd_info' )) {
		$ver_info = gd_info ();
		preg_match ( '/\d/', $ver_info ['GD Version'], $match );
		$gd_ver = $match [0];
		return $match [0];
	} else {
		return;
	}
}

function kescape($string) {
	return htmlspecialchars($string, ENT_COMPAT, 'UTF-8');
}

ob_end_flush();
