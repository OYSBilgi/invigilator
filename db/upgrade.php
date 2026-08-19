<?php
// This file is part of Moodle invigilator for Moodle - http://moodle.org/
//
// Moodle invigilator is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle invigilator is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with MailTest.  If not, see <http://www.gnu.org/licenses/>.
/**
 * Upgrade steps for the quizaccess_invigilator plugin.
 *
 * @package    quizaccess_invigilator
 * @copyright  2021 Brain Station 23
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade the plugin database.
 *
 * @param int $oldversion the version we are upgrading from.
 * @return bool
 */
function xmldb_quizaccess_invigilator_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026081900) {

        // Table holding the screen recording segments.
        $table = new xmldb_table('quizaccess_invigilator_rec');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('cmid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('quizid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('sessionid', XMLDB_TYPE_CHAR, '64', null, XMLDB_NOTNULL, null, null);
        $table->add_field('sequence', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('filename', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '');
        $table->add_field('recording', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('mimetype', XMLDB_TYPE_CHAR, '64', null, XMLDB_NOTNULL, null, 'video/webm');
        $table->add_field('duration', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('filesize', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
        $table->add_key('quizid', XMLDB_KEY_FOREIGN, ['quizid'], 'quiz', ['id']);

        $table->add_index('sessionid-sequence', XMLDB_INDEX_NOTUNIQUE, ['sessionid', 'sequence']);
        $table->add_index('cmid-userid', XMLDB_INDEX_NOTUNIQUE, ['cmid', 'userid']);
        $table->add_index('timecreated', XMLDB_INDEX_NOTUNIQUE, ['timecreated']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2026081900, 'quizaccess', 'invigilator');
    }

    return true;
}
