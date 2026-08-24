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
 * Scheduled clean up of expired screen recordings.
 *
 * @package    quizaccess_invigilator
 * @copyright  2021 Brain Station 23
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quizaccess_invigilator\task;

use core\task\scheduled_task;
use quizaccess_invigilator\recording_manager;

defined('MOODLE_INTERNAL') || die();

/**
 * Deletes captures that are older than the configured retention period, and fills in
 * any album thumbnails that are still missing.
 *
 * @package    quizaccess_invigilator
 * @copyright  2021 Brain Station 23
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cleanup_recordings extends scheduled_task {

    /**
     * Name shown on the scheduled tasks page.
     *
     * @return string
     */
    public function get_name() {
        return get_string('task:cleanuprecordings', 'quizaccess_invigilator');
    }

    /**
     * Delete expired frames, in batches so one run cannot block the cron for long, then build
     * a bounded number of the thumbnails the album is still missing.
     */
    public function execute() {
        $retention = recording_manager::get_setting('recordingretention');

        if ($retention <= 0) {
            mtrace('quizaccess_invigilator: retention is disabled, no frames deleted.');
        } else {
            $total = 0;
            do {
                $deleted = recording_manager::purge_expired($retention);
                $total += $deleted;
            } while ($deleted > 0);

            mtrace("quizaccess_invigilator: deleted {$total} expired frames.");
        }

        // Frames captured before the album existed have no thumbnail yet.
        $built = recording_manager::backfill_thumbnails(500);
        mtrace("quizaccess_invigilator: built {$built} missing album thumbnails.");
    }
}
