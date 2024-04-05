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
 * Test report.
 *
 * @package   local_community
 * @copyright 2024 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use core_reportbuilder\system_report_factory;

$inpopup = optional_param('popup', 0, PARAM_BOOL);
$id = required_param('id', PARAM_INT);

require_login(null, false);
$systemcontext = context_system::instance();

// Redirect if the user is a guest.
if (isguestuser()) {
    $url = new moodle_url($CFG->wwwroot);
    redirect($url);
    die();
}

$community = $DB->get_record('local_community', ['id' => $id], '*', MUST_EXIST);

$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/community/members.php');
$PAGE->set_title(get_string('memberslist', 'local_community'));
$PAGE->set_heading(get_string('memberslist', 'local_community') . ' - ' . $community->name);

if ($inpopup) {
    $PAGE->set_pagelayout('popup');
} else {
    $PAGE->set_pagelayout('report');
}

echo $OUTPUT->header();

if (empty($community->cohortid)) {
    throw new moodle_exception('notcohortincommunity', 'local_community');
}

// Check if the user is a member of the community.
$ismember = cohort_is_member($community->cohortid, $USER->id);

if (!$ismember && !has_capability('local/community:manage', $systemcontext)) {
    throw new moodle_exception('nopermissiontoviewmembers', 'local_community');
}

$report = system_report_factory::create(local_community\systemreports\members::class, $systemcontext,
                                            'local_community', 'members', $id, ['communityid' => $community->id]);

echo $report->output();

echo $OUTPUT->footer();
