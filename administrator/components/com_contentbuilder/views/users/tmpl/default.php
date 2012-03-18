<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        http://www.crosstec.de
 * @license     GNU/GPL
*/

defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.mootools');
?>
<form action="index.php" method="post" name="adminForm">

<div style="float:left">
<input type="text" size="25" name="users_search" id="users_search" value="<?php echo $this->lists['users_search'];?>" class="text_area" onchange="document.adminForm.submit();" />
<input type="button" value="<?php echo JText::_( 'COM_CONTENTBUILDER_SEARCH' ); ?>" class="button" onclick="this.form.submit();" />
<input type="button" value="<?php echo JText::_( 'COM_CONTENTBUILDER_RESET' ); ?>" class="button" onclick="document.getElementById('users_search').value='';document.adminForm.submit();" />
</div> 
    
<div style="float:right">
<select onchange="if(this.selectedIndex == 1 || this.selectedIndex == 2){document.adminForm.task.value=this.options[this.selectedIndex].value;document.adminForm.submit();}">
    <option> - <?php echo JText::_( 'COM_CONTENTBUILDER_PUBLISHED_UNPUBLISHED' ); ?> - </option>
    <option value="publish"><?php echo JText::_( 'PUBLISH' ); ?></option>
    <option value="unpublish"><?php echo JText::_( 'UNPUBLISH' ); ?></option>
</select>
<select onchange="if(this.selectedIndex != 0){document.adminForm.task.value=this.options[this.selectedIndex].value;document.adminForm.submit();}">
    <option> - <?php echo JText::_( 'COM_CONTENTBUILDER_SET_VERIFICATION' ); ?> - </option>
    <option value="verified_view"><?php echo JText::_( 'COM_CONTENTBUILDER_VERIFIED_VIEW' ); ?></option>
    <option value="not_verified_view"><?php echo JText::_( 'COM_CONTENTBUILDER_UNVERIFIED_VIEW' ); ?></option>
    <option value="verified_new"><?php echo JText::_( 'COM_CONTENTBUILDER_VERIFIED_NEW' ); ?></option>
    <option value="not_verified_new"><?php echo JText::_( 'COM_CONTENTBUILDER_UNVERIFIED_NEW' ); ?></option>
    <option value="verified_edit"><?php echo JText::_( 'COM_CONTENTBUILDER_VERIFIED_EDIT' ); ?></option>
    <option value="not_verified_edit"><?php echo JText::_( 'COM_CONTENTBUILDER_UNVERIFIED_EDIT' ); ?></option>
</select>
</div> 
    
<div style="clear:both;"></div>
    
<div id="editcell">
    <table class="adminlist">
    <thead>
        <tr>
            <th width="5">
                <?php echo JHTML::_('grid.sort', JText::_( 'COM_CONTENTBUILDER_ID' ), 'id', $this->lists['order_Dir'], $this->lists['order'] ); ?>
            </th>
            <th width="20">
              <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->items ); ?>);" />
            </th>
            <th>
                <?php echo JHTML::_('grid.sort', JText::_( 'COM_CONTENTBUILDER_NAME' ), 'name', $this->lists['order_Dir'], $this->lists['order'] ); ?>
            </th>
            <th>
                <?php echo JHTML::_('grid.sort', JText::_( 'COM_CONTENTBUILDER_USERNAME' ), 'username', $this->lists['order_Dir'], $this->lists['order'] ); ?>
            </th>
            <th>
                <?php echo JHTML::_('grid.sort', JText::_( 'COM_CONTENTBUILDER_VERIFIED_VIEW' ), 'verified_view', $this->lists['order_Dir'], $this->lists['order'] ); ?>
            </th>
            <th>
                <?php echo JHTML::_('grid.sort', JText::_( 'COM_CONTENTBUILDER_VERIFIED_NEW' ), 'verified_new', $this->lists['order_Dir'], $this->lists['order'] ); ?>
            </th>
            <th>
                <?php echo JHTML::_('grid.sort', JText::_( 'COM_CONTENTBUILDER_VERIFIED_EDIT' ), 'verified_edit', $this->lists['order_Dir'], $this->lists['order'] ); ?>
            </th>
            <th width="5">
                <?php echo JHTML::_('grid.sort', JText::_( 'COM_CONTENTBUILDER_PUBLISHED' ), 'published', $this->lists['order_Dir'], $this->lists['order'] ); ?>
            </th>
        </tr>
    </thead>
    <?php
    $k = 0;
    $n = count( $this->items );
    for ($i=0; $i < $n; $i++)
    {
        $row = $this->items[$i];
        $checked    = JHTML::_( 'grid.id', $i, $row->id );
        $link = JRoute::_( 'index.php?option=com_contentbuilder&controller=users&tmpl='.JRequest::getCmd('tmpl','').'&task=edit&form_id='.JRequest::getInt('form_id',0).'&joomla_userid='. $row->id );
        if($row->published === null){
           $row->published = 1; 
        }
        $published 	= JHTML::_('grid.published', $row, $i );
        $verified_view 	= contentbuilder_helpers::listVerifiedView($row, $i);
        $verified_new 	= contentbuilder_helpers::listVerifiedNew($row, $i);
        $verified_edit 	= contentbuilder_helpers::listVerifiedEdit($row, $i);
        ?>
        <tr class="<?php echo "row$k"; ?>">
            <td>
                <?php echo $row->id; ?>
            </td>
            <td>
              <?php echo $checked; ?>
            </td>
            <td>
                <a href="<?php echo $link; ?>"><?php echo $row->name; ?></a>
            </td>
            <td>
                <a href="<?php echo $link; ?>"><?php echo $row->username; ?></a>
            </td>
            <td>
              <?php echo $verified_view; ?>
            </td>
            <td>
              <?php echo $verified_new; ?>
            </td>
            <td>
              <?php echo $verified_edit; ?>
            </td>
            <td>
              <?php echo $published; ?>
            </td>
        </tr>
        <?php
        $k = 1 - $k;
    }
    ?>
        <tfoot>
            <tr>
                <td colspan="9"><?php echo $this->pagination->getListFooter(); ?></td>
            </tr>
        </tfoot>

    </table>
</div>

<input type="hidden" name="option" value="com_contentbuilder" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="controller" value="users" />
<input type="hidden" name="form_id" value="<?php echo JRequest::getInt('form_id',0);?>" />
<input type="hidden" name="tmpl" value="<?php echo JRequest::getWord('tmpl','');?>" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
<?php echo JHtml::_('form.token'); ?>
</form>

