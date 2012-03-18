<?php
/**
 * @version $Id$
 * Kunena Component
 * @package Kunena
 *
 * @Copyright (C) 2008 - 2011 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
// Dont allow direct linking
defined ( '_JEXEC' ) or die ();

kimport('spam.recaptcha');

class CKunenaPost {
	public $allow = 0;

	function __construct() {
		$this->do = JRequest::getCmd ( 'do', '' );
		$this->action = JRequest::getCmd ( 'action', '' );

		$this->_app = & JFactory::getApplication ();
		$this->config = KunenaFactory::getConfig ();
		$this->_session = KunenaFactory::getSession ();
		$this->_db = &JFactory::getDBO ();
		$this->document = JFactory::getDocument ();
		require_once (KPATH_SITE . '/lib/kunena.poll.class.php');
		$this->poll =& CKunenaPolls::getInstance();

		$this->my = JFactory::getUser ();
		$this->me = KunenaFactory::getUser ();

		$this->id = JRequest::getInt ( 'id', 0 );
		if (! $this->id) {
			$this->id = JRequest::getInt ( 'parentid', 0 );
		}
		if (! $this->id) {
		// Support for old $replyto variable in post reply/quote
			$this->id = JRequest::getInt ( 'replyto', 0 );
		}
		$this->catid = JRequest::getInt ( 'catid', 0 );

		$this->msg_cat = null;

		$this->allow = 1;

		$this->cat_default_allow = null;
		$this->allow_topic_icons = null;

		$template = KunenaFactory::getTemplate();
		$this->params = $template->params;

		$this->numLink = null;
		$this->replycount= null;
	}

	// Temporary function to handle old style permission handling
	// TODO: Remove this when all functions are using new style
	protected function load() {
		if ($this->msg_cat)
			return true;

		if ($this->id) {
			// Check that message and category exists and fill some information for later use
			$query = "SELECT m.*, (mm.locked OR c.locked) AS locked, c.locked AS catlocked, t.message,
					c.name AS catname, c.parent AS catparent, c.pub_access,
					c.review, c.class_sfx, p.id AS poll_id, c.allow_anonymous,
					c.post_anonymous, c.allow_polls
				FROM #__kunena_messages AS m
				INNER JOIN #__kunena_messages AS mm ON mm.id=m.thread
				INNER JOIN #__kunena_messages_text AS t ON t.mesid=m.id
				INNER JOIN #__kunena_categories AS c ON c.id=m.catid
				LEFT JOIN #__kunena_polls AS p ON m.id=p.threadid
				WHERE m.id={$this->_db->Quote($this->id)}";

			$this->_db->setQuery ( $query );
			$this->msg_cat = $this->_db->loadObject ();
			if (! $this->msg_cat) {
				KunenaError::checkDatabaseError();
				echo JText::_ ( 'COM_KUNENA_POST_INVALID' );
				return false;
			}

			// Make sure that category id is from the message (post may have been moved)
			if ($this->do != 'domovepostnow' && $this->do != 'domergepostnow' && $this->do != 'dosplit') {
				$this->catid = $this->msg_cat->catid;
			}
			$this->cat_default_allow = $this->msg_cat->allow_anonymous;
		} else if ($this->catid) {
			// Check that category exists and fill some information for later use
			$this->_db->setQuery ( "SELECT 0 AS id, 0 AS thread, id AS catid, name AS catname, parent AS catparent, pub_access, locked, locked AS catlocked, review, class_sfx, allow_anonymous, post_anonymous, allow_polls FROM #__kunena_categories WHERE id={$this->_db->Quote($this->catid)}" );
			$this->msg_cat = $this->_db->loadObject ();
			if (! $this->msg_cat) {
				KunenaError::checkDatabaseError();
				echo JText::_ ( 'COM_KUNENA_NO_ACCESS' );
				return false;
			}
			$this->cat_default_allow = $this->msg_cat->allow_anonymous;
		} else {
			//get default category
			$this->_db->setQuery ( "SELECT c.allow_anonymous FROM `#__kunena_categories` AS c
				INNER JOIN `#__kunena_categories` AS p ON c.parent=p.id AND p.parent=0
				WHERE c.id IN ({$this->_session->allowed}) ORDER BY p.ordering, p.name, c.ordering, c.name LIMIT 1" );
			$this->cat_default_allow = $this->_db->loadResult ();
			KunenaError::checkDatabaseError();
		}

		// Special check to verify if topic icons are allowed when do new post and when catid is true
		if ( isset($this->msg_cat->id)) {
			if ($this->msg_cat->id == 0) $this->allow_topic_icons = 1;
		}

		// Check if anonymous user needs to log in
		if ($this->my->id == 0 && (! $this->config->pubwrite || ($this->catid && ! $this->_session->canRead ( $this->catid )))) {
			$this->header = JText::_('COM_KUNENA_LOGIN_NOTIFICATION');
			$this->body = JText::_('COM_KUNENA_LOGIN_FORUM');
			CKunenaTools::loadTemplate ( '/login.php' );
			return false;
		}
		// Check user access rights
		if (!empty ( $this->msg_cat->catparent ) && ! $this->_session->canRead ( $this->catid ) && ! CKunenaTools::isAdmin ()) {
			echo JText::_('COM_KUNENA_NO_ACCESS');
			return false;
		}

		return true;
	}

	protected function post() {
		if ($this->isUserBanned() )
			return false;
		if ($this->isIPBanned())
			return false;

		$this->fields ['name'] = JRequest::getString ( 'authorname', $this->getAuthorName () );
		$this->fields ['email'] = JRequest::getString ( 'email', null );
		$this->fields ['subject'] = JRequest::getVar ( 'subject', null, 'POST', 'string', JREQUEST_ALLOWRAW );
		$this->fields ['message'] = JRequest::getVar ( 'message', null, 'POST', 'string', JREQUEST_ALLOWRAW );
		$this->fields ['topic_emoticon'] = JRequest::getInt ( 'topic_emoticon', null );

		$this->options ['attachments'] = 1;
		$this->options ['anonymous'] = JRequest::getInt ( 'anonymous', 0 );
		$this->options ['subscribe'] = JRequest::getVar ( 'subscribeMe', '' );
		$contentURL = JRequest::getVar ( 'contentURL', '' );

		// These store above data into session
		if ($this->tokenProtection ())
			return false;
		if ($this->floodProtection ())
			return false;
		$this->verifyCaptcha ();

		require_once (KUNENA_PATH_LIB . '/kunena.posting.class.php');
		$message = new CKunenaPosting ( );
		if (! $this->id) {
			$success = $message->post ( $this->catid, $this->fields, $this->options );
		} else {
			$success = $message->reply ( $this->id, $this->fields, $this->options );
		}

		if ($success) {
			$success = $message->save ();
		}

		// Handle errors
		if (! $success) {
			$errors = $message->getErrors ();
			foreach ( $errors as $field => $error ) {
				$this->_app->enqueueMessage ( $field . ': ' . $error, 'error' );
			}
			$this->_app->setUserState('com_kunena.postfields', array('catid'=>$this->catid, 'fields'=>$this->fields, 'options'=>$this->options));
			$this->redirectBack ();
		}

		$catinfo = $message->parent;
		$userid = $message->get ( 'userid' );
		$id = $message->get ( 'id' );
		$thread = $message->get('thread');
		$subject = $message->get('subject');
		$holdPost = $message->get ( 'hold' );

		$polltitle = JRequest::getString ( 'poll_title', 0 );
		$optionsnumbers = JRequest::getInt ( 'number_total_options', '' );
		$polltimetolive = JRequest::getString ( 'poll_time_to_live', 0 );

		//Insert in the database the informations for the poll and the options for the poll
		$poll_exist = null;
		if (! empty ( $optionsnumbers ) && ! empty ( $polltitle )) {
			$poll_exist = "1";
			//Begin Poll management options
			$poll_optionsID = JRequest::getVar('polloptionsID', array (), 'post', 'array');
			$optvalue = array();
			foreach($poll_optionsID as $opt) {
				if ( !empty($opt) ) $optvalue[] = $opt;
			}

			if ( !empty($optvalue) ) $this->poll->save_new_poll ( $polltimetolive, $polltitle, $id, $optvalue );
		}

		// TODO: replace this with better solution
		$this->_db->setQuery ( "SELECT COUNT(*) AS totalmessages FROM #__kunena_messages WHERE thread={$this->_db->Quote($thread)}" );
		$result = $this->_db->loadObject ();
		KunenaError::checkDatabaseError();
		$threadPages = ceil ( $result->totalmessages / $this->config->messages_per_page );
		//construct a useable URL (for plaintext - so no &amp; encoding!)
		jimport ( 'joomla.environment.uri' );
		$uri = & JURI::getInstance ( JURI::base () );
		$LastPostUrl = $uri->toString ( array ('scheme', 'host', 'port' ) ) . str_replace ( '&amp;', '&', CKunenaLink::GetThreadPageURL ( 'view', $this->catid, $thread, $threadPages, $this->config->messages_per_page, $id ) );

		$message->emailToSubscribers($LastPostUrl, $this->config->allowsubscriptions && ! $holdPost, $this->config->mailmod || $holdPost, $this->config->mailadmin || $holdPost);

		$redirectmsg = '';

		$subscribeMe = JRequest::getVar ( 'subscribeMe', '' );

		//now try adding any new subscriptions if asked for by the poster
		if ($subscribeMe == 1) {
			$this->_db->setQuery ( "INSERT INTO #__kunena_subscriptions (thread,userid) VALUES ({$this->_db->Quote($thread)},{$this->_db->Quote($this->my->id)})" );

			if (@$this->_db->query ()) {
				$redirectmsg .= JText::_ ( 'COM_KUNENA_POST_SUBSCRIBED_TOPIC' ) . '<br />';
			} else {
				$redirectmsg .= JText::_ ( 'COM_KUNENA_POST_NO_SUBSCRIBED_TOPIC' ) . '<br />';
			}
		}

		if ($holdPost == 1) {
			$redirectmsg .= JText::_ ( 'COM_KUNENA_POST_SUCCES_REVIEW' );
		} else {
			$redirectmsg .= JText::_ ( 'COM_KUNENA_POST_SUCCESS_POSTED' );
		}
		while (@ob_end_clean());
		$this->_app->redirect ( CKunenaLink::GetMessageURL ( $id, $this->catid, 0, false ), $redirectmsg );
	}

	protected function newtopic($do) {
		$this->reply($do);
	}

	protected function reply($do) {
		if (!$this->load())
			return false;
		if ($this->lockProtection ())
			return false;
		if ($this->floodProtection ())
			return false;
		if ($this->isUserBanned() )
			return false;
		if ($this->isIPBanned())
			return false;

		$this->kunena_editmode = 0;

		$saved = $this->_app->getUserState('com_kunena.postfields');
		$this->_app->setUserState('com_kunena.postfields', null);

		$message = $this->msg_cat;
		if ($this->catid && $this->msg_cat->id > 0) {
			if ($do == 'quote') {
				// FIXME: do better than this
				$mestext = preg_replace('/\[confidential\](.*?)\[\/confidential\]/su', '', $message->message );
				$this->message_text .= "[quote=\"{$message->name}\" post={$message->id}]" .  $mestext . "[/quote]";
			} else {
				$this->message_text = '';
			}
			$reprefix = JString::substr ( $message->subject, 0, JString::strlen ( JText::_ ( 'COM_KUNENA_POST_RE' ) ) ) != JText::_ ( 'COM_KUNENA_POST_RE' ) ? JText::_ ( 'COM_KUNENA_POST_RE' ) . ' ' : '';
			$this->subject = $message->subject;
			$this->resubject = $reprefix . $this->subject;
			$this->parent = $message->parent;
		} else {
			$this->message_text = '';
			$this->resubject = '';
			$this->parent = 0;

			$this->_db->setQuery ( "SELECT id,post_anonymous FROM #__kunena_categories WHERE parent!=0 AND allow_anonymous='1'" );
			$anynomouscatid = $this->_db->loadObjectList ();
			KunenaError::checkDatabaseError();

			$arrayanynomousbox = array();
			foreach( $anynomouscatid as $item ) {
				$arrayanynomousbox[] = '"'.$item->id.'":'.$item->post_anonymous;
			}

			$arrayanynomousbox = implode(',',$arrayanynomousbox);
			$this->document->addScriptDeclaration('var arrayanynomousbox={'.$arrayanynomousbox.'}');

			$this->_db->setQuery ( "SELECT id FROM #__kunena_categories WHERE parent!=0 AND allow_polls='1'" );
			$pollcatid = $this->_db->loadResultArray ();
			KunenaError::checkDatabaseError();

			$arraypollcatid = array();
			foreach( $pollcatid as $id ) {
				$arraypollcatid[] = '"'.$id.'":1';
			}

			$arraypollcatid = implode(',',$arraypollcatid);
			$this->document->addScriptDeclaration('var pollcategoriesid = {'.$arraypollcatid.'};');

			$options = array ();
			$this->selectcatlist = CKunenaTools::KSelectList ( 'catid', $options, '', false, 'postcatid', isset($saved['catid']) ? $saved['catid'] : $this->catid );
		}
		$this->authorName = $this->getAuthorName ();
		$this->emoid = 0;
		$this->action = 'post';

		$this->allow_anonymous = $this->cat_default_allow && $this->my->id;
		$this->anonymous = ($this->allow_anonymous) && ! empty ( $this->msg_cat->post_anonymous );
		$this->allow_name_change = 0;
		if (! $this->my->id || $this->config->changename || ! empty ( $this->msg_cat->allow_anonymous ) || CKunenaTools::isModerator ( $this->my->id, $this->catid )) {
			$this->allow_name_change = 1;
		}

		// check if this user is already subscribed to this topic but only if subscriptions are allowed
		$this->cansubscribe = 0;
		if ($this->my->id && $this->config->allowsubscriptions && $this->config->topic_subscriptions != 'disabled') {
			$this->cansubscribe = 1;
			$this->subscriptionschecked = $this->config->subscriptionschecked == 1;
			if ($this->msg_cat && $this->msg_cat->thread) {
				$this->_db->setQuery ( "SELECT thread FROM #__kunena_subscriptions WHERE userid={$this->_db->Quote($this->my->id)} AND thread={$this->_db->Quote($this->msg_cat->thread)}" );
				$subscribed = $this->_db->loadResult ();
				if (KunenaError::checkDatabaseError() || $subscribed) {
					$this->cansubscribe = 0;
				}
			}
		}

		if ($saved) {
			$this->catid = $saved['catid'];
			$this->message_text = $saved['fields']['message'];
			$this->resubject = $saved['fields']['subject'];
			$this->authorName = $saved['fields']['name'];
			$this->emoid = $saved['fields']['topic_emoticon'];
			$this->email = $saved['fields']['email'];
			if (isset($saved['options']['anonymous'])) $this->anonymous = $saved['options']['anonymous'];
			if (isset($saved['options']['subscribe'])) $this->subscriptionschecked = $saved['options']['subscribe'];
		}

		if ($this->id)
			$this->title = JText::_ ( 'COM_KUNENA_POST_REPLY_TOPIC' ) . ' ' . $this->subject;
		else
			$this->title = JText::_ ( 'COM_KUNENA_POST_NEW_TOPIC' );

		CKunenaTools::loadTemplate ( '/editor/form.php' );
	}

	protected function edit() {
		if (!$this->load())
			return false;
		if ($this->lockProtection ())
			return false;
		if ($this->isUserBanned() )
			return false;
		if ($this->isIPBanned())
			return false;

		$saved = $this->_app->getUserState('com_kunena.postfields');
		$this->_app->setUserState('com_kunena.postfields', null);

		$message = $this->msg_cat;
		if ($message->parent==0) $this->allow_topic_icons = 1;

		$allowEdit = 0;
		if (CKunenaTools::isModerator ( $this->my->id, $this->catid )) {
			// Moderator can edit any message
			$allowEdit = 1;
		} else if ($this->my->id && $this->my->id == $message->userid) {
			$allowEdit = CKunenaTools::editTimeCheck ( $message->modified_time, $message->time );
		}

		if ($allowEdit == 1) {
			// Load attachments
			require_once(KUNENA_PATH_LIB.'/kunena.attachments.class.php');
			$attachments = CKunenaAttachments::getInstance ();
			$this->attachments = array_pop($attachments->get($message->id));

			$this->kunena_editmode = 1;

			$this->message_text = $message->message;
			$this->resubject = $message->subject;
			$this->authorName = $message->name;
			$this->email = $message->email;
			$this->id = $message->id;
			$this->catid = $message->catid;
			$this->parent = $message->parent;
			$this->emoid = $message->topic_emoticon;
			$this->action = 'edit';

			//save the options for query after and load the text options, the number options is for create the fields in the form after
			if ($message->poll_id) {
				$this->polldatasedit = $this->poll->get_poll_data ( $this->id );
				if ($this->kunena_editmode) {
					$this->polloptionstotal = count ( $this->polldatasedit );
				}
			}

			$this->allow_anonymous = ! empty ( $this->msg_cat->allow_anonymous ) && $message->userid;
			$this->anonymous = 0;
			$this->allow_name_change = 0;
			if (! $this->my->id || $this->config->changename || ! empty ( $this->msg_cat->allow_anonymous ) || CKunenaTools::isModerator ( $this->my->id, $this->catid )) {
				$this->allow_name_change = 1;
			}
			if (!$this->allow_name_change && $message->userid == $this->my->id) $this->authorName = $this->getAuthorName ();

			$this->title = JText::_ ( 'COM_KUNENA_POST_EDIT' ) . ' ' . $this->resubject;

			if ($saved) {
				$this->authorName = $saved['fields']['name'];
				$this->email = $saved['fields']['email'];
				$this->resubject = $saved['fields']['subject'];
				$this->message_text = $saved['fields']['message'];
				$this->emoid = $saved['fields']['topic_emoticon'];
				if (isset($saved['options']['anonymous'])) $this->anonymous = $saved['options']['anonymous'];
			}
			$this->modified_reason = isset($saved['fields']['modified_reason']) ? $saved['fields']['modified_reason'] : '';

			CKunenaTools::loadTemplate ( '/editor/form.php' );
		} else {
			while (@ob_end_clean());
			$this->_app->redirect ( CKunenaLink::GetKunenaURL ( false ), JText::_ ( 'COM_KUNENA_POST_NOT_MODERATOR' ) );
		}
	}

	protected function editpostnow() {
		if (!$this->load())
			return false;
		if ($this->isUserBanned() )
			return false;
		if ($this->isIPBanned())
			return false;

		$this->fields ['name'] = JRequest::getString ( 'authorname', $this->msg_cat->name );
		$this->fields ['email'] = JRequest::getString ( 'email', null );
		$this->fields ['subject'] = JRequest::getVar ( 'subject', null, 'POST', 'string', JREQUEST_ALLOWRAW );
		$this->fields ['message'] = JRequest::getVar ( 'message', null, 'POST', 'string', JREQUEST_ALLOWRAW );
		$this->fields ['topic_emoticon'] = JRequest::getInt ( 'topic_emoticon', null );
		$this->fields ['modified_reason'] = JRequest::getString ( 'modified_reason', null );

		$this->options ['attachments'] = 1;
		$this->options ['anonymous'] = JRequest::getInt ( 'anonymous', 0 );

		// This stores above data into session
		if ($this->tokenProtection ())
			return false;

		require_once (KUNENA_PATH_LIB . '/kunena.posting.class.php');
		$message = new CKunenaPosting ( );
		$success = $message->edit ( $this->id, $this->fields, $this->options );
		if ($success) {
			$success = $message->save ();
		}

		// Handle errors
		if (! $success) {
			$errors = $message->getErrors ();
			foreach ( $errors as $field => $error ) {
				$this->_app->enqueueMessage ( $field . ': ' . $error, 'error' );
			}
			$this->_app->setUserState('com_kunena.postfields', array('catid'=>$this->catid, 'fields'=>$this->fields, 'options'=>$this->options));
			$this->redirectBack ();
		}

		$mes = $message->parent;

		if ($this->config->pollenabled) {
			$polltitle = JRequest::getString ( 'poll_title', 0 );
			$optionsnumbers = JRequest::getInt ( 'number_total_options', '' );
			$polltimetolive = JRequest::getString ( 'poll_time_to_live', 0 );
			$poll_optionsID = JRequest::getVar('polloptionsID', array (), 'post', 'array');
			$optvalue = array();
			foreach($poll_optionsID as $opt) {
				if ( !empty($opt) ) $optvalue[] = $opt;
			}

			//need to check if the poll exist, if it's not the case the poll is insered like new poll
			if (! $mes->poll_id) {
				if ( !empty($optvalue) ) $this->poll->save_new_poll ( $polltimetolive, $polltitle, $this->id, $optvalue );
			} else {
				if (empty ( $polltitle ) && empty($poll_optionsID)) {
					//The poll is deleted because the polltitle and the options are empty
					$this->poll->delete_poll ( $this->id );
				} else {
					$this->poll->update_poll_edit ( $polltimetolive, $this->id, $polltitle, $optionsnumbers, $poll_optionsID );
				}
			}
		}

		$this->_app->enqueueMessage ( JText::_ ( 'COM_KUNENA_POST_SUCCESS_EDIT' ) );
		if ($this->msg_cat->review && !CKunenaTools::isModerator($this->my->id,$this->catid)) {
			$this->_app->enqueueMessage ( JText::_ ( 'COM_KUNENA_GEN_MODERATED' ) );
		}
		while (@ob_end_clean());
		$this->_app->redirect ( CKunenaLink::GetMessageURL ( $this->id, $this->catid, 0, false ) );
	}

	protected function delete() {
		if ($this->tokenProtection ('get'))
			return false;
		if ($this->isUserBanned() )
			return false;
		if ($this->isIPBanned())
			return false;

		require_once (KUNENA_PATH_LIB . '/kunena.posting.class.php');
		$message = new CKunenaPosting ( );
		$success = $message->delete ( $this->id );

		// Handle errors
		if (! $success) {
			$errors = $message->getErrors ();
			foreach ( $errors as $field => $error ) {
				$this->_app->enqueueMessage ( $field . ': ' . $error, 'error' );
			}
		} else {
			$this->_app->enqueueMessage ( JText::_ ( 'COM_KUNENA_POST_SUCCESS_DELETE') );
		}
		while (@ob_end_clean());
		$this->_app->redirect ( CKunenaLink::GetMessageURL ( $this->id, $this->catid, 0, false ) );
	}

	protected function undelete() {
		if ($this->tokenProtection ('get'))
			return false;
		if ($this->isUserBanned() )
			return false;
		if ($this->isIPBanned())
			return false;

		require_once (KUNENA_PATH_LIB . '/kunena.posting.class.php');
		$message = new CKunenaPosting ( );
		$success = $message->undelete ( $this->id );

		// Handle errors
		if (! $success) {
			$errors = $message->getErrors ();
			foreach ( $errors as $field => $error ) {
				$this->_app->enqueueMessage ( $field . ': ' . $error, 'error' );
			}
		} else {
			$this->_app->enqueueMessage ( JText::_ ( 'COM_KUNENA_POST_SUCCESS_UNDELETE') );
		}
		while (@ob_end_clean());
		$this->_app->redirect ( CKunenaLink::GetMessageURL ( $this->id, $this->catid, 0, false ) );
	}

	protected function permdelete() {
		if ($this->tokenProtection ('get'))
			return false;
		if (!$this->load())
			return false;
		// FIXME: we need better permission control
		if ($this->moderatorProtection ())
			return false;
		if ($this->isUserBanned() )
			return false;
		if ($this->isIPBanned())
			return false;

		require_once (KUNENA_PATH_LIB . '/kunena.moderation.class.php');
		$kunena_mod = CKunenaModeration::getInstance ();

		$delete = $kunena_mod->deleteMessagePerminantly ( $this->id, true );
		if (! $delete) {
			$this->_app->enqueueMessage( $kunena_mod->getErrorMessage ());
		} else {
			$this->_app->enqueueMessage( JText::_ ( 'COM_KUNENA_POST_SUCCESS_DELETE' ));
		}

		if ($this->msg_cat->parent) {
			$this->redirectBack ();
		} else {
			while (@ob_end_clean());
			$this->_app->redirect ( CKunenaLink::GetCategoryURL ( 'showcat', $this->catid, false ));
		}
	}

	protected function deletethread() {
		if ($this->tokenProtection ('get'))
			return false;
		if (!$this->load())
			return false;
		if ($this->moderatorProtection ())
			return false;
		if ($this->isUserBanned() )
			return false;
		if ($this->isIPBanned())
			return false;

		require_once (KUNENA_PATH_LIB . '/kunena.moderation.class.php');
		$kunena_mod = CKunenaModeration::getInstance ();

		$delete = $kunena_mod->deleteThread ( $this->id );
		if (! $delete) {
			$message = $kunena_mod->getErrorMessage ();
		} else {
			$message = JText::_ ( 'COM_KUNENA_TOPIC_SUCCESS_DELETE' );
		}

		while (@ob_end_clean());
		$this->_app->redirect ( CKunenaLink::GetCategoryURL ( 'showcat', $this->catid, false ), $message );
	}

	protected function moderate($modchoices='',$modthread = false) {
		if (!$this->load())
			return false;
		if ($this->moderatorProtection ())
			return false;
		if ($this->isUserBanned() )
			return false;
		if ($this->isIPBanned())
			return false;

		require_once (KUNENA_PATH_LIB . '/kunena.moderation.class.php');

		$this->moderateTopic = $modthread;
		$this->moderateMultiplesChoices = $modchoices;

		// Get list of latest messages:
		$query = "SELECT id,subject FROM #__kunena_messages WHERE catid={$this->_db->Quote($this->catid)} AND parent=0 AND hold=0 AND moved=0 AND thread!={$this->_db->Quote($this->msg_cat->thread)} ORDER BY id DESC";
		$this->_db->setQuery ( $query, 0, 30 );
		$messagesList = $this->_db->loadObjectlist ();
		if (KunenaError::checkDatabaseError()) return;

		// Get thread and reply count from current message:
		$query = "SELECT t.id,t.subject,COUNT(mm.id) AS replies FROM #__kunena_messages AS m
			INNER JOIN #__kunena_messages AS t ON m.thread=t.id
			LEFT JOIN #__kunena_messages AS mm ON mm.thread=m.thread AND mm.id > m.id
			WHERE m.id={$this->_db->Quote($this->id)}
			GROUP BY m.thread";
		$this->_db->setQuery ( $query, 0, 1 );
		$this->threadmsg = $this->_db->loadObject ();
		if (KunenaError::checkDatabaseError()) return;

		$messages =array ();
		if ($this->moderateTopic) {
			$messages [] = JHTML::_ ( 'select.option', 0, JText::_ ( 'COM_KUNENA_MODERATION_MOVE_TOPIC' ) );
		} else {
			$messages [] = JHTML::_ ( 'select.option', 0, JText::_ ( 'COM_KUNENA_MODERATION_CREATE_TOPIC' ) );
		}
		$messages [] = JHTML::_ ( 'select.option', -1, JText::_ ( 'COM_KUNENA_MODERATION_ENTER_TOPIC' ) );
		foreach ( $messagesList as $mes ) {
			$messages [] = JHTML::_ ( 'select.option', $mes->id, kunena_htmlspecialchars ( $mes->subject ) );
		}
		$this->messagelist = JHTML::_ ( 'select.genericlist', $messages, 'targettopic', 'class="inputbox"', 'value', 'text', 0, 'kmod_targettopic' );

		$options=array();
		$this->categorylist = CKunenaTools::KSelectList ( 'targetcat', $options, 'class="inputbox kmove_selectbox"', false, 'kmod_categories', $this->catid );
		$this->message = $this->msg_cat;
		$this->user = KunenaFactory::getUser($this->msg_cat->userid);

		CKunenaTools::loadTemplate ( '/moderate/moderate.php' );
	}

	protected function domoderate() {
		if (!$this->load())
			return false;
		if ($this->tokenProtection ())
			return false;
		if ($this->moderatorProtection ())
			return false;
		if ($this->isUserBanned() )
			return false;
		if ($this->isIPBanned())
			return false;

		require_once (KUNENA_PATH_LIB . '/kunena.moderation.class.php');

		$mode = JRequest::getVar ( 'mode', KN_MOVE_MESSAGE );
		$targetSubject = JRequest::getString ( 'subject', '' );
		$targetCat = JRequest::getInt ( 'targetcat', 0 );
		$targetId = JRequest::getInt ( 'targetid', 0 );
		if (!$targetId) $targetId = JRequest::getInt ( 'targettopic', 0 );
		$shadow = JRequest::getInt ( 'shadow', 0 );
		$changesubject = JRequest::getInt ( 'changesubject', 0 );

		$moderation = CKunenaModeration::getInstance ();
		$success = $moderation->move($this->id, $targetCat, $targetSubject, $targetId, $mode, $shadow, $changesubject);
		if (! $success) {
			$this->_app->enqueueMessage( $moderation->getErrorMessage () );
		} else {
			$this->_app->enqueueMessage( JText::_ ( 'COM_KUNENA_POST_SUCCESS_MOVE' ));
		}
		while (@ob_end_clean());
		$this->_app->redirect ( CKunenaLink::GetMessageURL ( $this->id, $this->catid, 0, false ) );
	}

	protected function subscribe() {
		if ($this->tokenProtection ('get'))
			return false;
		if (!$this->load())
			return false;
		$success_msg = JText::_ ( 'COM_KUNENA_POST_NO_SUBSCRIBED_TOPIC' );
		$this->_db->setQuery ( "SELECT thread FROM #__kunena_messages WHERE id='{$this->id}'" );
		if ($this->id && $this->my->id && $this->_db->query ()) {
			$thread = $this->_db->loadResult ();
			$this->_db->setQuery ( "INSERT INTO #__kunena_subscriptions (thread,userid) VALUES ({$this->_db->Quote($thread)},{$this->_db->Quote($this->my->id)})" );

			if (@$this->_db->query () && $this->_db->getAffectedRows () == 1) {
				$success_msg = JText::_ ( 'COM_KUNENA_POST_SUBSCRIBED_TOPIC' );

				// Activity integration
				$activity = KunenaFactory::getActivityIntegration();
				$activity->onAfterSubscribe($thread, 1);
			}
		}
		while (@ob_end_clean());
		$this->_app->redirect ( CKunenaLink::GetLatestPageAutoRedirectURL ( $this->id, $this->config->messages_per_page ), $success_msg );
	}

	protected function unsubscribe() {
		if ($this->tokenProtection ('get'))
			return false;
		if (!$this->load())
			return false;
		$success_msg = JText::_ ( 'COM_KUNENA_POST_NO_UNSUBSCRIBED_TOPIC' );
		$this->_db->setQuery ( "SELECT MAX(thread) AS thread FROM #__kunena_messages WHERE id={$this->_db->Quote($this->id)}" );
		if ($this->id && $this->my->id && $this->_db->query ()) {
			$thread = $this->_db->loadResult ();
			$this->_db->setQuery ( "DELETE FROM #__kunena_subscriptions WHERE thread={$this->_db->Quote($thread)} AND userid={$this->_db->Quote($this->my->id)}" );

			if ($this->_db->query () && $this->_db->getAffectedRows () == 1) {
				$success_msg = JText::_ ( 'COM_KUNENA_POST_UNSUBSCRIBED_TOPIC' );

				// Activity integration
				$activity = KunenaFactory::getActivityIntegration();
				$activity->onAfterSubscribe($thread, 0);
			}
		}
		while (@ob_end_clean());
		$this->_app->redirect ( CKunenaLink::GetLatestPageAutoRedirectURL ( $this->id, $this->config->messages_per_page ), $success_msg );
	}

	protected function favorite() {
		if ($this->tokenProtection ('get'))
			return false;
		if (!$this->load())
			return false;
		$success_msg = JText::_ ( 'COM_KUNENA_POST_NO_FAVORITED_TOPIC' );
		$this->_db->setQuery ( "SELECT thread FROM #__kunena_messages WHERE id={$this->_db->Quote($this->id)}" );
		if ($this->id && $this->my->id && $this->_db->query ()) {
			$thread = $this->_db->loadResult ();
			$this->_db->setQuery ( "INSERT INTO #__kunena_favorites (thread,userid) VALUES ({$this->_db->Quote($thread)},{$this->_db->Quote($this->my->id)})" );

			if (@$this->_db->query () && $this->_db->getAffectedRows () == 1) {
				$success_msg = JText::_ ( 'COM_KUNENA_POST_FAVORITED_TOPIC' );

				// Activity integration
				$activity = KunenaFactory::getActivityIntegration();
				$activity->onAfterFavorite($thread, 1);
			}
		}
		while (@ob_end_clean());
		$this->_app->redirect ( CKunenaLink::GetLatestPageAutoRedirectURL ( $this->id, $this->config->messages_per_page ), $success_msg );
	}

	protected function unfavorite() {
		if ($this->tokenProtection ('get'))
			return false;
		if (!$this->load())
			return false;
		$success_msg = JText::_ ( 'COM_KUNENA_POST_NO_UNFAVORITED_TOPIC' );
		$this->_db->setQuery ( "SELECT MAX(thread) AS thread FROM #__kunena_messages WHERE id={$this->_db->Quote($this->id)}" );
		if ($this->id && $this->my->id && $this->_db->query ()) {
			$thread = $this->_db->loadResult ();
			$this->_db->setQuery ( "DELETE FROM #__kunena_favorites WHERE thread={$this->_db->Quote($thread)} AND userid={$this->_db->Quote($this->my->id)}" );

			if ($this->_db->query () && $this->_db->getAffectedRows () == 1) {
				$success_msg = JText::_ ( 'COM_KUNENA_POST_UNFAVORITED_TOPIC' );

				// Activity integration
				$activity = KunenaFactory::getActivityIntegration();
				$activity->onAfterFavorite($thread, 0);
			}
		}
		while (@ob_end_clean());
		$this->_app->redirect ( CKunenaLink::GetLatestPageAutoRedirectURL ( $this->id, $this->config->messages_per_page ), $success_msg );
	}

	protected function sticky() {
		if ($this->tokenProtection ('get'))
			return false;
		if (!$this->load())
			return false;
		if ($this->moderatorProtection ())
			return false;
		if ($this->isUserBanned() )
			return false;
		if ($this->isIPBanned())
			return false;

		$success_msg = JText::_ ( 'COM_KUNENA_POST_STICKY_NOT_SET' );
		$this->_db->setQuery ( "update #__kunena_messages set ordering=1 where id={$this->_db->Quote($this->id)}" );
		if ($this->id && $this->_db->query () && $this->_db->getAffectedRows () == 1) {
			$success_msg = JText::_ ( 'COM_KUNENA_POST_STICKY_SET' );

			// Activity integration
			$activity = KunenaFactory::getActivityIntegration();
			$activity->onAfterSticky($this->id, 1);
		}
		while (@ob_end_clean());
		$this->_app->redirect ( CKunenaLink::GetLatestPageAutoRedirectURL ( $this->id, $this->config->messages_per_page ), $success_msg );
	}

	protected function unsticky() {
		if ($this->tokenProtection ('get'))
			return false;
		if (!$this->load())
			return false;
		if ($this->moderatorProtection ())
			return false;
		if ($this->isUserBanned() )
			return false;
		if ($this->isIPBanned())
			return false;

		$success_msg = JText::_ ( 'COM_KUNENA_POST_STICKY_NOT_UNSET' );
		$this->_db->setQuery ( "update #__kunena_messages set ordering=0 where id={$this->_db->Quote($this->id)}" );
		if ($this->id && $this->_db->query () && $this->_db->getAffectedRows () == 1) {
			$success_msg = JText::_ ( 'COM_KUNENA_POST_STICKY_UNSET' );

			// Activity integration
			$activity = KunenaFactory::getActivityIntegration();
			$activity->onAfterSticky($this->id, 0);
		}
		while (@ob_end_clean());
		$this->_app->redirect ( CKunenaLink::GetLatestPageAutoRedirectURL ( $this->id, $this->config->messages_per_page ), $success_msg );
	}

	protected function lock() {
		if ($this->tokenProtection ('get'))
			return false;
		if (!$this->load())
			return false;
		if ($this->moderatorProtection ())
			return false;
		if ($this->isUserBanned() )
			return false;
		if ($this->isIPBanned())
			return false;

		$success_msg = JText::_ ( 'COM_KUNENA_POST_LOCK_NOT_SET' );
		$this->_db->setQuery ( "update #__kunena_messages set locked=1 where id={$this->_db->Quote($this->id)}" );
		if ($this->id && $this->_db->query () && $this->_db->getAffectedRows () == 1) {
			$success_msg = JText::_ ( 'COM_KUNENA_POST_LOCK_SET' );

			// Activity integration
			$activity = KunenaFactory::getActivityIntegration();
			$activity->onAfterLock($this->id, 1);
		}
		while (@ob_end_clean());
		$this->_app->redirect ( CKunenaLink::GetLatestPageAutoRedirectURL ( $this->id, $this->config->messages_per_page ), $success_msg );
	}

	protected function unlock() {
		if ($this->tokenProtection ('get'))
			return false;
		if (!$this->load())
			return false;
		if ($this->moderatorProtection ())
			return false;
		if ($this->isUserBanned() )
			return false;
		if ($this->isIPBanned())
			return false;

		$success_msg = JText::_ ( 'COM_KUNENA_POST_LOCK_NOT_UNSET' );
		$this->_db->setQuery ( "update #__kunena_messages set locked=0 where id={$this->_db->Quote($this->id)}" );
		if ($this->id && $this->_db->query () && $this->_db->getAffectedRows () == 1) {
			$success_msg = JText::_ ( 'COM_KUNENA_POST_LOCK_UNSET' );

			// Activity integration
			$activity = KunenaFactory::getActivityIntegration();
			$activity->onAfterLock($this->id, 0);
		}
		while (@ob_end_clean());
		$this->_app->redirect ( CKunenaLink::GetLatestPageAutoRedirectURL ( $this->id, $this->config->messages_per_page ), $success_msg );
	}

	protected function approve() {
		if ($this->tokenProtection ('get'))
			return false;
		if (!$this->load())
			return false;
		if ($this->moderatorProtection ())
			return false;
		if ($this->isUserBanned() )
			return false;
		if ($this->isIPBanned())
			return false;

		require_once (KUNENA_PATH_LIB . '/kunena.posting.class.php');
		$message = new CKunenaPosting();
		$message->action($this->id);
		if ($message->canApprove()) {
			$success_msg = JText::_ ( 'COM_KUNENA_MODERATE_1APPROVE_FAIL' );
			$this->_db->setQuery ( "UPDATE #__kunena_messages SET hold=0 WHERE id={$this->_db->Quote($this->id)}" );
			if ($this->id && $this->_db->query () && $this->_db->getAffectedRows () == 1) {
				$success_msg = JText::_ ( 'COM_KUNENA_MODERATE_APPROVE_SUCCESS' );
				$this->_db->setQuery ( "SELECT COUNT(*) AS totalmessages FROM #__kunena_messages WHERE thread={$this->_db->Quote($this->msg_cat->thread)}" );
				$result = $this->_db->loadObject ();
				KunenaError::checkDatabaseError();
				$threadPages = ceil ( $result->totalmessages / $this->config->messages_per_page );
				//construct a useable URL (for plaintext - so no &amp; encoding!)
				jimport ( 'joomla.environment.uri' );
				$uri = & JURI::getInstance ( JURI::base () );
				$LastPostUrl = $uri->toString ( array ('scheme', 'host', 'port' ) ) . str_replace ( '&amp;', '&', CKunenaLink::GetThreadPageURL ( 'view', $this->catid, $this->msg_cat->thread, $threadPages, $this->config->messages_per_page, $this->id ) );
				$message->emailToSubscribers($LastPostUrl, $this->config->allowsubscriptions, $this->config->mailmod, $this->config->mailadmin);
				CKunenaTools::modifyCategoryStats($this->id, $this->msg_cat->parent, $this->msg_cat->time,$this->msg_cat->catid);
			}
		}
		while (@ob_end_clean());
		$this->_app->redirect ( CKunenaLink::GetMessageURL ( $this->id, $this->catid, 0, false ), $success_msg );
	}

	function hasThreadHistory() {
		if (! $this->config->showhistory || $this->id == 0)
			return false;
		return true;
	}

	function displayThreadHistory() {
		if (! $this->config->showhistory || $this->id == 0)
			return;

		//get all the messages for this thread
		$query = "SELECT m.*, t.* FROM #__kunena_messages AS m
			LEFT JOIN #__kunena_messages_text AS t ON m.id=t.mesid
			WHERE thread='{$this->msg_cat->thread}' AND hold='0'
			ORDER BY time DESC";
		$this->_db->setQuery ( $query, 0, $this->config->historylimit );
		$this->messages = $this->_db->loadObjectList ();
		if (KunenaError::checkDatabaseError()) return;

		$this->replycount = count($this->messages);

		//get attachments
		$mesids = array();
		foreach ($this->messages as $mes) {
			$mesids[]=$mes->id;
		}
		$mesids = implode(',', $mesids);
		require_once(KUNENA_PATH_LIB.'/kunena.attachments.class.php');
		$attachments = CKunenaAttachments::getInstance ();
		$this->attachmentslist = $attachments->get($mesids);

		$this->subject = $this->msg_cat->subject;

		CKunenaTools::loadTemplate ( '/editor/history.php' );
	}

	public function getNumLink($mesid ,$replycnt) {
		if ($this->config->ordering_system == 'replyid') {
			$this->numLink = CKunenaLink::GetSamePageAnkerLink( $mesid, '#' .$replycnt );
		} else {
			$this->numLink = CKunenaLink::GetSamePageAnkerLink ( $mesid, '#' . $mesid );
		}

		return $this->numLink;
	}

	protected function getAuthorName() {
		if (! $this->my->id) {
			$name = '';
		} else {
			$name = $this->config->username ? $this->my->username : $this->my->name;
		}
		return $name;
	}

	protected function moderatorProtection() {
		if (! CKunenaTools::isModerator ( $this->my->id, $this->catid )) {
			$this->_app->enqueueMessage ( JText::_ ( 'COM_KUNENA_POST_NOT_MODERATOR' ), 'notice' );
			if (!empty($this->fields)) $this->_app->setUserState('com_kunena.postfields', array('catid'=>$this->catid, 'fields'=>$this->fields, 'options'=>$this->options));
			$this->redirectBack ();
			return true;
		}
		return false;
	}

	protected function tokenProtection($method='post') {
		// get the token put in the message form to check that the form has been valided successfully
		if (JRequest::checkToken ($method) == false) {
			$this->_app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
			if (!empty($this->fields)) $this->_app->setUserState('com_kunena.postfields', array('catid'=>$this->catid, 'fields'=>$this->fields, 'options'=>$this->options));
			$this->redirectBack ();
			return true;
		}
		return false;
	}

	protected function lockProtection() {
		if ($this->msg_cat && $this->msg_cat->locked && ! CKunenaTools::isModerator ( $this->my->id, $this->catid )) {
			if ($this->msg_cat->catlocked) {
				$this->_app->enqueueMessage ( JText::_ ( 'COM_KUNENA_POST_ERROR_CATEGORY_LOCKED' ), 'error' );
			} else {
				$this->_app->enqueueMessage ( JText::_ ( 'COM_KUNENA_POST_ERROR_TOPIC_LOCKED' ), 'error' );
			}
			if (!empty($this->fields)) $this->_app->setUserState('com_kunena.postfields', array('catid'=>$this->catid, 'fields'=>$this->fields, 'options'=>$this->options));
			$this->redirectBack ();
			return true;
		}
		return false;
	}

	public function isUserBanned() {
		$profile = KunenaFactory::getUser();
		$banned = $profile->isBanned();
		if ($banned) {
			kimport('userban');
			$banned = KunenaUserBan::getInstanceByUserid($profile->userid, true);
			if (!$banned->isLifetime()) {
				require_once(KPATH_SITE.'/lib/kunena.timeformat.class.php');
				$this->_app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_POST_ERROR_USER_BANNED_NOACCESS_EXPIRY', CKunenaTimeformat::showDate($banned->expiration)), 'error' );
				$this->redirectBack();
				return true;
			} else {
				$this->_app->enqueueMessage ( JText::_ ( 'COM_KUNENA_POST_ERROR_USER_BANNED_NOACCESS' ), 'error' );
				$this->redirectBack();
				return true;
			}
		}
		return false;
	}

	protected function isIPBanned() {
		// Disabled for now..
		return false;

		kimport('userban');
		$banned = KunenaUserBan::getInstanceByIP($_SERVER['REMOTE_ADDR']);

		if ( $banned ) {
			if (!$banned->isLifetime()) {
				require_once(KPATH_SITE.'/lib/kunena.timeformat.class.php');
				$this->_app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_POST_ERROR_IP_BANNED_NOACCESS_EXPIRY', CKunenaTimeformat::showDate( $banned->expiration) ), 'error' );
				$this->redirectBack();
				return true;
			} else {
				$this->_app->enqueueMessage ( JText::_ ( 'COM_KUNENA_POST_ERROR_IP_BANNED_NOACCESS' ), 'error' );
				$this->redirectBack();
				return true;
			}
		}
		return false;
	}

	public function floodProtection() {
		// Flood protection
		$ip = $_SERVER ["REMOTE_ADDR"];

		if ($this->config->floodprotection && ! CKunenaTools::isModerator ( $this->my->id, $this->catid )) {
			$this->_db->setQuery ( "SELECT MAX(time) FROM #__kunena_messages WHERE ip={$this->_db->Quote($ip)}" );
			$lastPostTime = $this->_db->loadResult ();
			if (KunenaError::checkDatabaseError()) return false;

			if ($lastPostTime + $this->config->floodprotection > CKunenaTimeformat::internalTime ()) {
				echo JText::_ ( 'COM_KUNENA_POST_TOPIC_FLOOD1' ) . ' ' . $this->config->floodprotection . ' ' . JText::_ ( 'COM_KUNENA_POST_TOPIC_FLOOD2' ) . '<br />';
				echo JText::_ ( 'COM_KUNENA_POST_TOPIC_FLOOD3' );
				return true;
			}
		}
		return false;
	}

	function displayAttachments($attachments) {
		$this->attachments = $attachments;
		CKunenaTools::loadTemplate('/view/message.attachments.php');
	}

	function display() {
		if (! $this->allow)
			return;
		if ($this->action == "post") {
			$this->post ();
			return;
		} else if ($this->action == "cancel") {
			$this->_app->enqueueMessage ( JText::_ ( 'COM_KUNENA_SUBMIT_CANCEL' ) );
			return;
		}

		switch ($this->do) {
			case 'new' :
				$this->newtopic ( $this->do );
				break;

			case 'reply' :
			case 'quote' :
				$this->reply ( $this->do );
				break;

			case 'edit' :
				$this->edit ();
				break;

			case 'editpostnow' :
				$this->editpostnow ();
				break;

			case 'delete' :
				$this->delete ();
				break;

			case 'undelete' :
				$this->undelete ();
				break;

			case 'deletethread' :
				$this->deletethread ();
				break;

			case 'moderate' :
				$this->moderate ();
				break;

			case 'moderatethread' :
				$this->moderate ('',true);
				break;

			case 'merge' :
				$this->moderate ('modmergemessage',false);
				break;

			case 'move' :
				$this->moderate ('modmovemessage',false);
				break;

			case 'split' :
				$this->moderate ('modsplitmultpost',false);
				break;

			case 'movetopic' :
				$this->moderate ('modmovetopic',true);
				break;

			case 'mergetopic' :
				$this->moderate ('modmergetopic',true);
				break;

			case 'domoderate' :
				$this->domoderate ();
				break;

			case 'permdelete' :
				$this->permdelete();
				break;

			case 'subscribe' :
				$this->subscribe ();
				break;

			case 'unsubscribe' :
				$this->unsubscribe ();
				break;

			case 'favorite' :
				$this->favorite ();
				break;

			case 'unfavorite' :
				$this->unfavorite ();
				break;

			case 'sticky' :
				$this->sticky ();
				break;

			case 'unsticky' :
				$this->unsticky ();
				break;

			case 'lock' :
				$this->lock ();
				break;

			case 'unlock' :
				$this->unlock ();
				break;

			case 'approve' :
				$this->approve ();
				break;
		}
	}

	function setTitle($title) {
		$this->document->setTitle ( $title . ' - ' . $this->config->board_title );
	}

	public function hasCaptcha() {
		if (!empty($this->kunena_editmode)) return false;
		return ((!$this->me->exists() && $this->config->captcha) || ($this->me->exists() && !$this->me->isModerator() && $this->me->posts < $this->config->captcha_post_limit));
	}

	public function displayCaptcha() {
		if (! $this->hasCaptcha ())
			return;

		$captcha = KunenaSpamRecaptcha::getInstance();
		$html = $captcha->getHtml();
		if ( !$html ) {
			$this->_app->enqueueMessage ( $captcha->getError(), 'error' );
			$this->redirectBack ();
			return false;
		}
		echo $html;
		return true;
	}

	public function verifyCaptcha() {
		if (! $this->hasCaptcha ())
			return;

		$captcha = KunenaSpamRecaptcha::getInstance();
		$success = $captcha->checkAnswer ();
		if ( !$success ) {
			$this->_app->setUserState('com_kunena.postfields', array('catid'=>$this->catid, 'fields'=>$this->fields, 'options'=>$this->options));
			$this->_app->enqueueMessage ( $captcha->getError(), 'error' );
			$this->redirectBack ();
			return false;
		}
		return true;
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
	function redirectBack() {
		$httpReferer = JRequest::getVar ( 'HTTP_REFERER', JURI::base ( true ), 'server' );
		while (@ob_end_clean());
		$this->_app->redirect ( $httpReferer );
	}
}
