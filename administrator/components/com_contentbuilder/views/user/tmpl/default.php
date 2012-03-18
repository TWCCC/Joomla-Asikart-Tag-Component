<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        http://www.crosstec.de
 * @license     GNU/GPL
*/

JHTML::_( 'behavior.calendar' );
?>

<div style="float:right">
<input type="button" value="<?php echo JText::_( 'COM_CONTENTBUILDER_SAVE' ); ?>" class="button" onclick="document.adminForm.task.value='save';document.adminForm.submit();" />
<input type="button" value="<?php echo JText::_( 'COM_CONTENTBUILDER_CANCEL' ); ?>" class="button" onclick="document.adminForm.task.value='cancel';document.adminForm.submit();" />
</div> 

<form action="index.php" method="post" name="adminForm" id="adminForm">
<div style="clear:both;"></div>
    
<div class="col100">
<table class="adminlist">
    <tr class="row0">
        <td width="20%" align="right" class="key">
            <?php echo JText::_( 'COM_CONTENTBUILDER_ID' ); ?>
        </td>
        <td>
            <?php echo htmlentities($this->subject->id, ENT_QUOTES, 'UTF-8');?>
        </td>
    </tr>
    <tr class="row0">
        <td width="20%" align="right" class="key">
            <?php echo JText::_( 'COM_CONTENTBUILDER_NAME' ); ?>
        </td>
        <td>
            <?php echo htmlentities($this->subject->name, ENT_QUOTES, 'UTF-8');?>
        </td>
    </tr>
    <tr class="row0">
        <td width="20%" align="right" class="key">
            <?php echo JText::_( 'COM_CONTENTBUILDER_USERNAME' ); ?>
        </td>
        <td>
            <?php echo htmlentities($this->subject->username, ENT_QUOTES, 'UTF-8');?>
        </td>
    </tr>
    <tr class="row0">
        <td width="20%" align="right" class="key">
            <label for="limit_add"><?php echo JText::_('COM_CONTENTBUILDER_PERM_LIMIT_ADD'); ?>:</label>
        </td>
        <td>
            <input id="limit_add" name="limit_add" type="text" value="<?php echo $this->subject->limit_add; ?>"/>
        </td>
    </tr>
    <tr class="row0">
        <td width="20%" align="right" class="key">
            <label for="limit_edit"><?php echo JText::_('COM_CONTENTBUILDER_PERM_LIMIT_EDIT'); ?>:</label>
        </td>
        <td>
            <input id="limit_edit" name="limit_edit" type="text" value="<?php echo $this->subject->limit_edit; ?>"/>
        </td>
    </tr>
    <tr class="row0">
        <td width="20%" align="right" class="key">
            <label for="verification_date_view"><?php echo JText::_('COM_CONTENTBUILDER_PERM_VERIFICATION_DATE_VIEW'); ?>:</label>
        </td>
        <td>
            <input id="verification_date_view" name="verification_date_view" type="text" value="<?php echo $this->subject->verification_date_view; ?>"/>
            <input type="checkbox" id="verified_view" name="verified_view" value="1"<?php echo $this->subject->verified_view ? ' checked="checked"' : ''; ?>/> <label for="verified_view"><?php echo JText::_( 'COM_CONTENTBUILDER_VERIFIED_VIEW' ); ?></label>
            <script type="text/javascript">
            <!--
            Calendar.setup({
                    inputField     :    "verification_date_view",
                    ifFormat       :    "%Y-%m-%d",
                    align          :    "Bl",
                    singleClick    :    true
            });
            //-->
            </script>
        </td>
    </tr>
    <tr class="row0">
        <td width="20%" align="right" class="key">
            <label for="verification_date_new"><?php echo JText::_('COM_CONTENTBUILDER_PERM_VERIFICATION_DATE_NEW'); ?>:</label>
        </td>
        <td>
            <input id="verification_date_new" name="verification_date_new" type="text" value="<?php echo $this->subject->verification_date_new; ?>"/>
            <input type="checkbox" id="verified_new" name="verified_new" value="1"<?php echo $this->subject->verified_new ? ' checked="checked"' : ''; ?>/> <label for="verified_new"><?php echo JText::_( 'COM_CONTENTBUILDER_VERIFIED_NEW' ); ?></label>
            <script type="text/javascript">
            <!--
            Calendar.setup({
                    inputField     :    "verification_date_new",
                    ifFormat       :    "%Y-%m-%d",
                    align          :    "Bl",
                    singleClick    :    true
            });
            //-->
            </script>
        </td>
    </tr>
    <tr class="row0">
        <td width="20%" align="right" class="key">
            <label for="verification_date_edit"><?php echo JText::_('COM_CONTENTBUILDER_PERM_VERIFICATION_DATE_EDIT'); ?>:</label>
        </td>
        <td>
            <input id="verification_date_edit" name="verification_date_edit" type="text" value="<?php echo $this->subject->verification_date_edit; ?>"/>
            <input type="checkbox" id="verified_edit" name="verified_edit" value="1"<?php echo $this->subject->verified_edit ? ' checked="checked"' : ''; ?>/> <label for="verified_edit"><?php echo JText::_( 'COM_CONTENTBUILDER_VERIFIED_EDIT' ); ?></label>
            <script type="text/javascript">
            <!--
            Calendar.setup({
                    inputField     :    "verification_date_edit",
                    ifFormat       :    "%Y-%m-%d",
                    align          :    "Bl",
                    singleClick    :    true
            });
            //-->
            </script>
        </td>
    </tr>
    <tr class="row0">
        <td width="20%" align="right" class="key">
            <label for="published"><?php echo JText::_( 'PUBLISHED' ); ?></label>
        </td>
        <td>
            <input type="checkbox" id="published" name="published" value="1"<?php echo $this->subject->published ? ' checked="checked"' : ''; ?>/>
            
        </td>
    </tr>
</table>
</div>
    

<input type="hidden" name="option" value="com_contentbuilder" />
<input type="hidden" name="task" id="task" value="" />
<input type="hidden" name="form_id" value="<?php echo JRequest::getInt('form_id',0); ?>" />
<input type="hidden" name="joomla_userid" value="<?php echo $this->subject->id; ?>" />
<input type="hidden" name="cb_id" value="<?php echo $this->subject->cb_id; ?>" />
<input type="hidden" name="tmpl" value="<?php echo JRequest::getCmd('tmpl',''); ?>" />
<input type="hidden" name="controller" value="users" />
<?php echo JHtml::_('form.token'); ?>
</form>