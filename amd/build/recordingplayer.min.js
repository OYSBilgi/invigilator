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
 * Plays the frames of one capture session in order, so a sampled attempt watches
 * like a time lapse instead of a folder full of images.
 *
 * @module     quizaccess_invigilator/recordingplayer
 * @copyright  2021 Brain Station 23
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// The lightbox module is pulled in for its side effect: loading it binds the handler that opens
// a full size view when the frame is clicked.
define(['quizaccess_invigilator/lightbox2'], function() {

    return {

        /**
         * Wire the player up to the frame list rendered by recordings.php.
         *
         * @param {Object} props frames, the status template and the button labels.
         */
        init: function(props) {
            var image = document.getElementById('invigilator-player-frame');
            var zoomlink = document.getElementById('invigilator-player-link');
            var status = document.getElementById('invigilator-player-status');
            var list = document.getElementById('invigilator-player-list');
            var toggle = document.getElementById('invigilator-player-toggle');
            var previous = document.getElementById('invigilator-player-prev');
            var next = document.getElementById('invigilator-player-next');
            var seek = document.getElementById('invigilator-player-seek');
            var speed = document.getElementById('invigilator-player-speed');
            var frames = props.frames || [];
            var current = -1;
            var timer = null;

            if (!image || !frames.length) {
                return;
            }

            /**
             * Keep the next frame in the browser cache so playback does not flicker.
             *
             * @param {number} index
             */
            var preload = function(index) {
                if (index >= 0 && index < frames.length) {
                    var preloader = new window.Image();
                    preloader.src = frames[index].url;
                }
            };

            /**
             * Show one frame and bring the controls in line with it.
             *
             * @param {number} index position in the session.
             */
            var show = function(index) {
                if (index < 0 || index >= frames.length) {
                    return;
                }
                current = index;
                image.src = frames[index].url;

                if (zoomlink) {
                    // Enlarging always shows the frame that is on screen right now.
                    zoomlink.href = frames[index].url;
                    zoomlink.setAttribute('data-title', frames[index].time);
                }

                if (status) {
                    status.textContent = props.playingnow
                        .replace('{$a->number}', index + 1)
                        .replace('{$a->total}', frames.length)
                        .replace('{$a->time}', frames[index].time);
                }
                if (seek) {
                    seek.value = index;
                }
                if (list) {
                    var items = list.querySelectorAll('[data-invigilator-frame]');
                    for (var i = 0; i < items.length; i++) {
                        items[i].parentNode.classList.toggle('current', i === index);
                    }
                    if (items[index] && items[index].scrollIntoView) {
                        items[index].scrollIntoView({block: 'nearest'});
                    }
                }

                preload(index + 1);
            };

            /**
             * Stop the playback, leaving the current frame on screen.
             */
            var pause = function() {
                if (timer) {
                    window.clearInterval(timer);
                    timer = null;
                }
                if (toggle) {
                    toggle.textContent = props.playlabel;
                }
            };

            /**
             * Step through the frames at the chosen speed, starting over once the end is reached.
             */
            var play = function() {
                pause();

                if (current >= frames.length - 1) {
                    show(0);
                }

                var framespersecond = speed ? parseInt(speed.value, 10) : 2;
                timer = window.setInterval(function() {
                    if (current >= frames.length - 1) {
                        pause();
                        return;
                    }
                    show(current + 1);
                }, 1000 / (framespersecond || 1));

                if (toggle) {
                    toggle.textContent = props.pauselabel;
                }
            };

            if (toggle) {
                toggle.addEventListener('click', function() {
                    if (timer) {
                        pause();
                    } else {
                        play();
                    }
                });
            }

            if (previous) {
                previous.addEventListener('click', function() {
                    pause();
                    show(current - 1);
                });
            }

            if (next) {
                next.addEventListener('click', function() {
                    pause();
                    show(current + 1);
                });
            }

            if (seek) {
                seek.addEventListener('input', function() {
                    pause();
                    show(parseInt(seek.value, 10));
                });
            }

            if (speed) {
                speed.addEventListener('change', function() {
                    // Restart the timer so the new speed takes effect straight away.
                    if (timer) {
                        play();
                    }
                });
            }

            if (zoomlink) {
                // Looking at a still while the player runs on underneath helps nobody.
                zoomlink.addEventListener('click', pause);
            }

            if (list) {
                list.addEventListener('click', function(event) {
                    var link = event.target.closest('[data-invigilator-frame]');
                    if (!link) {
                        return;
                    }
                    event.preventDefault();
                    pause();
                    show(parseInt(link.getAttribute('data-invigilator-frame'), 10));
                });
            }

            show(0);
        }
    };
});
