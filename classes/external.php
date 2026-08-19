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
 * Extrarnal for the quizaccess_invigilator plugin.
 *
 * @package   quizaccess_invigilator
 * @copyright 2021 Brain Station 23
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

// Moodle 4.2 moved the external API classes into the core_external namespace. Alias whichever
// set of classes this site ships so the plugin runs unchanged on Moodle 4.0 and later.
if (class_exists('\\core_external\\external_api')) {
    class_alias('\\core_external\\external_api', 'quizaccess_invigilator_external_api');
    class_alias('\\core_external\\external_function_parameters', 'quizaccess_invigilator_external_params');
    class_alias('\\core_external\\external_value', 'quizaccess_invigilator_external_value');
    class_alias('\\core_external\\external_single_structure', 'quizaccess_invigilator_external_structure');
    class_alias('\\core_external\\external_warnings', 'quizaccess_invigilator_external_warnings');
} else {
    require_once($CFG->libdir . '/externallib.php');
    class_alias('external_api', 'quizaccess_invigilator_external_api');
    class_alias('external_function_parameters', 'quizaccess_invigilator_external_params');
    class_alias('external_value', 'quizaccess_invigilator_external_value');
    class_alias('external_single_structure', 'quizaccess_invigilator_external_structure');
    class_alias('external_warnings', 'quizaccess_invigilator_external_warnings');
}

/**
 * External class.
 *
 * @package quizaccess_invigilator
 * @copyright 2021 Brain Station 23
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quizaccess_invigilator_external extends quizaccess_invigilator_external_api
{

    /**
     * Store parameters.
     *
     * @return quizaccess_invigilator_external_params
     */
    public static function send_screenshot_parameters() {
        return new quizaccess_invigilator_external_params(
            array(
                'courseid' => new quizaccess_invigilator_external_value(PARAM_INT, 'course id'),
                'cmid' => new quizaccess_invigilator_external_value(PARAM_INT, 'screenshot id'),
                'quizid' => new quizaccess_invigilator_external_value(PARAM_INT, 'screenshot quiz id'),
                'screenshot' => new quizaccess_invigilator_external_value(PARAM_RAW, 'webcam photo')
            )
        );
    }

    /**
     * Store the screenshots in Moodle subsystems and insert in log table
     *
     * @param mixed $courseid
     * @param mixed $cmid
     * @param mixed $quizid Quizid OR cmid
     * @param mixed $screenshot
     *
     * @return array
     * @throws dml_exception
     * @throws file_exception
     * @throws invalid_parameter_exception
     * @throws stored_file_creation_exception
     */
    public static function send_screenshot($courseid, $cmid, $quizid, $screenshot) {
        global $DB, $USER;

        // Validate the params.
        self::validate_parameters(
            self::send_screenshot_parameters(),
            array(
                'courseid' => $courseid,
                'cmid' => $cmid,
                'quizid' => $quizid,
                'screenshot' => $screenshot
            )
        );
        $filepath = "/";

        $context = context_module::instance($cmid);
        self::validate_context($context);
        require_capability('quizaccess/invigilator:sendscreenshot', $context);

        // Save file.
        $warnings = array();

        // Insert log with blank path.
        $record = new stdClass();
        $record->courseid = $courseid;
        $record->cmid = $cmid;
        $record->quizid = $quizid;
        $record->userid = $USER->id;
        $record->screenshot = $filepath;
        $record->timecreated = time();
        $screenshotid = $DB->insert_record('quizaccess_invigilator_logs', $record, true);

        $record = new stdClass();
        $record->filearea = 'picture';
        $record->component = 'quizaccess_invigilator';
        $record->filepath = '';
        $record->itemid = $screenshotid;
        $record->license = '';
        $record->author = '';

        $fs = get_file_storage();
        $record->filepath = file_correct_filepath($record->filepath);

        // For base64 to file.
        $data = $screenshot;
        list($type, $data) = explode(';', $data);
        list(, $data) = explode(',', $data);
        $data = base64_decode($data);
        $filename = 'screenshot-' . $screenshotid . '-' . $USER->id . '-' . $courseid . '-' . time() . rand(1, 1000) . '.png';

        $data = self::add_timecode_to_image($data);

        $record->courseid = $courseid;
        $record->filename = $filename;
        $record->contextid = $context->id;
        $record->userid = $USER->id;

        $fs->create_file_from_string($record, $data);

        $url = moodle_url::make_pluginfile_url(
            $context->id,
            $record->component,
            $record->filearea,
            $record->itemid,
            $record->filepath,
            $record->filename,
            false
        );

        // Update filepath in log.
        $updateddata = new stdClass();
        $updateddata->id = $screenshotid;
        $updateddata->courseid = $courseid;
        $updateddata->cmid = $cmid;
        $updateddata->quizid = $quizid;
        $updateddata->userid = $USER->id;
        $updateddata->screenshot = "{$url}";
        $updateddata->timecreated = time();
        $DB->update_record('quizaccess_invigilator_logs', $updateddata);

        $result = array();
        $result['screenshotid'] = $screenshotid;
        $result['warnings'] = $warnings;

        return $result;
    }


    /**
     * Cam shots return parameters.
     *
     * @return quizaccess_invigilator_external_structure
     */
    public static function send_screenshot_returns() {
        return new quizaccess_invigilator_external_structure(
            array(
                'screenshotid' => new quizaccess_invigilator_external_value(PARAM_INT, 'screenshot sent id'),
                'warnings' => new quizaccess_invigilator_external_warnings()
            )
        );
    }



    /**
     * Parameters accepted when a recording segment is sent.
     *
     * @return quizaccess_invigilator_external_params
     */
    public static function send_recording_parameters() {
        return new quizaccess_invigilator_external_params(
            array(
                'courseid' => new quizaccess_invigilator_external_value(PARAM_INT, 'course id'),
                'cmid' => new quizaccess_invigilator_external_value(PARAM_INT, 'course module id of the quiz'),
                'quizid' => new quizaccess_invigilator_external_value(PARAM_INT, 'quiz id'),
                'sessionid' => new quizaccess_invigilator_external_value(PARAM_ALPHANUMEXT,
                    'client generated id grouping the segments of one attempt'),
                'sequence' => new quizaccess_invigilator_external_value(PARAM_INT,
                    'zero based position of this segment inside the session'),
                'mimetype' => new quizaccess_invigilator_external_value(PARAM_RAW,
                    'mime type the browser recorded with, for example video/webm;codecs=vp9'),
                'duration' => new quizaccess_invigilator_external_value(PARAM_INT, 'length of this segment in seconds'),
                'recording' => new quizaccess_invigilator_external_value(PARAM_RAW, 'the segment as a base64 data url')
            )
        );
    }

    /**
     * Store one screen recording segment.
     *
     * Each segment is a standalone playable file, so a lost connection or a crashed browser
     * only ever costs the segment that was being recorded at the time.
     *
     * @param int $courseid
     * @param int $cmid
     * @param int $quizid
     * @param string $sessionid
     * @param int $sequence
     * @param string $mimetype
     * @param int $duration
     * @param string $recording base64 data url holding the segment.
     * @return array
     * @throws dml_exception
     * @throws file_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     */
    public static function send_recording($courseid, $cmid, $quizid, $sessionid, $sequence, $mimetype, $duration, $recording) {
        global $USER;

        $params = self::validate_parameters(
            self::send_recording_parameters(),
            array(
                'courseid' => $courseid,
                'cmid' => $cmid,
                'quizid' => $quizid,
                'sessionid' => $sessionid,
                'sequence' => $sequence,
                'mimetype' => $mimetype,
                'duration' => $duration,
                'recording' => $recording
            )
        );

        $context = context_module::instance($params['cmid']);
        self::validate_context($context);
        require_capability('quizaccess/invigilator:sendrecording', $context);

        $warnings = array();

        if (!\quizaccess_invigilator\recording_manager::is_enabled()) {
            $warnings[] = array(
                'item' => 'recording',
                'itemid' => 0,
                'warningcode' => 'recordingdisabled',
                'message' => get_string('warning:recordingdisabled', 'quizaccess_invigilator')
            );
            return array('recordingid' => 0, 'warnings' => $warnings);
        }

        $binary = self::decode_data_url($params['recording']);
        if ($binary === '') {
            $warnings[] = array(
                'item' => 'recording',
                'itemid' => 0,
                'warningcode' => 'emptyrecording',
                'message' => get_string('warning:emptyrecording', 'quizaccess_invigilator')
            );
            return array('recordingid' => 0, 'warnings' => $warnings);
        }

        $maxsize = \quizaccess_invigilator\recording_manager::get_max_segment_size();
        if (strlen($binary) > $maxsize) {
            $warnings[] = array(
                'item' => 'recording',
                'itemid' => 0,
                'warningcode' => 'recordingtoolarge',
                'message' => get_string('warning:recordingtoolarge', 'quizaccess_invigilator', display_size($maxsize))
            );
            return array('recordingid' => 0, 'warnings' => $warnings);
        }

        $record = \quizaccess_invigilator\recording_manager::store_segment(
            $params['courseid'],
            $params['cmid'],
            $params['quizid'],
            $USER->id,
            $params['sessionid'],
            max(0, (int)$params['sequence']),
            $params['mimetype'],
            max(0, (int)$params['duration']),
            $binary
        );

        return array('recordingid' => (int)$record->id, 'warnings' => $warnings);
    }

    /**
     * What send_recording returns.
     *
     * @return quizaccess_invigilator_external_structure
     */
    public static function send_recording_returns() {
        return new quizaccess_invigilator_external_structure(
            array(
                'recordingid' => new quizaccess_invigilator_external_value(PARAM_INT, 'id of the stored segment, 0 if not stored'),
                'warnings' => new quizaccess_invigilator_external_warnings()
            )
        );
    }

    /**
     * Turn a "data:video/webm;base64,...." string into the raw bytes it holds.
     *
     * @param string $dataurl
     * @return string the decoded data, or an empty string if the input was not usable.
     */
    protected static function decode_data_url($dataurl) {
        $commapos = strpos($dataurl, ',');
        if ($commapos === false) {
            return '';
        }

        $decoded = base64_decode(substr($dataurl, $commapos + 1), true);

        return $decoded === false ? '' : $decoded;
    }

    /**
     * Check user capability
     * @param array $params
     * @param context $context
     * @param int $USER
     * @return void
     * @throws dml_exception
     * @throws moodle_exception
     * @throws required_capability_exception
     */
    protected static function request_user_require_capability(array $params, context $context, $USER) {
        $user = core_user::get_user($params['userid'], '*', MUST_EXIST);
        core_user::require_active_user($user);

        // Extra checks so only users with permissions can view other users reports.
        if ($USER->id != $user->id) {
            require_capability('quizaccess/invigilator:viewreport', $context);
        }
    }

    /**
     * Adds timestamp information to captured image.
     * @param string $data
     * @return string
     */
    private static function add_timecode_to_image ($data) {
        global $CFG;

        $image = imagecreatefromstring($data);
        imagefilledrectangle($image, 0, 0, 120, 22, imagecolorallocatealpha($image, 255, 255, 255, 60));
        imagefttext($image, 9, 0, 4, 16, imagecolorallocate($image, 0, 0, 0),
            $CFG->dirroot . '/mod/quiz/accessrule/invigilator/assets/Roboto-Light.ttf', date('d-m-Y H:i:s') );
        ob_start();
        imagepng($image);
        $data = ob_get_clean();
        ob_end_clean();
        imagedestroy($image);
        return $data;
    }
}
