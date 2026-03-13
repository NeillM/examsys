<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

namespace testing\datagenerator;

/**
 * Generates ExamSys folder.
 *
 * @author Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2019 The University of Nottingham
 * @package testing
 * @subpackage datagenerator
 */
class folder extends generator
{
    /** @var int Stores how many courses have been created. */
    protected static $folderscreated = 0;

    /**
     * Create a new folder
     *
     * @param array parameters
     *  string parameters[ownerID]
     * options are (name, colour, deleted, parent)
     * @return array
     * @throws data_error If passed parameter is invalid
     */
    public function create_folder($parameters)
    {
        if (empty($parameters['ownerID'])) {
            throw new data_error('ownerID must be provided');
        }
        $folderscreated = ++self::$folderscreated;
        $defaults = [
            'name' => 'Folder ' . $folderscreated,
            'colour' => 'yellow',
            'deleted' => false,
            'ownerid' => $parameters['ownerID'],
            'parent' => null,
        ];
        $settings = $this->set_defaults_and_clean($defaults, $parameters);
        $now = date('Y-m-d H:i:s');
        if ($settings['deleted']) {
            $settings['deleted'] = $now;
        } else {
            $settings['deleted'] = null;
        }
        if ($settings['parent']) {
            // Get the parent folders name.
            $parent_name = \folder_utils::get_folder_name($settings['parent'], $this->db);
            if ($parent_name === null) {
                throw new data_error('parent folder not found');
            }
            // The folder structure is stored in its name.
            $settings['name'] = $parent_name . ';' . $settings['name'];
        }
        $query = $this->db->prepare('INSERT INTO folders VALUES (NULL, ?, ?, ?, ?, ?)');
        $query->bind_param('issss', $parameters['ownerID'], $settings['name'], $now, $settings['colour'], $settings['deleted']);
        if (!$query->execute()) {
            throw new data_error('Create new folder failed with parameters: ' . $parameters['ownerID'] . '--' . implode('--', $settings));
        }
        $settings['id'] = $query->insert_id;
        $query->close();
        return $settings;
    }
}
