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

declare(strict_types=1);

namespace local_community\entities;

use lang_string;
use core_reportbuilder\local\entities\base;
use core_reportbuilder\local\filters\date;
use core_reportbuilder\local\filters\text;
use core_reportbuilder\local\filters\number;
use core_reportbuilder\local\filters\select;
use core_reportbuilder\local\helpers\format;
use core_reportbuilder\local\report\column;
use core_reportbuilder\local\report\filter;

/**
 * Community entity
 *
 * @package     local_community
 * @copyright   2023 David Herney @ BambuCo
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class community extends base {

    /**
     * Database tables that this entity uses and their default aliases
     *
     * @return array
     */
    protected function get_default_table_aliases(): array {
        return [
            'user' => 'u',
            'cohort' => 'ch',
            'cohort_members' => 'chm',
            'local_community' => 'lc',
        ];
    }

    /**
     * The default title for this entity
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('community', 'local_community');
    }

    /**
     * Initialise the entity, add all user fields and all 'visible' user profile fields
     *
     * @return base
     */
    public function initialise(): base {

        $columns = $this->get_all_columns();

        foreach ($columns as $column) {
            $this->add_column($column);
        }

        $filters = $this->get_all_filters();
        foreach ($filters as $filter) {
            $this
                ->add_filter($filter)
                ->add_condition($filter);
        }

        return $this;
    }

    /**
     * Add extra columns to report.
     * @return array
     * @throws \coding_exception
     */
    protected function get_all_columns(): array {
        $chmembersalias = $this->get_table_alias('cohort_members');
        $communityalias = $this->get_table_alias('local_community');
        $useralias = $this->get_table_alias('user');

        $columns[] = (new column(
            'name',
            new lang_string('name', 'local_community'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->add_fields("$communityalias.name")
            ->set_type(column::TYPE_TEXT)
            ->set_is_sortable(true);

        $columns[] = (new column(
            'idnumber',
            new lang_string('idnumber'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->add_fields("$communityalias.idnumber")
            ->set_type(column::TYPE_TEXT)
            ->set_is_sortable(true);

        $columns[] = (new column(
            'email',
            new lang_string('email'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->add_fields("$communityalias.email")
            ->set_type(column::TYPE_TEXT)
            ->set_is_sortable(true);

        $columns[] = (new column(
            'useridlink',
            new lang_string('userid', 'local_community'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->add_join("INNER JOIN {cohort_members} {$chmembersalias}
                ON {$communityalias}.cohortid = {$chmembersalias}.cohortid")
            ->add_join("INNER JOIN {user} {$useralias}
                ON {$useralias}.id = {$chmembersalias}.userid")
            ->add_fields("$chmembersalias.userid")
            ->set_type(column::TYPE_INTEGER)
            ->set_is_sortable(true)
            ->set_callback(static function(?int $userid): string {
                return \html_writer::link(new \moodle_url('/user/profile.php',
                                        ['id' => $userid]),
                                        $userid,
                                        ['target' => '_blank']);
            });

        $columns[] = (new column(
            'userfirstname',
            new lang_string('firstname'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->add_join("INNER JOIN {cohort_members} {$chmembersalias}
                ON {$communityalias}.cohortid = {$chmembersalias}.cohortid")
            ->add_join("INNER JOIN {user} {$useralias}
                ON {$useralias}.id = {$chmembersalias}.userid")
            ->add_fields("$useralias.firstname, $useralias.id")
            ->set_type(column::TYPE_TEXT)
            ->set_is_sortable(true)
            ->set_callback(static function(?string $firstname, object $obj): string {
                return \html_writer::link(new \moodle_url('/user/profile.php',
                                        ['id' => $obj->id]),
                                        $firstname,
                                        ['target' => '_blank']);
            });

        $columns[] = (new column(
            'userlastname',
            new lang_string('lastname'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->add_join("INNER JOIN {cohort_members} {$chmembersalias}
                ON {$communityalias}.cohortid = {$chmembersalias}.cohortid")
            ->add_join("INNER JOIN {user} {$useralias}
                ON {$useralias}.id = {$chmembersalias}.userid")
            ->add_fields("$useralias.lastname")
            ->set_type(column::TYPE_TEXT)
            ->set_is_sortable(true);

        $columns[] = (new column(
            'membersince',
            new lang_string('membersince', 'local_community'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->add_join("INNER JOIN {cohort_members} {$chmembersalias}
                ON {$communityalias}.cohortid = {$chmembersalias}.cohortid")
            ->add_fields("$chmembersalias.timeadded")
            ->set_type(column::TYPE_TIMESTAMP)
            ->set_callback([format::class, 'userdate'])
            ->set_is_sortable(true);

        return $columns;
    }

    /**
     * Return list of all available filters
     *
     * @return filter[]
     */
    protected function get_all_filters(): array {

        $filters = [];
        $chmembersalias = $this->get_table_alias('cohort_members');
        $communityalias = $this->get_table_alias('local_community');
        $useralias = $this->get_table_alias('user');

        $filters[] = (new filter(
            text::class,
            'name',
            new lang_string('name', 'local_community'),
            $this->get_entity_name(),
            "$communityalias.name",
        ))
            ->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'userfirstname',
            new lang_string('firstname'),
            $this->get_entity_name(),
            "$useralias.firstname",
        ))
            ->add_joins($this->get_joins())
            ->add_join("INNER JOIN {cohort_members} {$chmembersalias}
                ON {$communityalias}.cohortid = {$chmembersalias}.cohortid")
            ->add_join("INNER JOIN {user} {$useralias}
                ON {$useralias}.id = {$chmembersalias}.userid");

        $filters[] = (new filter(
            text::class,
            'userlastname',
            new lang_string('lastname'),
            $this->get_entity_name(),
            "$useralias.lastname",
        ))
            ->add_joins($this->get_joins())
            ->add_join("INNER JOIN {cohort_members} {$chmembersalias}
                ON {$communityalias}.cohortid = {$chmembersalias}.cohortid")
            ->add_join("INNER JOIN {user} {$useralias}
                ON {$useralias}.id = {$chmembersalias}.userid");

        return $filters;
    }

}
