<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        http://www.crosstec.de
 * @license     GNU/GPL
*/

// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

class ContentbuilderViewAjax extends JView
{
    function display($tpl = null)
    {
        // Get data from the model
        $data = $this->get('Data');
        $this->assignRef( 'data', $data );
        parent::display($tpl);
    }
}
