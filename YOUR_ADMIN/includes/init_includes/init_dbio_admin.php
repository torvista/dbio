<?php
// -----
// Part of the DataBase I/O Manager (aka DbIo) plugin, created by Cindy Merkin (cindy@vinosdefrutastropicales.com)
// Copyright (c) 2016, Vinos de Frutas Tropicales.
//
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

define('DBIO_CURRENT_VERSION', '1.1.1-beta1');
define('DBIO_CURRENT_UPDATE_DATE', '2017-01-xx');

$version_release_date = DBIO_CURRENT_VERSION . ' (' . DBIO_CURRENT_UPDATE_DATE . ')';

function init_dbio_next_sort ($menu_key) 
{
    global $db;
    $next_sort = $db->Execute('SELECT MAX(sort_order) as max_sort FROM ' . TABLE_ADMIN_PAGES . " WHERE menu_key='$menu_key'");
    return $next_sort->fields['max_sort'] + 1;
}

$configurationGroupTitle = 'Database I/O Manager Settings';
$configuration = $db->Execute("SELECT configuration_group_id FROM " . TABLE_CONFIGURATION_GROUP . " WHERE configuration_group_title = '$configurationGroupTitle' LIMIT 1");
if ($configuration->EOF) {
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION_GROUP . " 
                 (configuration_group_title, configuration_group_description, sort_order, visible) 
                 VALUES ('$configurationGroupTitle', '$configurationGroupTitle', '1', '1');");
    $cgi = $db->Insert_ID(); 
    $db->Execute("UPDATE " . TABLE_CONFIGURATION_GROUP . " SET sort_order = $cgi WHERE configuration_group_id = $cgi;");
} else {
    $cgi = $configuration->fields['configuration_group_id'];
}

// ----
// Record the configuration's current version in the database.
//
if (!defined ('DBIO_MODULE_VERSION')) {
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, set_function) VALUES ('Version/Release Date', 'DBIO_MODULE_VERSION', '" . $version_release_date . "', 'The Database I/O Manager (DbIo) version number and release date.', $cgi, 1, now(), 'trim(')");
 
    $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function ) VALUES ( 'CSV: Delimiter', 'DBIO_CSV_DELIMITER', ',', 'Enter the single character that is used to separate columns within any DbIo CSV file.  To use the tab-character as the delimiter value, enter the word <b>TAB</b>.  (Default: <b>,</b>)', $cgi, 5, now(), NULL, NULL)");

    $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function ) VALUES ( 'CSV: Enclosure', 'DBIO_CSV_ENCLOSURE', '\"', 'Enter the single character used to <em>enclose</em> fields within any DbIo CSV file.  (Default: <b>\"</b>)', $cgi, 6, now(), NULL, NULL)");
  
    $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function ) VALUES ( 'CSV: Escape', 'DBIO_CSV_ESCAPE', '\\\\', 'Enter the single character used as the escape-character within any DbIo CSV file.  (Default: <b>backslash</b>)', $cgi, 7, now(), NULL, NULL)");
  
    $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function ) VALUES ( 'CSV: Encoding', 'DBIO_CHARSET', 'utf8', 'Choose the type of encoding to be associated with DbIo CSV files.  If you use Microsoft&reg; Excel, choose <b>latin1</b>.  (Default: <b>utf8</b>).', $cgi, 10, now(), NULL, 'zen_cfg_select_option(array(\'utf8\', \'latin1\'),')");
  
    $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function ) VALUES ( 'CSV: Import Date Format', 'DBIO_IMPORT_DATE_FORMAT', 'm-d-y', 'Choose the format used for <em>date</em> and <em>datetime</em> fields in any DbIo CSV file.  (Default: <b>m-d-y</b>)', $cgi, 11, now(), NULL, 'zen_cfg_select_option(array(\'m-d-y\', \'d-m-y\', \'y-m-d\'),')");

    $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function ) VALUES ( 'Maximum Execution Time (seconds)', 'DBIO_MAX_EXECUTION_TIME', '60', 'Enter the maximum execution time for a DbIo operation, in seconds (default: 60).', $cgi, 20, now(), NULL, NULL)");

    $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function ) VALUES ( 'Split File: Record Count', 'DBIO_SPLIT_RECORD_COUNT', '2000', 'Sometimes, splitting a .csv file into multiple, smaller files can help if your server is timing out on an <em>import</em> operation or if an exported .csv is too large to download in a single chunk.  Enter the number of records (default: 2000) at which to split these files using the <em>Database I/O Manager</em>.', $cgi, 25, now(), NULL, NULL)");    
    
    $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function ) VALUES ( 'Default File Sort Order', 'DBIO_FILE_SORT_DEFAULT', '3d', 'Choose the default sort-order that the <em>Database I/O Manager</em> uses when displaying the I/O files it has discovered, one of:<br /><br /><b>1a</b>: File Name, ascending<br /><b>1d</b>: File Name, descending<br /><b>2a</b>: File Size, ascending<br /><b>2d</b>: File Size, descending<br /><b>3a</b>: File Date, ascending<br /><b>3d</b>: File Date, descending (default)', $cgi, 26, now(), NULL, 'zen_cfg_select_option(array(\'1a\', \'1d\', \'2a\', \'2d\', \'3a\', \'3d\'),')");

    $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function ) VALUES ( 'Enable Debug?', 'DBIO_DEBUG', 'false', 'Identify whether (true) or not (false, the default) the DbIo debug is to be enabled.  When enabled, <b>all</b> I/O status is written to a <em>dbio-*.log</em> file in your store\'s /YOUR_ADMIN/dbio/logs folder.', $cgi, 600, now(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),')");

    $db->Execute ("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function ) VALUES ( 'Debug Date Format', 'DBIO_DEBUG_DATE_FORMAT', 'Y-m-d H:i:s', 'Enter the formatting string used to timestamp all DbIo log entries.', $cgi, 601, now(), NULL, NULL)");
}

// -----
// Update the configuration table to reflect the current version, if it's not already set.
//
if (!defined ('DBIO_MODULE_VERSION')) {
    define ('DBIO_MODULE_VERSION', '0.0.0');
} elseif (DBIO_MODULE_VERSION != $version_release_date) {
    $db->Execute ("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '" . $version_release_date . "' WHERE configuration_key = 'DBIO_MODULE_VERSION' LIMIT 1");
}

// -----
// Plugin version-specific updates ...
//
if (version_compare (DBIO_MODULE_VERSION, '1.1.0', '<')) {
    $db->Execute ("INSERT IGNORE INTO " . TABLE_CONFIGURATION . " ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function ) VALUES ( '<em>Products</em> Import:  Allow Duplicate Models?', 'DBIO_PRODUCTS_ALLOW_DUPLICATE_MODELS', 'No', 'When performing a <em>Products</em> import, should an imported record be allowed if it would create a product with a duplicated model number?  (Default: <b>No</b>)', $cgi, 100, now(), NULL, 'zen_cfg_select_option(array(\'Yes\', \'No\'),')");
}

// ----
// Create the database tables for the I/O processing.
//
$sql = "CREATE TABLE IF NOT EXISTS " . TABLE_DBIO_STATS . " (
    dbio_stats_id int(11) NOT NULL auto_increment,
    report_name varchar(255) NOT NULL default '',
    action varchar(128) NOT NULL default '',
    record_count int(11) NOT NULL default '0',
    errors int(11) NOT NULL default '0',
    warnings int(11) NOT NULL default '0',  
    inserts int(11) NOT NULL default '0',  
    updates int(11) NOT NULL default '0',
    parse_time float NOT NULL default '0',
    memory_usage int(11) NOT NULL default '0',
    memory_peak_usage int(11) NOT NULL default '0',
    date_added datetime NOT NULL default '0001-01-01 00:00:00',
    PRIMARY KEY  (dbio_stats_id)
) ENGINE=MyISAM";
$db->Execute ($sql);
if (!$sniffer->field_exists (TABLE_DBIO_STATS, 'report_name')) {
    $db->Execute ("ALTER TABLE " . TABLE_DBIO_STATS . " ADD report_name varchar(255) NOT NULL default '' AFTER dbio_stats_id");
}

// -----
// Register the admin-level pages for use.
//
if (function_exists ('zen_page_key_exists')) {
    if (!zen_page_key_exists ('toolsDbIo')) {
        zen_register_admin_page ('toolsDbIo', 'BOX_TOOLS_DBIO', 'FILENAME_DBIO_MANAGER', '', 'tools', 'Y', init_dbio_next_sort ('tools'));
      
    }
    if (!zen_page_key_exists ('configDbIo')) {
        zen_register_admin_page('configDbIo', 'BOX_CONFIGURATION_DBIO', 'FILENAME_CONFIGURATION', "gID=$cgi", 'configuration', 'Y', init_dbio_next_sort ('configuration'));
      
    }
}
