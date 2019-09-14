<?php
/**
 * Export Moodle database to an archive file.
 *
 * @package    tool_dbadmin
 * @copyright  2019 Robo Technical Group LLC {@link http://www.robotech.group}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Load required libraries.
require_once($CFG->libdir . '/dtllib.php');
require_once('dbadmin_exception.php');

// Constants
define('TOOL_DBADMIN_EXPORTER_DB_FILENAME', 'db.xml');
define('TOOL_DBADMIN_EXPORTER_OUTDIR_DEFAULT', $CFG->dataroot .
    DIRECTORY_SEPARATOR . 'backups' .
    DIRECTORY_SEPARATOR . 'db');
define('TOOL_DBADMIN_EXPORTER_COMPRESS_DEFAULT', 'zlib');

// Constant arrays
const TOOL_DBADMIN_EXPORTER_COMPRESSIONS = array(
    'none' => array('', 'file://', ''),
    'gzip' => array('zlib', 'compress.zlib://', '.gz'),
    'zlib' => array('zlib', 'compress.zlib://', '.gz'),
    'bz2' => array('bz2', 'compress.bzip2://', '.bz2'),
    'bzip2' => array('bz2', 'compress.bzip2://', '.bz2'),
);


class exporter extends xml_database_exporter {
    /** @var string Compression scheme. */
    protected $compress;
    /** @var resource File pointer for current table output file. */
    protected $currtablefileptr;
    /** @var string Filepath for current table output file. */
    protected $currtablefilepath;
    /** @var string Filename (only)  for current table output file. */
    protected $currtablefilename;
    /** @var resource File pointer for db output file. */
    protected $dbfileptr;
    /** @var string Filepath for db output file. */
    protected $dbfilepath;
    /** @var progress_trace Progress tracing object. */
    protected $feedback;
    /** @var resource File pointer for current output file. */
    protected $file;
    /** @var string Extension for output files */
    protected $fileext;
    /** @var string File scheme prefix (e.g. file://). */
    protected $filescheme;
    /** @var string Output directory. */
    protected $outdir;
    /** @var bool Place XML elements on individual lines. */
    protected $prettyprint;
    /** @var int Record count for current table. */
    protected $reccount;


    /**
     * Overrides @see xml_database_exporter::_construct()
     * 
     * @param moodle_database $mdb Moodle database to export.
     * @param string $dir Output directory for database export.
     *   Defaults to $CFG->dataroot/db/backup
     * @param string $compression Compression scheme for the export
     *   files. Can be none (or null), gzip (i.e. zlib, default), or bzip2
     *   (bz2 is acceptable, too).
     * @param progress_trace $feedback Progress tracing object.
     *   Default value is null.
     * @param bool $check_schema Indicate whether to check the
     *   database schema before exporting. Default value is true
     *   (i.e. check schema).
     * @param bool $prettyprint Place XML elements on separate lines.
     *   Default value is whether $CFG->debug === DEBUG_DEVELOPER
     * @return exporter
     */
    public function __construct(moodle_database $mdb,
            $dir = TOOL_DBADMIN_EXPORTER_OUTDIR_DEFAULT,
            $compression = 'gzip',
            progress_trace $feedback = null, $check_schema = true,
            $prettyprint = null) {
        global $CFG;
        parent::__construct($mdb, $check_schema);
        $this->outdir = $dir . DIRECTORY_SEPARATOR . date('Ymd.His');
        $this->set_compression_scheme($compression);
        if ( empty($feedback) ) {
            $this->feedback = new null_progress_trace();
        } else {
            $this->feedback = $feedback;
        }   // if ( ! $feedback )
        $this->prettyprint = empty($prettyprint)
            ? (
                $CFG->debug === DEBUG_DEVELOPER
                ? true
                : false
            )
            : $prettyprint;
    }   // __construct()

    /**
     * Overrides @see xml_database_exporter::begin_database_export()
     */
    public function begin_database_export($version, $release, $timestamp,
            $description) {
        global $CFG;
        $this->feedback->output(get_string('begindbexport',
            TOOL_DBADMIN_PLUGIN_NAME,
            ($CFG->debugdisplay == 1
                ? $this->outdir
                : 'export directory')));
        $this->file = $this->dbfileptr;
        parent::begin_database_export($version, $release, $timestamp,
            $description);
    }   // begin_database_export()

    /**
     * Overrides @see xml_database_exporter::begin_table_export()
     */
    public function begin_table_export(xmldb_table $table) {
        $this->feedback->output(get_string('begintableexport',
            TOOL_DBADMIN_PLUGIN_NAME, $table->getName()),1);
        $this->currtablefilename = $table->getName() . $this->fileext;
        $this->currtablefilepath = $this->filescheme .
            $this->outdir . DIRECTORY_SEPARATOR .
            $this->currtablefilename;
        $this->currtablefileptr = fopen(
            $this->currtablefilepath, 'wb');
        if ( $this->currtablefileptr === false ) {
            throw new dbadmin_exception(
                'outdirnotwriteable', $this->dbfilepath);
        }   // if ( ! $this->currtablefileptr )

        $this->reccount = 0;
        $this->file = $this->currtablefileptr;
        parent::begin_table_export($table);
    }   // begin_table_export()

    /**
     * Disable CLI maintenance mode for provided Moodle instance.
     */
    public function disable_maintenance() {
        // TODO: Disable CLI maintenance mode
        $this->feedback->output('TODO: Disable CLI maintenance mode.');
    }   // disable_maintenance()

    /**
     * Enable CLI maintenance mode for provided Moodle instance.
     */
    public function enable_maintenance(string $message = null) {
        // TODO: Enable CLI maintenance mode
        $this->feedback->output('TODO: Enable CLI maintenance mode.');
    }   // enable_maintenance()

    /**
     * Overrides @see database_exporter::export_database()
     */
    public function export_database($description = null) {
        global $CFG;
        if ( ! is_dir($this->outdir) ) {
            if ( $CFG->debugdisplay == 1) {
                $this->feedback->output(get_string('creatingoutdir',
                TOOL_DBADMIN_PLUGIN_NAME, $this->outdir));
            }   // if ( $CFG->debugdisplay )
            mkdir($this->outdir, $CFG->directorypermissions, true);
            if ( ! is_dir($this->outdir) ) {
                throw new dbadmin_exception(
                    'outdirfail', $this->outdir);
            }   // if ( ! is_dir($this->outdir) )
        }   // if ( ! is_dir($this->outdir) )

        $this->dbfilepath = 'file://' .
            $this->outdir . DIRECTORY_SEPARATOR .
            TOOL_DBADMIN_EXPORTER_DB_FILENAME;
        $this->dbfileptr = fopen($this->dbfilepath, 'wb');
        if ( $this->dbfileptr === false ) {
            throw new dbadmin_exception(
                'outdirnotwriteable', $this->dbfilepath);
        }   // if ( ! $this->dbfileptr )

        parent::export_database($description);
    }   // export_database( )

    /**
     * Overrides @see xml_database_exporter::export_table_data()
     */
    public function export_table_data(xmldb_table $table, $data)
    {
        $this->reccount++;
        parent::export_table_data($table, $data);
        if ( $this->reccount % TOOL_DBADMIN_STATUS_COUNT == 0 ) {
            $this->feedback->output(get_string('numrecords',
                TOOL_DBADMIN_PLUGIN_NAME, $this->reccount),2);
        }   // if ( $this->reccount % TOOL_DBADMIN_STATUS_COUNT )
    }   // export_table_data()

    /**
     * Overrides @see xml_database_exporter::finish_database_export()
     */
    public function finish_database_export() {
        global $CFG;
        $this->file = $this->dbfileptr;
        parent::finish_database_export();
        fclose($this->dbfileptr);
        @chmod($this->dbfilepath, $CFG->filepermissions);
    }   // finish_database_export()

    /**
     * Overrides @see xml_database_exporter::finish_table_export()
     */
    public function finish_table_export(xmldb_table $table) {
        global $CFG;
        parent::finish_table_export($table);
        $this->feedback->output(get_string('numrecords',
            TOOL_DBADMIN_PLUGIN_NAME, $this->reccount),2);
        fclose($this->currtablefileptr);
        @chmod($this->currtablefilepath, $CFG->filepermissions);

        // Add element to database file
        fwrite($this->dbfileptr, '<file name="');
        fwrite($this->dbfileptr, $this->currtablefilename);
        fwrite($this->dbfileptr, '" compression="');
        fwrite($this->dbfileptr, $this->compress);
        fwrite($this->dbfileptr, '" reccount="');
        fwrite($this->dbfileptr, $this->reccount);
        fwrite($this->dbfileptr, '" />');
        if ( $this->prettyprint ) {
            fwrite($this->dbfileptr, PHP_EOL);
        }   // if ( $this->prettyprint )
    }   // finish_table_export()

    /**
     * Sets member variables to given compression scheme.
     * @param string $scheme Compression scheme.
     *   Can be none (or null), gzip (i.e. zlib, default), or bzip2
     *   (bz2 is acceptable, too).
     * @return void
     */
    protected function set_compression_scheme($scheme) {
        if ( array_key_exists(strtolower($scheme),
                TOOL_DBADMIN_EXPORTER_COMPRESSIONS) ) {
            $compression = TOOL_DBADMIN_EXPORTER_COMPRESSIONS[
                strtolower($scheme)];
            // Test for required library
            if ( strlen($compression[0]) > 0 && ! extension_loaded($compression[0])) {
                if ( $compression[0] == 'zlib' ) {
                    $this->feedback->output(get_string('nozlib',
                        TOOL_DBADMIN_PLUGIN_NAME));
                    $this->set_compression_scheme('none');
                } else {
                    $this->feedback->output(get_string('extensionnotloaded',
                        TOOL_DBADMIN_PLUGIN_NAME, $scheme));
                    $this->set_compression_scheme('zlib');
                }   // if ( $compression[0] == 'zlib' )
            } else {
                // Library for requested compression scheme is loaded; use it.
                $this->compress = strtolower($scheme);
                $this->filescheme = $compression[1];
                $this->fileext = $compression[2];
            }   // if ( ! extension_loaded(...))
        } else {
            $this->feedback->output(get_string('compressionnotfound',
                TOOL_DBADMIN_PLUGIN_NAME, $scheme));
            $this->set_compression_scheme('zlib');
        }   // if ( array_key_exists($scheme, ...) )
    }   // set_compression_scheme()



    // Protected methods

    /**
     * Implements @see xml_database_exporter::output()
     * 
     * @param string $text String to output.
     * @return void
    */
    protected function output($text) {
        fwrite($this->file, $text);
        if ( $this->prettyprint ) {
            fwrite($this->file, PHP_EOL);
        }   // if ( $this->prettyprint )
    }   // output( )
}   // class exporter