<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Class containing form definition to manage an improve criteria.
 *
 * @package   local_community
 * @copyright 2023 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_community\forms;
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/formslib.php');

use moodleform;

/**
 * The form for handling editing an improve criteria.
 *
 * @copyright 2023 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class community extends moodleform {

    /**
     * @var object List of local data.
     */
    protected $_data;

    /**
     * Form definition.
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        // This contains the data of this form.
        $this->_data = isset($this->_customdata['data']) ? $this->_customdata['data'] : null;
        $filemanageroptions = $this->_customdata['filemanageroptions'];
        $userid = $this->_customdata['userid'];

        $mform->addElement('text', 'name', get_string('name', 'local_community'), ['maxlength' => 255]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('textarea', 'description', get_string('description', 'local_community'));
        $mform->setType('description', PARAM_TEXT);

        $mform->addElement('filemanager', 'banner', get_string('banner', 'local_community'), null, $filemanageroptions);
        $mform->addHelpButton('banner', 'banner', 'local_community');

        $mform->addElement('text', 'idnumber', get_string('idnumber'), ['maxlength' => 63]);
        $mform->setType('idnumber', PARAM_TEXT);
        $mform->addRule('idnumber', null, 'required', null, 'client');

        $mform->addElement('text', 'email', get_string('email'), ['maxlength' => 127]);
        $mform->setType('email', PARAM_EMAIL);

        $mform->addElement('text', 'phone', get_string('phone', 'local_community'), ['maxlength' => 31]);
        $mform->setType('phone', PARAM_TEXT);

        $mform->addElement('text', 'address', get_string('address'), ['maxlength' => 255]);
        $mform->setType('address', PARAM_TEXT);

        $mform->addElement('text', 'registercode', get_string('registercode', 'local_community'), ['maxlength' => 63]);
        $mform->setType('registercode', PARAM_TEXT);

        $mform->addElement('checkbox', 'public', get_string('public', 'local_community'));
        $mform->setType('public', PARAM_INT);
        $mform->addHelpButton('public', 'public', 'local_community');

        $mform->addElement('hidden', 'id', null);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'u', $userid);
        $mform->setType('u', PARAM_INT);

        $this->add_action_buttons();

        // Finally set the current form data.
        $this->set_data($this->_data);
    }

}
