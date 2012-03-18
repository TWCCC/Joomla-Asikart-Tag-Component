<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        http://www.crosstec.de
 * @license     GNU/GPL
*/

$new_allowed = class_exists('cbFeMarker') ? contentbuilder::authorizeFe('new') : contentbuilder::authorize('new');
$edit_allowed = class_exists('cbFeMarker') ? contentbuilder::authorizeFe('edit') : contentbuilder::authorize('edit');
$delete_allowed = class_exists('cbFeMarker') ? contentbuilder::authorizeFe('delete') : contentbuilder::authorize('delete');
$view_allowed = class_exists('cbFeMarker') ? contentbuilder::authorizeFe('view') : contentbuilder::authorize('view');
$fullarticle_allowed = class_exists('cbFeMarker') ? contentbuilder::authorizeFe('fullarticle') : contentbuilder::authorize('fullarticle');
?>
<?php JFactory::getDocument()->addStyleDeclaration($this->theme_css);?>
<?php JFactory::getDocument()->addScriptDeclaration($this->theme_js);?>
<a name="article_up"></a>
<script type="text/javascript">
<!--
function contentbuilder_delete(){
    var confirmed = confirm('<?php echo JText::_('COM_CONTENTBUILDER_CONFIRM_DELETE_MESSAGE');?>');
    if(confirmed){
        location.href = '<?php echo 'index.php?option=com_contentbuilder&controller=edit&task=delete&layout='.JRequest::getVar('layout', '').'&view=edit&id='.JRequest::getInt('id', 0).'&cid[]='.JRequest::getCmd('record_id', 0).'&Itemid='.JRequest::getInt('Itemid',0).'&limitstart='.JRequest::getInt('limitstart',0).'&filter_order='.JRequest::getCmd('filter_order'); ?>';
    }
}
//-->
</script>
<div class="cbEditableWrapper" id="cbEditableWrapper<?php echo $this->id; ?>">
<?php
if($this->show_page_heading&& $this->page_title){
?>
<h1 class="contentheading">
<?php echo $this->page_title; ?>
</h1>
<?php
}
?>
<?php echo  $this->event->afterDisplayTitle;?>
<?php
ob_start();
?>
<span class="cbToolBar" style="float: right; text-align: right;">
<?php
if( $this->record_id && $edit_allowed && $this->create_articles && $fullarticle_allowed){
?>
<button class="button cbButton cbArticleSettingsButton" onclick="if(document.getElementById('cbArticleOptions').style.display == 'none'){document.getElementById('cbArticleOptions').style.display='block'}else{document.getElementById('cbArticleOptions').style.display='none'};"><?php echo JText::_('COM_CONTENTBUILDER_SHOW_ARTICLE_SETTINGS')?></button>
<?php
}
if ( ($edit_allowed || $new_allowed) && !$this->edit_by_type) {
    if(JRequest::getVar('cb_controller') != 'edit' && !JRequest::getVar('return','') && !$this->latest){
?>
<button class="button cbButton cbApplyButton" onclick="document.getElementById('contentbuilder_task').value='apply';contentbuilder.onSubmit();"><?php echo trim($this->apply_button_title) != '' ? htmlentities($this->apply_button_title, ENT_QUOTES, 'UTF-8') : JText::_('COM_CONTENTBUILDER_APPLY')?></button>
<?php
    }
?>
<button class="button cbButton cbSaveButton" onclick="<?php echo $this->latest ? "document.getElementById('contentbuilder_task').value='apply';" : ''?>contentbuilder.onSubmit();"><?php echo trim($this->save_button_title) != '' ? htmlentities($this->save_button_title, ENT_QUOTES, 'UTF-8') : JText::_('COM_CONTENTBUILDER_SAVE')?></button>
<?php
}else if( $this->record_id && $edit_allowed && $this->create_articles && $this->edit_by_type && $fullarticle_allowed){
?>
<button class="button cbButton cbArticleSettingsButton" onclick="document.getElementById('contentbuilder_task').value='apply';contentbuilder.onSubmit();"><?php echo JText::_('COM_CONTENTBUILDER_APPLY_ARTICLE_SETTINGS')?></button>
<?php
}
if ($this->record_id && $delete_allowed) {
?> 
<button class="button cbButton cbDeleteButton" onclick="contentbuilder_delete();"><?php echo JText::_('COM_CONTENTBUILDER_DELETE')?></button>
<?php
}
if(!JRequest::getInt('backtolist',0) && !JRequest::getVar('return','')){
    if(!JRequest::getInt('jsback',0)){
        if($this->back_button){
?>
<button class="button cbButton cbBackButton" onclick="location.href='<?php echo JRoute::_( 'index.php?option=com_contentbuilder&controller=details&layout='.JRequest::getVar('layout', '').'&id='.JRequest::getInt('id', 0).'&record_id='.JRequest::getCmd('record_id', 0).'&Itemid='.JRequest::getInt('Itemid',0).'&limitstart='.JRequest::getInt('limitstart',0).'&filter_order='.JRequest::getCmd('filter_order') ); ?>'"><?php echo JText::_('COM_CONTENTBUILDER_BACK')?></button>
<?php
        }
    }else{
?>
<button class="button cbButton cbBackButton" onclick="history.back(-1);void(0);"><?php echo JText::_('COM_CONTENTBUILDER_BACK')?></button>
<?php       
    }
}else{
    if($this->back_button && !JRequest::getVar('return','')){
?>
<button class="button cbButton cbBackButton" onclick="location.href='<?php echo JRoute::_( 'index.php?option=com_contentbuilder&controller=list&layout='.JRequest::getVar('layout', '').'&id='.JRequest::getInt('id', 0).'&limitstart='.JRequest::getInt('limitstart',0).'&filter_order='.JRequest::getCmd('filter_order').'&Itemid='.JRequest::getInt('Itemid',0) ); ?>'"><?php echo JText::_('COM_CONTENTBUILDER_BACK')?></button>
<?php
    }
}
?>
</span>
<?php
$buttons = ob_get_contents();
ob_end_clean();

if( JRequest::getInt('cb_show_top_bar',1) ){
    ?>
    <div style="clear:right;"></div>
    <?php
    echo $buttons;
}

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
if( JRequest::getInt('cb_show_top_bar',1) ){
?>
<br/>
<br/>
<?php
}
if(!$this->created_by && !$this->created && !$this->modified_by && !$this->modified && !$this->edit_by_type){
?>
<br/>
<br/>
<?php
}
?>

<?php
if($this->create_articles && $fullarticle_allowed){
    JHTML::_('behavior.tooltip');
?>
<?php
if(!$this->edit_by_type){
?>
<form name="adminForm" id="adminform" onsubmit="return false;" action="<?php echo JRoute::_( 'index.php?option=com_contentbuilder&controller=edit&layout='.JRequest::getVar('layout', '').'&id='.JRequest::getInt('id', 0).'&record_id='.JRequest::getCmd('record_id',  '').'&Itemid='.JRequest::getInt('Itemid',0).'&limitstart='.JRequest::getInt('limitstart',0).'&filter_order='.JRequest::getCmd('filter_order') ); ?>" method="post" enctype="multipart/form-data">
<?php
}
?>
            <?php
            if($this->edit_by_type){
            ?>
            <form name="adminForm" id="adminform" onsubmit="return false;" action="<?php echo JRoute::_( 'index.php?option=com_contentbuilder&controller=edit&layout='.JRequest::getVar('layout', '').'&id='.JRequest::getInt('id', 0).'&record_id='.JRequest::getCmd('record_id',  '').'&Itemid='.JRequest::getInt('Itemid',0).'&limitstart='.JRequest::getInt('limitstart',0).'&filter_order='.JRequest::getCmd('filter_order') ); ?>" method="post" enctype="multipart/form-data">
            <?php
            }
            ?>
<?php
    if(!$this->is15){
        if($this->frontend){
?>
<!-- Joomla 1.6 -->

<?php   } ?>

<div id="cbArticleOptions" style="display:none;">

<?php echo JHtml::_('sliders.start','content-sliders-'.$this->id, array('useCookie'=>1)); ?>

<?php echo JHtml::_('sliders.panel',empty($this->id) ? JText::_('COM_CONTENT_NEW_ARTICLE') : JText::sprintf('COM_CONTENT_EDIT_ARTICLE', $this->id), 'article-details'); ?>

<fieldset class="adminform">
    <ul class="adminformlist">
        <li><?php echo $this->article_options->getLabel('alias'); ?>
            <?php echo $this->article_options->getInput('alias'); ?></li>

        <li><?php echo $this->article_options->getLabel('catid'); ?>
            <?php echo $this->article_options->getInput('catid'); ?></li>

        <!--<li><?php echo $this->article_options->getLabel('state'); ?>
	<?php echo $this->article_options->getInput('state'); ?></li>-->
        
        <li><?php echo $this->article_options->getLabel('access'); ?>
            <?php echo $this->article_options->getInput('access'); ?></li>

        <li><?php echo $this->article_options->getLabel('featured'); ?>
            <?php echo $this->article_options->getInput('featured'); ?></li>

        <li><?php echo $this->article_options->getLabel('language'); ?>
            <?php echo $this->article_options->getInput('language'); ?></li>
        <?php
        if(!$this->limited_options){
        ?>
        <li><?php echo $this->article_options->getLabel('id'); ?>
            <?php echo $this->article_options->getInput('id'); ?></li>
        <?php
        }
        ?>
    </ul>
    <div class="clr"></div>
</fieldset>

<?php echo JHtml::_('sliders.panel',JText::_('COM_CONTENT_FIELDSET_PUBLISHING'), 'publishing-details'); ?>
<fieldset class="panelform">
    <ul class="adminformlist">
        
        <?php
        if(!$this->limited_options && JFactory::getApplication()->isAdmin()){
        ?>
        <li><?php echo $this->article_options->getLabel('created_by'); ?>
            <?php echo $this->article_options->getInput('created_by'); ?></li>
          
        <?php
        }
        ?>
        <li><?php echo $this->article_options->getLabel('created_by_alias'); ?>
            <?php echo $this->article_options->getInput('created_by_alias'); ?></li>
        
        <?php
        if(!$this->limited_options){
        ?>
        <li><?php echo $this->article_options->getLabel('created'); ?>
            <?php echo $this->article_options->getInput('created'); ?></li>
        <?php
        }
        ?>
        
        <li><?php echo $this->article_options->getLabel('publish_up'); ?>
            <?php echo $this->article_options->getInput('publish_up'); ?></li>
                                            
        <li><?php echo $this->article_options->getLabel('publish_down'); ?>
            <?php echo $this->article_options->getInput('publish_down'); ?></li>
        <?php
        if(!$this->limited_options){
        ?>                                
        <?php if ($this->article_settings->modified_by) : ?>
            <li><?php echo $this->article_options->getLabel('modified_by'); ?>
                <?php echo $this->article_options->getInput('modified_by'); ?></li>
                                                        
            <li><?php echo $this->article_options->getLabel('modified'); ?>
                <?php echo $this->article_options->getInput('modified'); ?></li>
        <?php endif; ?>
                                            
        <?php if ($this->article_settings->version) : ?>
            <li><?php echo $this->article_options->getLabel('version'); ?>
                <?php echo $this->article_options->getInput('version'); ?></li>
        <?php endif; ?>
                                            
        <?php if ($this->article_settings->hits) : ?>
            <li><?php echo $this->article_options->getLabel('hits'); ?>
                <?php echo $this->article_options->getInput('hits'); ?></li>
        <?php endif; ?>
        <?php
        }
        ?>
    </ul>
</fieldset>

<?php
if(!$this->limited_options){
?> 
<?php $fieldSets = $this->article_options->getFieldsets('attribs');?>
<?php foreach ($fieldSets as $name => $fieldSet) : ?>
    <?php if(!in_array($name, array('editorConfig', 'basic-limited'))) : ?>
    <?php echo JHtml::_('sliders.panel', JText::_($fieldSet->label), $name . '-options'); ?>
    <?php if (isset($fieldSet->description) && trim($fieldSet->description)) : ?>
        <p class="tip"><?php echo $this->escape(JText::_($fieldSet->description)); ?></p>
    <?php endif; ?>
    <fieldset class="panelform">
        <ul class="adminformlist">
            <?php foreach ($this->article_options->getFieldset($name) as $field) : ?>
                <li><?php echo $field->label; ?><?php echo $field->input; ?></li>
            <?php endforeach; ?>
        </ul>
    </fieldset>
    <?php endif; ?>
<?php endforeach; ?>
<?php
}
?>
<?php echo JHtml::_('sliders.panel', JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS'), 'meta-options'); ?>
<fieldset class="panelform">
    <?php echo $this->article_options->getLabel('metadesc'); ?>
    <?php echo $this->article_options->getInput('metadesc'); ?>

    <?php echo $this->article_options->getLabel('metakey'); ?>
    <?php echo $this->article_options->getInput('metakey'); ?>
    <?php
    if(!$this->limited_options){
    ?>
    <?php foreach ($this->article_options->getGroup('metadata') as $field): ?>
        <?php if ($field->hidden): ?>
            <?php echo $field->input; ?>
        <?php else: ?>
            <?php echo $field->label; ?>
            <?php echo $field->input; ?>
        <?php endif; ?>
    <?php endforeach; ?>
    <?php
    }
    ?>
</fieldset>
        
<?php echo JHtml::_('sliders.end'); ?>
<?php 
/*
require_once( JPATH_SITE . '/administrator/components/com_content/helpers/content.php' ) ; 
if (ContentHelper::getActions($this->article_settings->catid)->get('core.admin')): 
?>
    <div class="width-100 fltlft">
        <?php echo JHtml::_('sliders.start', 'permissions-sliders-' . $this->id, array('useCookie' => 1)); ?>

        <?php echo JHtml::_('sliders.panel', JText::_('COM_CONTENT_FIELDSET_RULES'), 'access-rules'); ?>

        <fieldset class="panelform">
            <?php //echo $this->article_options->getLabel('rules'); ?>
        <?php echo $this->article_options->getInput('rules'); ?>
        </fieldset>

        <?php echo JHtml::_('sliders.end'); ?>
    </div>
<?php endif; */ ?>
<!-- Joomla 1.6 end -->
<br/>
<br/>
</div>
<?php
    }else{
        
        if($this->frontend){
            $document = JFactory::getDocument();
            $document->addScript( JURI::root(true).'/includes/js/joomla.javascript.js' );
        }
        
        jimport('joomla.html.pane');
        $pane = &JPane::getInstance('sliders', array('allowAllClose' => true));
?>
        <script language="javascript" type="text/javascript">
        <!--
        var sectioncategories = new Array;
        <?php
        $i = 0;
        foreach ($this->sectioncategories as $k=>$this->rows) {
                foreach ($this->rows as $v) {
                        echo "sectioncategories[".$i++."] = new Array( '$k','".addslashes( $v->id )."','".addslashes( $v->title )."' );\n\t\t";
                }
        }
        ?>
        //-->
        </script>
        <div id="cbArticleOptions" style="display:none;">
        <table  class="adminform">
        <tr>
                <td>
                        <label for="alias">
                                <?php echo JText::_( 'Alias' ); ?>
                        </label>
                </td>
                <td>
                        <input class="inputbox" type="text" name="alias" id="alias" size="40" maxlength="255" value="<?php echo $this->row->alias; ?>" title="<?php echo JText::_( 'ALIASTIP' ); ?>" />
                </td>
                <td>
                        <label>
                        <?php echo JText::_( 'Frontpage' ); ?>
                        </label>
                </td>
                <td>
                        <?php echo $this->lists['frontpage']; ?>
                </td>
        </tr>
        <tr>
                <td>
                        <label for="sectionid">
                                <?php echo JText::_( 'Section' ); ?>
                        </label>
                </td>
                <td>
                        <?php echo $this->lists['sectionid']; ?>
                </td>
                <td>
                        <label for="catid">
                                <?php echo JText::_( 'Category' ); ?>
                        </label>
                </td>
                <td>
                        <?php echo $this->lists['catid']; ?>
                </td>
        </tr>
        
        <tr>
                <td>
                        <label for="ordering">
                                <?php echo JText::_( 'Ordering' ); ?>
                        </label>
                </td>
                <td colspan="2">
                        <?php echo $this->lists['ordering']; ?>
                </td>
        </tr>
        
        </table>
        <?php
        $create_date 	= null;
        $nullDate 		= JFactory::getDBO()->getNullDate();

        // used to hide "Reset Hits" when hits = 0
        if ( !$this->row->hits ) {
                $visibility = 'style="display: none; visibility: hidden;"';
        } else {
                $visibility = '';
        }

        if(!$this->limited_options){
        ?>
        <table width="100%" style="border: 1px dashed silver; padding: 5px; margin-bottom: 10px;">
        <?php
        if ( $this->row->id ) {
        ?>
        <tr>
                <td>
                        <strong><?php echo JText::_( 'Article ID' ); ?>:</strong>
                </td>
                <td>
                        <?php echo $this->row->id; ?>
                </td>
        </tr>
        <?php
        }
        ?>
        <tr>
                <td>
                        <strong><?php echo JText::_( 'State' ); ?></strong>
                </td>
                <td>
                        <?php echo $this->row->state > 0 ? JText::_( 'Published' ) : ($this->row->state < 0 ? JText::_( 'Archived' ) : JText::_( 'Draft Unpublished' ) );?>
                </td>
        </tr>
        <tr>
                <td>
                        <strong><?php echo JText::_( 'Hits' ); ?></strong>
                </td>
                <td>
                        <?php echo $this->row->hits;?>
                </td>
        </tr>
        <tr>
                <td>
                        <strong><?php echo JText::_( 'Revised' ); ?></strong>
                </td>
                <td>
                        <?php echo $this->row->version;?> <?php echo JText::_( 'times' ); ?>
                </td>
        </tr>
        <tr>
                <td>
                        <strong><?php echo JText::_( 'Created' ); ?></strong>
                </td>
                <td>
                        <?php
                        if ( $this->row->created == $nullDate ) {
                                echo JText::_( 'New document' );
                        } else {
                                echo JHTML::_('date',  $this->row->created,  JText::_('DATE_FORMAT_LC2') );
                        }
                        ?>
                </td>
        </tr>
        <tr>
                <td>
                        <strong><?php echo JText::_( 'Modified' ); ?></strong>
                </td>
                <td>
                        <?php
                                if ( $this->row->modified == $nullDate ) {
                                        echo JText::_( 'Not modified' );
                                } else {
                                        echo JHTML::_('date',  $this->row->modified, JText::_('DATE_FORMAT_LC2'));
                                }
                        ?>
                </td>
        </tr>
        </table>
        
<?php
        }
        
        $title = JText::_( 'Parameters - Article' );
        echo $pane->startPane("content-pane");
        echo $pane->startPanel( $title, "detail-page" );
        echo $this->article_options->render('details');

        if(!$this->limited_options){
            $title = JText::_( 'Parameters - Advanced' );
            echo $pane->endPanel();
            echo $pane->startPanel( $title, "params-page" );
            echo $this->article_options->render('params', 'advanced');
        }
        
        $title = JText::_( 'Metadata Information' );
        echo $pane->endPanel();
        echo $pane->startPanel( $title, "metadata-page" );
        echo $this->article_options->render('meta', 'metadata');

        echo $pane->endPanel();
        echo $pane->endPane();
?>
        </div>
<?php
    }
?>
        <input type="hidden" name="Itemid" value="<?php echo JRequest::getInt('Itemid',0); ?>"/>
        <input type="hidden" name="task" id="contentbuilder_task" value="save"/>
        <input type="hidden" name="backtolist" value="<?php echo JRequest::getInt('backtolist',0);?>"/>
        <input type="hidden" name="return" value="<?php echo JRequest::getVar('return','');?>"/>
        <?php echo JHtml::_('form.token'); ?>
        <?php
        if($this->edit_by_type){
        ?>
        </form>
        <?php
        }
        ?>
        <?php echo $this->event->beforeDisplayContent; ?>
        <?php echo $this->toc ?>
        <?php echo $this->tpl ?>
        <?php echo $this->event->afterDisplayContent; ?>
        <br/>
        <?php
        if( JRequest::getInt('cb_show_bottom_bar', 1) ){
            ?>
            <div style="clear:right;"></div>
            <?php
            echo $buttons; 
        }
        ?>
<?php
if(!$this->edit_by_type){
?>
</form>
<?php
}
?>
<?php
}else{
    if($this->edit_by_type){
?>
    <form name="adminForm" id="adminform" onsubmit="return false;" action="<?php echo JRoute::_( 'index.php?option=com_contentbuilder&controller=edit&layout='.JRequest::getVar('layout', '').'&id='.JRequest::getInt('id', 0).'&record_id='.JRequest::getCmd('record_id',  '').'&Itemid='.JRequest::getInt('Itemid',0).'&limitstart='.JRequest::getInt('limitstart',0).'&filter_order='.JRequest::getCmd('filter_order') ); ?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="Itemid" value="<?php echo JRequest::getInt('Itemid',0); ?>"/>
    <input type="hidden" name="task" id="contentbuilder_task" value="save"/>
    <input type="hidden" name="backtolist" value="<?php echo JRequest::getInt('backtolist',0);?>"/>
    <input type="hidden" name="return" value="<?php echo JRequest::getVar('return','');?>"/>
    <?php echo JHtml::_('form.token'); ?>
    </form>
    <?php echo $this->event->beforeDisplayContent; ?>
    <?php echo $this->toc ?>
    <?php echo $this->tpl ?>
    <?php echo $this->event->afterDisplayContent; ?>
    <br/>
    <?php
    if( JRequest::getInt('cb_show_bottom_bar', 1) ){
        ?>
        <div style="clear:right;"></div>
        <?php
        echo $buttons; 
    }
    ?>
<?php
    } else {
?>
    <form name="adminForm" id="adminform" onsubmit="return false;" action="<?php echo JRoute::_( 'index.php?option=com_contentbuilder&controller=edit&layout='.JRequest::getVar('layout', '').'&id='.JRequest::getInt('id', 0).'&record_id='.JRequest::getCmd('record_id',  '').'&Itemid='.JRequest::getInt('Itemid',0).'&limitstart='.JRequest::getInt('limitstart',0).'&filter_order='.JRequest::getCmd('filter_order') ); ?>" method="post" enctype="multipart/form-data">
    <?php echo $this->event->beforeDisplayContent; ?>
    <?php echo $this->toc ?>
    <?php echo $this->tpl ?>
    <?php echo $this->event->afterDisplayContent; ?>
    <input type="hidden" name="Itemid" value="<?php echo JRequest::getInt('Itemid',0); ?>"/>
    <input type="hidden" name="task" id="contentbuilder_task" value="save"/>
    <input type="hidden" name="backtolist" value="<?php echo JRequest::getInt('backtolist',0);?>"/>
    <input type="hidden" name="return" value="<?php echo JRequest::getVar('return','');?>"/>
    <?php echo JHtml::_('form.token'); ?>
    </form>
    <br/>
    <?php
    if( JRequest::getInt('cb_show_bottom_bar', 1) ){
        ?>
        <div style="clear:right;"></div>
        <?php
        echo $buttons; 
    }
    ?>
<?php
    }
}

if(JRequest::getInt('cb_show_author',1)){
?>

<?php if($this->modified_by): ?>

<?php if($this->modified): ?>
<span class="small created-by"><?php echo JText::_('COM_CONTENTBUILDER_LAST_UPDATED_ON');?> <?php echo JHTML::_('date', $this->modified, JText::_('DATE_FORMAT_LC2')); ?></span>
<?php endif; ?>

<span class="small created-by"><?php echo JText::_('COM_CONTENTBUILDER_BY');?> <?php echo $this->modified_by; ?></span>

<?php endif; 
}
?>
</div>

