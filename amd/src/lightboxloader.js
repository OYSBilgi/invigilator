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
 * Loads the lightbox so that clicking a captured image opens it at full size.
 *
 * The lightbox binds itself when the module is loaded, so a page only needs to pull it in.
 * This wrapper exists to give js_call_amd() a function to call: calling the lightbox's own
 * init() a second time would bind its handlers twice.
 *
 * @module     quizaccess_invigilator/lightboxloader
 * @copyright  2021 Brain Station 23
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['quizaccess_invigilator/lightbox2'], function() {

    return {

        /**
         * Nothing to do: requiring the lightbox above is what enables it.
         *
         * @return {boolean}
         */
        init: function() {
            return true;
        }
    };
});
