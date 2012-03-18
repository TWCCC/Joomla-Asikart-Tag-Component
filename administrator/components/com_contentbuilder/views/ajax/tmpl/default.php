<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        http://www.crosstec.de
 * @license     GNU/GPL
 */
defined( '_JEXEC' ) or die( 'Restricted access' );

ob_end_clean();
header("Content-type: text/plain; charset=UTF-8");

echo $this->data;

exit;