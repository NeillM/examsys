<?php
// This file is part of Rogo
//
// Rogo is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogo is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogo.  If not, see <http://www.gnu.org/licenses/>.

/**
*
* Save marks for individual textbox questions
*
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require '../../include/staff_auth.inc';
require '../../include/errors.inc';

$status = 'ERROR';

$paperID = (isset($_POST['paper_id'])) ? $_POST['paper_id'] : false;
$q_id = (isset($_POST['q_id'])) ? $_POST['q_id'] : false;
$log_id = (isset($_POST['log_id'])) ? $_POST['log_id'] : false;
$marker_id = (isset($_POST['marker_id'])) ? $_POST['marker_id'] : false;
$mark = (isset($_POST['mark'])) ? $_POST['mark'] : false;
$phase = (isset($_POST['phase'])) ? $_POST['phase'] : false;
$log = (isset($_POST['log'])) ? $_POST['log'] : false;
$user_id = (isset($_POST['user_id'])) ? $_POST['user_id'] : false;

if ($paperID !== false and $q_id !== false and $log_id !== false and $marker_id !== false and $mark !== false and $phase !== false and $log !== false and $user_id !== false) {
  if ($mark != 'NULL') {
    $sql = <<< QUERY
INSERT INTO textbox_marking (paperID, q_id, answer_id, markerID, mark, comments, date, phase, logtype, student_userID)
VALUES (?, ?, ?, ?, ?, '', NOW(), ?, ?, ?) ON DUPLICATE KEY UPDATE
markerID = ?, mark = ?, date = NOW()
QUERY;

    try {
      $result = $mysqli->prepare($sql);
      if ($result) {
        $result->bind_param('iiiidiiiid', $paperID, $q_id, $log_id, $marker_id, $mark, $phase, $log, $user_id, $marker_id, $mark);
        $result2 = $result->execute();
        if ($result !== false) {
          $status = 'OK';
        }
        $result->close();
      }
    } catch (exception $ex) {
      // No need to do anything
    }
  } else {
    $sql = <<< QUERY
DELETE FROM textbox_marking WHERE answer_id = ? AND phase = ?
QUERY;
    try {
      $result = $mysqli->prepare($sql);
      if ($result) {
        $result->bind_param('ii', $log_id, $phase);
        $result2 = $result->execute();
        if ($result !== false) {
          $status = 'OK';
        }
        $result->close();
      }
    } catch (exception $ex) {
      // No need to do anything
    }
  }
}

echo $status;
