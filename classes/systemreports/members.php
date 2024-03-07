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

namespace local_community\systemreports;

use local_community\entities\community;
use core_reportbuilder\local\helpers\database;
use core_reportbuilder\system_report;
use core_reportbuilder\local\report\action;

/**
 * Community members report.
 *
 * @package   local_community
 * @copyright 2024 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class members extends system_report {

    /**
     * @var \stdClass
     */
    public $community = null;

    /**
     * Initialise report, we need to set the main table, load our entities and set columns/filters
     */
    protected function initialise(): void {
        global $PAGE, $USER, $DB;

        $communityid = $this->get_parameter('communityid', 0, 'int');

        if (empty($communityid)) {
            return;
        }

        $this->community = $DB->get_record('local_community', ['id' => $communityid], '*', MUST_EXIST);

        // We need to ensure page context is always set, as required by output and string formatting.
        $PAGE->set_context($this->get_context());

        // Our main entity, it contains all of the column definitions that we need.
        $entitycommunity = new community();
        $entitymainalias = $entitycommunity->get_table_alias('local_community');

        $this->set_main_table('local_community', $entitymainalias);
        $this->add_entity($entitycommunity);

        // Add the base condition to the report.
        if (empty($this->community)) {
            return;
        }

        $ismanager = has_capability('local/community:manage', \context_system::instance()) || $this->community->userid == $USER->id;

        $param = database::generate_param_name();
        $where[] = "$entitymainalias.id = :$param";
        $params[$param] = $this->community->id;

        $wheresql = implode(' AND ', $where);

        $this->add_base_condition_sql($wheresql, $params);

        // Now we can call our helper methods to add the content we want to include in the report.
        $this->add_columns();
        $this->add_filters();
        $this->add_base_fields("{$entitymainalias}.id");

        // Set if report can be downloaded.
        $this->set_downloadable($ismanager);
    }

    /**
     * Validates access to view this report
     *
     * @return bool
     */
    protected function can_view(): bool {
        global $USER;

        if (isguestuser() || empty($this->community)) {
            return false;
        }

        if ($this->community->userid == $USER->id || has_capability('local/community:manage', \context_system::instance())) {
            return true;
        }

        return cohort_is_member($this->community->cohortid, $USER->id);

    }

    /**
     * Adds the columns we want to display in the report
     *
     * They are all provided by the entities we previously added in the {@see initialise} method, referencing each by their
     * unique identifier
     */
    public function add_columns(): void {
        $columns = [
            'community:useridlink',
            'community:userfirstname',
            'community:userlastname',
            'community:membersince',
        ];

        $this->add_columns_from_entities($columns);
    }

    /**
     * Adds the filters we want to display in the report
     *
     * They are all provided by the entities we previously added in the {@see initialise} method, referencing each by their
     * unique identifier
     */
    protected function add_filters(): void {
        $filters = [
            'community:userfirstname',
            'community:userlastname',
        ];

        $this->add_filters_from_entities($filters);
    }
}
