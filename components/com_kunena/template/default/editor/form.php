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

global $topic_emoticons;

require_once (KPATH_SITE . '/lib/kunena.poll.class.php');
$kunena_poll = CKunenaPolls::getInstance();
$kunena_poll->call_javascript_form();
include_once (KUNENA_PATH_LIB . '/kunena.bbcode.js.php');
include_once (KUNENA_PATH_LIB . '/kunena.special.js.php');
JHTML::_('behavior.formvalidation');
JHTML::_('behavior.tooltip');
//keep session alive while editing
JHTML::_('behavior.keepalive');

$document = JFactory::getDocument ();
if ($this->my->id) {
	$document->addScriptDeclaration('// <![CDATA[
		var kunena_anonymous_name = "'.JText::_('COM_KUNENA_USERNAME_ANONYMOUS').'";
	// ]]>');
 }
 $document->addScriptDeclaration('// <![CDATA[
 function kShowDetail(srcElement) {' . 'var targetID, srcElement, targetElement, imgElementID, imgElement;' . 'targetID = srcElement.id + "_details";' . 'imgElementID = srcElement.id + "_img";' . 'targetElement = document.getElementById(targetID);' . 'imgElement = document.getElementById(imgElementID);' . 'if (targetElement.style.display == "none") {' . 'targetElement.style.display = "";' . 'imgElement.src = "' . KUNENA_JLIVEURL . '/components/com_kunena/template/default/images/emoticons/w00t.png";' . '} else {' . 'targetElement.style.display = "none";' . 'imgElement.src = "' . KUNENA_JLIVEURL . '/components/com_kunena/template/default/images/emoticons/pinch.png";' . '}}
 // ]]>');
$this->setTitle ( $this->title );

$this->k=0;
?>
<?php CKunenaTools::loadTemplate ( '/pathway.php' )?>

<form class="postform form-validate" id="postform" action="<?php echo CKunenaLink::GetPostURL()?>"
	method="post" name="postform" enctype="multipart/form-data" onsubmit="return myValidate(this);">
	<input type="hidden" name="action" value="<?php echo $this->action ?>" />
	<?php if (!isset($this->selectcatlist)) : ?>
	<input type="hidden" name="catid" value="<?php echo intval($this->catid) ?>" />
	<?php endif; ?>
	<input type="hidden" name="id" value="<?php echo intval($this->id) ?>" />
	<?php if (! empty ( $this->kunena_editmode )) : ?>
	<input type="hidden" name="do" value="editpostnow" />
	<?php endif; ?>
	<?php echo JHTML::_( 'form.token' ); ?>

<div class="kblock">
	<div class="kheader">
		<h2><span><?php echo $this->escape($this->title)?></span></h2>
	</div>
	<div class="kcontainer">
		<div class="kbody">
<table class="kblocktable<?php echo isset ( $msg_cat->class_sfx ) ? ' kblocktable' . $this->escape($msg_cat->class_sfx) : ''?>" id="kpostmessage">
	<tbody id="kpost-message">
		<?php if (isset($this->selectcatlist)): ?>
		<tr id="kpost-category" class="krow<?php echo 1 + $this->k^=1 ?>">
			<td class="kcol-first"><strong><?php echo JText::_('COM_KUNENA_POST_IN_CATEGORY')?></strong></td>
			<td class="kcol-mid"><?php echo $this->selectcatlist?></td>
		</tr>
		<?php endif; ?>

		<?php if ($this->my->id) : ?>
		<tr class="krow<?php echo 1 + $this->k^=1 ?>" id="kanynomous-check" <?php if ((!$this->allow_anonymous && $this->catid != 0) || !$this->cat_default_allow ): ?>style="display:none;"<?php endif; ?>>
			<td class="kcol-first">
				<strong><?php echo JText::_('COM_KUNENA_POST_AS_ANONYMOUS'); ?></strong>
			</td>
			<td class="kcol-mid">
				<input type="checkbox" id="kanonymous" name="anonymous" value="1" <?php if ($this->anonymous) echo 'checked="checked"'; ?> />
				<label for="kanonymous"><?php echo JText::_('COM_KUNENA_POST_AS_ANONYMOUS_DESC'); ?></label>
			</td>
		</tr>
		<?php endif; ?>

		<tr class="krow<?php echo 1 + $this->k^=1 ?>" id="kanynomous-check-name"
		<?php if ( $this->my->id && !$this->config->changename && !$this->cat_default_allow ): ?>style="display:none;"<?php endif; ?>>
			<td class="kcol-first">
				<strong><?php echo JText::_('COM_KUNENA_GEN_NAME'); ?></strong>
			</td>
			<td class="kcol-mid">
				<input type="text" id="kauthorname" name="authorname" size="35" class="kinputbox postinput required" maxlength="35" value="<?php echo $this->escape($this->authorName);?>" <?php echo !$this->allow_name_change ? 'disabled="disabled" ' : ''; ?> />
			</td>
		</tr>

		<?php if ($this->config->askemail && !$this->my->id) : ?>
		<tr class = "krow<?php echo 1+ $this->k^=1 ?>">
			<td class = "kcol-first"><strong><?php echo JText::_('COM_KUNENA_GEN_EMAIL');?></strong></td>
			<td class="kcol-mid">
				<input type="text" id="email" name="email"  size="35" class="kinputbox postinput required validate-email" maxlength="35" value="<?php echo !empty($this->email) ? $this->escape($this->email) : '' ?>" />
				<br />
				<?php echo $this->config->showemail == '0' ? JText::_('COM_KUNENA_POST_EMAIL_NEVER') : JText::_('COM_KUNENA_POST_EMAIL_REGISTERED'); ?>
			</td>
		</tr>
		<?php endif; ?>

		<tr id="kpost-subject" class="krow<?php echo 1 + $this->k^=1 ?>">
			<td class="kcol-first">
				<strong><?php echo JText::_('COM_KUNENA_GEN_SUBJECT'); ?></strong>
			</td>

			<td class="kcol-mid"><input type="text" class="kinputbox postinput required" name="subject" id="subject" size="35"
				maxlength="<?php echo $this->escape($this->config->maxsubject); ?>" value="<?php echo $this->escape($this->resubject); ?>" tabindex="1" />
			</td>
		</tr>

		<?php if (($this->allow_topic_icons && $this->config->topicicons) || ($this->catid == 0 && $this->config->topicicons)) : ?>
		<tr id="kpost-topicicons" class="krow<?php echo 1 + $this->k^=1 ?>">
			<td class="kcol-first">
				<strong><?php echo JText::_('COM_KUNENA_GEN_TOPIC_ICON'); ?></strong>
			</td>

			<td class="kcol-mid">
				<?php foreach ($topic_emoticons as $emoid=>$emoimg): ?>
				<input type="radio" name="topic_emoticon" value="<?php echo intval($emoid); ?>" <?php echo $this->emoid == $emoid ? ' checked="checked" ':'' ?> />
				<img src="<?php echo $this->escape(JURI::Root() . CKunenaTools::getTemplateImage("icons/{$emoimg}"));?>" alt="" border="0" />
				<?php endforeach; ?>
			</td>
		</tr>
		<?php endif; ?>

		<?php
		// Show bbcode editor
		CKunenaTools::loadTemplate('/editor/bbcode.php');
		?>

		<?php
		if ($this->config->allowfileupload || ($this->config->allowfileregupload && $this->my->id != 0)
			|| ($this->config->allowimageupload || ($this->config->allowimageregupload && $this->my->id != 0)
			|| CKunenaTools::isModerator ( $this->my->id, $this->catid ))) :
			//$this->document->addScript ( KUNENA_DIRECTURL . 'js/plupload/gears_init.js' );
			//$this->document->addScript ( KUNENA_DIRECTURL . 'js/plupload/plupload.full.min.js' );
			?>
		<tr id="kpost-attachments" class="krow<?php echo 1 + $this->k^=1;?>">
			<td class="kcol-first">
				<strong><?php echo JText::_('COM_KUNENA_EDITOR_ATTACHMENTS') ?></strong>
			</td>
			<td class="kcol-mid">
				<div id="kattachment-id" class="kattachment">
					<span class="kattachment-id-container"></span>

					<input class="kfile-input-textbox" type="text" readonly="readonly" />
					<div class="kfile-hide hasTip" title="<?php echo JText::_('COM_KUNENA_FILE_EXTENSIONS_ALLOWED')?>::<?php echo $this->escape($this->config->imagetypes); ?>,<?php echo $this->escape($this->config->filetypes); ?>" >
						<input type="button" value="<?php echo  JText::_('COM_KUNENA_EDITOR_ADD_FILE'); ?>" class="kfile-input-button kbutton" />
						<input id="kupload" class="kfile-input hidden" name="kattachment" type="file" />
					</div>
					<a href="#" class="kattachment-remove kbutton" style="display: none"><?php echo  JText::_('COM_KUNENA_GEN_REMOVE_FILE'); ?></a>
					<a href="#" class="kattachment-insert kbutton" style="display: none"><?php echo  JText::_('COM_KUNENA_EDITOR_INSERT'); ?></a>
				</div>

				<?php
				// Include attachments template if we have any
				if ( isset ( $this->attachments ) ) CKunenaTools::loadTemplate('/editor/attachments.php')
				?>
			</td>
		</tr>
		<?php endif; ?>

		<?php if (!empty($this->cansubscribe)) : ?>
		<tr id="kpost-subscribe" class="krow<?php echo 1 + $this->k^=1;?>">
			<td class="kcol-first">
				<strong><?php echo JText::_('COM_KUNENA_POST_SUBSCRIBE'); ?></strong>
			</td>
			<td class="kcol-mid">
				<?php if ($this->subscriptionschecked == 1) : ?>
				<input type="checkbox" name="subscribeMe" value="1" checked="checked" />
				<i><?php echo JText::_('COM_KUNENA_POST_NOTIFIED'); ?></i>
				<?php else : ?>
				<input type="checkbox" name="subscribeMe" value="1" />
				<i><?php echo JText::_('COM_KUNENA_POST_NOTIFIED'); ?></i>
				<?php endif; ?>
			</td>
		</tr>
		<?php endif; ?>
		<?php
		//Begin captcha
		if ($this->hasCaptcha()) : ?>
		<tr id="kpost-captcha" class="krow<?php echo 1 + $this->k^=1;?>">
			<td class="kcol-first">
				<strong><?php echo JText::_('COM_KUNENA_CAPDESC'); ?></strong>
			</td>
			<td class="kcol-mid">
				<?php $this->displayCaptcha() ?>
			</td>
		</tr>
		<?php endif;
		// Finish captcha
		?>
		<tr id="kpost-buttons" class="krow1">
			<td id="kpost-buttons" colspan="2">
				<input type="submit" name="ksubmit" class="kbutton"
				value="<?php echo (' ' . JText::_('COM_KUNENA_GEN_CONTINUE') . ' ');?>"
				title="<?php echo (JText::_('COM_KUNENA_EDITOR_HELPLINE_SUBMIT'));?>" tabindex="3" />
				<input type="button" name="cancel" class="kbutton"
				value="<?php echo (' ' . JText::_('COM_KUNENA_GEN_CANCEL') . ' ');?>"
				onclick="javascript:window.history.back();"
				title="<?php echo (JText::_('COM_KUNENA_EDITOR_HELPLINE_CANCEL'));?>" tabindex="4" />
			</td>
		</tr>
	</tbody>
</table>
</div>
</div>
</div>
<?php
if (!$this->authorName) {
	echo '<script type="text/javascript">document.postform.authorname.focus();</script>';
} else if (!$this->resubject) {
	echo '<script type="text/javascript">document.postform.subject.focus();</script>';
} else {
	echo '<script type="text/javascript">document.postform.message.focus();</script>';
}
?>
</form><?php if ($this->hasThreadHistory ()) : ?>
<?php $this->displayThreadHistory (); ?>
<?php endif; ?>