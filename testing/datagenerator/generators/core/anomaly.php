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

namespace testing\datagenerator;

/**
 * Generates data related to anomalies.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2021 The University of Nottingham
 * @package testing
 * @subpackage datagenerator
 */
class Anomaly extends generator
{
    /**
     * Creates an anomlay record.
     *
     * Required params:
     * - paperID: The id of the paper the anomaly is on
     * - userID: The id of the user that registered the anomaly
     * - type: The type of anomaly
     *
     * @param array|\stdClass $parameters
     * @return array
     * @throws \testing\datagenerator\data_error
     */
    public function createAnomaly($parameters): array
    {
        // If an object is passed convert it into an array.
        if (is_object($parameters)) {
            $parameters = (array)$parameters;
        }
        // Check that the right type has been passed.
        if (!is_array($parameters)) {
            throw new data_error('Must pass an array or object');
        }
        // Check that the required parameters have been passed.
        if (empty($parameters['paperid'])) {
            throw new data_error('Must pass paperid');
        }
        if (empty($parameters['userid'])) {
            throw new data_error('Must pass userid');
        }
        if (empty($parameters['type'])) {
            throw new data_error('Must pass type');
        }
        $defaults = [
            'paperid' => null,
            'userid' =>  null,
            'type' =>  null,
            'screen' =>  null,
            'previous' => null,
            'current' => null,
            'time' => null,
        ];
        $values = $this->set_defaults_and_clean($defaults, $parameters);
        $data = array(
            'userid' => $values['userid'],
            'paperid' => $values['paperid'],
            'screen' => $values['screen'],
        );
        if ($values['type'] == \Anomaly::CLOCK) {
            $data['previous'] = $values['previous'];
            $data['current'] = $values['current'];
            $values['details'] = array('previous' => $values['previous'], 'current' => $values['current']);
            $anomaly = new \ClockAnomaly($data);
            $insert = $anomaly->insert();
            $values['timestamp'] = $insert['timestamp'];
            $values['id'] = $insert['id'];
        } else {
            throw new data_error('Unknown anomaly type');
        }

        $values['typename'] = \Anomaly::getType($values['type']);
        // Override anomaly time if provided.
        if (!empty($values['time'])) {
            $values['timestamp'] = $values['time'];
            $sql = 'UPDATE anomaly SET time = ? WHERE id = ?';
            $query = $this->db->prepare($sql);
            $query->bind_param('ii', $values['time'], $values['id']);
            $query->execute();
            $query->close();
        }
        return $values;
    }
}
