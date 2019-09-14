<?php
/**
 * Common functions for cross-platform database administration.
 *
 * @package    tool_dbadmin
 * @copyright  2019 Robo Technical Group LLC {@link http://www.robotech.group}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

// Load required libraries.

// Constants
define('TOOL_DBADMIN_LINE_LENGTH', 75);
define('TOOL_DBADMIN_PARAM_DESC', 'description');
define('TOOL_DBADMIN_PARAM_TYPE', 'type');
define('TOOL_DBADMIN_PARAM_DEFAULT', 'default');
define('TOOL_DBADMIN_PLUGIN_NAME', 'tool_dbadmin');
define('TOOL_DBADMIN_STATUS_COUNT', 5000); // Frequency of updates when processing records

// Constant arrays
// Should Oracle be included in the blacklist?
const TOOL_DBADMIN_DRIVER_BLACKLIST = array(
    'sqlite3',
);


// Define common parameters.
// Individual components can expand on this array.
define('TOOL_DBADMIN_PARAM_MAINT', 'maintenance');
define('TOOL_DBADMIN_PARAM_HELP', 'help');
$paramlist=array(
    TOOL_DBADMIN_PARAM_MAINT =>
        array(
            TOOL_DBADMIN_PARAM_DESC =>
                get_string('maintparamdesc', TOOL_DBADMIN_PLUGIN_NAME),
            TOOL_DBADMIN_PARAM_TYPE => 'string',
            TOOL_DBADMIN_PARAM_DEFAULT => null,
        ),
    TOOL_DBADMIN_PARAM_HELP =>
        array(
            TOOL_DBADMIN_PARAM_DESC =>
                get_string('helpparamdesc', TOOL_DBADMIN_PLUGIN_NAME),
            TOOL_DBADMIN_PARAM_TYPE => null,
            TOOL_DBADMIN_PARAM_DEFAULT => false,
        ),
);


// Load available database drivers.
$dbdrivers = array();
tool_dbadmin_get_drivers();


/**
 * Returns help text for a CLI tool.
 * 
 * @param string $scriptname Name of script.
 * @param string $toolname Descriptive name of tool.
 * @param string $description Description of tool.
 * @param string $example Example of how to call tool.
 * @param array $params Command-line parameters that the tool accepts.
 * @return string Formatted help text for CLI tool.
 */
function tool_dbadmin_build_help(string $scriptname, string $toolname,
        string $description, string $example, array $params) {
    $longestparam = 0;
    foreach ( $params as $key => $value ) {
        $display = tool_dbadmin_format_param( $key, $value );
        if ( strlen( $display ) > $longestparam ) {
            $longestparam = strlen( $display );
        }   // if ( strlen( $key ) > $longestparam )
    }   // for ( $key )
    $leftmargin = $longestparam + 6;
    $toreturn = $scriptname . PHP_EOL .
        $toolname . PHP_EOL .
        PHP_EOL .
        tool_dbadmin_linewrap($description, 0) . PHP_EOL .
        PHP_EOL .
        get_string('options', TOOL_DBADMIN_PLUGIN_NAME) . ':' . PHP_EOL;
    foreach ( $params as $key => $value ) {
        $display = tool_dbadmin_format_param( $key, $value );
        $pad = $longestparam - strlen( $display );
        $toreturn .= '--' . $display .
            str_repeat( ' ', $pad ) . '  ' .
            tool_dbadmin_linewrap($value[TOOL_DBADMIN_PARAM_DESC], $leftmargin) .
            PHP_EOL;
    }   // foreach ( $params )
    $toreturn .= PHP_EOL .
        get_string('example', TOOL_DBADMIN_PLUGIN_NAME) . ':' . PHP_EOL .
        $example . PHP_EOL;

    return $toreturn;
}   // tool_dbadmin_build_help()


/**
 * Formats a parameter for display.
 * 
 * Includes parameter name and data type.
 * @param string $param Name of parameter to display.
 * @param array $attribs Attributes of parameter (from $paramlist array).
 * @return string Formatted string (e.g. "--help", "--outdir=STRING").
 */
function tool_dbadmin_format_param(string $param, array $attribs) {
    if ( is_null($attribs[TOOL_DBADMIN_PARAM_TYPE]) ) {
        return $param;
    } else {
        return $param . '=' . strtoupper($attribs[TOOL_DBADMIN_PARAM_TYPE]);
    }   // if ( ! $attribs[TOOL_DBADMIN_PARAM_TYPE] )
}   // tool_dbadmin_format_param()


/**
 * Populates $dbdrivers.
 * 
 * Populates the global $dbdrivers array with list of database drivers
 * available to this Moodle instance. Adapted slightly from
 * tool_dbtransfer_get_drivers().
 */
function tool_dbadmin_get_drivers() {
    global $CFG, $dbdrivers;

    $files = new RegexIterator(new DirectoryIterator("$CFG->libdir/dml"),
        '|^.*_moodle_database\.php$|');

    foreach ( $files as $file ) {
        $matches = null;
        preg_match('|^([a-z0-9]+)_([a-z]+)_moodle_database\.php$|',
            $file->getFilename(), $matches);
        if ( ! $matches ) {
            continue;
        }   // if ( ! $matches )

        $dbtype = $matches[1];
        $dblibrary = $matches[2];

        foreach ( TOOL_DBADMIN_DRIVER_BLACKLIST as $ignore ) {
            if ($dbtype === $ignore) {
                // Blacklist unfinished drivers.
                continue 2;
            }
        }   // foreach ( $ignore )

        $targetdb = moodle_database::get_driver_instance($dbtype, $dblibrary,
            false);
        if ($targetdb->driver_installed() !== true) {
            continue;
        }   // if ( ! $targetdb->driverinstalled() )

        $driver = $dbtype . '/' . $dblibrary;
        $dbdrivers[$driver] = $targetdb->get_name();
    }   // foreach ( $file )
}   // tool_dbadmin_get_drivers()


/**
 * Returns an array with the parameters of the CLI tool.
 * 
 * Used at the beginning of a tool to collect arguments passed at the command line.
 * @param array $params List of parameters (usually $paramlist).
 * @return array Array with the list of parameters and their default values.
 */
function tool_dbadmin_get_full_params(array $params) {
    $toreturn = array();
    foreach ( $params as $param => $attrs ) {
        $toreturn[ $param ] = $attrs[ TOOL_DBADMIN_PARAM_DEFAULT ];
    }   // foreach ( $param => $attrs )
    return $toreturn;
}   // tool_dbadmin_get_full_params()

/**
 * Returns an array with the "short" version of the parameters of the CLI tool.
 * 
 * Used at the beginning of a tool to collect arguments passed at the
 * command line. Note: If multiple parameters begin with the same letter, only
 * the last parameter in the array will be used.
 * @param array $params List of parameters (usually $paramlist).
 * @return array Array with the list of single-letter parameters and their
 *               corresponding full-length parameters.
 */
function tool_dbadmin_get_short_params(array $params) {
    $toreturn = array();
    foreach ( array_keys( $params ) as $param ) {
        $toreturn[substr($param, 0, 1)] = $param;
    }   // foreach ( $param => $attrs )
    return $toreturn;
}   // tool_dbadmin_get_short_params()


/**
 * Fits a line to the width of the screen.
 * 
 * Fits a line to the width of the screen (defined by
 * TOOL_DBADMIN_LINE_LENGTH) with the specified left margin. Note: The first
 * line does not include the padding for the left margin.
 * @param string $towrap Line to wrap.
 * @param int $leftmargin Number of spaces to include to the left of the text.
 * @return string Formatting string with the given margin.
 */
function tool_dbadmin_linewrap(string $towrap, int $leftmargin = 0) {
    // Remove excess whitespace and line breaks
    $towrap = preg_replace("/\s\s+/", " ", trim($towrap));
    $linelength = TOOL_DBADMIN_LINE_LENGTH - $leftmargin;
    if ( strlen($towrap) <= $linelength ) {
        return $towrap;
    }   // if ( strlen($towrap) ... )

    $start = str_repeat(' ', $leftmargin);
    $toreturn = '';
    $line = 0;
    while ( strlen($towrap) > $linelength ) {
        // Find the last string in the first $linelength characters
        $space = strrpos($towrap, ' ',
            -(strlen($towrap) - $linelength));
        if ( $space === false ) {
            // Could not find an appropriate place to break within $linelength
            // Try to find any whitespace
            $space = strrpos($towrap, ' ');
            if ( $space === false ) {
                // Could not find an appropriate place to break anywhere
                // Print what remains
                break;
            }   // if ( ! $space )
        }   // if ( ! $space )
        
        $toreturn .=
            ( $line == 0 ? '' : $start ) .
            substr($towrap, 0, $space) . PHP_EOL;
        $towrap = substr($towrap, $space + 1);
        $line++;
    }   // while ( strlen($towrap) ... )
    if ( strlen($towrap) > 0 ) {
        $toreturn .= $start . $towrap . PHP_EOL;
    }   // if ( strlen($towrap) )

    // Drop the ending PHP_EOL
    return substr($toreturn, 0, -1);
}   // tool_dbadmin_linewrap()
