<?php
/**
 * @version $Id: access.php 4050 2010-12-21 17:59:50Z mahagr $
 * Kunena Component
 * @package Kunena
 *
 * @Copyright (C) 2008 - 2011 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 *
 **/
//
// Dont allow direct linking
defined( '_JEXEC' ) or die('');

class KunenaAccessJoomla15 extends KunenaAccess {
	function __construct() {
		if (KUNENA_JOOMLA_COMPAT != '1.5')
			return null;
		$this->priority = 25;
	}

	protected function loadAdmins() {
		$db = JFactory::getDBO ();
		$query = "SELECT u.id AS userid, 0 AS catid
			FROM #__users AS u
			WHERE u.block='0' AND u.usertype IN ('Administrator', 'Super Administrator')";
		$db->setQuery ( $query );
		$list = (array) $db->loadObjectList ();
		KunenaError::checkDatabaseError ();
		return parent::loadAdmins($list);
	}

	protected function loadModerators() {
		$db = JFactory::getDBO ();
		$query = "SELECT u.id AS userid, m.catid
				FROM #__users AS u
				INNER JOIN #__kunena_users AS ku ON u.id=ku.userid
				LEFT JOIN #__kunena_moderation AS m ON u.id=m.userid
				LEFT JOIN #__kunena_categories AS c ON m.catid=c.id
				WHERE u.block='0' AND ku.moderator='1' AND (m.catid IS NULL OR c.moderated='1')";
		$db->setQuery ( $query );
		$list = (array) $db->loadObjectList ();
		KunenaError::checkDatabaseError ();
		return parent::loadModerators($list);
	}

	protected function loadAllowedCategories($user) {
		$user = JFactory::getUser($user);

		// Workaround for missing aid
		$my = JFactory::getUser();
		if ($user->id == $my->id) {
			// Current user
			$user = $my;
		} elseif ($user->id) {
			// Other users
			$aid->aid = 1 ;
			$acl = JFactory::getACL();
			$grp = $acl->getAroGroup($user->id);
			if ($acl->is_group_child_of($grp->name, 'Registered') ||  $acl->is_group_child_of($grp->name, 'Public Backend')) {
				$user->aid = 2 ;
			}
		}

		// Get all Joomla user groups for current user
		$usergroups = $this->_get_user_groups($user->id);

		$categories = KunenaCategory::loadCategories();
		$catlist = array();
		foreach ( $categories as $category ) {
			// Check if user is a moderator
			if (self::isModerator($user->id, $category->id)) {
				$catlist[$category->id] = $category->id;
			}
			// Check against Joomla access level
			elseif ($category->accesstype == 'joomla.level') {
				// 0 = Public, 1 = Registered, 2 = Special
				if ( $category->access <= $user->get('aid') ) {
					$catlist[$category->id] = $category->id;
				}
			}
			// Check against Joomla user group
			elseif ($category->accesstype == 'none') {
				// pub_access: 0 = Public, -1 = All registered, 1 = Nobody, >1 = Group ID
				// admin_access: 0 = Nobody, >1 = Group ID
				if ($category->pub_access == 0
					|| ($user->id > 0 && $category->pub_access == - 1)
					|| ($category->pub_access > 1 && self::_has_rights ( $usergroups, $category->pub_access, $category->pub_recurse ))
					|| ($category->pub_access > 0 && $category->admin_access > 0 && $category->admin_access != $category->pub_access && self::_has_rights ( $usergroups, $category->admin_access, $category->admin_recurse ))
				) {
					$catlist[$category->id] = $category->id;
				}
			}
		}
		return $catlist;
	}

	protected function checkSubscribers($category, &$userids) {
		if (empty($userids)) {
			return;
		}

		$userlist = implode(',', $userids);

		$db = JFactory::getDBO ();
		$query = new KunenaDatabaseQuery();
		$query->select('u.id');
		$query->from('#__users AS u');
		$query->where("u.block=0");
		$query->where("u.id IN ({$userlist})");

		if ($category->accesstype == 'joomla.level') {
			// Check against Joomla access level
			if ( $category->access > 1 ) {
				// Special users: not in registered group
				$query->where("u.gid!=18");
			}
		} elseif ($category->accesstype == 'none') {
			// All users are allowed to see Public (0) or All Registered (-1) categories
			if ($category->pub_access <= 0) return;
			// Check against Joomla user groups
			$public = $this->_get_groups($category->pub_access, $category->pub_recurse);
			// Ignore admin_access if pub_access has the same group
			$admin = $category->admin_access != $category->pub_access ? $this->_get_groups($category->admin_access, $category->admin_recurse) : array();
			$groups = implode ( ',', array_unique ( array_merge ( $public, $admin ) ) );
			if ($groups) {
				$query->join('INNER', "#__core_acl_aro AS a ON u.id=a.value AND a.section_value='users'");
				$query->join('INNER', "#__core_acl_groups_aro_map AS g ON g.aro_id=a.id");
				$query->where("g.group_id IN ({$groups})");
			}
		} else {
			return;
		}

		$db->setQuery ($query);
		$userids = (array) $db->loadResultArray();
		KunenaError::checkDatabaseError();
	}

	protected function _has_rights($usergroups, $groupid, $recurse) {
		// Check the group itself
		if (in_array($groupid, $usergroups))
			return 1;
		// Check the children
		if ($usergroups && $recurse) {
			$childs = $this->_get_groups($groupid, $recurse);
			if (array_intersect($childs, $usergroups))
				return 1;
		}
		return 0;
	}

	protected function _get_groups($groupid, $recurse) {
		static $groups = false;

		// Public and All Registered: Allow all users
		if ($groupid <= 0) return array();
		// If no recursion is needed, just return the group ID
		if (!$recurse) return array($groupid);
		// Otherwise return the group and all its children
		if ($groups === false) {
			// Cache results
			$result = JFactory::getACL ()->_getBelow( '#__core_acl_aro_groups', 'g1.id, g2.id AS parent', null, null, null, null );
			$groups = array();
			foreach ($result as $group) {
				$groups[$group->parent][] = $group->id;
			}
		}
		return isset($groups[$groupid]) ? $groups[$groupid] : array();
	}

	protected function _get_user_groups($userid) {
		static $cache = array();

		$userid = intval($userid);
		if (!$userid) return array(29);

		if (!isset($cache[$userid])) {
			$db = JFactory::getDbo();

			$query = new KunenaDatabaseQuery();
			$query->select('g.id');
			$query->from('#__core_acl_aro AS o');
			$query->join('INNER', '#__core_acl_groups_aro_map AS gm ON gm.aro_id=o.id');
			$query->join('INNER', '#__core_acl_aro_groups AS g ON g.id = gm.group_id');
			$query->where("(o.section_value='users' AND o.value=". $db->quote($userid) .')');

			$db->setQuery($query);
			$cache[$userid] = $db->loadResultArray();
		}
		return $cache[$userid];
	}
}