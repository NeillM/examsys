<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require '../include/staff_auth.inc';

  if ($_POST['ok']) {
    // Record the change in 'track_changes'.
    $result = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL,'Exam Script',?,?,?,?,NOW(),'Reassigned temporary user')");
    $result->bind_param('isss', $_POST['paperID'], $userID, $_POST['temp_userID'], $_POST['userID']);
    $result->execute();
    $result->close();
  
    // Transfer records in log.
    $result = $mysqli->prepare("UPDATE log" . $_POST['log_type'] . " SET userID=?, student_grade=?, year=? WHERE userID=? AND q_paper=? AND started=?");
    $result->bind_param('issiis', $_POST['userID'], $_POST['grade'], $_POST['year'], $_POST['temp_userID'], $_POST['paperID'], $_POST['started']);
    $result->execute();
    $result->close();

    // Transfer textbox marking (just in case marking done before marks reasignment).
    $result = $mysqli->prepare("UPDATE textbox_marking SET student_userID=? WHERE student_userID=? AND paperID=?");
    $result->bind_param('iii', $_POST['userID'], $_POST['temp_userID'], $_POST['paperID']);
    $result->execute();
    $result->close();

    // Transfer any student notes.
    $result = $mysqli->prepare("UPDATE student_notes SET userID=? WHERE userID=? AND paper_id=?");
    $result->bind_param('iii', $_POST['userID'], $_POST['temp_userID'], $_POST['paperID']);
    $result->execute();
    $result->close();
    
    // Free up the temporary account.
    $result = $mysqli->prepare("DELETE FROM temp_users WHERE assigned_account=?");
    $result->bind_param('s', $_POST['assigned_account']);
    $result->execute();
    $result->close();
  }
?>
<html>
<head>
<title>Reassign Script to User</title>
<style>
body {background-color:#ECE9D8; color:black; font-family:Arial,sans-serif; margin:0px}
</style>
</head>

<body onload="window.opener.location.reload(); window.close();">

</body>
</html>