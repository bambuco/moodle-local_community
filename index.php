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
 * List of available communities.
 *
 * @package   local_community
 * @copyright 2023 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

$userid = optional_param('u', $USER->id, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_INT);
$confirm = optional_param('confirm', '', PARAM_ALPHANUM);

require_login(null, false);

if ($userid != $USER->id) {
    require_capability('local/community:manage', context_system::instance());
} else {
    $userid = $USER->id;
    require_capability('local/community:manageown', context_system::instance());
}

$syscontext = context_system::instance();

$PAGE->set_context($syscontext);
$PAGE->set_url('/local/community/index.php');
$PAGE->set_pagelayout('incourse');
$PAGE->set_heading(get_string('communitieslist', 'local_community'));
$PAGE->set_title(get_string('communitieslist', 'local_community'));

echo $OUTPUT->header();

// Delete an avatar, after confirmation.
if ($delete && confirm_sesskey()) {
    $community = $DB->get_record('local_community', ['id' => $delete], '*', MUST_EXIST);

    if (!$community->userid == $USER->id && !has_capability('local/community:manage', $syscontext)) {
        throw new moodle_exception('nopermissiontodelete', 'local_community');
    } else {
        $userid = $community->userid;
    }

    if ($confirm != md5($delete)) {
        $params = $USER->id == $userid ? [] : ['u' => $userid];
        $returnurl = new moodle_url('/local/community/index.php', $params);
        echo $OUTPUT->heading(get_string('communitydelete', 'local_community'), 3);
        $optionsyes = ['delete' => $delete, 'confirm' => md5($delete), 'sesskey' => sesskey(), 'u' => $userid];
        echo $OUTPUT->confirm(get_string('deletecheck', '', "'{$community->name}'"),
                                new moodle_url($returnurl, $optionsyes), $returnurl);
        echo $OUTPUT->footer();
        die;
    } else if (data_submitted()) {

        \local_community\community::delete($community);
        $msg = 'communitydeleted';
    }
}

if (!empty($msg)) {
    $msg = get_string($msg, 'local_community');
    echo $OUTPUT->notification($msg, 'notifysuccess');
}

$list = $DB->get_records('local_community', ['userid' => $userid], 'name');

$renderable = new \local_community\output\communities($list, $userid);
$renderer = $PAGE->get_renderer('local_community');

echo $renderer->render($renderable);

echo $OUTPUT->footer();
