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
 * Tests that an attempt cannot start until the student agrees.
 *
 * @package    quizaccess_invigilator
 * @copyright  2021 Brain Station 23
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quizaccess_invigilator;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/quiz/accessrule/invigilator/rule.php');

/**
 * Tests that an attempt cannot start until the student agrees.
 *
 * @package    quizaccess_invigilator
 * @copyright  2021 Brain Station 23
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \quizaccess_invigilator
 */
final class preflight_check_test extends \advanced_testcase {

    /**
     * The rule, built without a quiz: neither method under test looks at one.
     *
     * @return \quizaccess_invigilator
     */
    protected function rule(): \quizaccess_invigilator {
        return (new \ReflectionClass(\quizaccess_invigilator::class))->newInstanceWithoutConstructor();
    }

    public function test_new_attempts_must_pass_the_check(): void {
        $this->resetAfterTest();

        // Starting a new attempt: the form is shown, and the post to startattempt.php is validated.
        $this->assertTrue($this->rule()->is_preflight_check_required(null));
        $this->assertTrue($this->rule()->is_preflight_check_required(0));
        $this->assertTrue($this->rule()->is_preflight_check_required(''));

        // Continuing an attempt that exists: the student already agreed when it was started.
        $this->assertFalse($this->rule()->is_preflight_check_required(42));
    }

    /**
     * The data the browser posts once a screen is really being shared.
     *
     * @param array $extra values to add or override.
     * @return array
     */
    protected function shared_screen_data(array $extra = []): array {
        return array_merge([
            'invigilator_window_surface' => 'live',
            'invigilator_share_state' => 'true',
        ], $extra);
    }

    public function test_attempt_is_refused_without_a_screen_share(): void {
        $this->resetAfterTest();

        $expected = get_string('youmustsharescreen', 'quizaccess_invigilator');

        // Nothing shared at all, even with the box ticked.
        $errors = $this->rule()->validate_preflight_check(['invigilator' => 1], [], [], null);
        $this->assertEquals($expected, $errors['invigilator']);

        // The share was stopped again before the form was posted.
        $errors = $this->rule()->validate_preflight_check([
            'invigilator' => 1,
            'invigilator_window_surface' => 'live',
            'invigilator_share_state' => '0',
        ], [], [], null);
        $this->assertEquals($expected, $errors['invigilator']);

        // A made up surface value is not a share either.
        $errors = $this->rule()->validate_preflight_check([
            'invigilator' => 1,
            'invigilator_window_surface' => 'browser',
            'invigilator_share_state' => 'true',
        ], [], [], null);
        $this->assertEquals($expected, $errors['invigilator']);
    }

    public function test_attempt_is_refused_without_the_agreement(): void {
        $this->resetAfterTest();

        $expected = get_string('youmustagree', 'quizaccess_invigilator');

        $errors = $this->rule()->validate_preflight_check($this->shared_screen_data(), [], [], null);
        $this->assertEquals($expected, $errors['invigilator']);

        $errors = $this->rule()->validate_preflight_check(
            $this->shared_screen_data(['invigilator' => 0]), [], [], null);
        $this->assertEquals($expected, $errors['invigilator']);
    }

    public function test_attempt_is_allowed_after_both_steps(): void {
        $this->resetAfterTest();

        $this->assertEquals([], $this->rule()->validate_preflight_check(
            $this->shared_screen_data(['invigilator' => 1]), [], [], null));
    }

    public function test_existing_errors_are_kept(): void {
        $this->resetAfterTest();

        $errors = $this->rule()->validate_preflight_check(
            $this->shared_screen_data(['invigilator' => 1]), [], ['other' => 'boom'], null);
        $this->assertEquals(['other' => 'boom'], $errors);
    }

}
