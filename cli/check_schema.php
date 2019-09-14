<?php
/**
 * Report schema errors in Moodle database.
 * Modification of @see database_exporter::export_database()
 * @package    tool_dbadmin
 * @copyright  2019 Robo Technical Group LLC {@link http://www.robotech.group}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

// Load required libraries.
require(__DIR__ . '/../../../../config.php');
require_once($CFG->libdir . '/clilib.php');
require_once(__DIR__ . '/../locallib.php');
require_once(__DIR__ . '/../classes/exporter.php');

// When testing and debugging
if ( $CFG->debug === DEBUG_DEVELOPER ) {
    get_string_manager()->reset_caches();
}   // if ( $CFG->debug )

// Constants
define('TOOL_DBADMIN_SCHEMA_EXEC', 'check_schema.php');

// Collect CLI parameters.
list($cliargs, $unrecognized) = cli_get_params(
    tool_dbadmin_get_full_params($paramlist),
    tool_dbadmin_get_short_params($paramlist)
);

if ( $unrecognized ) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}   // if ( $unrecognized )

if ( $cliargs[TOOL_DBADMIN_PARAM_HELP] ) {
    cli_writeln(tool_dbadmin_build_help(TOOL_DBADMIN_SCHEMA_EXEC,
        get_string('clischemaname', TOOL_DBADMIN_PLUGIN_NAME),
        get_string('clischemadesc', TOOL_DBADMIN_PLUGIN_NAME),
        get_string('clischemaexample', TOOL_DBADMIN_PLUGIN_NAME),
        $paramlist));
    exit(0);
}   // if ( $cliargs[TOOL_DBADMIN_PARAM_HELP] )


$check_options = array('changedcolumns' => false); // Column types may be fixed by transfer.
$mgr = $DB->get_manager();
$schema = $mgr->get_install_xml_schema();
$errors = $mgr->check_database_schema($schema, $check_options);
if ( $errors ) {
    foreach ( $errors as $table => $items ) {
        cli_writeln(get_string('tableheader',
            TOOL_DBADMIN_PLUGIN_NAME, $table));
        foreach ( $items as $item ) {
            cli_write(' ');
            cli_writeln($item);
        }   // foreach ( $item )
    }   // foreach ( $table=>$items )
} else {
    cli_writeln(get_string('cleanschema', TOOL_DBADMIN_PLUGIN_NAME));
}   // if ( $errors )
