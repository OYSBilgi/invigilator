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
 * Records the shared screen in short, standalone segments and uploads them as they finish.
 *
 * The MediaRecorder is restarted for every segment on purpose: each upload is then a complete,
 * playable file on its own, memory stays flat during long quizzes, and a crashed browser or a
 * closed window only ever costs the segment that was in progress.
 *
 * @module     quizaccess_invigilator/recorder
 * @copyright  2021 Brain Station 23
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['core/ajax'], function(Ajax) {

    var MIME_CANDIDATES = [
        'video/webm;codecs=vp9',
        'video/webm;codecs=vp8',
        'video/webm',
        'video/mp4'
    ];

    var state = {
        config: null,
        stream: null,
        recorder: null,
        chunks: [],
        sequence: 0,
        segmentStart: 0,
        segmentTimer: null,
        running: false,
        mimetype: '',
        uploading: 0
    };

    /**
     * First container the browser can actually record with.
     *
     * @return {string} a mime type, or an empty string when recording is not possible here.
     */
    var pickMimeType = function() {
        if (typeof window.MediaRecorder === 'undefined') {
            return '';
        }
        if (typeof window.MediaRecorder.isTypeSupported !== 'function') {
            // Very old implementation, webm is the only sensible guess.
            return 'video/webm';
        }
        for (var i = 0; i < MIME_CANDIDATES.length; i++) {
            if (window.MediaRecorder.isTypeSupported(MIME_CANDIDATES[i])) {
                return MIME_CANDIDATES[i];
            }
        }
        return '';
    };

    /**
     * Whether the shared screen is still being delivered.
     *
     * @return {boolean}
     */
    var isStreamLive = function() {
        if (!state.stream || !state.stream.active) {
            return false;
        }
        var tracks = state.stream.getVideoTracks();
        return tracks.length > 0 && tracks[0].readyState === 'live';
    };

    /**
     * Ask the browser to hand us a smaller, slower stream so the segments stay small.
     *
     * Nothing depends on this succeeding: browsers are free to ignore the constraints.
     *
     * @return {Promise}
     */
    var applyQualityConstraints = function() {
        var track = state.stream.getVideoTracks()[0];
        if (!track || typeof track.applyConstraints !== 'function') {
            return Promise.resolve();
        }
        return track.applyConstraints({
            width: {max: state.config.recordingwidth},
            frameRate: {max: state.config.recordingframerate}
        }).catch(function() {
            return true;
        });
    };

    /**
     * Send one finished segment to the server.
     *
     * @param {Blob} blob the recorded segment.
     * @param {number} sequence its position in the session.
     * @param {number} duration how many seconds it covers.
     * @return {Promise}
     */
    var uploadSegment = function(blob, sequence, duration) {
        var maxbytes = state.config.recordingmaxsize * 1024 * 1024;
        if (!blob || !blob.size) {
            return Promise.resolve();
        }
        if (blob.size > maxbytes) {
            window.console.warn('Invigilator: recording segment ' + sequence + ' dropped, it is larger than the ' +
                state.config.recordingmaxsize + 'MB limit. Lower the segment length or the bitrate.');
            return Promise.resolve();
        }

        state.uploading++;

        return new Promise(function(resolve) {
            var reader = new FileReader();
            reader.onloadend = function() {
                var request = {
                    methodname: 'quizaccess_invigilator_send_recording',
                    args: {
                        courseid: state.config.courseid,
                        cmid: state.config.cmid,
                        quizid: state.config.quizid,
                        sessionid: state.config.sessionid,
                        sequence: sequence,
                        mimetype: state.mimetype,
                        duration: duration,
                        recording: reader.result
                    }
                };

                Ajax.call([request])[0].done(function(data) {
                    if (data.warnings && data.warnings.length) {
                        window.console.warn('Invigilator: recording segment ' + sequence + ' rejected.', data.warnings);
                    }
                    state.uploading--;
                    resolve();
                }).fail(function(error) {
                    window.console.warn('Invigilator: recording segment ' + sequence + ' could not be sent.', error);
                    state.uploading--;
                    resolve();
                });
            };
            reader.onerror = function() {
                state.uploading--;
                resolve();
            };
            reader.readAsDataURL(blob);
        });
    };

    /**
     * Record the next segment, and keep going until something stops us.
     */
    var recordSegment = function() {
        if (!state.running || !isStreamLive()) {
            state.running = false;
            return;
        }

        var options = {mimeType: state.mimetype};
        if (state.config.recordingbitrate) {
            options.videoBitsPerSecond = state.config.recordingbitrate * 1000;
        }

        try {
            state.recorder = new window.MediaRecorder(state.stream, options);
        } catch (error) {
            // Some browsers reject the bitrate hint, retry with defaults before giving up.
            try {
                state.recorder = new window.MediaRecorder(state.stream, {mimeType: state.mimetype});
            } catch (fallbackerror) {
                window.console.warn('Invigilator: screen recording is not available in this browser.', fallbackerror);
                state.running = false;
                return;
            }
        }

        state.chunks = [];
        state.segmentStart = Date.now();

        state.recorder.ondataavailable = function(event) {
            if (event.data && event.data.size) {
                state.chunks.push(event.data);
            }
        };

        state.recorder.onstop = function() {
            var duration = Math.round((Date.now() - state.segmentStart) / 1000);
            var blob = new Blob(state.chunks, {type: state.mimetype});
            var sequence = state.sequence++;
            state.chunks = [];

            uploadSegment(blob, sequence, duration);

            if (state.running && isStreamLive()) {
                recordSegment();
            } else {
                state.running = false;
            }
        };

        state.recorder.onerror = function(event) {
            window.console.warn('Invigilator: the recorder reported an error.', event);
        };

        state.recorder.start();

        state.segmentTimer = window.setTimeout(function() {
            closeCurrentSegment();
        }, state.config.recordingsegment * 1000);
    };

    /**
     * Stop the running recorder, which flushes the current segment through onstop.
     */
    var closeCurrentSegment = function() {
        if (state.segmentTimer) {
            window.clearTimeout(state.segmentTimer);
            state.segmentTimer = null;
        }
        if (state.recorder && state.recorder.state !== 'inactive') {
            state.recorder.stop();
        }
    };

    return {

        /**
         * Start recording the given screen share stream.
         *
         * @param {MediaStream} stream the stream returned by getDisplayMedia.
         * @param {Object} config courseid, cmid, quizid, sessionid and the recording settings.
         * @return {boolean} false when this browser cannot record.
         */
        start: function(stream, config) {
            if (state.running) {
                return true;
            }

            state.mimetype = pickMimeType();
            if (!state.mimetype) {
                window.console.warn('Invigilator: this browser has no MediaRecorder, only screenshots will be taken.');
                return false;
            }

            state.config = config;
            state.stream = stream;
            state.sequence = 0;
            state.running = true;

            var track = stream.getVideoTracks()[0];
            if (track) {
                track.addEventListener('ended', function() {
                    // The student stopped sharing: flush what we have and stop cleanly.
                    state.running = false;
                    closeCurrentSegment();
                });
            }

            applyQualityConstraints().then(function() {
                recordSegment();
                return true;
            }).catch(function() {
                recordSegment();
            });

            return true;
        },

        /**
         * Stop recording and upload the segment that is in progress.
         */
        stop: function() {
            state.running = false;
            closeCurrentSegment();
        },

        /**
         * Whether a recording is currently being made.
         *
         * @return {boolean}
         */
        isRunning: function() {
            return state.running;
        },

        /**
         * How many segments have been completed so far.
         *
         * @return {number}
         */
        getSegmentCount: function() {
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
