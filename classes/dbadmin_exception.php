<?php
/**
 * @package    tool_dbadmin
 * @copyright  2019 Robo Technical Group LLC {@link https://www.robotech.group}, portions 2011 Petr Skoda {@link http://skodak.org/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Exception class for tool_dbadmin
 * @see moodle_exception
 */
class dbadmin_exception extends moodle_exception {
    /**
     * @param string $errorcode Name of string to lookup in string manager.
     * @param string $a Additional error information.
     * @param string $link URI to provide in error message.
     *   Defaults to Moodle admin page.
     * @param string $debuginfo Additional debugging information.
     */
    function __construct($errorcode, $a = null, $link = '',
            $debuginfo = null) {
        global $CFG;
        if (empty($link)) {
            $link = "$CFG->wwwroot/$CFG->admin/";
        }
        parent::__construct($errorcode, 'tool_dbadmin', $link, $a,
            $debuginfo);
    }
}
