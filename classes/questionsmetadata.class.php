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
 * Question metadata helper functions
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @author Richard Aspden <richard@getjohn.co.uk>
 * @copyright Copyright (c) 2021 The University of Nottingham
 */
class QuestionsMetadata
{
    /**
     * Get question metadata
     * @param int $qid question ID
     * @param string $type the type
     * @return string
     */
    public static function get(int $qid, string $type)
    {
        $configObject = Config::get_instance();
        $sql = $configObject->db->prepare('SELECT value FROM questions_metadata WHERE questionID = ? AND type = ?');
        $sql->bind_param('is', $qid, $type);
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
     * Get multiple question metadata entries (or all)
     * @param int $qid question ID
     * @param string[] $types keys to fetch, or unset to fetch all metadata associated with question
     * @return string[] associated type as key => value array
     */
    public static function getArray(int $qid, array $types = [])
    {
        $configObject = Config::get_instance();
        $sql = 'SELECT type, value FROM questions_metadata WHERE questionID = ?';
        if (!empty($types)) {
            $sql .= ' AND type IN (' . implode(',', array_fill(0, count($types), '?')) . ')';
        }
        $result = $configObject->db->prepare($sql);
        $result->bind_param('i' . str_repeat('s', count($types)), $qid, ...$types);
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
     * Set/update question metadata
     * @param int $qid question identifier
     * @param string $type type
     * @param string $value value
     * @throws coding_exception
     */
    public static function set(int $qid, string $type, string $value)
    {
        if (strlen($value) > 2500) {
            throw new coding_exception('Maximum metadata size exceeded');
        }
        $configObject = Config::get_instance();
        $current = self::get($qid, $type);
        if ($value != $current) {
            if ($current === '') {
                $sql = $configObject->db->prepare(
                    'INSERT INTO questions_metadata (id, questionID, type, value) VALUES (NULL, ?, ?, ?)'
                );
                $sql->bind_param('iss', $qid, $type, $value);
            } else {
                $sql = $configObject->db->prepare(
                    'UPDATE questions_metadata SET value = ? WHERE questionID = ? AND type = ?'
                );
                $sql->bind_param('sis', $value, $qid, $type);
            }
            $sql->execute();
            $sql->close();
        }
    }

    /**
     * Set multiple question metadata entries. Simple wrapper around self::set
     * @param int $qid question ID
     * @param string[] $types keys to set, with type as array key
     */
    public static function setArray(int $qid, array $typesAndValues = [])
    {
        foreach ($typesAndValues as $type => $value) {
            self::set($qid, $type, $value);
        }
    }
}
