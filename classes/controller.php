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
 * Class containing the general controls.
 *
 * @package   local_community
 * @copyright 2023 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_community;

/**
 * Component controller.
 *
 * @copyright 2023 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class controller {

    /**
     * Get the public communities.
     *
     * @return array
     */
    public static function get_publiccommunities() {
        global $DB;
        $records = $DB->get_records('local_community', ['public' => 1], 'name');

        foreach ($records as $key => $record) {
            if (empty($record->cohortid)) {
                unset($records[$key]);
            }

            $cohort = $DB->get_record('cohort', ['id' => $record->cohortid]);

            // Only visible cohorts are available to link.
            if (empty($cohort) || $cohort->visible == 0) {
                unset($records[$key]);
            }
        }

        return $records;
    }

    /**
     * Get the user communities.
     *
     * @param int $userid The user id.
     * @return array
     */
    public static function get_usercommunities(int $userid) : array {
        global $DB;

        $sql = "SELECT c.*
                    FROM {local_community} c
                    INNER JOIN {cohort} ch ON ch.id = c.cohortid
                    INNER JOIN {cohort_members} chm ON chm.cohortid = ch.id AND chm.userid = :userid
                ORDER BY c.name";
        $list = $DB->get_records_sql($sql, ['userid' => $userid]);

        return $list;
    }

    /**
     * Get all communities.
     *
     * @return array
     */
    public static function get_communities() : array {
        global $DB;

        $list = $DB->get_records('local_community', [], 'name');

        return $list;
    }

}
