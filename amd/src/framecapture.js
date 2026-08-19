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
 * Samples the shared screen at a fixed interval and uploads every frame as a compressed image.
 *
 * Filming a whole exam would cost hundreds of megabytes per student, so the screen is sampled
 * instead: one image every few seconds, all frames of an attempt sharing a session id. The report
 * plays them back in order, which watches like a time lapse of the attempt.
 *
 * @module     quizaccess_invigilator/framecapture
 * @copyright  2021 Brain Station 23
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['core/ajax'], function(Ajax) {

    var state = {
        config: null,
        video: null,
        canvas: null,
        timer: null,
        sequence: 0,
        running: false,
        mimetype: 'image/jpeg',
        uploading: 0
    };

    /**
     * Whether the shared screen is still being delivered.
     *
     * @return {boolean}
     */
    var isStreamLive = function() {
        var stream = state.video ? state.video.srcObject : null;
        if (!stream || !stream.active) {
            return false;
        }
        var tracks = stream.getVideoTracks();
        return tracks.length > 0 && tracks[0].readyState === 'live';
    };

    /**
     * Send one frame to the server.
     *
     * @param {string} dataurl the frame, base64 encoded.
     * @param {number} sequence its position in the session.
     * @return {Promise}
     */
    var uploadFrame = function(dataurl, sequence) {
        // A data url carries about a third more bytes than the image itself.
        var maxbytes = state.config.recordingmaxsize * 1024 * 1024;
        var bytes = Math.round((dataurl.length - dataurl.indexOf(',') - 1) * 3 / 4);

        if (bytes > maxbytes) {
            window.console.warn('Invigilator: frame ' + sequence + ' dropped, it is larger than the ' +
                state.config.recordingmaxsize + 'MB limit. Lower the capture width or the quality.');
            return Promise.resolve();
        }

        state.uploading++;

        return new Promise(function(resolve) {
            var request = {
                methodname: 'quizaccess_invigilator_send_frame',
                args: {
                    courseid: state.config.courseid,
                    cmid: state.config.cmid,
                    quizid: state.config.quizid,
                    sessionid: state.config.sessionid,
                    sequence: sequence,
                    mimetype: state.mimetype,
                    duration: state.config.recordinginterval,
                    recording: dataurl
                }
            };

            Ajax.call([request])[0].done(function(data) {
                if (data.warnings && data.warnings.length) {
                    window.console.warn('Invigilator: frame ' + sequence + ' rejected.', data.warnings);
                }
                state.uploading--;
                resolve();
            }).fail(function(error) {
                window.console.warn('Invigilator: frame ' + sequence + ' could not be sent.', error);
                state.uploading--;
                resolve();
            });
        });
    };

    /**
     * Draw the current screen onto the working canvas and hand back the encoded image.
     *
     * @return {string} a data url, or an empty string when there was nothing to capture.
     */
    var grabFrame = function() {
        var video = state.video;
        if (!video || !video.videoWidth || !video.videoHeight) {
            return '';
        }

        var width = Math.min(state.config.recordingwidth, video.videoWidth);
        var height = Math.round(width * video.videoHeight / video.videoWidth);

        state.canvas.width = width;
        state.canvas.height = height;
        state.canvas.getContext('2d').drawImage(video, 0, 0, width, height);

        return state.canvas.toDataURL(state.mimetype, state.config.recordingquality / 100);
    };

    /**
     * Capture one frame, unless the share has ended in the meantime.
     */
    var tick = function() {
        if (!state.running) {
            return;
        }
        if (!isStreamLive()) {
            // The student stopped sharing. Stop cleanly rather than uploading black frames.
            state.running = false;
            window.clearInterval(state.timer);
            state.timer = null;
            return;
        }

        var dataurl = grabFrame();
        if (dataurl) {
            uploadFrame(dataurl, state.sequence++);
        }
    };

    return {

        /**
         * Start sampling the shared screen shown by the given video element.
         *
         * @param {HTMLVideoElement} video the element the screen share is playing in.
         * @param {Object} config courseid, cmid, quizid, sessionid and the capture settings.
         * @return {boolean} false when this browser cannot capture frames.
         */
        start: function(video, config) {
            if (state.running) {
                return true;
            }

            var canvas = document.createElement('canvas');
            if (!canvas.getContext || !canvas.toDataURL) {
                window.console.warn('Invigilator: this browser cannot capture screen frames.');
                return false;
            }

            state.config = config;
            state.video = video;
            state.canvas = canvas;
            state.sequence = 0;
            state.running = true;

            // Take the first frame straight away, so a very short attempt is still covered.
            tick();
            state.timer = window.setInterval(tick, config.recordinginterval * 1000);

            return true;
        },

        /**
         * Take one last frame and stop sampling.
         */
        stop: function() {
            if (state.running) {
                tick();
            }
            state.running = false;
            if (state.timer) {
                window.clearInterval(state.timer);
                state.timer = null;
            }
        },

        /**
         * Whether frames are currently being captured.
         *
         * @return {boolean}
         */
        isRunning: function() {
            return state.running;
        },

        /**
         * How many frames have been captured so far.
         *
         * @return {number}
         */
        getFrameCount: function() {
            return state.sequence;
        },

        /**
         * Whether any upload is still on its way to the server.
         *
         * @return {boolean}
         */
        hasPendingUploads: function() {
            return state.uploading > 0;
        }
    };
});
