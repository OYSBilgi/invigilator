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
 * Screen recording report for the quizaccess_invigilator plugin.
 *
 * Lists the recording sessions of a quiz and plays the segments of one session back to back.
 *
 * @package   quizaccess_invigilator
 * @copyright 2021 Brain Station 23
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */

require_once(__DIR__ . '/../../../../config.php');
require_once($CFG->libdir . '/tablelib.php');

use quizaccess_invigilator\recording_manager;

$courseid = required_param('courseid', PARAM_INT);
$cmid = required_param('cmid', PARAM_INT);
$sessionid = optional_param('sessionid', '', PARAM_ALPHANUMEXT);
$studentid = optional_param('studentid', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);

list($course, $cm) = get_course_and_cm_from_cmid($cmid, 'quiz');
require_login($course, true, $cm);

$context = context_module::instance($cmid, MUST_EXIST);
require_capability('quizaccess/invigilator:viewrecording', $context);

$baseparams = ['courseid' => $courseid, 'cmid' => $cmid];
$baseurl = new moodle_url('/mod/quiz/accessrule/invigilator/recordings.php', $baseparams);

$pageurl = clone $baseurl;
if ($sessionid) {
    $pageurl->param('sessionid', $sessionid);
}

$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('course');
$PAGE->set_context($context);
$PAGE->set_title($course->shortname . ': ' . get_string('recordingsreport', 'quizaccess_invigilator'));
$PAGE->set_heading($course->fullname . ': ' . get_string('recordingsreport', 'quizaccess_invigilator'));
$PAGE->navbar->add(get_string('recordingsreport', 'quizaccess_invigilator'), $baseurl);

// Deleting a session.
if ($action === 'delete' && $sessionid) {
    require_capability('quizaccess/invigilator:deleterecording', $context);
    require_sesskey();

    $deleted = recording_manager::delete_session($sessionid, $cmid);
    redirect($baseurl, get_string('recordingdeleted', 'quizaccess_invigilator', $deleted), 3);
}

echo $OUTPUT->header();

if (!recording_manager::is_enabled()) {
    echo $OUTPUT->notification(get_string('warning:recordingdisabled', 'quizaccess_invigilator'), 'notifywarning');
}

if ($sessionid) {
    // Playback of a single session.
    $segments = recording_manager::get_segments($cmid, $sessionid);

    if (empty($segments)) {
        echo $OUTPUT->notification(get_string('norecordings', 'quizaccess_invigilator'), 'notifymessage');
        echo $OUTPUT->single_button($baseurl, get_string('back'), 'get');
        echo $OUTPUT->footer();
        die();
    }

    $first = reset($segments);
    $student = core_user::get_user($first->userid);
    $playlist = [];
    $totalduration = 0;
    foreach ($segments as $segment) {
        $totalduration += $segment->duration;
        $playlist[] = [
            'url' => recording_manager::get_segment_url($context->id, $segment->id, $segment->filename)->out(false),
            'label' => userdate($segment->timecreated, get_string('strftimedatetimeaccurate', 'langconfig')),
            'duration' => (int)$segment->duration,
        ];
    }

    echo $OUTPUT->heading(fullname($student), 3);
    echo html_writer::tag('p', get_string('recordingsessionsummary', 'quizaccess_invigilator', (object)[
        'segments' => count($segments),
        'duration' => format_time($totalduration),
        'start' => userdate($first->timecreated),
    ]));

    echo html_writer::start_div('invigilator-player-wrapper');
    echo html_writer::tag('video', '', [
        'id' => 'invigilator-player',
        'class' => 'invigilator-player',
        'controls' => 'controls',
        'preload' => 'metadata',
        'playsinline' => 'playsinline',
    ]);
    echo html_writer::div('', 'invigilator-player-status', ['id' => 'invigilator-player-status']);
    echo html_writer::start_tag('ol', ['id' => 'invigilator-player-list', 'class' => 'invigilator-player-list']);
    foreach ($playlist as $index => $item) {
        echo html_writer::tag('li',
            html_writer::link('#', $item['label'] . ' (' . format_time($item['duration']) . ')',
                ['data-invigilator-segment' => $index]),
            ['class' => 'invigilator-player-item']);
    }
    echo html_writer::end_tag('ol');
    echo html_writer::end_div();

    $PAGE->requires->js_call_amd('quizaccess_invigilator/recordingplayer', 'init', [[
        'segments' => $playlist,
        'playingnow' => get_string('playingsegment', 'quizaccess_invigilator'),
    ]]);

    echo $OUTPUT->single_button($baseurl, get_string('back'), 'get');
    echo $OUTPUT->footer();
    die();
}

// List of sessions.
$sessions = recording_manager::get_sessions($cmid, $studentid ?: null);

echo html_writer::tag('p', get_string('recordingsreportdesc', 'quizaccess_invigilator'));

if (empty($sessions)) {
    echo $OUTPUT->notification(get_string('norecordings', 'quizaccess_invigilator'), 'notifymessage');
    echo $OUTPUT->footer();
    die();
}

$table = new flexible_table('invigilator-recordings-' . $courseid . '-' . $cmid);
$table->define_columns(['fullname', 'email', 'started', 'duration', 'segments', 'size', 'actions']);
$table->define_headers([
    get_string('user'),
    get_string('email'),
    get_string('recordingstart', 'quizaccess_invigilator'),
    get_string('recordingduration', 'quizaccess_invigilator'),
    get_string('recordingsegments', 'quizaccess_invigilator'),
    get_string('recordingsize', 'quizaccess_invigilator'),
    get_string('actions', 'quizaccess_invigilator'),
]);
$table->define_baseurl($pageurl);
$table->set_attribute('class', 'generaltable generalbox reporttable');
$table->setup();

$candelete = has_capability('quizaccess/invigilator:deleterecording', $context);

foreach ($sessions as $session) {
    $playurl = new moodle_url($baseurl, ['sessionid' => $session->sessionid]);
    $actions = html_writer::link($playurl, get_string('recordingplay', 'quizaccess_invigilator'),
        ['class' => 'btn btn-secondary btn-sm']);

    if ($candelete) {
        $deleteurl = new moodle_url($baseurl, [
            'sessionid' => $session->sessionid,
            'action' => 'delete',
            'sesskey' => sesskey(),
        ]);
        $actions .= ' ' . html_writer::link($deleteurl, get_string('delete'), [
            'class' => 'btn btn-danger btn-sm',
            'onclick' => "return confirm('" . get_string('recordingdeleteconfirm', 'quizaccess_invigilator') . "');",
        ]);
    }

    $table->add_data([
        fullname($session),
        s($session->email),
        userdate($session->timestart),
        format_time((int)$session->duration),
        (int)$session->segments,
        display_size((int)$session->filesize),
        $actions,
    ]);
}

$table->finish_html();

echo $OUTPUT->footer();
