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
 * Lists the capture sessions of a quiz and plays the frames of one session as a time lapse.
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
    $frames = recording_manager::get_frames($cmid, $sessionid);

    if (empty($frames)) {
        echo $OUTPUT->notification(get_string('norecordings', 'quizaccess_invigilator'), 'notifymessage');
        echo $OUTPUT->single_button($baseurl, get_string('back'), 'get');
        echo $OUTPUT->footer();
        die();
    }

    $first = reset($frames);
    $student = core_user::get_user($first->userid);
    $playlist = [];
    $covered = 0;
    foreach ($frames as $frame) {
        $covered += $frame->duration;
        $playlist[] = [
            'url' => recording_manager::get_frame_url($context->id, $frame->id, $frame->filename)->out(false),
            'label' => userdate($frame->timecreated, get_string('strftimetime', 'langconfig')),
            'time' => userdate($frame->timecreated),
        ];
    }

    echo $OUTPUT->heading(fullname($student), 3);
    echo html_writer::tag('p', get_string('recordingsessionsummary', 'quizaccess_invigilator', (object)[
        'frames' => count($frames),
        'duration' => format_time($covered),
        'start' => userdate($first->timecreated),
    ]));

    echo html_writer::start_div('invigilator-player-wrapper');

    // The frame is wrapped in a lightbox link so it can be opened at its full size. The player
    // keeps the link pointing at whichever frame is on screen.
    echo html_writer::start_tag('a', [
        'id' => 'invigilator-player-link',
        'class' => 'invigilator-player-zoom',
        'href' => $playlist[0]['url'],
        'data-lightbox' => 'invigilator-frame',
        'data-title' => $playlist[0]['time'],
        'title' => get_string('clicktoenlarge', 'quizaccess_invigilator'),
    ]);
    echo html_writer::empty_tag('img', [
        'id' => 'invigilator-player-frame',
        'class' => 'invigilator-player-frame',
        'src' => $playlist[0]['url'],
        'alt' => get_string('recordingsreport', 'quizaccess_invigilator'),
    ]);
    echo html_writer::end_tag('a');
    echo html_writer::div(get_string('clicktoenlarge', 'quizaccess_invigilator'), 'invigilator-player-zoomhint');

    // Controls: play or pause, step by one frame, and how fast the frames are shown.
    $controls = html_writer::tag('button', get_string('player:play', 'quizaccess_invigilator'), [
        'id' => 'invigilator-player-toggle', 'type' => 'button', 'class' => 'btn btn-primary btn-sm']);
    $controls .= ' ' . html_writer::tag('button', get_string('player:previous', 'quizaccess_invigilator'), [
        'id' => 'invigilator-player-prev', 'type' => 'button', 'class' => 'btn btn-secondary btn-sm']);
    $controls .= ' ' . html_writer::tag('button', get_string('player:next', 'quizaccess_invigilator'), [
        'id' => 'invigilator-player-next', 'type' => 'button', 'class' => 'btn btn-secondary btn-sm']);

    $speedoptions = [];
    foreach ([1, 2, 4, 8] as $fps) {
        $speedoptions[$fps] = get_string('player:fps', 'quizaccess_invigilator', $fps);
    }
    $controls .= ' ' . html_writer::label(get_string('player:speed', 'quizaccess_invigilator'),
        'invigilator-player-speed', true, ['class' => 'ml-2 mr-1']);
    $controls .= html_writer::select($speedoptions, 'invigilatorplayerspeed', 2, false,
        ['id' => 'invigilator-player-speed', 'class' => 'custom-select']);

    echo html_writer::div($controls, 'invigilator-player-controls');

    echo html_writer::empty_tag('input', [
        'type' => 'range',
        'id' => 'invigilator-player-seek',
        'class' => 'invigilator-player-seek',
        'min' => 0,
        'max' => count($playlist) - 1,
        'value' => 0,
        'step' => 1,
        'aria-label' => get_string('recordingframes', 'quizaccess_invigilator'),
    ]);

    echo html_writer::div('', 'invigilator-player-status', ['id' => 'invigilator-player-status']);

    echo html_writer::start_tag('ol', ['id' => 'invigilator-player-list', 'class' => 'invigilator-player-list']);
    foreach ($playlist as $index => $item) {
        echo html_writer::tag('li',
            html_writer::link('#', $item['label'], ['data-invigilator-frame' => $index]),
            ['class' => 'invigilator-player-item']);
    }
    echo html_writer::end_tag('ol');
    echo html_writer::end_div();

    $PAGE->requires->js_call_amd('quizaccess_invigilator/recordingplayer', 'init', [[
        'frames' => $playlist,
        'playingnow' => get_string('playingframe', 'quizaccess_invigilator'),
        'playlabel' => get_string('player:play', 'quizaccess_invigilator'),
        'pauselabel' => get_string('player:pause', 'quizaccess_invigilator'),
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
$table->define_columns(['fullname', 'email', 'started', 'duration', 'frames', 'size', 'actions']);
$table->define_headers([
    get_string('user'),
    get_string('email'),
    get_string('recordingstart', 'quizaccess_invigilator'),
    get_string('recordingduration', 'quizaccess_invigilator'),
    get_string('recordingframes', 'quizaccess_invigilator'),
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
        (int)$session->frames,
        display_size((int)$session->filesize),
        $actions,
    ]);
}

$table->finish_html();

echo $OUTPUT->footer();
