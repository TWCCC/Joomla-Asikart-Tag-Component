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

// Fixes a bug in Joomla 2.5.0 Beta1:
if (!JFactory::getApplication()->isAdmin()) return;

require_once( JApplicationHelper::getPath( 'toolbar_html' ) );

$task = JRequest::getCmd( 'task' );

switch ($task)
{
    case "new":
    case "edit":
    case "edit2":
        CKunenaToolbar::_EDIT();

        break;

    case "cancel":
        CKunenaToolbar::DEFAULT_MENU();

        break;

    case "showconfig":

        CKunenaToolbar::_EDIT_CONFIG();

        break;

    case "showCss":
        CKunenaToolbar::CSS_MENU();

        break;

    case "profiles":
        CKunenaToolbar::_PROFILE_MENU();

        break;

    case "instructions": break;

    case "newmoderator":
        CKunenaToolbar::_NEWMOD_MENU();

        break;

    case "userprofile":
        CKunenaToolbar::_EDITUSER_MENU();

        break;

    case "moveusermessages":
        CKunenaToolbar::_MOVEUSERMESSAGES_MENU();

        break;

    case "pruneforum":
        CKunenaToolbar::_PRUNEFORUM_MENU();

        break;

    case "syncusers":
        CKunenaToolbar::_SYNCUSERS_MENU();

        break;

    case "showAdministration":
        CKunenaToolbar::_ADMIN();

        break;

    case "showprofiles":
        CKunenaToolbar::_PROFILE_MENU();

        break;

	case "showsmilies":
        CKunenaToolbar::_SHOWSMILEY_MENU();

        break;

    case "editsmiley":
        CKunenaToolbar::_EDITSMILEY_MENU();

        break;

    case "newsmiley":
        CKunenaToolbar::_NEWSMILEY_MENU();

        break;

	case "ranks":
        CKunenaToolbar::_SHOWRANKS_MENU();

        break;

    case "editRank":
        CKunenaToolbar::_EDITRANK_MENU();

        break;

    case "newRank":
        CKunenaToolbar::_NEWRANK_MENU();

        break;

    case "showtrashview":
        CKunenaToolbar::_TRASHVIEW_MENU();

        break;

    case "trashpurge":
        CKunenaToolbar::_TRASHVIEW_PURGE();

        break;

	case "showsystemreport":
        CKunenaToolbar::_SYSTEMREPORT_MENU();

        break;

	case "showTemplates":
        CKunenaToolbar::_SHOWTEMPLATES_MENU();

        break;

	case "editKTemplate":
        CKunenaToolbar::_EDITKTEMPLATE_MENU();

        break;

	case "chooseCSSTemplate":
        CKunenaToolbar::_CHOOSECSS_MENU();

        break;

	case "editTemplateCSS":
        CKunenaToolbar::_EDITCSS_MENU();

        break;

	case "cpanel":
	case "":
        CKunenaToolbar::_CPANEL_MENU();

        break;

    default:

        CKunenaToolbar::BACKONLY_MENU();

        break;

}

?>