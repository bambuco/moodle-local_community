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
 * Class containing the community class.
 *
 * @package   local_community
 * @copyright 2024 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_community;

include_once($CFG->dirroot . '/cohort/lib.php');

/**
 * Class community.
 *
 * @copyright 2023 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class community {

    /**
     * Get the banner for a community.
     *
     * @param object $community The community object.
     * @return string|null The banner URL or null if not found.
     */
    public static function get_banner(object $community) : ?string {
        global $OUTPUT;

        $syscontext = \context_system::instance();
        $fs = get_file_storage();
        $files = $fs->get_area_files($syscontext->id, 'local_community', 'communitybanner', $community->id);

        foreach ($files as $file) {
            $filename = $file->get_filename();

            if (!empty($filename) && $filename != '.') {
                $path = '/' . implode('/', [$file->get_contextid(),
                                                    'local_community',
                                                    'communitybanner',
                                                    $file->get_itemid() . $file->get_filepath() . $filename]);

                // Only one banner by community.
                return (string)\moodle_url::make_file_url('/pluginfile.php', $path);

            }
        }

        // Use as default the generated image.
        $banner = $OUTPUT->get_generated_image_for_id($community->id);

        return $banner;
    }

    /**
     * Create a new community.
     *
     * @param object $community The community data.
     * @return int The new community id.
     */
    public static function create($community) {
        global $DB;

        $community->timecreated = time();
        $community->timemodified = $community->timecreated;

        if (empty($community->public)) {
            $community->public = 0;
        }

        if (empty($community->description)) {
            $community->description = '';
        }

        $syscontext = \context_system::instance();

        $canpublish = has_capability('local/community:autopublish', $syscontext);

        $cohort = new \stdClass();
        $cohort->name = get_string('communitycohortnametpl', 'local_community', $community->name);

        if (!empty($community->idnumber)) {
            $cohort->idnumber = 'c-' . $community->idnumber;
        }

        $cohort->visible = $canpublish ? 1 : 0;
        $cohort->contextid = $syscontext->id;

        $community->cohortid = cohort_add_cohort($cohort);
        cohort_add_member($community->cohortid, $community->userid);

        $id = $DB->insert_record('local_community', $community, true);

        $event = \local_community\event\community_created::create(array(
            'objectid' => $id,
            'context' => $syscontext,
        ));
        $event->trigger();

        return $id;
    }

    /**
     * Delete a community.
     *
     * @param int|object $id or $community object.
     */
    public static function delete($community) {
        global $DB;

        if (is_int($community)) {
            $community = $DB->get_record('local_community', ['id' => $community]);
        }

        if ($community) {
            $syscontext = \context_system::instance();

            $event = \local_community\event\community_deleted::create([
                'objectid' => $community->id,
                'context' => $syscontext,
            ]);
            $event->trigger();

            $fs = get_file_storage();
            $files = $fs->get_area_files($syscontext->id, 'local_community', 'communitybanner', $community->id);

            foreach ($files as $file) {
                $file->delete();
            }

            $DB->delete_records('local_community', ['id' => $community->id]);

            if ($community->cohortid) {
                cohort_remove_member($community->cohortid, $event->objectid);
                cohort_update_cohort((object)[
                    'id' => $community->cohortid,
                    'visible' => 0,
                    'name' => 'd-' . $community->name,
                    'idnumber' => 'd-' . $community->idnumber,
                ]);
            }
        }
    }
}
