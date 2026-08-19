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
$string['setting:screenshotdelay'] = "The delay between screenshots in seconds.";
$string['setting:screenshotdelay_desc'] = "Given value will be the delay in seconds between each screenshot";
$string['setting:screenshotwidth'] = "The width of the screenshot image in pixel.";
$string['setting:screenshotwidth_desc'] = "Given value will be the width of the screenshot. The image height will be scaled to that";
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
$string['screenhtml'] = '<span><video id="invigilator-video-screen" width="320" height="240" autoplay></video></span><canvas id="invigilator-canvas-screen" style="display:none;"></canvas><img id="invigilator-photo-screen" alt="The picture will appear in this box." style="display:none;"/><span class="invigilator-output-screen" style="display:none;"></span><span id="invigilator-log-screen" style="display:none;"></span><span id="invigilator-recording-status" class="invigilator-recording-status"></span>';
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
$string['invigilator:sendrecording'] = 'Send screen recordings while attempting a quiz';
$string['invigilator:viewrecording'] = 'View the screen recordings captured during a quiz';
$string['invigilator:deleterecording'] = 'Delete captured screen recordings';

// Screen recording settings.
$string['setting:recordingheading'] = 'Screen recording';
$string['setting:recordingheading_desc'] = 'Besides the periodic screenshots, the shared screen can be recorded as video. The recording is stored as short segments so a lost connection or a closed browser only costs the segment being recorded at the time.';
$string['setting:enablerecording'] = 'Enable screen recording';
$string['setting:enablerecording_desc'] = 'If enabled, the shared screen is recorded as video for the whole attempt, in addition to the screenshots.';
$string['setting:recordingsegment'] = 'Segment length (seconds)';
$string['setting:recordingsegment_desc'] = 'How long each recorded segment is before it is uploaded. Shorter segments upload more often and lose less if the browser crashes; longer segments create fewer files.';
$string['setting:recordingwidth'] = 'Recording width (pixels)';
$string['setting:recordingwidth_desc'] = 'The shared screen is scaled down to at most this width before it is recorded. Smaller values produce much smaller files.';
$string['setting:recordingframerate'] = 'Frame rate (frames per second)';
$string['setting:recordingframerate_desc'] = 'Frames per second to record. Five is plenty to see what a student is doing and keeps the files small.';
$string['setting:recordingbitrate'] = 'Video bitrate (kbps)';
$string['setting:recordingbitrate_desc'] = 'Target bitrate of the recording. Around 300 kbps is enough for readable screen content.';
$string['setting:recordingmaxsize'] = 'Maximum segment size (MB)';
$string['setting:recordingmaxsize_desc'] = 'Segments larger than this are rejected. Keep this below the PHP post_max_size of the server, remembering that the upload is base64 encoded and therefore about a third larger than the file itself.';
$string['setting:recordingretention'] = 'Keep recordings for (days)';
$string['setting:recordingretention_desc'] = 'Recordings older than this are deleted by a scheduled task. Use 0 to keep recordings until they are deleted by hand.';

// Recording report.
$string['recordingsreport'] = 'View screen recordings';
$string['recordingsreportdesc'] = 'Every recording session of this quiz. A session holds all the segments recorded during one attempt and is played back as one video.';
$string['norecordings'] = 'No screen recordings have been stored for this quiz yet.';
$string['recordingstart'] = 'Started';
$string['recordingduration'] = 'Duration';
$string['recordingsegments'] = 'Segments';
$string['recordingsize'] = 'Size';
$string['recordingplay'] = 'Play';
$string['recordingdeleteconfirm'] = 'Delete this whole recording session?';
$string['recordingdeleted'] = '{$a} recording segments deleted.';
$string['recordingsessionsummary'] = '{$a->segments} segments, {$a->duration} in total, starting {$a->start}.';
$string['playingsegment'] = 'Playing segment {$a->number} of {$a->total} ({$a->time})';

// Recording messages.
$string['alert:recordingunsupported'] = 'This browser cannot record the screen. Screenshots are still being taken. Please use an up to date Chrome, Edge or Firefox.';
$string['alert:recordingfailed'] = 'The screen recording stopped unexpectedly. Please keep this window open for the whole attempt.';
$string['warning:recordingdisabled'] = 'Screen recording is switched off for this site.';
$string['warning:emptyrecording'] = 'The recording segment was empty and has been discarded.';
$string['warning:recordingtoolarge'] = 'The recording segment is larger than the {$a} limit and has been discarded.';

// Scheduled task.
$string['task:cleanuprecordings'] = 'Delete expired invigilator screen recordings';

// Privacy.
$string['privacy:metadata:quizaccess_invigilator_rec'] = 'Stores the screen recording segments captured during a quiz attempt';
$string['privacy:metadata:quizaccess_invigilator_rec:userid'] = 'The ID of the user who was recorded';
$string['privacy:metadata:quizaccess_invigilator_rec:recording'] = 'Link to a recorded segment of the screen';
$string['privacy:metadata:quizaccess_invigilator_rec:timecreated'] = 'The time the segment was recorded';
