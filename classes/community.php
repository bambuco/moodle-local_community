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
}
