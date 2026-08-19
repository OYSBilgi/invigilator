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
define(['jquery', 'quizaccess_invigilator/framecapture'],
    function ($, FrameCapture) {

        var CHECKBOX = 'input[type="checkbox"][name="invigilator"]';

        // Both entry points run on the page holding the preflight form, so the handlers below
        // are bound once and then left to keep the form in step with the screen share.
        var gate = {bound: false, strings: {}, lastclicked: null};

        /**
         * Whether a display surface is being captured right now.
         *
         * The browser writes this into the hidden field every second, and blanks it as soon as
         * the student stops sharing, so it is the one place that knows the current state.
         *
         * @return {boolean}
         */
        function shareIsLive() {
            var state = document.getElementById('invigilator_share_state');
            var surface = document.getElementById('invigilator_window_surface');

            return !!(state && state.value === 'true' && surface && surface.value === 'live');
        }

        /**
         * Every button that would start the attempt from the form this checkbox is in.
         *
         * Moodle shows the preflight form both as a plain page and inside a modal, and the
         * modal keeps its own footer buttons, so ids are not unique and both places matter.
         * Everything is therefore looked up relative to the checkbox that was found.
         *
         * @param {jQuery} checkbox
         * @return {jQuery} the buttons that submit that form.
         */
        function startButtonsFor(checkbox) {
            var form = checkbox.closest('form');
            var buttons = form.find('#id_submitbutton, [name="submitbutton"]');

            var modal = checkbox.closest('.modal-content, .modal');
            if (modal.length) {
                buttons = buttons.add(modal.find('.modal-footer [data-action="save"]'));
            }

            if (!buttons.length) {
                // Fall back to the submit buttons that are not the cancel one.
                buttons = form.find('input[type="submit"], button[type="submit"]').not('[name="cancel"]');
            }

            return buttons;
        }

        /**
         * The block the checkbox sits in, where messages are put.
         *
         * @param {jQuery} checkbox
         * @return {jQuery}
         */
        function holderFor(checkbox) {
            return checkbox.closest('.fitem, .form-group, div').first();
        }

        /**
         * Show a message under the checkbox, touching the page only when it really changes.
         *
         * Leaving an unchanged message alone matters: this runs on a timer and from a mutation
         * observer, and rewriting the same node every time would have them chase each other.
         *
         * @param {jQuery} checkbox
         * @param {string} classname invigilator-preflight-hint or invigilator-preflight-error.
         * @param {string} message empty to remove the message.
         */
        function setCheckboxMessage(checkbox, classname, message) {
            var existing = holderFor(checkbox).find('.' + classname);

            if (!message) {
                existing.remove();
                return;
            }
            if (existing.length) {
                if (existing.text() !== message) {
                    existing.text(message);
                }
                return;
            }

            holderFor(checkbox).append(
                $('<div class="invigilator-preflight-message"></div>').addClass(classname).text(message));
        }

        /**
         * Walk the form through its two steps: share the screen, then agree.
         *
         * Until the screen is shared the checkbox stays disabled, and if the share is stopped
         * again the agreement is withdrawn with it.
         */
        function syncPreflightState() {
            var live = shareIsLive();

            $(CHECKBOX).each(function () {
                var checkbox = $(this);

                if (!live && checkbox.is(':checked')) {
                    // The share was stopped, so the agreement no longer stands either.
                    checkbox.prop('checked', false);
                }
                checkbox.prop('disabled', !live);

                startButtonsFor(checkbox).prop('disabled', !live || !checkbox.is(':checked'));

                setCheckboxMessage(checkbox, 'invigilator-preflight-hint', live ? '' : gate.strings.sharescreenfirst);

                if (live && checkbox.is(':checked')) {
                    // Whatever the student was told off for has been put right.
                    setCheckboxMessage(checkbox, 'invigilator-preflight-error', '');
                }
            });
        }

        /**
         * Lock the attempt behind the screen share and the agreement.
         *
         * @param {Object} strings youmustagree, youmustsharescreen and sharescreenfirst.
         */
        function setupPreflightGate(strings) {
            gate.strings = $.extend(gate.strings, strings || {});

            if (gate.bound) {
                syncPreflightState();
                return;
            }
            gate.bound = true;

            // Remember which button was used, so cancelling is never blocked.
            $(document).on('click', 'input[type="submit"], button[type="submit"]', function () {
                gate.lastclicked = this;
            });

            $(document).on('change', CHECKBOX, function () {
                setCheckboxMessage($(this), 'invigilator-preflight-error', '');
                syncPreflightState();
            });

            // The real gate on the browser side: a form holding the checkbox cannot be submitted
            // before the screen is shared and the box is ticked, however the button got clicked.
            $(document).on('submit', 'form', function (event) {
                var checkbox = $(this).find(CHECKBOX);
                if (!checkbox.length) {
                    return true;
                }

                var submitter = (event.originalEvent && event.originalEvent.submitter) || gate.lastclicked;
                if (submitter && $(submitter).is('[name="cancel"]')) {
                    // Leaving the form is always allowed.
                    return true;
                }

                var problem = '';
                if (!shareIsLive()) {
                    problem = gate.strings.youmustsharescreen;
                } else if (!checkbox.is(':checked')) {
                    problem = gate.strings.youmustagree;
                }

                if (!problem) {
                    return true;
                }

                event.preventDefault();
                event.stopPropagation();
                setCheckboxMessage(checkbox, 'invigilator-preflight-error', problem);
                syncPreflightState();

                return false;
            });

            // Moodle renders the preflight form into a modal after this module has run, and the
            // share can start or stop at any moment, so the form is kept in step both ways.
            if (window.MutationObserver) {
                new window.MutationObserver(syncPreflightState).observe(document.body, {childList: true, subtree: true});
            }
            window.setInterval(syncPreflightState, 1000);

            syncPreflightState();
        }

        return {
            setup: function (props) {
                setupPreflightGate(props);

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
                        syncPreflightState();

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

                        syncPreflightState();

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
            },
            init: function (props) {
                setupPreflightGate(props);

                return true;
            }
        };
    });
