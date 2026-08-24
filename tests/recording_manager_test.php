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
 * Tests for the screen frame storage helper.
 *
 * @package    quizaccess_invigilator
 * @copyright  2021 Brain Station 23
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quizaccess_invigilator;

/**
 * Tests for the screen frame storage helper.
 *
 * @package    quizaccess_invigilator
 * @copyright  2021 Brain Station 23
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \quizaccess_invigilator\recording_manager
 */
final class recording_manager_test extends \advanced_testcase {

    /** @var \stdClass the course used by the tests. */
    protected $course;

    /** @var \stdClass the quiz course module used by the tests. */
    protected $cm;

    /** @var \stdClass the student being recorded. */
    protected $student;

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $this->course = $generator->create_course();
        $quiz = $generator->create_module('quiz', ['course' => $this->course->id]);
        $this->cm = get_coursemodule_from_instance('quiz', $quiz->id);
        $this->student = $generator->create_and_enrol($this->course, 'student');
    }

    /**
     * Store one frame and hand back the record it produced.
     *
     * @param string $sessionid
     * @param int $sequence
     * @param string $body content of the fake frame.
     * @return \stdClass
     */
    protected function store(string $sessionid, int $sequence, string $body = 'frame'): \stdClass {
        return recording_manager::store_frame(
            $this->course->id,
            $this->cm->id,
            $this->cm->instance,
            $this->student->id,
            $sessionid,
            $sequence,
            'image/jpeg',
            10,
            $body
        );
    }

    public function test_mimetypes_are_normalised(): void {
        $this->assertEquals('image/jpeg', recording_manager::normalise_mimetype('image/jpeg'));
        $this->assertEquals('image/webp', recording_manager::normalise_mimetype('image/webp'));
        $this->assertEquals('image/jpeg', recording_manager::normalise_mimetype('video/webm;codecs=vp9'));
        $this->assertEquals('jpg', recording_manager::extension_for('image/jpeg'));
    }

    public function test_settings_fall_back_to_defaults(): void {
        $this->assertEquals(recording_manager::DEFAULTS['recordinginterval'],
            recording_manager::get_setting('recordinginterval'));

        set_config('recordinginterval', 25, 'quizaccess_invigilator');
        $this->assertEquals(25, recording_manager::get_setting('recordinginterval'));

        set_config('enablerecording', 0, 'quizaccess_invigilator');
        $this->assertFalse(recording_manager::is_enabled());
    }

    /**
     * A real, if boring, JPEG the thumbnail code can actually scale.
     *
     * @param int $width
     * @param int $height
     * @return string
     */
    protected function fake_screenshot(int $width = 1280, int $height = 720): string {
        $image = imagecreatetruecolor($width, $height);
        imagefilledrectangle($image, 0, 0, $width, $height, imagecolorallocate($image, 40, 90, 160));

        ob_start();
        imagejpeg($image, null, 70);
        $data = ob_get_clean();
        imagedestroy($image);

        return $data;
    }

    public function test_a_thumbnail_is_stored_beside_the_frame(): void {
        $this->resetAfterTest();
        $this->requires_gd();

        set_config('recordingthumbwidth', 200, 'quizaccess_invigilator');

        $record = $this->store('withthumb', 0, $this->fake_screenshot());
        $context = \context_module::instance($this->cm->id);

        $thumbnail = get_file_storage()->get_file($context->id, 'quizaccess_invigilator',
            recording_manager::THUMBFILEAREA, $record->id, '/', recording_manager::thumbnail_filename($record));

        $this->assertNotFalse($thumbnail, 'a thumbnail should have been stored');
        $this->assertEquals('thumb-withthumb-00000.jpg', $thumbnail->get_filename());

        $size = getimagesizefromstring($thumbnail->get_content());
        $this->assertEquals(200, $size[0], 'the thumbnail should be scaled to the configured width');
        $this->assertLessThan($record->filesize, $thumbnail->get_filesize());
    }

    public function test_frames_carry_the_urls_the_album_needs(): void {
        $this->resetAfterTest();
        $this->requires_gd();

        $withthumb = $this->store('album', 0, $this->fake_screenshot());
        $context = \context_module::instance($this->cm->id);

        $frames = recording_manager::get_frames_with_urls($context, 'album');
        $frame = reset($frames);

        $this->assertStringContainsString(recording_manager::FILEAREA, $frame->fullurl);
        $this->assertStringContainsString(recording_manager::THUMBFILEAREA, $frame->thumburl);
        $this->assertNotEquals($frame->fullurl, $frame->thumburl);
        $this->assertEquals($withthumb->id, $frame->id);
    }

    public function test_frames_without_a_thumbnail_fall_back_to_the_full_image(): void {
        $this->resetAfterTest();

        // Not an image, so no thumbnail can be built from it.
        $record = $this->store('nothumb', 0, 'not an image');
        $context = \context_module::instance($this->cm->id);

        $frames = recording_manager::get_frames_with_urls($context, 'nothumb');
        $frame = reset($frames);

        $this->assertEquals($frame->fullurl, $frame->thumburl);
        $this->assertEquals($record->id, $frame->id);
    }

    public function test_missing_thumbnails_are_backfilled(): void {
        $this->resetAfterTest();
        $this->requires_gd();

        $record = $this->store('backfill', 0, $this->fake_screenshot());
        $context = \context_module::instance($this->cm->id);
        $fs = get_file_storage();

        // Pretend the frame was captured before the album existed.
        $fs->delete_area_files($context->id, 'quizaccess_invigilator',
            recording_manager::THUMBFILEAREA, $record->id);
        $this->assertFalse($fs->get_file($context->id, 'quizaccess_invigilator',
            recording_manager::THUMBFILEAREA, $record->id, '/', recording_manager::thumbnail_filename($record)));

        $this->assertEquals(1, recording_manager::backfill_thumbnails(10));
        $this->assertNotFalse($fs->get_file($context->id, 'quizaccess_invigilator',
            recording_manager::THUMBFILEAREA, $record->id, '/', recording_manager::thumbnail_filename($record)));

        // Nothing left to do on the next run.
        $this->assertEquals(0, recording_manager::backfill_thumbnails(10));
    }

    public function test_deleting_a_frame_takes_its_thumbnail(): void {
        $this->resetAfterTest();
        $this->requires_gd();

        $record = $this->store('deleteme', 0, $this->fake_screenshot());
        $context = \context_module::instance($this->cm->id);

        $this->assertEquals(1, recording_manager::delete_session('deleteme', $this->cm->id));
        $this->assertFalse(get_file_storage()->get_file($context->id, 'quizaccess_invigilator',
            recording_manager::THUMBFILEAREA, $record->id, '/', recording_manager::thumbnail_filename($record)));
    }

    /**
     * Skip a test that cannot run without the image library.
     */
    protected function requires_gd(): void {
        if (!function_exists('imagecreatefromstring') || !function_exists('imagescale')) {
            $this->markTestSkipped('GD with imagescale is needed to build thumbnails.');
        }
    }

    public function test_storing_a_frame_saves_the_file_and_the_row(): void {
        global $DB;

        $record = $this->store('abc123', 0, 'the bytes');

        $this->assertGreaterThan(0, $record->id);
        $this->assertEquals('frame-abc123-00000.jpg', $record->filename);
        $this->assertEquals(strlen('the bytes'), $record->filesize);
        $this->assertStringContainsString('pluginfile.php', $record->recording);
        $this->assertTrue($DB->record_exists(recording_manager::TABLE, ['id' => $record->id]));

        $context = \context_module::instance($this->cm->id);
        $file = get_file_storage()->get_file($context->id, 'quizaccess_invigilator',
            recording_manager::FILEAREA, $record->id, '/', $record->filename);
        $this->assertNotFalse($file);
        $this->assertEquals('the bytes', $file->get_content());
    }

    public function test_frames_are_grouped_into_sessions(): void {
        $this->store('sessionone', 0);
        $this->store('sessionone', 1);
        $this->store('sessiontwo', 0);

        $sessions = recording_manager::get_sessions($this->cm->id);
        $this->assertCount(2, $sessions);

        $frames = recording_manager::get_frames($this->cm->id, 'sessionone');
        $this->assertCount(2, $frames);
        $this->assertEquals([0, 1], array_values(array_map(function($frame) {
            return (int)$frame->sequence;
        }, $frames)));
        $this->assertEquals(3, array_sum(array_map(function($session) {
            return (int)$session->frames;
        }, $sessions)));
    }

    public function test_deleting_a_session_removes_its_files(): void {
        global $DB;

        $first = $this->store('sessionone', 0);
        $this->store('sessionone', 1);
        $kept = $this->store('sessiontwo', 0);

        $this->assertEquals(2, recording_manager::delete_session('sessionone', $this->cm->id));
        $this->assertEquals(1, $DB->count_records(recording_manager::TABLE));

        $context = \context_module::instance($this->cm->id);
        $this->assertFalse(get_file_storage()->get_file($context->id, 'quizaccess_invigilator',
            recording_manager::FILEAREA, $first->id, '/', $first->filename));
        $this->assertNotFalse(get_file_storage()->get_file($context->id, 'quizaccess_invigilator',
            recording_manager::FILEAREA, $kept->id, '/', $kept->filename));
    }

    public function test_expired_recordings_are_purged(): void {
        global $DB;

        $old = $this->store('oldsession', 0);
        $new = $this->store('newsession', 0);

        $DB->set_field(recording_manager::TABLE, 'timecreated', time() - (10 * DAYSECS), ['id' => $old->id]);

        // Retention off keeps everything.
        $this->assertEquals(0, recording_manager::purge_expired(0));
        $this->assertEquals(2, $DB->count_records(recording_manager::TABLE));

        $this->assertEquals(1, recording_manager::purge_expired(7));
        $this->assertEquals([$new->id], array_keys($DB->get_records(recording_manager::TABLE)));
    }

    public function test_deleting_by_user_and_by_module(): void {
        global $DB;

        $this->store('sessionone', 0);
        $this->store('sessionone', 1);

        $this->assertEquals(2, recording_manager::delete_for_user($this->cm->id, $this->student->id));
        $this->assertEquals(0, $DB->count_records(recording_manager::TABLE));

        $this->store('sessiontwo', 0);
        $this->assertEquals(1, recording_manager::delete_for_cm($this->cm->id));
        $this->assertEquals(0, $DB->count_records(recording_manager::TABLE));
    }
}
