<?php

// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * Options metadata helper functions
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @author Richard Aspden <richard@getjohn.co.uk>
 * @copyright Copyright (c) 2021 The University of Nottingham
 */
class OptionsMetadata
{
    /**
     * Get option metadata
     * @param string $type the type
     * @param int $oid the option identifier
     * @return string
     */
    public static function get(string $type, int $oid)
    {
        $configObject = Config::get_instance();
        $sql = $configObject->db->prepare('SELECT value FROM options_metadata WHERE type = ? and optionID = ?');
        $sql->bind_param('si', $type, $oid);
        $sql->execute();
        $sql->store_result();
        $sql->bind_result($value);
        $rows = $sql->num_rows;
        $sql->fetch();
        if ($rows == 0) {
            $return = '';
        } else {
            $return = $value;
        }
        $sql->close();
        return $return;
    }

    /**
     * Set/update option metadata
     * @param string $type type
     * @param int $oid option identifier
     * @param string $value value
     * @throws coding_exception
     */
    public static function set(string $type, int $oid, string $value)
    {
        if (strlen($value) > 2500) {
            throw new coding_exception('Maximum metadata size exceeded');
        }
        $configObject = Config::get_instance();
        $current = self::get($type, $oid);
        if ($value != $current) {
            if ($current === '') {
                $sql = $configObject->db->prepare(
                    'INSERT INTO options_metadata (optionID, type, value) VALUES (?, ?, ?)'
                );
                $sql->bind_param('iss', $oid, $type, $value);
            } else {
                $sql = $configObject->db->prepare(
                    'UPDATE options_metadata SET value = ? WHERE optionID = ? AND type = ?'
                );
                $sql->bind_param('sis', $value, $oid, $type);
            }
            $sql->execute();
            $sql->close();
        }
    }
}
