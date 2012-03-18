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

// Dont allow direct linking

defined( '_JEXEC' ) or die();

jimport( 'joomla.html.parameter' );

// Kunena wide defines
require_once (JPATH_COMPONENT . '/lib/kunena.defines.php');
require_once(KUNENA_PATH_LIB . '/kunena.version.php');

// Load language from component if it doesn't exist in Joomla language folder
KunenaFactory::loadLanguage();

class KunenaApp {

	function __construct() {
		ob_start();

		// Display time it took to create the entire page in the footer
		jimport( 'joomla.error.profiler' );
		$__kstarttime = JProfiler::getmicrotime();

		$kunena_config = KunenaFactory::getConfig ();

		kimport('error');
		KunenaError::initialize();

// First of all take a profiling information snapshot for JFirePHP
if(JDEBUG){
	require_once (JPATH_COMPONENT . '/lib/kunena.profiler.php');
	$__profiler = KProfiler::GetInstance();
	$__profiler->mark('Start');
}

$func = JString::strtolower ( JRequest::getCmd ( 'func', JRequest::getCmd ( 'view', '' )) );
$do = JRequest::getCmd ( 'do', '' );
$task = JRequest::getCmd ( 'task', '' );
$format = JRequest::getCmd ( 'format', 'html' );
JRequest::setVar ( 'func', $func );

// Workaround for Joomla 1.7.3 login bug, see: https://github.com/joomla/joomla-platform/pull/740
if ($func == 'profile' && ($task == 'login' || $task == 'logout')) {
	require_once ( KUNENA_PATH_FUNCS . '/profile.php');
	$page = new CKunenaProfile(JFactory::getUser()->id, $task);
}

require_once(KUNENA_PATH . '/router.php');
if ($func && !isset(KunenaRouter::$functions[$func])) {
	// If func is not legal, raise joomla error
	return JError::raiseError( 404, 'Kunena function "' . $func . '" not found' );
}

$kunena_app = JFactory::getApplication ();

if (empty($_POST) && $format == 'html') {
	$me = KunenaFactory::getUser();
	$menu = JSite::getMenu ();
	$active = $menu->getActive ();

	// Joomla 1.6+ multi-language support
	if (isset($active->language) && $active->language != '*') {
		$language = JFactory::getDocument()->getLanguage();
		if (strtolower($active->language) != strtolower($language)) {
			$this->redirect (KunenaRoute::_(null, false));
		}
	}

	// Legacy menu item and Itemid=0 support with redirect and notice
	if (empty($active->query ['view'])) {
		$new = $menu->getItem (KunenaRoute::getItemID ());
		if ($new) {
			if ($active) {
				if ($active->route == $new->route) {
					KunenaError::warning(JText::sprintf('COM_KUNENA_WARNING_MENU_CONFLICT', $active->route, $active->id, $new->id), 'menu');
					$menu->setActive ( $new->id );
					$active = $new;
				} else {
					KunenaError::warning(JText::sprintf('COM_KUNENA_WARNING_MENU_LEGACY', $active->route, $active->id, $new->route, $new->id), 'menu');
					$this->redirect (KunenaRoute::_(null, false));
				}
			} else {
				KunenaError::warning(JText::sprintf('COM_KUNENA_WARNING_MENU_NO_ITEM_REDIRECT', $new->route, $new->id));
				$this->redirect (KunenaRoute::_(null, false));
			}
		} elseif (!$active) {
			KunenaError::warning(JText::sprintf('COM_KUNENA_WARNING_MENU_NO_ITEM'));
		}
	}
	if (!$func || $func == 'entrypage') {
		// If we are currently in entry page, we need to show and highlight default menu item
		if (!empty ( $active->query ['defaultmenu'] )) {
			$defaultitem = $active->query ['defaultmenu'];
			if ($defaultitem > 0) {
				$newitem = $menu->getItem ($defaultitem);
				if (!$newitem) {
					KunenaError::warning(JText::sprintf('COM_KUNENA_WARNING_MENU_NOT_EXISTS'), 'menu');
				} elseif (empty($newitem->component) || $newitem->component != 'com_kunena') {
					KunenaError::warning(JText::sprintf('COM_KUNENA_WARNING_MENU_NOT_KUNENA'), 'menu');
				} elseif ($active->route == $newitem->route) {
					// Special case: we are using Entry Page instead of menu alias and we have identical menu alias
					if ($active->id != $newitem->id) {
						$defaultitem = !empty ( $newitem->query ['defaultmenu'] ) ? $newitem->query ['defaultmenu'] : $newitem->id;
						$newitem2 = $menu->getItem ($defaultitem);
						if (empty($newitem2->component) || $newitem2->component != 'com_kunena') {
							$defaultitem = $newitem->id;
						}
						if ($defaultitem) {
							$menu->setActive ( $defaultitem );
							$active = $menu->getActive ();
						}
					}
				} else {
					$oldlocation = KunenaRoute::getCurrentMenu ();
					$menu->setActive ( $defaultitem );
					$active = $menu->getActive ();

					$newlocation = KunenaRoute::getCurrentMenu ();
					if (!$oldlocation || $oldlocation->id != $newlocation->id) {
						// Follow Default Menu Item if it's not in the same menu
						$this->redirect (KunenaRoute::_($defaultitem, false));
					}
				}
				if (is_object ( $active )) {
					foreach ( $active->query as $var => $value ) {
						if ($var == 'view')
							$var = 'func';
						if ($var == 'func' && $value == 'entrypage')
							$value = $func;
						JRequest::setVar ( $var, $value );
					}
					$func = JRequest::getCmd ( 'func' );
				}
			}
		}
	}
	$newItemid = KunenaRoute::getItemid();
	if ($active && $newItemid && !KunenaRoute::getCurrentMenu () && $active->id != $newItemid) {
		$newroute = KunenaRoute::_($newItemid, false);
		if (strpos('/'.$active->route, $newroute) === 0) {
			$menu->setActive ( $newItemid );
			$active = $menu->getActive ();
		} else {
			$this->redirect (KunenaRoute::_(null, false));
		}
	}
}

global $message;
global $kunena_this_cat;

// Get all the variables we need and strip them in case

$action = JRequest::getCmd ( 'action', '' );
$catid = JRequest::getInt ( 'catid', 0 );
$contentURL = JRequest::getVar ( 'contentURL', '' );
$email = JRequest::getVar ( 'email', '' );
$favoriteMe = JRequest::getVar ( 'favoriteMe', '' );
$fb_authorname = JRequest::getVar ( 'fb_authorname', '' );
$fb_thread = JRequest::getInt ( 'fb_thread', 0 );
$id = JRequest::getInt ( 'id', 0 );
$mesid = JRequest::getInt ( 'mesid', 0 );
$limit = JRequest::getInt ( 'limit', 0 );
$limitstart = JRequest::getInt ( 'limitstart', 0 );
$markaction = JRequest::getVar ( 'markaction', '' );
$message = JRequest::getVar ( 'message', '' );
$page = JRequest::getInt ( 'page', 0 );
$parentid = JRequest::getInt ( 'parentid', 0 );
$pid = JRequest::getInt ( 'pid', 0 );
$replyto = JRequest::getInt ( 'replyto', 0 );
$resubject = JRequest::getVar ( 'resubject', '' );
$rowid = JRequest::getInt ( 'rowid', 0 );
$rowItemid = JRequest::getInt ( 'rowItemid', 0 );
$subject = JRequest::getVar ( 'subject', '' );
$subscribeMe = JRequest::getVar ( 'subscribeMe', '' );
$thread = JRequest::getInt ( 'thread', 0 );
$topic_emoticon = JRequest::getVar ( 'topic_emoticon', '' );
$userid = JRequest::getInt ( 'userid', 0 );
$no_html = JRequest::getBool ( 'no_html', 0 );

// If JFirePHP is installed and enabled, leave a trace of the Kunena startup
if(JDEBUG == 1 && defined('JFIREPHP')){
	// FB::trace("Kunena Startup");
}

// Redirect Forum Jump
if (isset ( $_POST ['func'] ) && $func == "showcat") {
	header ( "HTTP/1.1 303 See Other" );
	header ( "Location: " . KunenaRoute::_ ( 'index.php?option=com_kunena&func=showcat&catid=' . $catid, false ) );
	$kunena_app->close ();
}

$kunena_my = &JFactory::getUser ();
$kunena_db = &JFactory::getDBO ();

$document = JFactory::getDocument();
$document->addScriptDeclaration('// <![CDATA[
var kunena_toggler_close = "'.JText::_('COM_KUNENA_TOGGLER_COLLAPSE').'";
var kunena_toggler_open = "'.JText::_('COM_KUNENA_TOGGLER_EXPAND').'";
// ]]>');

global $lang, $topic_emoticons;

// Class structure should be used after this and all the common task should be moved to this class
require_once (JPATH_COMPONENT . '/class.kunena.php');

// Central Location for all internal links
require_once (JPATH_COMPONENT . '/lib/kunena.link.class.php');

require_once (JPATH_COMPONENT . '/lib/kunena.smile.class.php');

// Redirect profile (menu item) to the right component
if ($func == 'profile' && !$do && empty($_POST)) {
	$redirect = 1;
	if (!empty($active)) {
		$params = new JParameter($active->params);
		$redirect = $params->get('integration', 1);
	}
	if ($redirect) {
		$profileIntegration = KunenaFactory::getProfile();
		if (!($profileIntegration instanceof KunenaProfileKunena)) {
			$url = CKunenaLink::GetProfileURL($kunena_my->id, false);
			if ($url) $this->redirect($url);
		}
	}
}

// Check for JSON request
if ($func == "json") {

	if(JDEBUG == 1 && defined('JFIREPHP')){
		FB::log('Kunena JSON request');
	}

	// URL format for JSON requests: e.g: index.php?option=com_kunena&func=json&action=autocomplete&do=getcat
	require_once (JPATH_COMPONENT . '/lib/kunena.ajax.helper.php');

	$ajaxHelper = &CKunenaAjaxHelper::getInstance();

	// Get the document object.
	$document =& JFactory::getDocument();

	// Set the MIME type for JSON output.
	$document->setMimeEncoding( 'application/json' );

	// Change the suggested filename.
	if ($action!='uploadfile') JResponse::setHeader( 'Content-Disposition', 'attachment; filename="kunena.json"' );

	$value = JRequest::getVar ( 'value', '' );

	JResponse::sendHeaders();

	if ($kunena_config->board_offline && ! CKunenaTools::isAdmin ()){
		// when the forum is offline, we don't entertain json requests
		json_encode ( array(
				'status' => '0',
				'error' => @sprintf(_KUNENA_FORUM_OFFLINE)) );
	}
	else {
		// Generate reponse
		echo $ajaxHelper->generateJsonResponse($action, $do, $value);
	}

	$kunena_app->close ();
}

if ($kunena_config->board_offline && ! CKunenaTools::isAdmin ()) {
	// if the board is offline
	echo $kunena_config->offline_message;
} else if ($kunena_config->regonly && ! $kunena_my->id) {
	// if we only allow registered users
	if (file_exists ( KUNENA_JTEMPLATEPATH . '/css/kunena.forum-min.css' )) {
		CKunenaTools::addStyleSheet ( KUNENA_JTEMPLATEURL . '/css/kunena.forum-min.css' );
	} else {
		CKunenaTools::addStyleSheet ( KUNENA_TMPLTCSSURL );
	}
	echo '<div id="Kunena">';
	$this->header = JText::_('COM_KUNENA_LOGIN_NOTIFICATION');
	$this->body = JText::_('COM_KUNENA_LOGIN_FORUM');
	CKunenaTools::loadTemplate('/login.php');
	echo '</div>';
} else {
	// =======================================================================================
	// Forum is online:

	//intercept the RSS request; we should stop afterwards
	if ($func == 'rss') {
		require_once ( JPATH_COMPONENT.'/funcs/rss.php');

		$feed = new CKunenaRSSView($catid);
		$feed->display();
		$kunena_app->close ();
	}

	if ($func == 'fb_pdf' || $func == 'pdf') {
		$httpReferer = JRequest::getVar ( 'HTTP_REFERER', JURI::base ( true ), 'server' );

		if (KUNENA_JOOMLA_COMPAT == '1.5') {
			include (JPATH_COMPONENT.'/lib/kunena.pdf.php');
			$kunena_app->close ();
		}
	}

	$format = JRequest::getCmd ( 'format', 'html' );
	if ($format != 'html') {
		echo "Kunena: Unsupported output format {$format}, please use only format=html or .html";
		$kunena_app->close ();
	}

	$integration = KunenaFactory::getProfile();
	$integration->open();

	//time format
	include_once (JPATH_COMPONENT . '/lib/kunena.timeformat.class.php');

	$document = & JFactory::getDocument ();

	if (file_exists ( KUNENA_ABSTMPLTPATH . '/initialize.php' )) {
		require_once ( KUNENA_ABSTMPLTPATH . '/initialize.php' );
	} else {
		require_once (KPATH_SITE . '/template/default/initialize.php');
	}

	// Insert WhoIsOnlineDatas
	require_once (KUNENA_PATH_LIB . '/kunena.who.class.php');

	$who =& CKunenaWhoIsOnline::getInstance();
	$who->insertOnlineDatas ();

	// include required libraries
	jimport('joomla.template.template');

	// Kunena Current Template Icons Pack
	if (file_exists ( KUNENA_ABSTMPLTPATH . '/icons.php' )) {
		include (KUNENA_ABSTMPLTPATH . '/icons.php');
	} else {
		include (KUNENA_PATH_TEMPLATE_DEFAULT . '/icons.php');
	}

	if(JDEBUG){
		$__profiler->mark('Session Start');
	}

	// We only save session for registered users
	$kunena_session = KunenaFactory::getSession ( true );
	if ($kunena_my->id > 0) {
		// new indicator handling
		if ($markaction == "allread") {
			if (!JRequest::checkToken()) {
				$kunena_app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
				while (@ob_end_clean());
				$kunena_app->redirect ( CKunenaLink::GetCategoryURL('listcat', $catid, false) );
			}
			$kunena_session->markAllCategoriesRead ();
		}
		if (!$kunena_session->save ()) $kunena_app->enqueueMessage ( JText::_('COM_KUNENA_ERROR_SESSION_SAVE_FAILED'), 'error' );

		if ($markaction == "allread") {
			while (@ob_end_clean());
			$kunena_app->redirect ( CKunenaLink::GetCategoryURL('listcat', $catid, false), JText::_('COM_KUNENA_GEN_ALL_MARKED') );
		}

		$userprofile = KunenaFactory::getUser();
		if (!$userprofile->exists()) {
			$userprofile->save();
		}

		// Assign previous visit without user offset to variable for templates to decide
		$this->prevCheck = $kunena_session->lasttime;

	} else {
		// For guests we don't show new posts
		$this->prevCheck = CKunenaTimeformat::internalTime()+60;
	}

	if(JDEBUG){
		$__profiler->mark('Session End');
	}

	//Get the topics this user has already read this session from #__kunena_sessions
	$this->read_topics = explode ( ',', $kunena_session->readtopics );


	/*       _\|/_
             (o o)
     +----oOO-{_}-OOo--------------------------------+
     |    Until this section we have included the    |
     |   necessary files and gathered the required   |
     |     variables. Now let's start processing     |
     |                     them                      |
     +----------------------------------------------*/

	//Check if the catid requested is a parent category, because if it is
	//the only thing we can do with it is 'listcat' and nothing else
	if ($func == "showcat") {
		if ($catid != 0) {
			$kunena_db->setQuery ( "SELECT parent FROM #__kunena_categories WHERE id='{$catid}'" );
			$catParent = intval($kunena_db->loadResult ());
			if (KunenaError::checkDatabaseError()) return;
		}
		if ($catid == 0 || $catParent == 0) {
			$this->redirect ( CKunenaLink::GetCategoryURL('listcat',$catid, false) );
		}
	}
	$kunena_app->setUserState( 'com_kunena.redirect', null);
	?>

<div id="Kunena"><?php
	if ($kunena_config->board_offline) {
		?>
<span id="fbOffline"><?php
		echo JText::_('COM_KUNENA_FORUM_IS_OFFLINE')?></span> <?php
	}
	?>
 <?php
	if(JDEBUG){
		$__profiler->mark('Profilebox Start');
	}

	CKunenaTools::loadTemplate('/menu.php');
	CKunenaTools::displayLoginBox();

 	if(JDEBUG){
		$__profiler->mark('Profilebox End');
	}

	// Handle help / rules menuitems
	if ($func == 'article') {
		$func = $do;
	}

	if(JDEBUG){
		$__profiler->mark('$func Start');
	}

	switch ($func) {
		case 'who' :
			require_once (KUNENA_PATH_LIB . '/kunena.who.class.php');
			$online =& CKunenaWhoIsOnline::getInstance();
			$online->displayWho();

			break;

		case 'announcement' :
			require_once (KUNENA_PATH_LIB . '/kunena.announcement.class.php');
			$ann = CKunenaAnnouncement::getInstance();
			$ann->display();

			break;

        case 'poll':
  			require_once (KUNENA_PATH_LIB . '/kunena.poll.class.php');
  			$kunena_polls =& CKunenaPolls::getInstance();
  			$kunena_polls->display();

            break;

		case 'polls':
			require_once (KUNENA_PATH_LIB . '/kunena.poll.class.php');
			$kunena_polls =& CKunenaPolls::getInstance();
			$kunena_polls->polldo();

			break;

		case 'stats' :
			require_once(KUNENA_PATH_LIB . '/kunena.stats.class.php');
			$kunena_stats = new CKunenaStats ( );
			$kunena_stats->showStats ();

			break;

		case 'myprofile' :
		case 'userprofile' :
		case 'fbprofile' :
		case 'profile' :
		case 'moderateuser' :
			require_once ( KUNENA_PATH_FUNCS . '/profile.php');
			$page = new CKunenaProfile($userid, $task ? $task : $do);
			$page->display();

			break;

		case 'userlist' :
			require_once (KUNENA_PATH_FUNCS . '/userlist.php');
			$page = new CKunenaUserlist();
			$page->display();

			break;

		case 'post' :
			require_once (KUNENA_PATH_FUNCS . '/post.php');
			$page = new CKunenaPost();
			$page->display();

			break;

		case 'view' :
			require_once (KUNENA_PATH_FUNCS . '/view.php');
			$layout = $kunena_app->getUserStateFromRequest( "com_kunena.view_layout", 'layout', 'view' );
			$page = new CKunenaView($layout, $catid, $id, $limitstart, $limit, $mesid);
			$page->display(true, true);

			break;

		case 'showcat' :
			require_once (KUNENA_PATH_FUNCS . '/showcat.php');
			$page = new CKunenaShowcat($catid, $page);
			$page->display();

			break;

		case 'listcat' :
			require_once (KUNENA_PATH_FUNCS . '/listcat.php');
			$page = new CKunenaListcat($catid);
			$page->display();

			break;

		case 'review' :
			require_once (KUNENA_PATH_LIB . '/kunena.review.php');
			$review = new CKunenaReview($catid);
			$review->display();

			break;

		case 'rules' :
		case 'help' :
			CKunenaTools::loadTemplate('/'.$func.'.php');

			break;

		case 'report' :
			require_once(KUNENA_PATH_LIB . '/kunena.report.class.php');
			$report = new CKunenaReport();
			$report->display();

			break;

		case 'latest' :
		case 'mylatest' :
		case 'noreplies' :
		case 'subscriptions' :
		case 'favorites' :
		case 'userposts' :
		case 'unapproved' :
		case 'deleted' :
			require_once (KUNENA_PATH_FUNCS . '/latestx.php');
			if ($do) $func = $do;
			$page = new CKunenaLatestX($func, $page);
			$page->display();

			break;

		case 'search' :
		case 'advsearch' :
			require_once (JPATH_COMPONENT . '/lib/kunena.search.class.php');

			$kunenaSearch = new CKunenaSearch ( );
			$kunenaSearch->show ();
			break;

		case 'markthisread' :
			if (!JRequest::checkToken('get')) {
				$kunena_app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
				while (@ob_end_clean());
				$kunena_app->redirect ( CKunenaLink::GetCategoryURL('showcat' , $catid, false ), JText::_('COM_KUNENA_GEN_FORUM_MARKED') );
			}
			// Mark all unread topics in the category to read
			$readTopics = $kunena_session->readtopics;
			$kunena_db->setQuery ( "SELECT thread FROM #__kunena_messages WHERE catid='{$catid}' AND parent=0 AND thread NOT IN ({$readTopics})" );
			$readForum = $kunena_db->loadResultArray ();
			if (KunenaError::checkDatabaseError()) return;
			$readTopics = implode(',', array_merge(explode(',', $readTopics), $readForum));
			$kunena_db->setQuery ( "UPDATE #__kunena_sessions set readtopics='$readTopics' WHERE userid=$kunena_my->id" );
			$kunena_db->query ();
			if (KunenaError::checkDatabaseError()) return;

			while (@ob_end_clean());
			$kunena_app->redirect ( CKunenaLink::GetCategoryURL('showcat' , $catid, false ), JText::_('COM_KUNENA_GEN_FORUM_MARKED') );
			break;

		case 'subscribecat' :
			if (!JRequest::checkToken('get')) {
				$kunena_app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
				if ($userid == 0) {
					while (@ob_end_clean());
					$kunena_app->redirect ( CKunenaLink::GetCategoryURL('showcat' , $catid, false ) );
				} else {
					while (@ob_end_clean());
					$kunena_app->redirect ( CKunenaLink::GetProfileURL($userid, false) );
				}
			}

			$success_msg = '';

			if ( $catid && $kunena_my->id ) {
				$query = "INSERT INTO #__kunena_subscriptions_categories (catid, userid) VALUES ('$catid','$kunena_my->id')";
				$kunena_db->setQuery ( $query );

				if (@$kunena_db->query () && $kunena_db->getAffectedRows () == 1) {
					$success_msg = JText::_('COM_KUNENA_GEN_CATEGORY_SUBCRIBED');
				}
				KunenaError::checkDatabaseError();
			}

			while (@ob_end_clean());
			$kunena_app->redirect ( CKunenaLink::GetCategoryURL('showcat' , $catid, false ), $success_msg );
			break;

		case 'unsubscribecat' :
			if (!JRequest::checkToken('get')) {
				$kunena_app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
				if ($userid == 0) {
					while (@ob_end_clean());
					$kunena_app->redirect ( CKunenaLink::GetCategoryURL('showcat' , $catid, false ), $success_msg );
				} else {
					while (@ob_end_clean());
					$kunena_app->redirect ( CKunenaLink::GetProfileURL($userid, false), $success_msg );
				}
			}
			$success_msg = '';
			if ($catid && $kunena_my->id ) {
				$query = "DELETE FROM #__kunena_subscriptions_categories WHERE catid=$catid AND userid=$kunena_my->id";
				$kunena_db->setQuery ( $query );

				if ($kunena_db->query () && $kunena_db->getAffectedRows () == 1) {
					$success_msg = JText::_('COM_KUNENA_GEN_CATEGORY_UNSUBCRIBED');
				}
				KunenaError::checkDatabaseError();
			}

			if ($userid == 0) {
				while (@ob_end_clean());
				$kunena_app->redirect ( CKunenaLink::GetCategoryURL('showcat' , $catid, false ), $success_msg );
			} else {
				while (@ob_end_clean());
				$kunena_app->redirect ( CKunenaLink::GetProfileURL($userid, false), $success_msg );
			}
			break;

		case 'karma' :
			include (JPATH_COMPONENT . '/lib/kunena.karma.php');

			break;

		case 'thankyou':
			require_once(JPATH_COMPONENT.'/lib/kunena.thankyou.php');
			$thankyou = new CKunenaThankyou ();
			$thankyou->setThankyou();

			break;

		case 'bulkactions' :
			switch ($do) {
				case "bulkDel" :
					CKunenaTools::KDeletePosts ( );

					break;

				case "bulkMove" :
					CKunenaTools::KMovePosts ( $catid );
					break;

				case "bulkFavorite" :
					if (!JRequest::checkToken()) {
						$kunena_app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
						while (@ob_end_clean());
						$kunena_app->redirect ( CKunenaLink::GetProfileURL($kunena_my->id, false) );
					}
					require_once(JPATH_ROOT.'/administrator/components/com_kunena/libraries/api.php');
					$KunenaUserAPI = new KunenaUserAPI();
					$cb = KGetArrayReverseInts ( "cb" );
					$result = $KunenaUserAPI->unfavoriteThreads($kunena_my->id, $cb);

					if ( $result ) {
						$message = JText::_('COM_KUNENA_USER_UNFAVORITE_YES');
					} else {
						$message = JText::_('COM_KUNENA_POST_UNFAVORITED_TOPIC');
					}

					while (@ob_end_clean());
					$kunena_app->redirect(CKunenaLink::GetProfileURL($kunena_my->id, false),$message);
					break;

				case "bulkSub" :
					if (!JRequest::checkToken()) {
						$kunena_app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
						while (@ob_end_clean());
						$kunena_app->redirect ( CKunenaLink::GetProfileURL($kunena_my->id, false) );
					}
					require_once(JPATH_ROOT.'/administrator/components/com_kunena/libraries/api.php');
					$KunenaUserAPI = new KunenaUserAPI();
					$cb = KGetArrayReverseInts ( "cb" );
					$result = $KunenaUserAPI->unsubscribeThreads($kunena_my->id, $cb);

					if ( $result ) {
						$message = JText::_('COM_KUNENA_USER_UNSUBSCRIBE_YES');
					} else {
						$message = JText::_('COM_KUNENA_POST_NO_UNSUBSCRIBED_TOPIC');
					}

					while (@ob_end_clean());
					$kunena_app->redirect(CKunenaLink::GetProfileURL($kunena_my->id, false),$message);
					break;
				case "bulkDelPerm" :
					CKunenaTools::KDeletePerm ( );
					break;

				case "bulkRestore" :
					CKunenaTools::KUndelete ( );
					break;
			}

			break;

		case 'template' :
			jimport('joomla.filesystem.path');
			$name = JRequest::getString ( 'name', JRequest::getString ( 'kunena_template', '', 'COOKIE' ) );
			while (@ob_end_clean());
			if ($name) {
				$name = JPath::clean($name);
				if (!is_readable ( KPATH_SITE . "/template/{$name}/template.xml" )) {
					$name = 'default';
				}
				setcookie ( 'kunena_template', $name, 0, JURI::root(true).'/' );
			} else {
				setcookie ( 'kunena_template', null, time () - 3600, JURI::root(true).'/' );
			}
			$kunena_app->redirect ( CKunenaLink::GetKunenaURL(false) );
			break;

		case 'credits' :
			include (JPATH_COMPONENT . '/lib/kunena.credits.php');

			break;

		default :
			require_once (KUNENA_PATH_FUNCS . '/listcat.php');
			$page = new CKunenaListcat($catid);
			$page->display();

			break;
	}

	if(JDEBUG){
		$__profiler->mark('$func End');
	}

	// Bottom Module
	CKunenaTools::showModulePosition( 'kunena_bottom' );

	// PDF and RSS
	if ($kunena_config->enablerss || $kunena_config->enablepdf) {
		if ($catid>0) {
			kimport('category');
			$category = KunenaCategory::getInstance($catid);
			if ($category->pub_access == 0 && $category->parent) $rss_params = '&amp;catid=' . (int) $catid;
		} else {
			$rss_params = '';
		}
		if (isset($rss_params) || $kunena_config->enablepdf) {
			echo '<div class="krss-block">';
			if ($kunena_config->enablepdf && $func == 'view' && KUNENA_JOOMLA_COMPAT == '1.5') {
				// FIXME: add better translation:
				echo CKunenaLink::GetPDFLink($catid, $limit, $limitstart, $id, CKunenaTools::showIcon ( 'kpdf', JText::_('PDF') ), 'nofollow', '', JText::_('PDF'));
			}
			if ($kunena_config->enablerss && isset($rss_params)) {
				if ( $kunena_config->rss_specification == 'atom1.0' ) $rss_specification = 'application/atom+xml';
				else $rss_specification = 'application/rss+xml';
				$document->addCustomTag ( '<link rel="alternate" type="'.$rss_specification.'" title="' . JText::_('COM_KUNENA_LISTCAT_RSS') . '" href="' . CKunenaLink::GetRSSURL($rss_params) . '" />' );
				echo CKunenaLink::GetRSSLink ( CKunenaTools::showIcon ( 'krss', JText::_('COM_KUNENA_LISTCAT_RSS') ), 'follow', $rss_params );
			}
			echo '</div>';
		}
	}

	$template = KunenaFactory::getTemplate();
	$this->params = $template->params;
	// Credits
	echo '<div class="kcredits kms"> ' . CKunenaLink::GetTeamCreditsLink ( $catid, JText::_('COM_KUNENA_POWEREDBY') ) . ' ' . CKunenaLink::GetCreditsLink ();
		if ($this->params->get('templatebyText') !=''):
	echo ' :: <a href ="'. $this->params->get('templatebyLink').'" rel="follow">' . $this->params->get('templatebyText') ;
	if ($this->params->get('templatebyName')) {
	echo ' '.$this->params->get('templatebyName') .'</a>';
	} else { echo '</a>'; }
	endif;
	echo '</div>';

	// display footer

	// Show total time it took to create the page
	$__ktime = JProfiler::getmicrotime() - $__kstarttime;
?>
	<div class="kfooter">
		<span class="kfooter-time"><?php echo JText::_('COM_KUNENA_FOOTER_TIME_TO_CREATE').'&nbsp;'.sprintf('%0.2f', $__ktime).'&nbsp;'.JText::_('COM_KUNENA_FOOTER_TIME_SECONDS');?></span>
	</div>
</div>
<!-- closes Kunena div -->
<?php
$document->addHeadLink( KunenaRoute::_(), 'canonical', 'rel', '' );
$integration = KunenaFactory::getProfile();
$integration->close();

//$params = JComponentHelper::getParams( 'com_kunena' );
//if ($params->get( 'show_page_title' )) $document->setTitle ( $params->get( 'page_title' ) );

if (empty($_POST) && $format == 'html') {
	$default = KunenaRoute::getDefault();
	if ($default) $menu->setActive($default->id);
}

} // end of online

if(JDEBUG == 1){
	$__profiler->mark('Done');
	$__queries = $__profiler->getQueryCount();
	if(defined('JFIREPHP')){
		FB::log($__profiler->getBuffer(), 'Kunena Profiler');
		if($__queries>50){
			FB::error($__queries, 'Kunena Queries');
		} else if($__queries>35){
			FB::warn($__queries, 'Kunena Queries');
		} else {
			FB::log($__queries, 'Kunena Queries');
		}
	}
}
	ob_end_flush();
	}
	/**
	* Escapes a value for output in a view script.
	*
	* If escaping mechanism is one of htmlspecialchars or htmlentities, uses
	* {@link $_encoding} setting.
	*
	* @param  mixed $var The output to escape.
	* @return mixed The escaped value.
	*/
	function escape($var)
	{
		return htmlspecialchars($var, ENT_COMPAT, 'UTF-8');
	}

	function redirect($url) {
		$app = JFactory::getApplication();
		$redirect = (array) $app->getUserState( 'com_kunena.redirect');
		if (isset($redirect[$url])) {
			// TODO: translate COM_KUNENA_WARNING_MENU_REDIRECT_LOOP
			KunenaError::warning(JText::sprintf('Infinite redirect loop detected in %s', $url), 'menu');
			return;
		}
		$redirect[$url] = 1;
		$app->setUserState( 'com_kunena.redirect', $redirect);
		while (@ob_end_clean());
		$app->redirect ($url);
	}
}

$kunena = new KunenaApp();
