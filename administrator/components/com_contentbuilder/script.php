<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        http://www.crosstec.de
 * @license     GNU/GPL
*/
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

if(!function_exists('contentbuilder_install_db')){
function contentbuilder_install_db(){
    
    $db = JFactory::getDBO();
    
    $tables = JFactory::getDBO()->getTableFields( JFactory::getDBO()->getTableList() );
        
    if(isset($tables[JFactory::getDBO()->getPrefix().'contentbuilder_forms'])){
        return true;
    }
    
    $query1 = "

CREATE TABLE `#__contentbuilder_articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` int(11) NOT NULL DEFAULT '0',
  `record_id` varchar(255) NOT NULL DEFAULT '0',
  `form_id` int(11) NOT NULL DEFAULT '0',
  `last_update` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `record_id` (`record_id`,`form_id`),
  KEY `article_id` (`article_id`,`record_id`),
  KEY `record_id_2` (`record_id`)
)";


$query2 = "
CREATE TABLE `#__contentbuilder_elements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `form_id` int(11) NOT NULL DEFAULT '0',
  `reference_id` varchar(255) NOT NULL DEFAULT '',
  `type` varchar(255) NOT NULL DEFAULT '',
  `change_type` varchar(255) NOT NULL DEFAULT '',
  `options` text NOT NULL,
  `custom_init_script` text NOT NULL,
  `custom_action_script` text NOT NULL,
  `custom_validation_script` text NOT NULL,
  `validation_message` text NOT NULL,
  `default_value` text NOT NULL,
  `hint` text NOT NULL,
  `label` varchar(255) NOT NULL DEFAULT '',
  `list_include` tinyint(1) NOT NULL DEFAULT '0',
  `search_include` tinyint(1) NOT NULL DEFAULT '1',
  `item_wrapper` text NOT NULL,
  `wordwrap` int(11) NOT NULL DEFAULT '0',
  `linkable` tinyint(1) NOT NULL DEFAULT '1',
  `editable` tinyint(1) NOT NULL DEFAULT '0',
  `validations` text NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `ordering` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `reference_id` (`reference_id`),
  KEY `form_id` (`form_id`,`reference_id`)
)";


$query3 = "
CREATE TABLE `#__contentbuilder_forms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL DEFAULT '',
  `reference_id` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `details_template` longtext NOT NULL,
  `details_prepare` longtext NOT NULL,
  `editable_template` longtext NOT NULL,
  `editable_prepare` longtext NOT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` varchar(255) NOT NULL DEFAULT '',
  `modified_by` varchar(255) NOT NULL DEFAULT '',
  `metadata` tinyint(1) NOT NULL DEFAULT '1',
  `export_xls` tinyint(1) NOT NULL DEFAULT '0',
  `print_button` tinyint(1) NOT NULL DEFAULT '1',
  `show_id_column` tinyint(1) NOT NULL DEFAULT '0',
  `use_view_name_as_title` tinyint(1) NOT NULL DEFAULT '0',
  `display_in` tinyint(1) NOT NULL DEFAULT '0',
  `edit_button` tinyint(1) NOT NULL DEFAULT '0',
  `list_state` tinyint(1) NOT NULL DEFAULT '0',
  `list_publish` tinyint(1) NOT NULL DEFAULT '0',
  `list_language` tinyint(1) NOT NULL DEFAULT '0',
  `list_article` tinyint(1) NOT NULL DEFAULT '0',
  `list_author` tinyint(1) NOT NULL DEFAULT '0',
  `select_column` tinyint(1) NOT NULL DEFAULT '0',
  `published_only` tinyint(1) NOT NULL DEFAULT '0',
  `own_only` tinyint(1) NOT NULL DEFAULT '0',
  `own_only_fe` tinyint(1) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `intro_text` text NOT NULL,
  `config` longtext NOT NULL,
  `default_section` int(11) NOT NULL DEFAULT '0',
  `default_category` int(11) NOT NULL DEFAULT '0',
  `default_lang_code` varchar(7) NOT NULL DEFAULT '*',
  `default_lang_code_ignore` tinyint(1) NOT NULL DEFAULT '0',
  `create_articles` tinyint(1) NOT NULL DEFAULT '1',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `initial_sort_order` varchar(255) NOT NULL DEFAULT '-1',
  `title_field` int(11) NOT NULL DEFAULT '0',
  `delete_articles` tinyint(1) NOT NULL DEFAULT '1',
  `edit_by_type` tinyint(1) NOT NULL DEFAULT '0',
  `email_notifications` tinyint(1) NOT NULL DEFAULT '1',
  `email_update_notifications` tinyint(1) NOT NULL DEFAULT '0',
  `limited_article_options` tinyint(1) NOT NULL DEFAULT '1',
  `limited_article_options_fe` tinyint(1) NOT NULL DEFAULT '1',
  `upload_directory` text NOT NULL,
  `protect_upload_directory` tinyint(1) NOT NULL DEFAULT '1',
  `last_update` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `limit_add` int(11) NOT NULL DEFAULT '0',
  `limit_edit` int(11) NOT NULL DEFAULT '0',
  `verification_required_view` tinyint(1) NOT NULL DEFAULT '0',
  `verification_days_view` float NOT NULL DEFAULT '0',
  `verification_required_new` tinyint(1) NOT NULL DEFAULT '0',
  `verification_days_new` float NOT NULL DEFAULT '0',
  `verification_required_edit` tinyint(1) NOT NULL DEFAULT '0',
  `verification_days_edit` float NOT NULL DEFAULT '0',
  `verification_url_view` text NOT NULL,
  `verification_url_new` text NOT NULL,
  `verification_url_edit` text NOT NULL,
  `show_all_languages_fe` tinyint(1) NOT NULL DEFAULT '1',
  `default_publish_up_days` int(11) NOT NULL DEFAULT '0',
  `default_publish_down_days` int(11) NOT NULL DEFAULT '0',
  `default_access` int(11) NOT NULL DEFAULT '0',
  `default_featured` tinyint(1) NOT NULL DEFAULT '0',
  `email_admin_template` text NOT NULL,
  `email_admin_subject` varchar(255) NOT NULL DEFAULT '',
  `email_admin_alternative_from` varchar(255) NOT NULL DEFAULT '',
  `email_admin_alternative_fromname` varchar(255) NOT NULL DEFAULT '',
  `email_admin_recipients` text NOT NULL,
  `email_admin_recipients_attach_uploads` text NOT NULL,
  `email_admin_html` tinyint(1) NOT NULL DEFAULT '0',
  `email_template` text NOT NULL,
  `email_subject` varchar(255) NOT NULL DEFAULT '',
  `email_alternative_from` varchar(255) NOT NULL DEFAULT '',
  `email_alternative_fromname` varchar(255) NOT NULL,
  `email_recipients` text NOT NULL,
  `email_recipients_attach_uploads` text NOT NULL,
  `email_html` tinyint(1) NOT NULL DEFAULT '0',
  `act_as_registration` tinyint(1) NOT NULL DEFAULT '0',
  `registration_username_field` varchar(255) NOT NULL DEFAULT '',
  `registration_password_field` varchar(255) NOT NULL DEFAULT '',
  `registration_password_repeat_field` varchar(255) NOT NULL DEFAULT '',
  `registration_name_field` varchar(255) NOT NULL DEFAULT '',
  `registration_email_field` varchar(255) NOT NULL DEFAULT '',
  `registration_email_repeat_field` varchar(255) NOT NULL DEFAULT '',
  `auto_publish` tinyint(1) NOT NULL DEFAULT '0',
  `force_login` tinyint(1) NOT NULL DEFAULT '0',
  `force_url` text NOT NULL,
  `registration_bypass_plugin` varchar(255) NOT NULL DEFAULT '',
  `registration_bypass_plugin_params` text NOT NULL,
  `registration_bypass_verification_name` varchar(255) NOT NULL DEFAULT '',
  `registration_bypass_verify_view` varchar(32) NOT NULL DEFAULT '',
  `theme_plugin` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `reference_id` (`reference_id`)
)";


$query4 = "
CREATE TABLE `#__contentbuilder_list_records` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `form_id` int(11) NOT NULL DEFAULT '0',
  `record_id` varchar(255) NOT NULL DEFAULT '',
  `state_id` int(11) NOT NULL DEFAULT '0',
  `reference_id` varchar(255) NOT NULL DEFAULT '',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `form_id` (`form_id`,`record_id`,`state_id`)
)";


$query5 = "
CREATE TABLE `#__contentbuilder_list_states` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `form_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `color` varchar(255) NOT NULL DEFAULT '',
  `action` varchar(255) NOT NULL DEFAULT '',
  `published` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
)";


$query6 = "
CREATE TABLE `#__contentbuilder_records` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `record_id` varchar(255) NOT NULL DEFAULT '',
  `reference_id` varchar(255) NOT NULL DEFAULT '',
  `edited` int(11) NOT NULL DEFAULT '0',
  `sef` varchar(50) NOT NULL DEFAULT '',
  `lang_code` varchar(7) NOT NULL DEFAULT '*',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_update` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `is_future` tinyint(1) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `record_id` (`record_id`),
  KEY `reference_id` (`reference_id`)
)";


$query7 = "
CREATE TABLE `#__contentbuilder_registered_users` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `record_id` varchar(255) NOT NULL DEFAULT '',
  `form_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`record_id`,`form_id`)
)";


$query8 = "
CREATE TABLE `#__contentbuilder_resource_access` (
  `form_id` int(11) NOT NULL DEFAULT '0',
  `element_id` varchar(100) NOT NULL DEFAULT '',
  `resource_id` varchar(100) NOT NULL,
  `hits` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `form_id` (`form_id`,`element_id`,`resource_id`)
)";

$query9 = "
CREATE TABLE `#__contentbuilder_storages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
)";

$query10 = "

CREATE TABLE `#__contentbuilder_storage_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `storage_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `is_group` tinyint(1) NOT NULL DEFAULT '0',
  `group_definition` text NOT NULL,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `storage_id` (`storage_id`,`name`)
)";

$query11 = "
CREATE TABLE `#__contentbuilder_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL DEFAULT '0',
  `form_id` int(11) NOT NULL DEFAULT '0',
  `records` int(11) NOT NULL DEFAULT '0',
  `verified_view` tinyint(1) NOT NULL DEFAULT '0',
  `verification_date_view` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `verified_new` tinyint(1) NOT NULL DEFAULT '0',
  `verification_date_new` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `verified_edit` tinyint(1) NOT NULL DEFAULT '0',
  `verification_date_edit` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `limit_add` int(11) NOT NULL DEFAULT '0',
  `limit_edit` int(11) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `userid` (`userid`,`form_id`)
)";


$query12 = "
CREATE TABLE `#__contentbuilder_verifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `verification_hash` varchar(255) NOT NULL DEFAULT '',
  `start_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `verification_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `verification_data` text NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `plugin` varchar(255) NOT NULL DEFAULT '',
  `ip` varchar(255) NOT NULL DEFAULT '',
  `is_test` tinyint(1) NOT NULL DEFAULT '0',
  `setup` text NOT NULL,
  `client` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `verification_hash` (`verification_hash`),
  KEY `user_id` (`user_id`)
)
";
    
    $db->setQuery($query1);
    $db->query();
    $db->setQuery($query2);
    $db->query();
    $db->setQuery($query3);
    $db->query();
    $db->setQuery($query4);
    $db->query();
    $db->setQuery($query5);
    $db->query();
    $db->setQuery($query6);
    $db->query();
    $db->setQuery($query7);
    $db->query();
    $db->setQuery($query8);
    $db->query();
    $db->setQuery($query9);
    $db->query();
    $db->setQuery($query10);
    $db->query();
    $db->setQuery($query11);
    $db->query();
    $db->setQuery($query12);
    $db->query();
}
}

class com_contentbuilderInstallerScript
{
    
        function getPlugins(){
            $plugins = array();
            $plugins['contentbuilder_verify'] = array();
            $plugins['contentbuilder_verify'][] = 'paypal';
            $plugins['contentbuilder_verify'][] = 'passthrough';
            $plugins['contentbuilder_validation'] = array();
            $plugins['contentbuilder_validation'][] = 'notempty';
            $plugins['contentbuilder_validation'][] = 'equal';
            $plugins['contentbuilder_validation'][] = 'email';
            $plugins['contentbuilder_validation'][] = 'date_not_before';
            $plugins['contentbuilder_validation'][] = 'date_is_valid';
            $plugins['contentbuilder_themes'] = array();
            $plugins['contentbuilder_themes'][] = 'khepri';
            $plugins['contentbuilder_themes'][] = 'blank';
            $plugins['system'] = array();
            $plugins['system'][] = 'contentbuilder_system';
            $plugins['contentbuilder_submit'] = array();
            $plugins['contentbuilder_submit'][] = 'submit_sample';
            $plugins['contentbuilder_listaction'] = array();
            $plugins['contentbuilder_listaction'][] = 'trash';
            $plugins['contentbuilder_listaction'][] = 'untrash';
            $plugins['content'] = array();
            $plugins['content'][] = 'contentbuilder_verify';
            $plugins['content'][] = 'contentbuilder_permission_observer';
            $plugins['content'][] = 'contentbuilder_image_scale';
            $plugins['content'][] = 'contentbuilder_download';
            $plugins['content'][] = 'contentbuilder_rating';
            return $plugins;
        }
        
        function installAndUpdate(){
            
            $db = JFactory::getDBO();
            
            $tables = JFactory::getDBO()->getTableFields( JFactory::getDBO()->getTableList() );
            // articles updates
            if(isset($tables[JFactory::getDBO()->getPrefix().'contentbuilder_articles'])){
                if(!isset( $tables[JFactory::getDBO()->getPrefix().'contentbuilder_articles']['type'] )){
                    JFactory::getDBO()->setQuery("ALTER TABLE `#__contentbuilder_articles` ADD `type` VARCHAR( 55 ) NOT NULL DEFAULT '' AFTER `article_id` , ADD `reference_id` VARCHAR( 100 ) NOT NULL DEFAULT '' AFTER `type` ");
                    JFactory::getDBO()->query();
                }
            }
            // storages updates
            if(isset($tables[JFactory::getDBO()->getPrefix().'contentbuilder_storages'])){
                if(!isset( $tables[JFactory::getDBO()->getPrefix().'contentbuilder_storages']['bytable'] )){
                    JFactory::getDBO()->setQuery("ALTER TABLE `#__contentbuilder_storages` ADD `bytable` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `title`  ");
                    JFactory::getDBO()->query();
                }
            }
            // forms updates
            if(isset($tables[JFactory::getDBO()->getPrefix().'contentbuilder_forms'])){
                if(!isset( $tables[JFactory::getDBO()->getPrefix().'contentbuilder_forms']['initial_order_dir'] )){
                    JFactory::getDBO()->setQuery("ALTER TABLE `#__contentbuilder_forms` ADD `initial_order_dir` VARCHAR( 4 ) NOT NULL DEFAULT 'desc' AFTER `initial_sort_order` ");
                    JFactory::getDBO()->query();
                }
                if(!isset( $tables[JFactory::getDBO()->getPrefix().'contentbuilder_forms']['list_rating'] )){
                    JFactory::getDBO()->setQuery("ALTER TABLE `#__contentbuilder_forms` ADD `list_rating` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `theme_plugin` ");
                    JFactory::getDBO()->query();
                }
                if(!isset( $tables[JFactory::getDBO()->getPrefix().'contentbuilder_forms']['rating_slots'] )){
                    JFactory::getDBO()->setQuery("ALTER TABLE `#__contentbuilder_forms` ADD `rating_slots` TINYINT( 1 ) NOT NULL DEFAULT '5' AFTER `list_rating`  ");
                    JFactory::getDBO()->query();
                }
                if(!isset( $tables[JFactory::getDBO()->getPrefix().'contentbuilder_forms']['rand_date_update'] )){
                    JFactory::getDBO()->setQuery("ALTER TABLE `#__contentbuilder_forms` ADD `rand_date_update` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `rating_slots` , ADD INDEX ( `rand_date_update` )   ");
                    JFactory::getDBO()->query();
                }
                if(!isset( $tables[JFactory::getDBO()->getPrefix().'contentbuilder_forms']['rand_update'] )){
                    JFactory::getDBO()->setQuery("ALTER TABLE `#__contentbuilder_forms` ADD `rand_update` INT NOT NULL DEFAULT '86400' AFTER `rand_date_update` ");
                    JFactory::getDBO()->query();
                }
                if(!isset( $tables[JFactory::getDBO()->getPrefix().'contentbuilder_forms']['article_record_impact_publish'] )){
                    JFactory::getDBO()->setQuery("ALTER TABLE `#__contentbuilder_forms` ADD `article_record_impact_publish` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `rand_update` , ADD `article_record_impact_language` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `article_record_impact_publish` ");
                    JFactory::getDBO()->query();
                }
                if(!isset( $tables[JFactory::getDBO()->getPrefix().'contentbuilder_forms']['allow_external_filter'] )){
                    JFactory::getDBO()->setQuery("ALTER TABLE `#__contentbuilder_forms` ADD `allow_external_filter` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `article_record_impact_language` ");
                    JFactory::getDBO()->query();
                }
                if(!isset( $tables[JFactory::getDBO()->getPrefix().'contentbuilder_forms']['show_filter'] )){
                    JFactory::getDBO()->setQuery("ALTER TABLE `#__contentbuilder_forms` ADD `show_filter` TINYINT( 1 ) NOT NULL DEFAULT '1' AFTER `allow_external_filter`  ");
                    JFactory::getDBO()->query();
                }
                if(!isset( $tables[JFactory::getDBO()->getPrefix().'contentbuilder_forms']['show_records_per_page'] )){
                    JFactory::getDBO()->setQuery("ALTER TABLE `#__contentbuilder_forms` ADD `show_records_per_page` TINYINT( 1 ) NOT NULL DEFAULT '1' AFTER `show_filter`  ");
                    JFactory::getDBO()->query();
                }
                if(!isset( $tables[JFactory::getDBO()->getPrefix().'contentbuilder_forms']['initial_list_limit'] )){
                    JFactory::getDBO()->setQuery("ALTER TABLE `#__contentbuilder_forms` ADD `initial_list_limit` TINYINT NOT NULL DEFAULT '20' AFTER `show_records_per_page` ");
                    JFactory::getDBO()->query();
                    
                    // exceptionally here
                    
                    JFactory::getDBO()->setQuery("ALTER TABLE `#__contentbuilder_articles` ADD INDEX ( `type` )");
                    JFactory::getDBO()->query();
                    
                    JFactory::getDBO()->setQuery("ALTER TABLE `#__contentbuilder_records` ADD INDEX ( `type` )");
                    JFactory::getDBO()->query();
                    
                } 
                if(!isset( $tables[JFactory::getDBO()->getPrefix().'contentbuilder_forms']['tag'] )){
                    JFactory::getDBO()->setQuery("ALTER TABLE `#__contentbuilder_forms` ADD `tag` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `name`, ADD INDEX ( `tag` ) ");
                    JFactory::getDBO()->query();
                }
                if(!isset( $tables[JFactory::getDBO()->getPrefix().'contentbuilder_forms']['save_button_title'] )){
                    JFactory::getDBO()->setQuery("ALTER TABLE `#__contentbuilder_forms` ADD `save_button_title` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `initial_list_limit` , ADD `apply_button_title` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `save_button_title` ");
                    JFactory::getDBO()->query();
                }  
                if(!isset( $tables[JFactory::getDBO()->getPrefix().'contentbuilder_forms']['filter_exact_match'] )){
                    JFactory::getDBO()->setQuery("ALTER TABLE `#__contentbuilder_forms` ADD `filter_exact_match` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `save_button_title`");
                    JFactory::getDBO()->query();
                }
                if(!isset( $tables[JFactory::getDBO()->getPrefix().'contentbuilder_forms']['initial_sort_order2'] )){
                    JFactory::getDBO()->setQuery("ALTER TABLE `#__contentbuilder_forms` ADD `initial_sort_order2` VARCHAR( 255 ) NOT NULL DEFAULT '-1' AFTER `initial_sort_order` , ADD `initial_sort_order3` VARCHAR( 255 ) NOT NULL DEFAULT '-1' AFTER `initial_sort_order2` ");
                    JFactory::getDBO()->query();
                }
            }
            // elements updates
            if(isset($tables[JFactory::getDBO()->getPrefix().'contentbuilder_elements'])){
                if(!isset( $tables[JFactory::getDBO()->getPrefix().'contentbuilder_elements']['order_type'] )){
                    JFactory::getDBO()->setQuery("ALTER TABLE `#__contentbuilder_elements` ADD `order_type` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `published` ");
                    JFactory::getDBO()->query();
                }
            }
            // records updates
            if(isset($tables[JFactory::getDBO()->getPrefix().'contentbuilder_records'])){
                if(!isset( $tables[JFactory::getDBO()->getPrefix().'contentbuilder_records']['rating_sum'] )){
                    JFactory::getDBO()->setQuery("ALTER TABLE `#__contentbuilder_records` ADD `rating_sum` INT( 10 ) NOT NULL DEFAULT '0' AFTER `is_future` ");
                    JFactory::getDBO()->query();
                }
                if(!isset( $tables[JFactory::getDBO()->getPrefix().'contentbuilder_records']['rating_count'] )){
                    JFactory::getDBO()->setQuery("ALTER TABLE `#__contentbuilder_records` ADD `rating_count` INT( 10 ) NOT NULL DEFAULT '0' AFTER `rating_sum` ");
                    JFactory::getDBO()->query();
                }
                if(!isset( $tables[JFactory::getDBO()->getPrefix().'contentbuilder_records']['lastip'] )){
                    JFactory::getDBO()->setQuery("ALTER TABLE `#__contentbuilder_records` ADD `lastip` VARCHAR( 50 ) NOT NULL DEFAULT '' AFTER `rating_count`  ");
                    JFactory::getDBO()->query();
                }
                if(!isset( $tables[JFactory::getDBO()->getPrefix().'contentbuilder_records']['type'] )){
                    JFactory::getDBO()->setQuery("ALTER TABLE `#__contentbuilder_records` ADD `type` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `id` ");
                    JFactory::getDBO()->query();
                }
                if(!isset( $tables[JFactory::getDBO()->getPrefix().'contentbuilder_records']['session_id'] )){
                    JFactory::getDBO()->setQuery("ALTER TABLE `#__contentbuilder_records` ADD `session_id` VARCHAR( 32 ) NOT NULL DEFAULT '' AFTER `lastip` ");
                    JFactory::getDBO()->query();
                }
                if(!isset( $tables[JFactory::getDBO()->getPrefix().'contentbuilder_records']['rand_date'] )){
                    JFactory::getDBO()->setQuery("ALTER TABLE `#__contentbuilder_records` ADD `rand_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `published` , ADD INDEX ( `rand_date` ) ");
                    JFactory::getDBO()->query();
                }
                if(!isset( $tables[JFactory::getDBO()->getPrefix().'contentbuilder_records']['metadesc'] )){
                    JFactory::getDBO()->setQuery("ALTER TABLE `#__contentbuilder_records` ADD `metakey` TEXT NOT NULL ,ADD `metadesc` TEXT NOT NULL ,ADD `robots` VARCHAR( 255 ) NOT NULL DEFAULT '',ADD `author` VARCHAR( 255 ) NOT NULL DEFAULT '',ADD `rights` VARCHAR( 255 ) NOT NULL DEFAULT '',ADD `xreference` VARCHAR( 255 ) NOT NULL DEFAULT ''");
                    JFactory::getDBO()->query();
                }
            }
            // element access
            if(isset($tables[JFactory::getDBO()->getPrefix().'contentbuilder_resource_access'])){
               if(!isset( $tables[JFactory::getDBO()->getPrefix().'contentbuilder_resource_access']['type'] )){
                    
                    // sorry but we have to truncate the table
                    JFactory::getDBO()->setQuery("TRUNCATE TABLE `#__contentbuilder_resource_access`");
                    JFactory::getDBO()->query();
                    
                    JFactory::getDBO()->setQuery("ALTER TABLE `#__contentbuilder_resource_access` ADD `type` VARCHAR( 100 ) NOT NULL DEFAULT '' FIRST  ");
                    JFactory::getDBO()->query();
                    
                    JFactory::getDBO()->setQuery("ALTER TABLE `#__contentbuilder_resource_access` DROP INDEX `form_id` ");
                    JFactory::getDBO()->query();
                    
                    JFactory::getDBO()->setQuery("ALTER TABLE `#__contentbuilder_resource_access` ADD UNIQUE ( `type` , `element_id` , `resource_id` )");
                    JFactory::getDBO()->query();
                } 
            }
            
            // switching to ints for record_id and reference_id
            if(isset($tables[JFactory::getDBO()->getPrefix().'contentbuilder_verifications'])){
                if(!isset( $tables[JFactory::getDBO()->getPrefix().'contentbuilder_verifications']['create_invoice'] )){
                    
                    JFactory::getDBO()->setQuery("ALTER TABLE `#__contentbuilder_verifications` ADD `create_invoice` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `verification_data` ");
                    JFactory::getDBO()->query();
                    
                    JFactory::getDBO()->setQuery("ALTER TABLE `#__contentbuilder_articles` CHANGE `reference_id` `reference_id` INT NOT NULL DEFAULT '0', CHANGE `record_id` `record_id` BIGINT NOT NULL DEFAULT '0'");
                    JFactory::getDBO()->query();
                    
                    JFactory::getDBO()->setQuery("ALTER TABLE `#__contentbuilder_elements` CHANGE `reference_id` `reference_id` INT NOT NULL DEFAULT '0'");
                    JFactory::getDBO()->query();
                    
                    JFactory::getDBO()->setQuery("ALTER TABLE `#__contentbuilder_forms` CHANGE `reference_id` `reference_id` INT NOT NULL DEFAULT '0'");
                    JFactory::getDBO()->query();
                    
                    JFactory::getDBO()->setQuery("ALTER TABLE `#__contentbuilder_list_records` CHANGE `record_id` `record_id` BIGINT NOT NULL DEFAULT '0', CHANGE `reference_id` `reference_id` INT NOT NULL DEFAULT '0'");
                    JFactory::getDBO()->query();
                    
                    JFactory::getDBO()->setQuery("ALTER TABLE `#__contentbuilder_rating_cache` CHANGE `record_id` `record_id` BIGINT NOT NULL DEFAULT '0'");
                    JFactory::getDBO()->query();
                    
                    JFactory::getDBO()->setQuery("ALTER TABLE `#__contentbuilder_registered_users` CHANGE `record_id` `record_id` BIGINT NOT NULL DEFAULT '0'");
                    JFactory::getDBO()->query();
                    
                    JFactory::getDBO()->setQuery("ALTER TABLE `#__contentbuilder_records` CHANGE `record_id` `record_id` BIGINT NOT NULL DEFAULT '0', CHANGE `reference_id` `reference_id` INT NOT NULL DEFAULT '0'");
                    JFactory::getDBO()->query();
                    
                    JFactory::getDBO()->setQuery("ALTER TABLE `#__contentbuilder_resource_access` CHANGE `element_id` `element_id` INT NOT NULL DEFAULT '0'");
                    JFactory::getDBO()->query();
                }
            }
            
            // rating cache
            if(!isset($tables[JFactory::getDBO()->getPrefix().'contentbuilder_rating_cache'])){
                JFactory::getDBO()->setQuery("CREATE TABLE `#__contentbuilder_rating_cache` (
                  `record_id` varchar(255) NOT NULL DEFAULT '',
                  `form_id` int(11) NOT NULL DEFAULT '0',
                  `ip` varchar(50) NOT NULL DEFAULT '',
                  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                  KEY `record_id` (`record_id`,`form_id`,`ip`),
                  KEY `date` (`date`)
                ) ;");
                JFactory::getDBO()->query();
            }
        
            // trying to ease the 0.9 update pain
            $db->setQuery("Select `type`,`reference_id` From #__contentbuilder_forms");
            $typeref = $db->loadAssocList();
            foreach($typeref As $tr){
                $db->setQuery("Update #__contentbuilder_records Set `type` = " . $db->Quote($tr['type'])." Where `type` = '' And reference_id = ".$db->Quote($tr['reference_id']));
                $db->query();
            }
            
            jimport('joomla.filesystem.file');
            jimport('joomla.filesystem.folder');
            jimport('joomla.version');
            
            // cleaning up leftovers (additional list view overrides, that didn't work with J! 1.7
            for($a = 1; $a <= 5; $a++){
                if(JFile::exists(JPATH_SITE.DS.'components'.DS.'com_contentbuilder'.DS.'views'.DS.'list'.DS.'tmpl'.DS.'custom'.$a.'.php')){
                    JFile::delete(JPATH_SITE.DS.'components'.DS.'com_contentbuilder'.DS.'views'.DS.'list'.DS.'tmpl'.DS.'custom'.$a.'.php');
                }
                if(JFile::exists(JPATH_SITE.DS.'components'.DS.'com_contentbuilder'.DS.'views'.DS.'list'.DS.'tmpl'.DS.'custom'.$a.'.xml')){
                    JFile::delete(JPATH_SITE.DS.'components'.DS.'com_contentbuilder'.DS.'views'.DS.'list'.DS.'tmpl'.DS.'custom'.$a.'.xml');
                }
            }
            
            $version = new JVersion();
            $plugins = $this->getPlugins();
            
            $base_path = JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'plugins';
            
            $folders = JFolder::folders($base_path);
            
            $installer = new JInstaller();
            
            foreach( $folders As $folder ){
                echo 'Installing plugin <b>' . $folder . '</b><br/>';
                $success = $installer->install( $base_path . DS . $folder );
                if(!$success){
                    echo 'Install failed for plugin <b>' . $folder . '</b><br/>';
                }
                echo '<hr/>';
            }
            
            foreach($plugins As $folder => $subplugs){
                foreach($subplugs As $plugin){
                    if(version_compare($version->getShortVersion(), '1.6', '>=')){
                        $db->setQuery('Update #__extensions Set `enabled` = 1 WHERE `type` = "plugin" AND `element` = "'.$plugin.'" AND `folder` = "'.$folder.'"');
                    } else {
                        $db->setQuery('Update #__plugins Set `published` = 1 WHERE `element` = "'.$plugin.'" AND `folder` = "'.$folder.'"');
                    }
                    $db->query();
                    echo 'Published plugin ' . $plugin . '<hr/>';
                }
            }
            
        }
        
	/**
	 * method to install the component
	 *
	 * @return void
	 */
	function install($parent) 
	{
            if(!version_compare(PHP_VERSION, '5.2.0', '>=')){
                echo '<b style="color:red">WARNING: YOU ARE RUNNING PHP VERSION "'.PHP_VERSION.'". ContentBuilder WON\'T WORK WITH THIS VERSION. PLEASE UPGRADE TO AT LEAST PHP 5.2.0, SORRY BUT YOU BETTER UNINSTALL THIS COMPONENT NOW!</b>';
            }
            
            contentbuilder_install_db();
            
            $this->installAndUpdate();
	}
        
        /**
	 * method to update the component
	 *
	 * @return void
	 */
	function update($parent) 
	{
            if(!version_compare(PHP_VERSION, '5.2.0', '>=')){
                echo '<b style="color:red">WARNING: YOU ARE RUNNING PHP VERSION "'.PHP_VERSION.'". ContentBuilder WON\'T WORK WITH THIS VERSION. PLEASE UPGRADE TO AT LEAST PHP 5.2.0, SORRY BUT YOU BETTER UNINSTALL THIS COMPONENT NOW!</b>';
            }
            
            $this->installAndUpdate();
        }
 
	/**
	 * method to uninstall the component
	 *
	 * @return void
	 */
	function uninstall($parent) 
	{
            jimport('joomla.version');
            $version = new JVersion();
            
            $db = JFactory::getDBO();
            
            $db->setQuery("Delete From #__menu Where `link` Like 'index.php?option=com_contentbuilder%'");
            $db->query();
            
            $plugins = $this->getPlugins();
            
            $installer = new JInstaller();
            
            foreach($plugins As $folder => $subplugs){
                foreach($subplugs As $plugin){
                    if(version_compare($version->getShortVersion(), '1.6', '>=')){
                        $db->setQuery('SELECT `extension_id` FROM #__extensions WHERE `type` = "plugin" AND `element` = "'.$plugin.'" AND `folder` = "'.$folder.'"');
                    } else {
                        $db->setQuery('SELECT `id` FROM #__plugins WHERE `element` = "'.$plugin.'" AND `folder` = "'.$folder.'"');
                    }

                    $id = $db->loadResult();
                    
                    if($id)
                    {
                        $installer->uninstall('plugin',$id,1);
                    } 
                }
            }
            
            $db = JFactory::getDBO();
            $db->setQuery("Select id From `#__menu` Where `alias` = 'root'");
            if(!$db->loadResult()){
                $db->setQuery("INSERT INTO `#__menu` VALUES(1, '', 'Menu_Item_Root', 'root', '', '', '', '', 1, 0, 0, 0, 0, 0, '0000-00-00 00:00:00', 0, 0, '', 0, '', 0, ( Select mlftrgt From (Select max(mlft.rgt)+1 As mlftrgt From #__menu As mlft) As tbone ), 0, '*', 0)");
                $db->query();
            }
	}
 
	/**
	 * method to run before an install/update/uninstall method
	 *
	 * @return void
	 */
	function preflight($type, $parent) 
	{
            $db = JFactory::getDBO();
            $db->setQuery("Select id From `#__menu` Where `alias` = 'root'");
            if(!$db->loadResult()){
                $db->setQuery("INSERT INTO `#__menu` VALUES(1, '', 'Menu_Item_Root', 'root', '', '', '', '', 1, 0, 0, 0, 0, 0, '0000-00-00 00:00:00', 0, 0, '', 0, '', 0, ( Select mlftrgt From (Select max(mlft.rgt)+1 As mlftrgt From #__menu As mlft) As tbone ), 0, '*', 0)");
                $db->query();
            }
	}
 
	/**
	 * method to run after an install/update/uninstall method
	 *
	 * @return void
	 */
	function postflight($type, $parent) 
	{
            $db = JFactory::getDBO();
            
            $db->setQuery("Select id From `#__menu` Where `alias` = 'root'");
            if(!$db->loadResult()){
                $db->setQuery("INSERT INTO `#__menu` VALUES(1, '', 'Menu_Item_Root', 'root', '', '', '', '', 1, 0, 0, 0, 0, 0, '0000-00-00 00:00:00', 0, 0, '', 0, '', 0, ( Select mlftrgt From (Select max(mlft.rgt)+1 As mlftrgt From #__menu As mlft) As tbone ), 0, '*', 0)");
                $db->query();
            }
            
            $db->setQuery("Update #__menu Set `title` = 'COM_CONTENTBUILDER' Where `alias`='contentbuilder'");
            $db->query();
            
            // try to restore the main menu items if they got lost
            
            $db->setQuery("Select component_id From #__menu Where `link`='index.php?option=com_contentbuilder' And parent_id = 1");
            $result = $db->loadResult();

            if(!$result) {
                
                $db->setQuery("Select extension_id From #__extensions Where `type` = 'component' And `element` = 'com_contentbuilder'");
                $comp_id = $db->loadResult();
                
                if($comp_id){
                    
                    $db->setQuery("INSERT INTO `#__menu` (`menutype`, `title`, `alias`, `note`, `path`, `link`, `type`, `published`, `parent_id`, `level`, `component_id`, `ordering`, `checked_out`, `checked_out_time`, `browserNav`, `access`, `img`, `template_style_id`, `params`, `lft`, `rgt`, `home`, `language`, `client_id`) VALUES ('main', 'COM_CONTENTBUILDER', 'contentbuilder', '', 'contentbuilder', 'index.php?option=com_contentbuilder', 'component', 0, 1, 1, ".$comp_id.", 0, 0, '0000-00-00 00:00:00', 0, 1, 'components/com_contentbuilder/views/logo_icon_cb.png', 0, '', ( Select mlftrgt From (Select max(mlft.rgt)+1 As mlftrgt From #__menu As mlft) As tbone ),( Select mrgtrgt From (Select max(mrgt.rgt)+2 As mrgtrgt From #__menu As mrgt) As filet ), 0, '', 1)");
                    $db->query();
                    $parent_id = $db->insertid();
                    
                    $db->setQuery("INSERT INTO `#__menu` (`menutype`, `title`, `alias`, `note`, `path`, `link`, `type`, `published`, `parent_id`, `level`, `component_id`, `ordering`, `checked_out`, `checked_out_time`, `browserNav`, `access`, `img`, `template_style_id`, `params`, `lft`, `rgt`, `home`, `language`, `client_id`) VALUES ('main', 'COM_CONTENTBUILDER_STORAGES', 'comcontentbuilderstorages', '', 'contentbuilder/comcontentbuilderstorages', 'index.php?option=com_contentbuilder&controller=storages', 'component', 0, ".$parent_id.", 2, ".$comp_id.", 0, 0, '0000-00-00 00:00:00', 0, 1, 'components/com_contentbuilder/views/logo_icon_cb.png', 0, '', ( Select mlftrgt From (Select max(mlft.rgt)+1 As mlftrgt From #__menu As mlft) As tbone ),( Select mrgtrgt From (Select max(mrgt.rgt)+2 As mrgtrgt From #__menu As mrgt) As filet ), 0, '', 1)");
                    $db->query();
                    
                    $db->setQuery("INSERT INTO `#__menu` (`menutype`, `title`, `alias`, `note`, `path`, `link`, `type`, `published`, `parent_id`, `level`, `component_id`, `ordering`, `checked_out`, `checked_out_time`, `browserNav`, `access`, `img`, `template_style_id`, `params`, `lft`, `rgt`, `home`, `language`, `client_id`) VALUES('main', 'COM_CONTENTBUILDER_LIST', 'comcontentbuilderlist', '', 'contentbuilder/comcontentbuilderlist', 'index.php?option=com_contentbuilder&controller=forms', 'component', 0, ".$parent_id.", 2, ".$comp_id.", 0, 0, '0000-00-00 00:00:00', 0, 1, 'components/com_contentbuilder/views/logo_icon_cb.png', 0, '', ( Select mlftrgt From (Select max(mlft.rgt)+1 As mlftrgt From #__menu As mlft) As tbone ),( Select mrgtrgt From (Select max(mrgt.rgt)+2 As mrgtrgt From #__menu As mrgt) As filet ), 0, '', 1)");
                    $db->query();
                    
                    $db->setQuery("INSERT INTO `#__menu` (`menutype`, `title`, `alias`, `note`, `path`, `link`, `type`, `published`, `parent_id`, `level`, `component_id`, `ordering`, `checked_out`, `checked_out_time`, `browserNav`, `access`, `img`, `template_style_id`, `params`, `lft`, `rgt`, `home`, `language`, `client_id`) VALUES('main', 'Try BreezingForms!', 'try-breezingforms', '', 'contentbuilder/try-breezingforms', 'index.php?option=com_contentbuilder&view=contentbuilder&market=true', 'component', 0, ".$parent_id.", 2, ".$comp_id.", 0, 0, '0000-00-00 00:00:00', 0, 1, 'class:component', 0, '', ( Select mlftrgt From (Select max(mlft.rgt)+1 As mlftrgt From #__menu As mlft) As tbone ),( Select mrgtrgt From (Select max(mrgt.rgt)+2 As mrgtrgt From #__menu As mrgt) As filet ), 0, '', 1)");
                    $db->query();
                    
                    $db->setQuery("INSERT INTO `#__menu` (`menutype`, `title`, `alias`, `note`, `path`, `link`, `type`, `published`, `parent_id`, `level`, `component_id`, `ordering`, `checked_out`, `checked_out_time`, `browserNav`, `access`, `img`, `template_style_id`, `params`, `lft`, `rgt`, `home`, `language`, `client_id`) VALUES('main', 'COM_CONTENTBUILDER_ABOUT', 'comcontentbuilderabout', '', 'contentbuilder/comcontentbuilderabout', 'index.php?option=com_contentbuilder&view=contentbuilder', 'component', 0, ".$parent_id.", 2, ".$comp_id.", 0, 0, '0000-00-00 00:00:00', 0, 1, 'class:component', 0, '', ( Select mlftrgt From (Select max(mlft.rgt)+1 As mlftrgt From #__menu As mlft) As tbone ),( Select mrgtrgt From (Select max(mrgt.rgt)+2 As mrgtrgt From #__menu As mrgt) As filet ), 0, '', 1)");
                    $db->query();
                    
                    $db->setQuery("Select max(mrgt.rgt)+1 From #__menu As mrgt");
                    $rgt = $db->loadResult();
                    
                    $db->setQuery("Update `#__menu` Set rgt = ".$rgt." Where `title` = 'Menu_Item_Root' And `alias` = 'root'");
                    $db->query();
                }
            }
	}
}

