<?php
/**
 * Export Moodle database to an archive file.
 *
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
define('TOOL_DBADMIN_EXPORT_EXEC', 'export.php');


// Additional CLI parameters for this tool.
define('TOOL_DBADMIN_EXPORT_OUTDIR_PARAM', 'outdir');
define('TOOL_DBADMIN_EXPORT_COMPRESS_PARAM', 'compress');
$paramlist = array(
    TOOL_DBADMIN_EXPORT_OUTDIR_PARAM =>
        array(
            TOOL_DBADMIN_PARAM_DESC =>
                get_string('outdirparamdesc', TOOL_DBADMIN_PLUGIN_NAME),
            TOOL_DBADMIN_PARAM_TYPE => 'string',
            TOOL_DBADMIN_PARAM_DEFAULT => TOOL_DBADMIN_EXPORTER_OUTDIR_DEFAULT,
        ),
    TOOL_DBADMIN_EXPORT_COMPRESS_PARAM =>
        array(
            TOOL_DBADMIN_PARAM_DESC =>
                get_string('compressparamdesc', TOOL_DBADMIN_PLUGIN_NAME),
            TOOL_DBADMIN_PARAM_TYPE => 'string',
            TOOL_DBADMIN_PARAM_DEFAULT => TOOL_DBADMIN_EXPORTER_COMPRESS_DEFAULT,
        ),
    ) + $paramlist;


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
    cli_writeln(tool_dbadmin_build_help(TOOL_DBADMIN_EXPORT_EXEC,
        get_string('cliexportname', TOOL_DBADMIN_PLUGIN_NAME),
        get_string('cliexportdesc', TOOL_DBADMIN_PLUGIN_NAME),
        get_string('cliexportexample', TOOL_DBADMIN_PLUGIN_NAME),
        $paramlist));
    exit(0);
}   // if ( $cliargs[TOOL_DBADMIN_PARAM_HELP] )


$outdir = $cliargs[TOOL_DBADMIN_EXPORT_OUTDIR_PARAM];
if ( ! is_dir($outdir) ) {
    mkdir($outdir, $CFG->directorypermissions, true);
}   // if ( ! is_dir($outdir) )
if ( ! is_dir($outdir) ) {
    cli_writeln(get_string('outdirfail', TOOL_DBADMIN_PLUGIN_NAME, $outdir));
    exit(1);
}   // if ( ! is_dir($outdir) )

$exporter = new exporter($DB, $outdir,
$cliargs[TOOL_DBADMIN_EXPORT_COMPRESS_PARAM],
new text_progress_trace());

if ( $cliargs[TOOL_DBADMIN_PARAM_MAINT] ) {
    $exporter->enable_maintenance($cliargs[TOOL_DBADMIN_PARAM_MAINT]);
}   // if ( array_key_exists($cliargs, ...) )

try {
    $exporter->export_database();
} finally {
    if ( $cliargs[TOOL_DBADMIN_PARAM_MAINT] ) {
        $exporter->disable_maintenance();
    }   // if ( array_key_exists($cliargs, ...) )
}   // try
