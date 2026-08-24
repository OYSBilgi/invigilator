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
 * Implementaton for the quizaccess_invigilator plugin.
 *
 * @package    quizaccess_invigilator
 * @copyright  2021 Brain Station 23
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


use quizaccess_invigilator\admin\setting_intrange;

global $ADMIN;

if ($hassiteconfig) {

    // What is captured, and how often.
    $settings->add(new admin_setting_heading('quizaccess_invigilator/captureheading',
        get_string('setting:captureheading', 'quizaccess_invigilator'),
        get_string('setting:captureheading_desc', 'quizaccess_invigilator')));

    $settings->add(new admin_setting_configcheckbox('quizaccess_invigilator/enablerecording',
        get_string('setting:enablerecording', 'quizaccess_invigilator'),
        get_string('setting:enablerecording_desc', 'quizaccess_invigilator'), 1));

    $settings->add(new setting_intrange('quizaccess_invigilator/recordinginterval',
        get_string('setting:recordinginterval', 'quizaccess_invigilator'),
        get_string('setting:recordinginterval_desc', 'quizaccess_invigilator'), 10, 2, 600));

    $settings->add(new setting_intrange('quizaccess_invigilator/recordingquality',
        get_string('setting:recordingquality', 'quizaccess_invigilator'),
        get_string('setting:recordingquality_desc', 'quizaccess_invigilator'), 60, 10, 100));

    $settings->add(new setting_intrange('quizaccess_invigilator/recordingwidth',
        get_string('setting:recordingwidth', 'quizaccess_invigilator'),
        get_string('setting:recordingwidth_desc', 'quizaccess_invigilator'), 1280, 320, 3840));

    $settings->add(new setting_intrange('quizaccess_invigilator/recordingthumbwidth',
        get_string('setting:recordingthumbwidth', 'quizaccess_invigilator'),
        get_string('setting:recordingthumbwidth_desc', 'quizaccess_invigilator'), 240, 80, 640));

    // How long the images are kept, and how big one is allowed to be.
    $settings->add(new admin_setting_heading('quizaccess_invigilator/storageheading',
        get_string('setting:storageheading', 'quizaccess_invigilator'),
        get_string('setting:storageheading_desc', 'quizaccess_invigilator')));

    $settings->add(new setting_intrange('quizaccess_invigilator/recordingretention',
        get_string('setting:recordingretention', 'quizaccess_invigilator'),
        get_string('setting:recordingretention_desc', 'quizaccess_invigilator'), 0, 0, 3650));

    $settings->add(new setting_intrange('quizaccess_invigilator/recordingmaxsize',
        get_string('setting:recordingmaxsize', 'quizaccess_invigilator'),
        get_string('setting:recordingmaxsize_desc', 'quizaccess_invigilator'), 2, 1, 50));
}
