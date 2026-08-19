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
 * Screen recording storage helper for the quizaccess_invigilator plugin.
 *
 * @package    quizaccess_invigilator
 * @copyright  2021 Brain Station 23
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quizaccess_invigilator;

use context;
use context_module;
use moodle_url;
use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Stores and retrieves the screen frames captured during a quiz attempt.
 *
 * Video of a whole exam is far too large to keep, so the screen is sampled instead: one
 * compressed image every few seconds, all frames of an attempt sharing a session id. The
 * report plays the frames of a session back in order, which watches like a time lapse of
 * the attempt while costing a fraction of the storage a video would.
 *
 * @package    quizaccess_invigilator
 * @copyright  2021 Brain Station 23
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class recording_manager {

    /** @var string Table holding one row per stored frame. */
    const TABLE = 'quizaccess_invigilator_rec';

    /** @var string File area the frames are stored in. */
    const FILEAREA = 'recording';

    /** @var array Fallbacks used until the admin settings have been saved for the first time. */
    const DEFAULTS = [
        'enablerecording' => 1,
        'recordinginterval' => 10,
        'recordingwidth' => 1280,
        'recordingquality' => 60,
        'recordingmaxsize' => 2,
        'recordingretention' => 0,
    ];

    /** @var array Mime types we accept from the browser, mapped to a file extension. */
    const MIMETYPES = [
        'image/jpeg' => 'jpg',
        'image/webp' => 'webp',
        'image/png' => 'png',
    ];

    /**
     * Work out the base mime type the browser reported.
     *
     * @param string $mimetype for example "image/jpeg".
     * @return string a mime type we know about, defaulting to image/jpeg.
     */
    public static function normalise_mimetype(string $mimetype): string {
        $base = strtolower(trim(explode(';', $mimetype)[0]));
        return isset(self::MIMETYPES[$base]) ? $base : 'image/jpeg';
    }

    /**
     * File extension to use for a mime type.
     *
     * @param string $mimetype
     * @return string
     */
    public static function extension_for(string $mimetype): string {
        return self::MIMETYPES[self::normalise_mimetype($mimetype)];
    }

    /**
     * One recording setting, falling back to the default while the settings are still unsaved.
     *
     * @param string $name one of the keys in self::DEFAULTS.
     * @return int
     */
    public static function get_setting(string $name): int {
        $value = get_config('quizaccess_invigilator', $name);
        if ($value === false || $value === null || $value === '') {
            return self::DEFAULTS[$name];
        }
        return (int)$value;
    }

    /**
     * Largest frame we accept, in bytes.
     *
     * @return int
     */
    public static function get_max_frame_size(): int {
        $configured = self::get_setting('recordingmaxsize');
        if ($configured <= 0) {
            $configured = self::DEFAULTS['recordingmaxsize'];
        }
        return $configured * 1024 * 1024;
    }

    /**
     * Store one captured frame and return the row it was logged as.
     *
     * @param int $courseid
     * @param int $cmid course module id of the quiz.
     * @param int $quizid
     * @param int $userid the user being recorded.
     * @param string $sessionid groups all frames of one attempt together.
     * @param int $sequence zero based position of this frame in the session.
     * @param string $mimetype mime type reported by the browser.
     * @param int $duration seconds this frame stands for, that is the capture interval.
     * @param string $binary the raw image data.
     * @return stdClass the stored record, with ->recording holding the pluginfile url.
     */
    public static function store_frame(int $courseid, int $cmid, int $quizid, int $userid, string $sessionid,
            int $sequence, string $mimetype, int $duration, string $binary): stdClass {
        global $DB;

        $mimetype = self::normalise_mimetype($mimetype);
        $context = context_module::instance($cmid);

        $record = new stdClass();
        $record->courseid = $courseid;
        $record->cmid = $cmid;
        $record->quizid = $quizid;
        $record->userid = $userid;
        $record->sessionid = $sessionid;
        $record->sequence = $sequence;
        $record->filename = '';
        $record->recording = '';
        $record->mimetype = $mimetype;
        $record->duration = $duration;
        $record->filesize = strlen($binary);
        $record->timecreated = time();
        $record->id = $DB->insert_record(self::TABLE, $record, true);

        $record->filename = 'frame-' . $sessionid . '-' . str_pad((string)$sequence, 5, '0', STR_PAD_LEFT)
            . '.' . self::extension_for($mimetype);

        $filerecord = (object)[
            'contextid' => $context->id,
            'component' => 'quizaccess_invigilator',
            'filearea' => self::FILEAREA,
            'itemid' => $record->id,
            'filepath' => '/',
            'filename' => $record->filename,
            'userid' => $userid,
            'mimetype' => $mimetype,
            'license' => '',
            'author' => '',
        ];

        $fs = get_file_storage();
        $existing = $fs->get_file($context->id, 'quizaccess_invigilator', self::FILEAREA, $record->id, '/', $record->filename);
        if ($existing) {
            $existing->delete();
        }
        $fs->create_file_from_string($filerecord, $binary);

        $record->recording = self::get_frame_url($context->id, $record->id, $record->filename)->out(false);

        $DB->update_record(self::TABLE, $record);

        return $record;
    }

    /**
     * Url the given frame is served from.
     *
     * @param int $contextid
     * @param int $itemid
     * @param string $filename
     * @return moodle_url
     */
    public static function get_frame_url(int $contextid, int $itemid, string $filename): moodle_url {
        return moodle_url::make_pluginfile_url($contextid, 'quizaccess_invigilator', self::FILEAREA,
            $itemid, '/', $filename, false);
    }

    /**
     * All recording sessions of a quiz, newest first, one row per session.
     *
     * @param int $cmid course module id of the quiz.
     * @param int|null $userid limit to a single user, or null for everybody.
     * @return array of objects with sessionid, userid, user name fields, frames, duration, filesize, timestart, timeend.
     */
    public static function get_sessions(int $cmid, ?int $userid = null): array {
        global $DB;

        $params = ['cmid' => $cmid];
        $where = 'r.cmid = :cmid';
        if ($userid) {
            $where .= ' AND r.userid = :userid';
            $params['userid'] = $userid;
        }

        // Select every name field the site might display, so fullname() has all it needs.
        $namefields = \core_user\fields::get_name_fields();
        $usernamefields = 'u.' . implode(', u.', $namefields) . ', u.email';
        $sql = "SELECT r.sessionid, r.userid, r.courseid, r.quizid, $usernamefields,
                       COUNT(r.id) AS frames,
                       SUM(r.duration) AS duration,
                       SUM(r.filesize) AS filesize,
                       MIN(r.timecreated) AS timestart,
                       MAX(r.timecreated) AS timeend
                  FROM {" . self::TABLE . "} r
                  JOIN {user} u ON u.id = r.userid
                 WHERE $where
              GROUP BY r.sessionid, r.userid, r.courseid, r.quizid, $usernamefields
              ORDER BY MIN(r.timecreated) DESC";

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * All frames of one session in playback order.
     *
     * @param int $cmid
     * @param string $sessionid
     * @return array of records from the recording table.
     */
    public static function get_frames(int $cmid, string $sessionid): array {
        global $DB;

        return $DB->get_records(self::TABLE, ['cmid' => $cmid, 'sessionid' => $sessionid], 'sequence ASC, id ASC');
    }

    /**
     * Delete every frame of one session, both the rows and the stored files.
     *
     * @param string $sessionid
     * @param int $cmid
     * @return int number of frames deleted.
     */
    public static function delete_session(string $sessionid, int $cmid): int {
        global $DB;

        $records = $DB->get_records(self::TABLE, ['cmid' => $cmid, 'sessionid' => $sessionid]);
        return self::delete_records($records);
    }

    /**
     * Delete every recording a user has in one quiz.
     *
     * @param int $cmid
     * @param int $userid
     * @return int number of frames deleted.
     */
    public static function delete_for_user(int $cmid, int $userid): int {
        global $DB;

        $records = $DB->get_records(self::TABLE, ['cmid' => $cmid, 'userid' => $userid]);
        return self::delete_records($records);
    }

    /**
     * Delete every recording stored for one quiz.
     *
     * @param int $cmid
     * @return int number of frames deleted.
     */
    public static function delete_for_cm(int $cmid): int {
        global $DB;

        $records = $DB->get_records(self::TABLE, ['cmid' => $cmid]);
        return self::delete_records($records);
    }

    /**
     * Delete recordings that are older than the configured retention period.
     *
     * @param int|null $retentiondays overrides the configured value when given.
     * @return int number of frames deleted.
     */
    public static function purge_expired(?int $retentiondays = null): int {
        global $DB;

        if ($retentiondays === null) {
            $retentiondays = self::get_setting('recordingretention');
        }
        if ($retentiondays <= 0) {
            // Retention disabled, keep everything.
            return 0;
        }

        $cutoff = time() - ($retentiondays * DAYSECS);
        $records = $DB->get_records_select(self::TABLE, 'timecreated < :cutoff', ['cutoff' => $cutoff], 'id ASC', '*', 0, 500);

        return self::delete_records($records);
    }

    /**
     * Delete the given recording rows together with their stored files.
     *
     * @param array $records records from the recording table.
     * @return int number of frames deleted.
     */
    protected static function delete_records(array $records): int {
        global $DB;

        if (empty($records)) {
            return 0;
        }

        $fs = get_file_storage();
        $deleted = 0;
        foreach ($records as $record) {
            try {
                $context = context_module::instance($record->cmid, IGNORE_MISSING);
            } catch (\moodle_exception $e) {
                $context = false;
            }
            if ($context) {
                $fs->delete_area_files($context->id, 'quizaccess_invigilator', self::FILEAREA, $record->id);
            }
            $DB->delete_records(self::TABLE, ['id' => $record->id]);
            $deleted++;
        }

        return $deleted;
    }

    /**
     * Whether screen capture is switched on for the site.
     *
     * @return bool
     */
    public static function is_enabled(): bool {
        return (bool)self::get_setting('enablerecording');
    }

    /**
     * Human readable size, used by the reports.
     *
     * @param int $bytes
     * @return string
     */
    public static function format_size(int $bytes): string {
        return display_size($bytes);
    }
}
