<?php
/**
 * @version $Id$
 * Kunena System Plugin
 * @package Kunena
 *
 * @Copyright (C) 2008 - 2011 www.kunena.org All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();

class plgSystemKunena extends JPlugin {

	function __construct(& $subject, $config) {
		// Check if Kunena API exists
		$api = JPATH_ADMINISTRATOR . '/components/com_kunena/api.php';
		if (! is_file ( $api ))
			return false;

		jimport ( 'joomla.application.component.helper' );
		// Check if Kunena component is installed/enabled
		if (! JComponentHelper::isEnabled ( 'com_kunena', true )) {
			return false;
		}

		// Load Kunena API
		require_once ($api);

		if (version_compare(JVERSION, '1.6','<')) {
			// Joomla 1.5: Fix bug
			$lang = JFactory::getLanguage();
			if (JFactory::getApplication()->isAdmin()) {
				$lang->load('com_kunena.menu', JPATH_ADMINISTRATOR) || $lang->load('com_kunena.menu', KPATH_ADMIN);
			}
		}

		parent::__construct ( $subject, $config );
	}

	// Joomla 1.5 support
	public function onAfterStoreUser($user, $isnew, $success, $msg) {
		if (version_compare(JVERSION, '1.6', '>')) return;
		return $this->onUserAfterSave($user, $isnew, $success, $msg);
	}
	// Joomla 1.6+ support
	public function onUserAfterSave($user, $isnew, $success, $msg) {
		//Don't continue if the user wasn't stored succesfully
		if (! $success) {
			return;
		}
		if ($isnew && intval($user ['id'])) {
			$user = KunenaFactory::getUser(intval($user ['id']));
			$user->save();
		}
	}
}
