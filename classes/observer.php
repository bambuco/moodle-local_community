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
 * Event observer.
 *
 * @package   local_community
 * @copyright 2024 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_community;

/**
 * Events observer.
 *
 * @package   local_community
 * @copyright 2024 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observer {

    /**
     * Disable the related cohort.
     *
     * @param \core\event\base $event
     */
    public static function cohort_deleted(\core\event\base $event) {
        global $DB;

        $community = $DB->get_record('local_community', ['cohortid' => $event->objectid], 'id');

        if ($community) {
            $DB->update_record('local_community', (object)[
                'id' => $community->id,
                'cohortid' => null,
                'public' => 0,
            ]);
        }
    }

    /**
     * Delete communities when the owner user is deleted.
     * Disable the related cohort.
     *
     * @param \core\event\base $event
     */
    public static function user_deleted(\core\event\base $event) {
        global $DB;

        $communities = $DB->get_records('local_community', ['userid' => $event->objectid]);

        foreach ($communities as $community) {
            \local_community\community::delete($community);
        }

    }

    //ToDo: Escuchar cuando se apruebe la cohorte, que es lo que hace que la Comunidad se pueda poner pública, y enviar mensaje
    // al dueño de la comunidad.

}
