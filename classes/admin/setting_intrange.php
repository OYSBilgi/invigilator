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
 * Admin setting holding a whole number inside a fixed range.
 *
 * @package    quizaccess_invigilator
 * @copyright  2021 Brain Station 23
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quizaccess_invigilator\admin;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/adminlib.php');

/**
 * A text setting that only accepts a whole number between a minimum and a maximum.
 *
 * The capture settings are typed in by hand, so a mistyped zero or a value that would flood
 * the file store is refused when the form is saved rather than silently corrected later.
 *
 * @package    quizaccess_invigilator
 * @copyright  2021 Brain Station 23
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class setting_intrange extends \admin_setting_configtext {

    /** @var int smallest value that is accepted. */
    protected $minvalue;

    /** @var int largest value that is accepted. */
    protected $maxvalue;

    /**
     * Constructor.
     *
     * @param string $name unique ascii name, in the form plugin/settingname.
     * @param string $visiblename the label shown on the settings page.
     * @param string $description the help text shown under the label.
     * @param int $defaultsetting value used until an admin saves one.
     * @param int $minvalue smallest accepted value.
     * @param int $maxvalue largest accepted value.
     */
    public function __construct($name, $visiblename, $description, $defaultsetting, $minvalue, $maxvalue) {
        $this->minvalue = $minvalue;
        $this->maxvalue = $maxvalue;

        parent::__construct($name, $visiblename, $description, $defaultsetting, PARAM_INT, 10);
    }

    /**
     * Refuse anything that is not a whole number inside the range.
     *
     * @param string $data the value the admin typed in.
     * @return bool|string true when the value is fine, otherwise the message to show.
     */
    public function validate($data) {
        $parentcheck = parent::validate($data);
        if ($parentcheck !== true) {
            return $parentcheck;
        }

        if (!is_numeric(trim($data)) || (int)$data != trim($data)
                || (int)$data < $this->minvalue || (int)$data > $this->maxvalue) {
            return get_string('error:outofrange', 'quizaccess_invigilator', (object)[
                'min' => $this->minvalue,
                'max' => $this->maxvalue,
            ]);
        }

        return true;
    }
}
