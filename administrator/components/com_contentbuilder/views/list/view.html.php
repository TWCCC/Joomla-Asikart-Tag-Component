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

class ContentbuilderViewList extends JView
{
    function display($tpl = null)
    {
        // Get data from the model
        $subject = $this->get('Data');
        
        if(!class_exists('cbFeMarker')){
            echo '
            <style type="text/css">
            .icon-48-logo_left { background-image: url(../administrator/components/com_contentbuilder/views/logo_left.png); }
            </style>
            ';
            JToolBarHelper::title(   '<img src="components/com_contentbuilder/views/logo_right.png" alt="" align="top" /> <span style="display:inline-block; vertical-align:middle"> :: ' . $subject->page_title . '</span>', 'logo_left.png' );
        }
        
        
        $pagination = $this->get('Pagination');
        $total = $this->get('Total');

        $state = $this->get( 'state' );
        $lists['order_Dir'] = $state->get( 'formsd_filter_order_Dir' );
        $lists['order'] = $state->get( 'formsd_filter_order' );
        $lists['filter'] = $state->get( 'formsd_filter' );
        $lists['filter_state'] = $state->get( 'formsd_filter_state' );
        $lists['filter_publish'] = $state->get( 'formsd_filter_publish' );
        $lists['filter_language'] = $state->get( 'formsd_filter_language' );
        $lists['limitstart'] = $state->get( 'limitstart' );

        JPluginHelper::importPlugin('contentbuilder_themes', $subject->theme_plugin);
        $dispatcher = JDispatcher::getInstance();
        $results = $dispatcher->trigger('getListViewCss', array());
        $this->assignRef( 'theme_css', implode('', $results));
        
        JPluginHelper::importPlugin('contentbuilder_themes', $subject->theme_plugin);
        $dispatcher = JDispatcher::getInstance();
        $results = $dispatcher->trigger('getListViewJavascript', array());
        $this->assignRef( 'theme_js', implode('', $results));
        
        $this->assignRef( 'show_filter', $subject->show_filter );
        $this->assignRef( 'show_records_per_page', $subject->show_records_per_page );
        
        $this->assignRef( 'page_class', $subject->page_class );
        $this->assignRef( 'show_page_heading', $subject->show_page_heading );
        $this->assignRef( 'slug', $subject->slug );
        $this->assignRef( 'slug2', $subject->slug2 );
        $this->assignRef( 'form_id', $subject->form_id );
        $this->assignRef( 'labels', $subject->labels );
        $this->assignRef( 'visible_cols', $subject->visible_cols );
        $this->assignRef( 'linkable_elements', $subject->linkable_elements );
        $this->assignRef( 'show_id_column', $subject->show_id_column );
        $this->assignRef( 'page_title', $subject->page_title );
        $this->assignRef( 'intro_text', $subject->intro_text );
        $this->assignRef( 'export_xls', $subject->export_xls );
        $this->assignRef( 'display_filter', $subject->display_filter );
        $this->assignRef( 'edit_button', $subject->edit_button );
        $this->assignRef( 'select_column', $subject->select_column );
        $this->assignRef( 'states', $subject->states );
        $this->assignRef( 'list_state', $subject->list_state );
        $this->assignRef( 'list_publish', $subject->list_publish );
        $this->assignRef( 'list_language', $subject->list_language );
        $this->assignRef( 'list_article', $subject->list_article );
        $this->assignRef( 'list_author', $subject->list_author );
        $this->assignRef( 'list_rating', $subject->list_rating );
        $this->assignRef( 'rating_slots', $subject->rating_slots );
        $this->assignRef( 'state_colors', $subject->state_colors );
        $this->assignRef( 'state_titles', $subject->state_titles );
        $this->assignRef( 'published_items', $subject->published_items );
        $this->assignRef( 'languages', $subject->languages );
        $this->assignRef( 'lang_codes', $subject->lang_codes );
        $this->assignRef( 'title_field', $subject->title_field );
        $this->assignRef( 'lists', $lists );
        $this->assignRef( 'items', $subject->items );
        $this->assignRef( 'pagination', $pagination );
        $this->assignRef( 'total', $total );
        $this->assignRef( 'own_only', $own_only = JFactory::getApplication()->isSite() ? $subject->own_only_fe : $subject->own_only );
        parent::display($tpl);
    }
}
