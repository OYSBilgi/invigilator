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
 * Strings for the quizaccess_invigilator plugin.
 *
 * @package    quizaccess_invigilator
 * @copyright  2021 Brain Station 23
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Invigilator';
$string['quizaccess_invigilator'] = 'quizaccess invigilator';
$string['invigilatorlabel'] = 'I agree with the validation process.';
$string['youmustagree'] = 'You must agree to validate your identity before continue.';
$string['notrequired'] = 'not required';
$string['invigilatorrequiredoption'] = 'must be acknowledged before starting an attempt';
$string['invigilatorrequired'] = 'Screenshot capture validation';
$string['invigilatorrequired_help'] = 'If enabled, students must agree to screenshot capture validation before starting the quiz attempt.';
$string['warning:allowscreenshare'] = 'Please allow screen share.';
$string['invigilatorheader'] = '<strong>To continue with this quiz attempt you must share your screen. You must choose entire monitor in screen sharing option.</strong>';
$string['picturesreport'] = 'View invigilator report';
$string['screensharemsg'] = '<strong>* Please allow screenshare for entire monitor.</strong><br/><strong>* Please dont close this window or your attempt will be closed</strong><br/>';
$string['screenhtml'] = '<span><video id="invigilator-video-screen" width="320" height="240" autoplay></video></span><span id="invigilator-log-screen" style="display:none;"></span><span id="invigilator-recording-status" class="invigilator-recording-status"></span>';
$string['sharescreen'] = 'Allow screen share to continue';
$string['sharescreenbtnlabel'] = 'Share screen';
$string['quizaccess_invigilator_label'] = 'Invigilator';
$string['invigilatorreports'] = 'Invigilator Reports';
$string['invigilatorreportsdesc'] = 'Invigilator Reports shows screenshots taken during quiz';
$string['dateverified'] = 'Date';
$string['actions'] = 'Action';
$string['name'] = 'Name';
$string['screenshot'] = 'Screenshot';
$string['notpermissionreport'] = 'You are not permitted to see this report';
$string['picturesusedreport'] = 'Screenshots';
$string['summarypagedesc'] = 'Summery report shows the number of screenshot each quiz and course have. You can delete all screenshots of a particulart quiz/course.';
$string['settings:deleteallsuccess'] = 'Screenshots deleted successfully';
$string['reportidheader'] = "Row ID";
$string['coursenameheader'] = "Course Name";
$string['quiznameheader'] = "Quiz Name";
$string['alert:screensharemsg'] = "Please share entire screen.";
$string['alert:restartattemptcommand'] = "Sorry !! You need to restart the attempt as you have stopped the screenshare.";
$string['alert:somethingwentwrong'] = "Something went wrong during taking the image.";
$string['invigilator:bulkdelete'] = 'Invigilator: Bulk Delete';
$string['invigilator_bulkdelete'] = 'Invigilator Bulk Delete';
$string['success'] = 'success';
$string['invalidtype'] = 'invalid type';
$string['invigilator:logs'] = 'Invigilator logs';
$string['invigilator:Logs'] = 'Invigilator Logs';
$string['imgdlt'] = 'Images deleted!';
$string['invigilator:summery'] = 'Invigilator Summary Report';
$string['invigilator:report'] = 'Invigilator Report';

$string['privacy:core_files'] = 'quizaccess_invigilator Screenshot images';
$string['privacy:metadata:core_files'] = 'This plugin stores screenshots of user\'s screenshare during taking quiz.';
$string['privacy:metadata:quizaccess_invigilator_logs'] = 'Stores all validations for reporting';
$string['privacy:metadata:quizaccess_invigilator_logs:userid'] = 'THe ID of user in quizaccess_invigilator_logs';
$string['privacy:metadata:quizaccess_invigilator_logs:screenshot'] = 'Link to Screenshots of the test.';

// Capabilities.
$string['invigilator:sendscreenshot'] = 'Send screenshots while attempting a quiz';
$string['invigilator:getscreenshot'] = 'View the screenshots captured during a quiz';
$string['invigilator:viewreport'] = 'View the invigilator report';
$string['invigilator:deletescreenshot'] = 'Delete captured screenshots';
$string['invigilator:sendrecording'] = 'Send captured screen frames while attempting a quiz';
$string['invigilator:viewrecording'] = 'View the screen frames captured during a quiz';
$string['invigilator:deleterecording'] = 'Delete captured screen frames';

// Screen recording settings.
$string['setting:enablerecording'] = 'Capture the screen';
$string['setting:enablerecording_desc'] = 'If disabled, students still have to share their screen to start an attempt, but no images are stored.';
$string['setting:recordingwidth'] = 'Screenshot width (pixels)';
$string['setting:recordingwidth_desc'] = 'Screenshots are scaled down to at most this width, keeping the aspect ratio of the shared screen. Allowed range 320 to 3840. With the default settings one screenshot is roughly 100-150 KB, so a one hour attempt costs about 40 MB.';
$string['setting:recordingmaxsize'] = 'Maximum size of one screenshot (MB)';
$string['setting:recordingmaxsize_desc'] = 'Screenshots larger than this are refused. Keep it below the PHP post_max_size of the server, remembering that the upload is base64 encoded and therefore about a third larger than the image itself.';
$string['setting:recordingretention'] = 'Keep screenshots for (days)';
$string['setting:recordingretention_desc'] = 'Screenshots older than this are deleted by a scheduled task. Use 0 to keep them until they are deleted by hand.';

// Recording report.
$string['recordingsreport'] = 'View screen captures';
$string['recordingsreportdesc'] = 'Every capture session of this quiz. A session holds all the frames captured during one attempt and is played back as a time lapse.';
$string['norecordings'] = 'No screen captures have been stored for this quiz yet.';
$string['recordingstart'] = 'Started';
$string['recordingduration'] = 'Duration';
$string['recordingsize'] = 'Size';
$string['recordingplay'] = 'Play';
$string['recordingdeleteconfirm'] = 'Delete this whole capture session?';
$string['recordingdeleted'] = '{$a} frames deleted.';
$string['recordingsessionsummary'] = '{$a->frames} frames covering {$a->duration}, starting {$a->start}.';

// Recording messages.
$string['alert:recordingunsupported'] = 'This browser cannot capture the screen. Please use an up to date Chrome, Edge or Firefox.';
$string['alert:recordingfailed'] = 'The screen capture stopped unexpectedly. Please keep this window open for the whole attempt.';
$string['warning:recordingdisabled'] = 'Screen capture is switched off for this site.';
$string['warning:emptyrecording'] = 'The captured frame was empty and has been discarded.';
$string['warning:recordingtoolarge'] = 'The captured frame is larger than the {$a} limit and has been discarded.';

// Scheduled task.
$string['task:cleanuprecordings'] = 'Delete expired invigilator screen captures';

// Privacy.
$string['privacy:metadata:quizaccess_invigilator_rec'] = 'Stores the screen frames captured during a quiz attempt';
$string['privacy:metadata:quizaccess_invigilator_rec:userid'] = 'The ID of the user who was recorded';
$string['privacy:metadata:quizaccess_invigilator_rec:recording'] = 'Link to a captured frame of the screen';
$string['privacy:metadata:quizaccess_invigilator_rec:timecreated'] = 'The time the frame was captured';
$string['setting:recordinginterval'] = 'Seconds between screenshots';
$string['setting:recordinginterval_desc'] = 'How many seconds pass between two screenshots. Ten seconds is a good balance between detail and storage: halving it doubles the storage an attempt costs. Allowed range 2 to 600.';
$string['setting:recordingquality'] = 'Screenshot quality';
$string['setting:recordingquality_desc'] = 'JPEG quality of each screenshot, from 10 to 100. Around 60 keeps the text on screen readable at a fraction of the size; above 85 the files grow quickly with little visible gain.';
$string['recordingframes'] = 'Frames';
$string['playingframe'] = 'Frame {$a->number} of {$a->total} ({$a->time})';
$string['player:play'] = 'Play';
$string['player:pause'] = 'Pause';
$string['player:previous'] = 'Previous frame';
$string['player:next'] = 'Next frame';
$string['player:speed'] = 'Speed';
$string['player:fps'] = '{$a} frames per second';

// Preflight steps: share the screen first, then agree.
$string['youmustsharescreen'] = 'You must share your entire screen before you can start the attempt.';
$string['sharescreenfirst'] = 'Share your screen first, then tick this box.';
$string['setting:captureheading'] = 'Screen capture';
$string['setting:captureheading_desc'] = 'While a student attempts the quiz, the shared screen is captured as an image every few seconds. The report plays the images of one attempt back in order, so a whole attempt can be watched as a time lapse. The two values that decide how much storage this costs are the interval and the quality below.';
$string['setting:storageheading'] = 'Storage';
$string['setting:storageheading_desc'] = 'How long the captured images are kept, and the size limit one image may reach.';
$string['error:outofrange'] = 'Enter a whole number between {$a->min} and {$a->max}.';
$string['clicktoenlarge'] = 'Click the screenshot to see it at full size.';
