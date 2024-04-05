<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin administration pages are defined here.
 *
 * @package     local_community
 * @category    admin
 * @copyright   2023 David Herney @ BambuCo
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_category('local_community', get_string('pluginname', 'local_community'));
    $ADMIN->add('root', $settings, 'competencies');
    $generalsettings = new admin_settingpage('local_community_general', get_string('generalsettings', 'local_community'));

    $ADMIN->add('local_community', new admin_externalpage('local_community_view',
                                                            new lang_string('communitieslist', 'local_community'),
                                                            "$CFG->wwwroot/local/community/index.php?all=1"));



    // General setting.
    $list = range(1, 10);
    $list = array_combine($list, $list);
    $generalsettings->add(new admin_setting_configselect('local_community/maxcommunities',
                                    new lang_string('maxcommunities', 'local_community'),
                                    new lang_string('maxcommunities_help', 'local_community'),
                                    '', $list));

    $settings->add('local_community', $generalsettings);
}

