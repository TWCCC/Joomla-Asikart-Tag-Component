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
 **/

// Dont allow direct linking
defined ( '_JEXEC' ) or die ();

class CKunenaUserlist {
	public $allow = false;

	function __construct() {
		$this->app = JFactory::getApplication ();
		$this->config = KunenaFactory::getConfig ();
		$this->db = JFactory::getDBO ();
		$this->my = JFactory::getUser ();
		$this->me = KunenaFactory::getUser ();

		$this->search = JRequest::getVar ( 'search', '' );
		$this->limitstart = JRequest::getInt ( 'limitstart', 0 );
		$this->limit = $querylimit = JRequest::getInt ( 'limit', (int)$this->config->userlist_rows );

		jimport ( 'joomla.html.pagination' );

		if( !$this->isAllowed() ) return false;

		$filter_order = $this->app->getUserStateFromRequest ( 'kunena.userlist.filter_order', 'filter_order', 'registerDate', 'cmd' );
		$filter_order_dir = $this->app->getUserStateFromRequest ( 'kunena.userlist.filter_order_dir', 'filter_order_Dir', 'asc', 'word' );
		$direction = ($filter_order_dir == 'asc' ? 'ASC' : 'DESC');
		$orderby = " ORDER BY {$this->db->nameQuote($filter_order)} {$direction}";

		if ( empty($this->search) ) {
			if ($this->config->userlist_count_users == '0' ) $where = '1';
			elseif ($this->config->userlist_count_users == '1' ) $where = '(block=0 OR activation="")';
			elseif ($this->config->userlist_count_users == '2' ) $where = '(block=0 AND activation="")';
			elseif ($this->config->userlist_count_users == '3' ) $where = 'block=0';
		} else {
			$where = '1';
		}


		// Total
		$this->db->setQuery ( "SELECT COUNT(*) FROM #__users WHERE {$where}" );
		$this->total = $this->db->loadResult ();
		KunenaError::checkDatabaseError();

		// Search total
		$query = "SELECT COUNT(*) FROM #__users AS u INNER JOIN #__kunena_users AS fu ON u.id=fu.userid WHERE {$where}";
		if ($this->search != "") {
			if (!JRequest::checkToken()) {
				$this->app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
				while (@ob_end_clean());
				$this->app->redirect ( CKunenaLink::GetUserlistURL() );
				return false;
			}
			$query .= " AND (u.name LIKE '%{$this->db->getEscaped($this->search)}%' OR u.username LIKE '%{$this->db->getEscaped($this->search)}%')";
		}

		$this->db->setQuery ( $query );
		$total = $this->db->loadResult ();
		KunenaError::checkDatabaseError();
		if ($this->limit > $total) {
			$this->limitstart = 0;
		}

		// this is need to show something when the user choose all, but we need to limit even the 'all' with a number
		if ( $this->limit == 0 || $this->limit > 150) $querylimit = '150';

		$useridAdmin = KUNENA_JOOMLA_COMPAT == '1.5' ? '62' : '42';

		// Select query
		$moderator = intval($this->me->isModerator());
		$query = "SELECT u.id, u.name, u.username, u.usertype, IF(fu.hideEmail=0 OR {$moderator},u.email,'') AS email, u.registerDate, u.lastvisitDate, fu.userid, fu.showOnline, fu.group_id, fu.posts, fu.karma, fu.uhits " . " FROM #__users AS u INNER JOIN #__kunena_users AS fu ON fu.userid = u.id WHERE {$where}";
		$this->searchuri = "";
		if ($this->search != "") {
			 $query .= " AND (name LIKE '%{$this->db->getEscaped($this->search)}%' OR username LIKE '%{$this->db->getEscaped($this->search)}%') AND u.id NOT IN ($useridAdmin)";
			$this->searchuri .= "&search=" . $this->search;
		} else {
			$query .= " AND u.id NOT IN ($useridAdmin)";
		}
		$query .= $orderby;
		$query .= " LIMIT $this->limitstart, $querylimit";

		$this->db->setQuery ( $query );
		$this->users = $this->db->loadObjectList ();
		KunenaError::checkDatabaseError();

		$userlist = array();
		foreach ($this->users as $user) {
			$userlist[intval($user->userid)] = intval($user->userid);
		}
		// Prefetch all users/avatars to avoid user by user queries during template iterations
		KunenaUser::loadUsers($userlist);

		// table ordering
		$this->order_dir = $filter_order_dir;
		$this->order = $filter_order;

		if ($this->search == "") {
			$this->search = JText::_('COM_KUNENA_USRL_SEARCH');
		}
		$this->pageNav = new JPagination ( $total, $this->limitstart, $this->limit );

		$this->allow = true;

		$template = KunenaFactory::getTemplate();
		$this->params = $template->params;
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

	function displayWhoIsOnline() {
		if ($this->config->showwhoisonline > 0) {
			require_once (KUNENA_PATH_LIB . '/kunena.who.class.php');
			$online =& CKunenaWhoIsOnline::getInstance();
			$online->displayWhoIsOnline();
		}
	}

	function displayForumJump() {
		if ($this->config->enableforumjump) {
			CKunenaTools::loadTemplate('/forumjump.php');
		}
	}

	function isAllowed() {
		if ($this->config->userlist_allowed == 1 && $this->my->id == 0 ) {
			$this->app->enqueueMessage ( JText::_ ( 'COM_KUNENA_USERLIST_NOT_ALLOWED' ), 'error' );
			return false;
		}
		return true;
	}

	function getLastvisitdate($date) {
		return $date;
	}

	function display() {
		if (! $this->allow) {
			echo JText::_('COM_KUNENA_NO_ACCESS');
			return;
		}
		CKunenaTools::loadTemplate('/userlist/userlist.php');
	}
}