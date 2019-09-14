<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'tool_dbadmin', language 'en'.
 *
 * @package    tool_dbadmin
 * @copyright  2019 Robo Technical Group LLC {@link https://www.robotech.group}, portions 2011 Petr Skoda {@link http://skodak.org/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['dbadmin'] = 'Cross-platform database backup manager';
$string['pluginname'] = 'DB backup manager';


$string['begindbexport'] = 'Exporting database to {$a}.';
$string['begintableexport'] = 'Exporting table {$a}.';
$string['cleanschema'] = 'Schema contains no errors.';
$string['clibmissing'] = 'Note: compression library {$a} is not installed.';
$string['cliexportdesc'] = 'It is strongly advised to disable the web server, ' .
    'activate the CLI maintenance mode, or use the --maintenance' .
    ' flag when using this script.';
$string['cliexportexample'] =
    "\$ sudo -u www-data /usr/bin/php admin/tool/dbadmin/export.php \\
    --maintenance='Database backup in progress.'";
$string['cliexportname'] = 'Database export script';
$string['clischemadesc'] = '';
$string['clischemaexample'] =
    "\$ sudo -u www-data /usr/bin/php admin/tool/dbadmin/check_schema.php";
$string['clischemaname'] = 'Verify Moodle database schema';
$string['compressparamdesc'] = 'Compression used on output files. ' .
    'Can be none, zlib (or gzip), or bzip2. Default is zlib.';
$string['compressionnotfound'] = 'Requested compression scheme {$a} not recognized; ' .
    'reverting to zlib.';
$string['creatingoutdir'] = 'Export directory {$a} does not exist; ' .
    'will attempt to create it.';
$string['extensionnotloaded'] = 'Library for compression scheme {$a} not loaded; ' .
    'reverting to zlib.';
$string['example'] = 'Example';
$string['helpparamdesc'] = 'Show this help message';
$string['maintparamdesc'] = 'Activate CLI maintenance mode with the provided ' .
    'message. Message string is optional.';
$string['nozlib'] = 'Zlib compression is unavailable; not compressing output files.';
$string['numrecords'] = 'Exported {$a} records.';
$string['options'] = 'Options';
$string['outdirfail'] = 'Output directory {$a} does not exist and cannot be ' .
    'created.';
$string['outdirnotwriteable'] = 'Cannot write to output directory {$a}.';
$string['outdirparamdesc'] = 'Directory where backup files will be stored. ' .
    'Default is ' . $CFG->dataroot . DIRECTORY_SEPARATOR .
    'backups' . DIRECTORY_SEPARATOR . 'db.';
$string['tableheader'] = 'Table name: {$a}';
$string['privacy:metadata'] = 'The Database transfer plugin does not store any personal data.';

// Unused strings
$string['clidriverlist'] = 'Available database drivers for migration';
$string['cliheading'] = 'Database migration - make sure nobody is accessing the server during migration!';
$string['climigrationnotice'] = 'Database migration in progress, please wait until the migration completes and server administrator updates configuration and deletes the $CFG->dataroot/climaintenance.html file.';
$string['convertinglogdisplay'] = 'Converting log display actions';
$string['dbexport'] = 'Database export';
$string['enablemaintenance'] = 'Enable maintenance mode';
$string['enablemaintenance_help'] = 'This option enables maintanance mode during and after the database migration, it prevents access of all users until the migration is completed. Please note that administrator has to manually delete $CFG->dataroot/climaintenance.html file after updating config.php settings to resume normal operation.';
$string['exportdata'] = 'Export data';
$string['notargetconectexception'] = 'Can not connect target database, sorry.';
$string['options'] = 'Options';
$string['targetdatabase'] = 'Target database';
$string['targetdatabasenotempty'] = 'Target database must not contain any tables with given prefix!';
$string['transferdata'] = 'Transfer data';
$string['transferdbintro'] = 'This script will transfer the entire contents of this database to another database server. It is often used for migration of data to different database type.';
$string['transferdbtoserver'] = 'Transfer this Moodle database to another server';
$string['transferringdbto'] = 'Transferring this {$a->dbtypefrom} database to {$a->dbtype} database "{$a->dbname}" on "{$a->dbhost}"';
