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
 * Lib for the quizaccess_invigilator plugin.
 *
 * @package    quizaccess_invigilator
 * @copyright  2021 Brain Station 23
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */


defined('MOODLE_INTERNAL') || die();
/**
 * Serve the files.
 *
 * @param stdClass $course the course object.
 * @param stdClass $cm the course module object.
 * @param context $context the context.
 * @param string $filearea the name of the file area.
 * @param array $args extra arguments (itemid, path).
 * @param bool $forcedownload whether or not force download.
 * @param array $options additional options affecting the file serving.
 * @return bool false if the file not found, just send the file otherwise and do not return anything.
 */
function quizaccess_invigilator_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $USER;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    $allowedareas = ['picture', \quizaccess_invigilator\recording_manager::FILEAREA];
    if (!in_array($filearea, $allowedareas, true)) {
        return false;
    }

    require_login($course, false, $cm);

    $itemid = array_shift($args);
    $filename = array_pop($args);
    if (!$args) {
        $filepath = '/';
    } else {
        $filepath = '/' .implode('/', $args) . '/';
    }
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'quizaccess_invigilator', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false;
    }

    // Only staff may look at other people's captures. Students can still see their own.
    $viewcapability = $filearea === 'picture'
        ? 'quizaccess/invigilator:getscreenshot'
        : 'quizaccess/invigilator:viewrecording';
    if ((int)$file->get_userid() !== (int)$USER->id && !has_capability($viewcapability, $context)) {
        return false;
    }

    // Recordings are played back in the browser, so they must stay seekable.
    send_stored_file($file, 0, 0, $forcedownload, $options);
}
