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
     * @param int $oid option ID
     * @param string $type the type
     * @return string
     */
    public static function get(int $oid, string $type)
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
     * Get multiple option metadata entries (or all)
     * @param int $oid option ID
     * @param string[] $types keys to fetch, or unset to fetch all metadata associated with question
     * @return string[] associated type as key => value array
     */
    public static function getArray(int $oid, array $types = [])
    {
        $configObject = Config::get_instance();
        $sql = 'SELECT type, value FROM options_metadata WHERE optionID = ?';
        if (!empty($types)) {
            $sql .= ' AND type IN (' . implode(',', array_fill(0, count($types), '?')) . ')';
        }
        $result = $configObject->db->prepare($sql);
        $result->bind_param('i' . str_repeat('s', count($types)), $oid, ...$types);
        $result->execute();
        $result->store_result();
        $type = '';
        $value = '';
        $return = [];
        $result->bind_result($type, $value);
        while ($result->fetch()) {
            $return[$type] = $value;
        }
        $result->close();
        return $return;
    }

    /**
     * Set/update option metadata
     * @param int $oid option ID
     * @param string $type type
     * @param string $value value
     * @throws coding_exception
     */
    public static function set(int $oid, string $type, string $value)
    {
        if (strlen($value) > 2500) {
            throw new coding_exception('Maximum metadata size exceeded');
        }
        $configObject = Config::get_instance();
        $current = self::get($oid, $type);
        if ($value != $current) {
            if ($value === '') {
                self::delete($oid, $type);
            } else {
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

    /**
     * Set multiple option metadata entries. Simple wrapper around self::set
     * @param int $oid option ID
     * @param string[] $types keys to set, with type as array key
     */
    public static function setArray(int $oid, array $typesAndValues = [])
    {
        foreach ($typesAndValues as $type => $value) {
            self::set($oid, $type, $value);
        }
    }

    /**
     * Delete option metadata entries
     * @param int $oid option ID
     * @param string[]|string|null $types keys to delete, or blank for all
     */
    public static function delete(int $oid, $types = [])
    {
        $configObject = Config::get_instance();
        $sql = 'DELETE FROM options_metadata WHERE optionID = ?';
        $bind_params = [$oid];
        if (!empty($types)) {
            if (!is_array($types)) {
                $types = [$types];
            }
            $sql .= ' AND type IN (' . implode(',', array_fill(0, count($types), '?')) . ')';
            $bind_params = array_merge($bind_params, $types);
        }
        $result = $configObject->db->prepare($sql);
        $result->bind_param('i' . str_repeat('s', count($types)), ...$bind_params);
        $result->execute();
    }
}
