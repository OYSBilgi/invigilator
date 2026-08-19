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
 * Plays the segments of one recording session one after another, so a chopped up
 * recording watches like a single video.
 *
 * @module     quizaccess_invigilator/recordingplayer
 * @copyright  2021 Brain Station 23
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([], function() {

    return {

        /**
         * Wire the player up to the segment list rendered by recordings.php.
         *
         * @param {Object} props segments and the label used for the status line.
         */
        init: function(props) {
            var video = document.getElementById('invigilator-player');
            var status = document.getElementById('invigilator-player-status');
            var list = document.getElementById('invigilator-player-list');
            var segments = props.segments || [];
            var current = -1;

            if (!video || !segments.length) {
                return;
            }

            /**
             * Load a segment and, unless it is the very first one, start playing straight away.
             *
             * @param {number} index position in the playlist.
             * @param {boolean} autoplay
             */
            var play = function(index, autoplay) {
                if (index < 0 || index >= segments.length) {
                    return;
                }
                current = index;
                video.src = segments[index].url;

                if (status) {
                    status.textContent = props.playingnow
                        .replace('{$a->number}', index + 1)
                        .replace('{$a->total}', segments.length)
                        .replace('{$a->time}', segments[index].label);
                }

                if (list) {
                    var items = list.querySelectorAll('[data-invigilator-segment]');
                    for (var i = 0; i < items.length; i++) {
                        items[i].parentNode.classList.toggle('current', i === index);
                    }
                }

                if (autoplay) {
                    var started = video.play();
                    if (started && typeof started.catch === 'function') {
                        // Autoplay can be blocked; the controls still work, so this is not an error.
                        started.catch(function() {
                            return true;
                        });
                    }
                }
            };

            video.addEventListener('ended', function() {
                if (current + 1 < segments.length) {
                    play(current + 1, true);
                } else if (status) {
                    status.textContent = '';
                }
            });

            video.addEventListener('error', function() {
                // A single unreadable segment should not stop the playback of the rest.
                if (current + 1 < segments.length) {
                    play(current + 1, true);
                }
            });

            if (list) {
                list.addEventListener('click', function(event) {
                    var link = event.target.closest('[data-invigilator-segment]');
                    if (!link) {
                        return;
                    }
                    event.preventDefault();
                    play(parseInt(link.getAttribute('data-invigilator-segment'), 10), true);
                });
            }

            play(0, false);
        }
    };
});
