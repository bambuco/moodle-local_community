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
 * Class containing renderers for the component.
 *
 * @package   local_community
 * @copyright 2023 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_community\output;

use renderable;
use renderer_base;
use templatable;

/**
 * Class containing data for the component.
 *
 * @copyright 2023 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class communities implements renderable, templatable {

    /**
     * @var array List of communities to print.
     */
    private $communities;

    /**
     * @var int The user id.
     */
    private $userid;

    /**
     * Constructor.
     *
     * @param array $communities List of communities to print.
     * @param int $userid The user id.
     */
    public function __construct(array $communities, int $userid) {
        $this->communities = $communities;
        $this->userid = $userid;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return array Context variables for the template
     */
    public function export_for_template(renderer_base $output) {
        global $CFG, $DB, $USER;

        $fulleditable = has_capability('local/community:manage', \context_system::instance());

        foreach ($this->communities as $community) {
            $owner = $DB->get_record('user', ['id' => $community->userid]);
            $owner->fullname = fullname($owner);
            $owner->profileurl = new \moodle_url('/user/profile.php', ['id' => $owner->id]);
            $community->owner = $owner;
            $community->bannerurl = \local_community\community::get_banner($community);

            if ($fulleditable || $community->userid == $USER->id) {
                $community->editable = true;
            }
        }

        $config = get_config('local_community');
        $maxcommunities = (int)$config->maxcommunities;
        $morecommunities = count($this->communities) < $maxcommunities;

        $defaultvariables = [
            'communities' => array_values($this->communities),
            'baseurl' => $CFG->wwwroot,
            'morecommunities' => $morecommunities,
            'sesskey' => sesskey(),
            'userid' => $this->userid,
        ];

        return $defaultvariables;
    }
}
