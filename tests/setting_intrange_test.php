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
 * Tests for the range checked admin setting.
 *
 * @package    quizaccess_invigilator
 * @copyright  2021 Brain Station 23
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quizaccess_invigilator;

use quizaccess_invigilator\admin\setting_intrange;

defined('MOODLE_INTERNAL') || die();

/**
 * Tests for the range checked admin setting.
 *
 * @package    quizaccess_invigilator
 * @copyright  2021 Brain Station 23
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \quizaccess_invigilator\admin\setting_intrange
 */
final class setting_intrange_test extends \advanced_testcase {

    /**
     * The interval setting as the settings page builds it.
     *
     * @return setting_intrange
     */
    protected function interval_setting(): setting_intrange {
        return new setting_intrange('quizaccess_invigilator/recordinginterval',
            'Seconds between screenshots', 'How many seconds pass between two screenshots', 10, 2, 600);
    }

    public function test_values_inside_the_range_are_accepted(): void {
        $this->resetAfterTest();

        $setting = $this->interval_setting();
        foreach (['2', '10', '45', '600'] as $value) {
            $this->assertTrue($setting->validate($value), "{$value} should be accepted");
        }
    }

    public function test_values_outside_the_range_are_refused(): void {
        $this->resetAfterTest();

        $setting = $this->interval_setting();
        $expected = get_string('error:outofrange', 'quizaccess_invigilator', (object)['min' => 2, 'max' => 600]);

        // A mistyped zero would flood the file store, and a negative or huge value is nonsense.
        $this->assertEquals($expected, $setting->validate('0'));
        $this->assertEquals($expected, $setting->validate('1'));
        $this->assertEquals($expected, $setting->validate('601'));
        $this->assertEquals($expected, $setting->validate('-10'));
    }

    public function test_values_that_are_not_whole_numbers_are_refused(): void {
        $this->resetAfterTest();

        $setting = $this->interval_setting();
        foreach (['', 'abc', '10.5', '1e3'] as $value) {
            $this->assertIsString($setting->validate($value), "'{$value}' should be refused");
        }
    }

    public function test_quality_range(): void {
        $this->resetAfterTest();

        $setting = new setting_intrange('quizaccess_invigilator/recordingquality',
            'Screenshot quality', 'JPEG quality of each screenshot', 60, 10, 100);

        $this->assertTrue($setting->validate('10'));
        $this->assertTrue($setting->validate('100'));
        $this->assertIsString($setting->validate('9'));
        $this->assertIsString($setting->validate('101'));
    }
}
