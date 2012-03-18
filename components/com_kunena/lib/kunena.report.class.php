<?php
/**
 * @version $Id $
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

class CKunenaReport {
	protected $_db;
	public $my;
	public $config;
	public $app;

	function __construct() {
		$this->do = JRequest::getCmd ( 'do', '' );
		$this->reason = strval ( JRequest::getVar ( 'reason' ) );
		$this->text = strval ( JRequest::getVar ( 'text' ) );
		$this->id = JRequest::getInt ( 'id', 0 );
		$this->catid = JRequest::getInt ( 'catid', 0 );
		$this->_db = JFactory::getDBO ();
		$this->my = JFactory::getUser ();
		$this->config = KunenaFactory::getConfig ();
		$this->app = JFactory::getApplication ();
	}

	public function reportAllowed() {
		jimport ( 'joomla.mail.helper' );

		if ( !$this->id ) {
			JError::raiseError ( 404, JText::_ ( 'COM_KUNENA_UNAVAILABLE') );
			return false;
		}

		if ($this->config->reportmsg == 0) {
			while (@ob_end_clean());
			$this->app->redirect ( CKunenaLink::GetThreadPageURL ( 'view', $this->catid, $this->id, NULL, NULL, $this->id, false ) );
			return false;
		}

		if (! $this->config->email || ! JMailHelper::isEmailAddress ( $this->config->email )) {
			$this->app->enqueueMessage ( JText::_ ( 'COM_KUNENA_EMAIL_INVALID' ), 'error' );
			while (@ob_end_clean());
			$this->app->redirect ( CKunenaLink::GetThreadPageURL ( 'view', $this->catid, $this->id, NULL, NULL, $this->id, false ) );
			return false;
		}

		if ($this->my->id == 0) {
			$this->app->enqueueMessage ( JText::_ ( 'COM_KUNENA_FORUM_UNAUTHORIZIED2' ), 'error' );
			while (@ob_end_clean());
			$this->app->redirect ( CKunenaLink::GetThreadPageURL ( 'view', $this->catid, $this->id, NULL, NULL, $this->id, false ) );
			return false;
		}

		return true;
	}

	protected function _getSenderName() {
		$name = $this->config->username ? "username" : "name";
		$this->_db->setQuery ( "SELECT {$name} FROM #__users WHERE id={$this->_db->Quote($this->my->id)}" );
		$sender = $this->_db->loadResult ();
		KunenaError::checkDatabaseError ();

		return $sender;
	}

	protected function _sendPrepareReport($type) {
		if (! $this->reportAllowed ())
			return false;

		if (! JRequest::checkToken ()) {
			while (@ob_end_clean());
			$this->app->redirect ( CKunenaLink::GetThreadPageURL ( 'view', $this->catid, $this->id, NULL, NULL, $this->id, false ), COM_KUNENA_ERROR_TOKEN, 'error' );
			return false;
		}

		if (! empty ( $this->reason ) && ! empty ( $this->text )) {
			if ($this->id) {
				$query = "SELECT a.id, a.thread, a.name AS nickname, a.catid, a.userid, a.subject, b.mesid, b.message AS msg_text, c.username, c.name, c.id AS userid
							FROM #__kunena_messages AS a
							INNER JOIN #__kunena_messages_text AS b ON b.mesid = a.id
							LEFT JOIN #__users AS c ON c.id = a.userid
							WHERE a.id={$this->_db->Quote($this->id)}";
				$this->_db->setQuery ( $query );
				$row = $this->_db->loadObject ();
				if (KunenaError::checkDatabaseError ())
					return;

				if ($row->userid && $row->username)
					$baduser = $this->config->username ? $row->username : $row->name;
				else
					$baduser = $row->nickname;

				if ($this->reason) {
					$subject = "[" . $this->config->board_title . " " . JText::_ ( 'COM_KUNENA_GEN_FORUM' ) . "] " . JText::_ ( 'COM_KUNENA_REPORT_MSG' ) . ": " . $this->reason;
				} else {
					$subject = "[" . $this->config->board_title . " " . JText::_ ( 'COM_KUNENA_GEN_FORUM' ) . "] " . JText::_ ( 'COM_KUNENA_REPORT_MSG' ) . ": " . $row->subject;
				}

				jimport ( 'joomla.environment.uri' );
				$uri = & JURI::getInstance ( JURI::base () );
				$msglink = $uri->toString ( array ('scheme', 'host', 'port' ) ) . str_replace ( '&amp;', '&', CKunenaLink::GetThreadPageURL ( 'view', $row->catid, $row->id, NULL, NULL, $row->id ) );

				$message = "" . JText::_ ( 'COM_KUNENA_REPORT_RSENDER' ) . " " . $this->_getSenderName ();
				$message .= "\n";
				$message .= "" . JText::_ ( 'COM_KUNENA_REPORT_RREASON' ) . " " . $this->reason;
				$message .= "\n";
				$message .= "" . JText::_ ( 'COM_KUNENA_REPORT_RMESSAGE' ) . " " . $this->text;
				$message .= "\n\n";
				$message .= "" . JText::_ ( 'COM_KUNENA_REPORT_POST_POSTER' ) . " " . $baduser;
				$message .= "\n";
				$message .= "" . JText::_ ( 'COM_KUNENA_REPORT_POST_SUBJECT' ) . " " . $row->subject;
				$message .= "\n";
				$message .= "" . JText::_ ( 'COM_KUNENA_REPORT_POST_MESSAGE' ) . "\n-----\n" . $row->msg_text;
				$message .= "\n-----\n\n";
				$message .= "" . JText::_ ( 'COM_KUNENA_REPORT_POST_LINK' ) . " " . $msglink;
				$message .= "\n\n\n\n** Powered by Kunena! - http://www.kunena.org **";
				$message = strtr ( $message, array ('&#32;' => '' ) );

				$emailToList = CKunenaTools::getEMailToList ( $row->catid, $row->thread, false, true, true, $this->my->id );

				if ($type == '0') {
					// send by mail
					$this->_sendReportToMail ( $message, $subject, $emailToList );
				} else if ($type == '1') {
					// send by PM
				// Function not yet implemented
				} else {
					// send by PM and mail
				// Function not yet implemented
				}
			}

		} else {
			// Do nothing empty subject or reason is empty
			$this->app->enqueueMessage ( JText::_ ( 'COM_KUNENA_REPORT_FORG0T_SUB_MES' ) );
			while (@ob_end_clean());
			$this->app->redirect ( CKunenaLink::GetReportURL () );
		}
	}

	protected function _sendReportToMail($message, $subject, $emailToList) {
		jimport ( 'joomla.mail.helper' );

		$sender = JMailHelper::cleanAddress ( $this->config->board_title . ' ' . JText::_ ( 'COM_KUNENA_GEN_FORUM' ) . ': ' . $this->_getSenderName () );
		$subject = JMailHelper::cleanSubject ( $subject );
		$message = JMailHelper::cleanBody ( $message );

		foreach ( $emailToList as $emailTo ) {
			if (! $emailTo->email || ! JMailHelper::isEmailAddress ( $emailTo->email ))
				continue;
			JUtility::sendMail ( $this->config->email, $sender, $emailTo->email, $subject, $message );
		}

		$this->app->enqueueMessage ( JText::_ ( 'COM_KUNENA_REPORT_SUCCESS' ) );
		while (@ob_end_clean());
		$this->app->redirect ( CKunenaLink::GetThreadPageURL ( 'view', $this->catid, $this->id, NULL, NULL, $this->id, false ) );
	}

	public function showForm() {
		if (! $this->reportAllowed ())
			return false;
		CKunenaTools::loadTemplate ( '/plugin/report/report.php' );
	}

	public function display() {
		switch ($this->do) {
			case 'sendreport' :
				$this->_sendPrepareReport ( '0' );
				break;
			default :
				$this->showForm ();
				break;
		}
	}
}

?>