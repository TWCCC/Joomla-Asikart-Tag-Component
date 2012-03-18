<?php
/**
 * @version		$Id$
 * @package		Kunena
 * @subpackage	com_kunena
 * @copyright	Copyright (C) 2008 - 2009 Kunena Team. All rights reserved..
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 * @link		http://www.kunena.org
 */
defined('_JEXEC') or die;
$this->document->addStyleSheet ( JURI::base().'components/com_kunena/install/media/install.css' );
if ($this->go == 'next') {
	if (KUNENA_JOOMLA_COMPAT == '1.5') {
		JHtml::_('behavior.mootools');
	} else {
		JHtml::_('behavior.framework', true);
	}
	$this->document = JFactory::getDocument();
	$this->document->addScriptDeclaration(" // <![CDATA[
window.addEvent('domready', function() {window.location='".JRoute::_("index.php?option=com_kunena&view=install&task=run&n={$this->cnt}&".JUtility::getToken() .'=1', false)."';});
// ]]>");
}
?>
<div id="stepbar">
	<div class="u">
		<div class="u">
			<div class="u"></div>
		</div>
	</div>
	<div class="n">
			<h1><?php echo JText::_('COM_KUNENA_INSTALL_STEPS') ?></h1>
			<?php $this->showSteps(); ?>
	</div>
	<div class="c">
		<div class="c">
			<div class="c"></div>
		</div>
	</div>
</div>

<div id="right">
	<div id="rightpad">
		<div id="step">
			<div class="u">
				<div class="u">
					<div class="u"></div>
				</div>
			</div>
			<div class="n">
<?php if ($this->step) :?>
				<div class="far-right">

							<div class="button1-left"><div class="next"><a onclick="<?php echo $this->getActionURL(); ?>"><?php echo $this->getAction(); ?></a></div></div>

				</div>
<?php endif; ?>
				<span class="step"><?php echo $this->steps[$this->step]['menu']; ?></span>

			</div>
			<div class="c">
				<div class="c">
					<div class="c"></div>
				</div>
			</div>
		</div>

		<div id="installer">
			<div class="u">
				<div class="u">
					<div class="u"></div>
				</div>
			</div>
			<div class="n">
<?php
if (!empty($this->requirements->fail)):
	echo $this->loadTemplate('reqfail');
elseif (!$this->step):
	echo $this->loadTemplate('start');
else:
	echo $this->loadTemplate('install');
endif;
?>
			</div>
			<div class="c">
				<div class="c">
					<div class="c"></div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="clr"></div>
