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
require_once($CFG->libdir . '/filelib.php');

$id = optional_param('id', 0, PARAM_INT);
$userid = optional_param('u', $USER->id, PARAM_INT);

require_login(null, true);

$syscontext = context_system::instance();

if ($userid != $USER->id) {
    require_capability('local/community:manage', $syscontext);
} else {
    require_capability('local/community:manageown', $syscontext);
}

$community = null;
if ($id) {
    $params = ['id' => $id];

    $community = $DB->get_record('local_community', $params, '*', MUST_EXIST);

    if ($community->userid != $userid && !has_capability('local/community:manage', $syscontext)) {
        throw new moodle_exception('nopermissiontomanage', 'local_community');
    } else {
        $userid = $community->userid;
    }
}

$config = get_config('local_community');

$currentcommunities = $DB->count_records('local_community', ['userid' => $userid]);
$maxcommunities = (int)$config->maxcommunities;

if ($currentcommunities >= $maxcommunities) {
    throw new moodle_exception('maxcommunitiesreached', 'local_community');
}

$PAGE->set_context($syscontext);
$PAGE->set_url('/local/community/index.php');
$PAGE->set_pagelayout('incourse');
$PAGE->set_heading(get_string('newcommunity', 'local_community'));
$PAGE->set_title(get_string('newcommunity', 'local_community'));

$filemanageroptions = [
                        'maxbytes' => $CFG->maxbytes,
                        'subdirs' => 0,
                        'maxfiles' => 1,
                        'accepted_types' => ['web_image']
                    ];
$draftitemid = file_get_submitted_draft_itemid('banner');
file_prepare_draft_area($draftitemid, $syscontext->id, 'local_community', 'communitybanner', $id, $filemanageroptions);

$data = ['filemanageroptions' => $filemanageroptions, 'userid' => $userid];
if ($community) {
    $data['data'] = $community;
    $community->banner = $draftitemid;
}

$returnparams = $USER->id == $community->userid ? [] : ['u' => $community->userid];
$form = new \local_community\forms\community(null, $data);
if ($form->is_cancelled()) {
    $url = new moodle_url($CFG->wwwroot . '/local/community/index.php', $returnparams);
    redirect($url);
} else if ($data = $form->get_data()) {
    if (!$community) {
        $community = new stdClass();
        $community->userid = $userid;
    }

    $community->name = $data->name;
    $community->description = $data->description;
    $community->idnumber = $data->idnumber;
    $community->email = $data->email;
    $community->phone = $data->phone;
    $community->address = $data->address;
    $community->registercode = $data->registercode;
    $community->public = $data->public;

    if ($id) {
        $community->timemodified = time();
        $DB->update_record('local_community', $community);

        $event = \local_community\event\community_updated::create(array(
            'objectid' => $community->id,
            'context' => $syscontext,
        ));
        $event->trigger();
    } else {
        $id = \local_community\community::create($community);
    }

    file_save_draft_area_files($data->banner, $syscontext->id, 'local_community', 'communitybanner',
                                $id, $filemanageroptions);

    $url = new moodle_url($CFG->wwwroot . '/local/community/index.php', $returnparams);
    redirect($url);
}

echo $OUTPUT->header();

$form->display();

echo $OUTPUT->footer();
