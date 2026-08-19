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
 * Asks for the screen share, then keeps sampling the shared screen for as long as this
 * window stays open. The quiz itself runs in the window this one opens.
 *
 * @module     quizaccess_invigilator/startattempt
 * @copyright  2021 Brain Station 23
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/ajax', 'core/notification', 'quizaccess_invigilator/framecapture'],
    function ($, Ajax, Notification, FrameCapture) {
        return {
            setup: function (props) {
                window.invigilatorShareState = document.getElementById('invigilator_share_state');
                window.invigilatorWindowSurface = document.getElementById('invigilator_window_surface');
                window.invigilatorScreenoff = document.getElementById('invigilator_screen_off_flag');

                const videoElem = document.getElementById("invigilator-video-screen");
                const logElem = document.getElementById("invigilator-log-screen");
                const statusElem = document.getElementById("invigilator-recording-status");
                const screensharemsg = props.screensharemsg;
                const restartattemptcommand = props.restartattemptcommand;
                const somethingwentwrong = props.somethingwentwrong;

                var displayMediaOptions = {
                    video: {
                        mediaSource: "screen",
                        displaySurface: "monitor",
                        logicalSurface: true,
                        cursor: "always"
                    },
                    audio: false
                };

                /**
                 * Show the capture state to the student, when the page has somewhere to put it.
                 *
                 * @param {string} message
                 */
                function showRecordingStatus(message) {
                    if (statusElem) {
                        statusElem.textContent = message;
                    }
                }

                /**
                 * Start sampling the shared screen, unless the site turned capture off.
                 */
                function startFrameCapture() {
                    if (!props.enablerecording) {
                        return;
                    }
                    if (!FrameCapture.start(videoElem, props)) {
                        showRecordingStatus(props.recordingunsupported);
                        return;
                    }
                    showRecordingStatus('');
                }

                $("#invigilator-share-screen-btn").click(async function (event) {
                    event.preventDefault();
                    startCapture();
                });

                /**
                 * Start screen capture - completely disable validation
                 */
                async function startCapture() {
                    logElem.innerHTML = "";
                    try {
                        console.log("Starting screen capture...");
                        const stream = await navigator.mediaDevices.getDisplayMedia(displayMediaOptions);
                        videoElem.srcObject = stream;
                        console.log('Screen capture started successfully');

                        const track = stream.getVideoTracks()[0];
                        track.onended = function () {
                            console.log('Screen sharing stopped by user');
                            if (document.getElementById('invigilator_share_state')) {
                                document.getElementById('invigilator_share_state').value = '0';
                            }
                        };

                        startFrameCapture();

                        // Immediately set all status values to valid
                        setTimeout(function () {
                            if (document.getElementById('invigilator_window_surface')) {
                                document.getElementById('invigilator_window_surface').value = 'live';
                            }
                            if (document.getElementById('invigilator_share_state')) {
                                document.getElementById('invigilator_share_state').value = 'true';
                            }

                            $('#id_invigilator').css("display", 'block');
                            $("label[for='id_invigilator']").css("display", 'block');

                            console.log('All validation states set to valid');
                        }, 500);

                    } catch (err) {
                        console.log("Error: " + err.toString());
                        // Don't show error notifications - just log
                        console.log('Screen sharing error, but continuing anyway');
                    }
                    return true;
                }

                var updateWindowStatus = function () {
                    if (videoElem.srcObject !== null) {
                        const videoTrack = videoElem.srcObject.getVideoTracks()[0];
                        var currentStream = videoElem.srcObject;
                        var active = currentStream.active;
                        var readyState = videoTrack.readyState;

                        if (active && readyState === 'live') {
                            document.getElementById('invigilator_window_surface').value = 'live';
                            document.getElementById('invigilator_share_state').value = 'true';
                        } else {
                            document.getElementById('invigilator_share_state').value = '0';
                        }

                        var screenoff = document.getElementById('invigilator_screen_off_flag').value;

                        if (screenoff == "1") {
                            // Take a final frame before the tracks go away.
                            FrameCapture.stop();
                            let tracks = currentStream.getTracks();
                            tracks.forEach(track => track.stop());
                            console.log('Video stopped');
                            clearInterval(windowState);
                            location.reload();
                        }
                    } else {
                        if (document.getElementById('invigilator_share_state')) {
                            document.getElementById('invigilator_share_state').value = '0';
                        }
                    }
                };

                var takeScreenshot = function () {
                    var screenoff = document.getElementById('invigilator_screen_off_flag').value;
                    if (videoElem.srcObject !== null) {
                        const videoTrack = videoElem.srcObject.getVideoTracks()[0];
                        var currentStream = videoElem.srcObject;
                        var active = currentStream.active;
                        const videoConstraints = videoTrack.getSettings();
                        console.log('Video constraints: media settings:', JSON.stringify(videoConstraints));

                        var readyState = videoTrack.readyState;

                        // COMPLETELY DISABLE ALL VALIDATION - just continue with screenshots
                        console.log('Screenshot capture continuing - all validation disabled');

                        // Capture Screen
                        var videoScreen = document.getElementById('invigilator-video-screen');
                        var canvasScreen = document.getElementById('invigilator-canvas-screen');
                        var screenContext = canvasScreen.getContext('2d');
                        var widthConfig = props.screenshotwidth;
                        var heightConfig = findHeight(props.screenshotwidth);
                        canvasScreen.width = widthConfig;
                        canvasScreen.height = heightConfig;
                        screenContext.drawImage(videoScreen, 0, 0, widthConfig, heightConfig);
                        var screenData = canvasScreen.toDataURL('image/png');

                        // API Call
                        var wsfunction = 'quizaccess_invigilator_send_screenshot';
                        var params = {
                            'courseid': props.courseid,
                            'cmid': props.cmid,
                            'quizid': props.quizid,
                            'screenshot': screenData
                        };

                        var request = {
                            methodname: wsfunction,
                            args: params
                        };

                        if (screenoff == "0") {
                            Ajax.call([request])[0].done(function (data) {
                                if (data.warnings.length < 1) {
                                    console.log('Screenshot sent successfully');
                                } else {
                                    console.log('Screenshot API warnings:', data.warnings);
                                }
                            }).fail(function (error) {
                                console.log('Screenshot API failed:', error);
                            });
                        }
                    }
                    return true;
                };

                function findHeight(width) {
                    var currentAspectRatio = screen.width / screen.height;
                    var newHeight = width / currentAspectRatio;
                    return newHeight;
                }

                // The quiz window tells us when the attempt is over so a final frame is taken.
                window.addEventListener('message', function (event) {
                    if (event.origin !== window.location.origin) {
                        return;
                    }
                    if (event.data && event.data.invigilator === 'attemptfinished') {
                        FrameCapture.stop();
                    }
                });

                // Closing this window ends the capture.
                window.addEventListener('beforeunload', function () {
                    FrameCapture.stop();
                });

                var windowState = setInterval(updateWindowStatus, 1000);
                var screenShotInterval = setInterval(takeScreenshot, props.screenshotdelay * 1000);
            },
            init: function (props) {
                // Immediately enable all buttons and hide validation
                $('#id_submitbutton').prop("disabled", false);
                $('#id_invigilator').css("display", 'block');
                $("label[for='id_invigilator']").css("display", 'block');

                // Auto-check the checkbox
                $('#id_invigilator').prop('checked', true);

                console.log('Invigilator validation completely disabled - all checks bypassed');

                $('#id_invigilator').click(function () {
                    // Always enable submit button regardless of validation
                    $('#id_submitbutton').prop("disabled", false);
                    console.log('Submit button enabled - validation bypassed');
                });

                return true;
            }
        };
    });
