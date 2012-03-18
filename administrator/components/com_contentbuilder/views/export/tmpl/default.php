<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        http://www.crosstec.de
 * @license     GNU/GPL
 */
defined( '_JEXEC' ) or die( 'Restricted access' );

@ob_end_clean();

require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'classes' . DS . 'contentbuilder_helpers.php');

$filename = "export-".date('Y-m-d_Hi').".xls";
header("Content-type: application/ms-excel;charset=UTF-8");
header('Content-Disposition: attachment; filename=' . $filename);
header("Pragma: no-cache");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
?>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<style>
<!--
.text {mso-number-format:\@}
-->
</style>
</head>
<body>
<table>
<tr>
<?php
if($this->data->show_id_column){
    echo '<th>'.JText::_('COM_CONTENTBUILDER_ID').'</th>';
}
foreach($this->data->visible_labels As $label){
    echo '<th>'.$label.'</th>';
}
?>
</tr>
<?php
foreach($this->data->items As $item){
    echo '<tr>';
    if($this->data->show_id_column){
        echo '<td>'.$item->colRecord.'</td>';
    }
    foreach($item As $key => $value){
        if($key != 'colRecord' && in_array(str_replace('col','',$key), $this->data->visible_cols)){
            if($value){
                echo '<td class="text">'.htmlentities((contentbuilder_is_internal_path($value) ? basename($value) : $value), ENT_QUOTES, 'UTF-8').'</td>';
            }else{
                echo '<td>&nbsp;</td>';
            }
        }
    }
    echo '</tr>';
}
?>
</table>
</body>
</html>

<?php
exit;
?>