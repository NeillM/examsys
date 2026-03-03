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

use testing\datagenerator\generator;

/**
 * Generates ExamSys change log data.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 The University of Nottingham
 * @package testing
 * @subpackage datagenerator
 */
class changelog extends generator
{
    /**
     * Create a change log for a paper.
     *
     * @param array $data
     * @return array
     * @throws data_error
     */
    public function createPaperLog(array $data): array
    {
        if (empty($data['paperid'])) {
            throw new data_error('paperid must be provided');
        }
        if (empty($data['userid'])) {
            throw new data_error('userid must be provided');
        }
        if (!isset($data['old'])) {
            throw new data_error('old must be provided');
        }
        if (!isset($data['new'])) {
            throw new data_error('new must be provided');
        }
        if (!isset($data['type'])) {
            throw new data_error('type must be provided');
        }

        $return = [
            'type' => 'Paper',
            'typeID' => $data['paperid'],
            'editor' => $data['userid'],
            'old' => $data['old'],
            'new' => $data['new'],
            'changed' => $data['date'],
            'part' => $data['type'],
        ];

        $return['id'] = $this->addEntry(
            'Paper',
            $data['paperid'],
            $data['userid'],
            $data['old'],
            $data['new'],
            $data['date'],
            $data['type'],
        );
        return $return;
    }

    /**
     * Adds an entry into track changes.
     *
     * @param string $type
     * @param int $id
     * @param int $user
     * @param string $original
     * @param string $new
     * @param string $time
     * @param string $part
     * @return int
     */
    protected function addEntry($type, $id, $user, $original, $new, $time, $part): int
    {
        $query = 'INSERT INTO track_changes(type, typeID, editor, old, new, changed, part) VALUES (?,?,?,?,?,?,?)';
        $result = $this->db->prepare($query);
        $result->bind_param('siissss', $type, $id, $user, $original, $new, $time, $part);
        $result->execute();
        return $this->db->insert_id;
    }
}
