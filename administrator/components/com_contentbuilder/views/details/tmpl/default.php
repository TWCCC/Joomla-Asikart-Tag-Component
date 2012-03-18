<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        http://www.crosstec.de
 * @license     GNU/GPL
*/
defined( '_JEXEC' ) or die( 'Restricted access' );

$edit_allowed = class_exists('cbFeMarker') ? contentbuilder::authorizeFe('edit') : contentbuilder::authorize('edit');
$delete_allowed = class_exists('cbFeMarker') ? contentbuilder::authorizeFe('delete') : contentbuilder::authorize('delete');
$view_allowed = class_exists('cbFeMarker') ? contentbuilder::authorizeFe('view') : contentbuilder::authorize('view');
JFactory::getDocument()->addScript(JURI::root(true).'/components/com_contentbuilder/assets/js/contentbuilder.js');
?>

<?php if($this->author) JFactory::getDocument()->setMetaData('author', $this->author);?>
<?php if($this->robots) JFactory::getDocument()->setMetaData('robots', $this->robots);?>
<?php if($this->rights) JFactory::getDocument()->setMetaData('rights', $this->rights);?>
<?php if($this->metakey) JFactory::getDocument()->setMetaData('keywords', $this->metakey);?>
<?php if($this->metadesc) JFactory::getDocument()->setMetaData('description', $this->metadesc);?>
<?php if($this->xreference) JFactory::getDocument()->setMetaData('xreference', $this->xreference);?>

<?php JFactory::getDocument()->addStyleDeclaration($this->theme_css);?>
<?php JFactory::getDocument()->addScriptDeclaration($this->theme_js);?>
<script type="text/javascript">
<!--
function contentbuilder_delete(){
    var confirmed = confirm('<?php echo JText::_('COM_CONTENTBUILDER_CONFIRM_DELETE_MESSAGE');?>');
    if(confirmed){
        location.href = '<?php echo 'index.php?option=com_contentbuilder&title='.JRequest::getVar('title', '').'&layout='.JRequest::getVar('layout', '').'&controller=edit&task=delete&view=edit&id='.JRequest::getInt('id', 0).'&cid[]='.JRequest::getCmd('record_id', 0).'&Itemid='.JRequest::getInt('Itemid',0).'&limitstart='.JRequest::getInt('limitstart',0).'&filter_order='.JRequest::getCmd('filter_order'); ?>';
    }
}
//-->
</script>
<?php
if($this->show_page_heading && $this->page_title){
?>
<h1 class="contentheading">
<?php echo $this->page_title; ?>
<span class="cbPrintBar" style="float: right; text-align: right;">
<?php
if($this->print_button):
?>
<a href="javascript:window.open('<?php echo JRoute::_('index.php?option=com_contentbuilder&title='.JRequest::getVar('title', '').'&layout='.JRequest::getVar('layout', '').'&controller=details&layout=print&tmpl=component&id='.JRequest::getInt('id', 0).'&record_id='.JRequest::getCmd('record_id', 0))?>','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');void(0);"><div class="cbPrintButton" style="background-image: url(<?php echo JURI::root(true); ?>/components/com_contentbuilder/images/printButton.png); background-repeat: no-repeat; width: 16px; height: 16px;" alt="Export"></div></a>
<?php
endif;
?>
</span>
</h1>
<?php
}
?>
<?php echo $this->event->afterDisplayTitle;?>

<?php
ob_start();
?>

<?php
if( ( JRequest::getInt('cb_show_details_back_button',1) && $this->show_back_button) || $delete_allowed || $edit_allowed ){
?>

<span class="cbToolBar" style="float: right; text-align: right;">
    
<?php
}
?>
    
<?php
    if ($edit_allowed) {
?>
<button class="button cbButton cbEditButton" onclick="location.href='<?php echo JRoute::_( 'index.php?option=com_contentbuilder&controller=edit&id='.JRequest::getInt('id', 0).'&record_id='.JRequest::getCmd('record_id', 0).'&Itemid='.JRequest::getInt('Itemid',0).'&layout='.JRequest::getVar('layout', '').'&limitstart='.JRequest::getInt('limitstart',0).'&filter_order='.JRequest::getCmd('filter_order') ); ?>'"><?php echo JText::_('COM_CONTENTBUILDER_EDIT')?></button>
<?php
    }
?>
<?php
    if ($delete_allowed) {
?>
<button class="button cbButton cbDeleteButton" onclick="contentbuilder_delete();"><?php echo JText::_('COM_CONTENTBUILDER_DELETE')?></button>
<?php
    }
?>
<?php if($this->show_back_button && JRequest::getBool('cb_show_details_back_button',1)): ?>
<button class="button cbButton cbBackButton" onclick="location.href='<?php echo JRoute::_( 'index.php?option=com_contentbuilder&title='.JRequest::getVar('title', '').'&controller=list&id='.JRequest::getInt('id', 0).'&layout='.JRequest::getVar('layout', '').'&limitstart='.JRequest::getInt('limitstart',0).'&filter_order='.JRequest::getCmd('filter_order').'&Itemid='.JRequest::getInt('Itemid',0) ); ?>'"><?php echo JText::_('COM_CONTENTBUILDER_BACK')?></button>
<?php endif; ?>

<?php
if( ( JRequest::getInt('cb_show_details_back_button',1) && $this->show_back_button) || $delete_allowed || $edit_allowed ){
?>

</span>

<?php
}
?>

<?php
$buttons = ob_get_contents();
ob_end_clean();

if( JRequest::getInt('cb_show_details_top_bar',1) ){
?>
<div style="clear:right;"></div>
<?php
    echo $buttons;
}
?>

<?php
if(JRequest::getInt('cb_show_author',1)){
?>

<?php if($this->created): ?>
<span class="small created-by"><?php echo JText::_('COM_CONTENTBUILDER_CREATED_ON');?> <?php echo JHTML::_('date', $this->created, JText::_('DATE_FORMAT_LC2')); ?></span>
<?php endif; ?>

<?php if($this->created_by): ?>
<span class="small created-by"><?php echo JText::_('COM_CONTENTBUILDER_BY');?> <?php echo $this->created_by; ?></span><br/>
<?php endif; ?>

<?php
}
?>

<?php
if( JRequest::getInt('cb_show_details_top_bar',1) && ( ( JRequest::getInt('cb_show_details_back_button',1) && $this->show_back_button ) || $delete_allowed || $edit_allowed ) ){
?>
<br/>
<br/>
<?php
}
?>

<?php echo $this->event->beforeDisplayContent; ?>
<?php echo $this->toc ?>
<?php echo $this->tpl ?>
<?php echo $this->event->afterDisplayContent; ?>


<?php
if(JRequest::getInt('cb_show_author',1)){
?>

<?php if($this->modified_by): ?>
<br/>

<?php if($this->modified): ?>
<span class="small created-by"><?php echo JText::_('COM_CONTENTBUILDER_LAST_UPDATED_ON');?> <?php echo JHTML::_('date', $this->modified, JText::_('DATE_FORMAT_LC2')); ?></span>
<?php endif; ?>

<span class="small created-by"><?php echo JText::_('COM_CONTENTBUILDER_BY');?> <?php echo $this->modified_by; ?></span>

<?php endif; ?>

<?php
}
?>

<br/>

<?php
if( JRequest::getInt('cb_show_details_bottom_bar', 1) ){
    echo $buttons;
?>
    <div style="clear:right;"></div>
<?php
}
?>