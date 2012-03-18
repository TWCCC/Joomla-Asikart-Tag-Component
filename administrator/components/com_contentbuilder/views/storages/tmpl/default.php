<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        http://www.crosstec.de
 * @license     GNU/GPL
*/

defined('_JEXEC') or die('Restricted access');

?>
<form action="index.php" method="post" name="adminForm">

<div id="editcell">
    <table class="adminlist">
    <thead>
        <tr>
            <th width="5">
                <?php echo JText::_( 'COM_CONTENTBUILDER_ID' ); ?>
            </th>
            <th width="20">
              <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->items ); ?>);" />
            </th>
            <th>
                <?php echo JHTML::_('grid.sort', JText::_( 'COM_CONTENTBUILDER_NAME' ), 'name', $this->lists['order_Dir'], $this->lists['order'] ); ?>
            </th>
            <th>
                <?php echo JHTML::_('grid.sort', JText::_( 'COM_CONTENTBUILDER_STORAGE_TITLE' ), 'title', $this->lists['order_Dir'], $this->lists['order'] ); ?>
            </th>
            <th width="8%" nowrap="nowrap">
                <?php echo JHTML::_('grid.sort',   JText::_( 'COM_CONTENTBUILDER_ORDERBY') , 'ordering', 'desc', @$this->lists['order'] ); ?>
                <?php if ($this->ordering) echo JHTML::_('grid.order',  $this->items ); ?>
            </th>
            <th width="5">
                <?php echo JText::_( 'COM_CONTENTBUILDER_PUBLISHED' ); ?>
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
        $link = JRoute::_( 'index.php?option=com_contentbuilder&controller=storages&task=edit&cid[]='. $row->id );
        $published 	= JHTML::_('grid.published', $row, $i );
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
                <a href="<?php echo $link; ?>"><?php echo $row->title; ?></a>
            </td>
            <td class="order" nowrap="nowrap">
                <span><?php echo $this->pagination->orderUpIcon( $i, true, 'orderup', 'Move Up', $this->ordering); ?></span>
                <span><?php echo $this->pagination->orderDownIcon( $i, $n, true, 'orderdown', 'Move Down', $this->ordering ); ?></span>
                <?php $disabled = $this->ordering ?  '' : 'disabled="disabled"'; ?>
                <input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" <?php echo $disabled ?> class="text_area" style="text-align: center" />
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
                <td colspan="8"><?php echo $this->pagination->getListFooter(); ?></td>
            </tr>
        </tfoot>

    </table>
</div>

<input type="hidden" name="option" value="com_contentbuilder" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="controller" value="storages" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
<?php echo JHtml::_('form.token'); ?>
</form>

