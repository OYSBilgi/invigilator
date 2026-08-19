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
 * Tests for the screen recording storage helper.
 *
 * @package    quizaccess_invigilator
 * @copyright  2021 Brain Station 23
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quizaccess_invigilator;

/**
 * Tests for the screen recording storage helper.
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
     * Store one segment and hand back the record it produced.
     *
     * @param string $sessionid
     * @param int $sequence
     * @param string $body content of the fake segment.
     * @return \stdClass
     */
    protected function store(string $sessionid, int $sequence, string $body = 'segment'): \stdClass {
        return recording_manager::store_segment(
            $this->course->id,
            $this->cm->id,
            $this->cm->instance,
            $this->student->id,
            $sessionid,
            $sequence,
            'video/webm;codecs=vp9',
            10,
            $body
        );
    }

    public function test_mimetypes_are_normalised(): void {
        $this->assertEquals('video/webm', recording_manager::normalise_mimetype('video/webm;codecs=vp9'));
        $this->assertEquals('video/mp4', recording_manager::normalise_mimetype('video/mp4'));
        $this->assertEquals('video/webm', recording_manager::normalise_mimetype('application/octet-stream'));
        $this->assertEquals('webm', recording_manager::extension_for('video/webm;codecs=vp8'));
    }

    public function test_settings_fall_back_to_defaults(): void {
        $this->assertEquals(recording_manager::DEFAULTS['recordingsegment'],
            recording_manager::get_setting('recordingsegment'));

        set_config('recordingsegment', 25, 'quizaccess_invigilator');
        $this->assertEquals(25, recording_manager::get_setting('recordingsegment'));

        set_config('enablerecording', 0, 'quizaccess_invigilator');
        $this->assertFalse(recording_manager::is_enabled());
    }

    public function test_storing_a_segment_saves_the_file_and_the_row(): void {
        global $DB;

        $record = $this->store('abc123', 0, 'the bytes');

        $this->assertGreaterThan(0, $record->id);
        $this->assertEquals('recording-abc123-00000.webm', $record->filename);
        $this->assertEquals(strlen('the bytes'), $record->filesize);
        $this->assertStringContainsString('pluginfile.php', $record->recording);
        $this->assertTrue($DB->record_exists(recording_manager::TABLE, ['id' => $record->id]));

        $context = \context_module::instance($this->cm->id);
        $file = get_file_storage()->get_file($context->id, 'quizaccess_invigilator',
            recording_manager::FILEAREA, $record->id, '/', $record->filename);
        $this->assertNotFalse($file);
        $this->assertEquals('the bytes', $file->get_content());
    }

    public function test_segments_are_grouped_into_sessions(): void {
        $this->store('sessionone', 0);
        $this->store('sessionone', 1);
        $this->store('sessiontwo', 0);

        $sessions = recording_manager::get_sessions($this->cm->id);
        $this->assertCount(2, $sessions);

        $segments = recording_manager::get_segments($this->cm->id, 'sessionone');
        $this->assertCount(2, $segments);
        $this->assertEquals([0, 1], array_values(array_map(function($segment) {
            return (int)$segment->sequence;
        }, $segments)));
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
