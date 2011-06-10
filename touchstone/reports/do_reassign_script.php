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
require '../include/errors.inc';

echo "Hi";
exit;
  
check_var('paperID', 'GET', true, false); 
check_var('userID', 'GET', true, false); 
check_var('temp_userID', 'GET', true, false); 

// Get start time of the paper.
$result = $mysqli->prepare("SELECT started FROM log2 WHERE userID=? AND q_paper=?");
$result->bind_param('ii', $_GET['temp_userID'], $_GET['paperID']);
$result->execute();
$result->bind_result($started);
$result->fetch();
$result->close();

// Get grade and student of the user.
$result = $mysqli->prepare("SELECT grade, yearofstudy, username FROM users WHERE id=?");
$result->bind_param('i', $_GET['userID']);
$result->execute();
$result->bind_result($grade, $yearofstudy, $new_username);
$result->fetch();
$result->close();

// Record the change in 'track_changes'.
$result = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL,'Exam Script',?,?,?,?,NOW(),'Reassigned temporary user')");
$result->bind_param('isss', $_GET['paperID'], $_GET['userID'], $_GET['temp_userID'], $new_username);
$result->execute();
$result->close();

// Transfer records in logX.
$result = $mysqli->prepare("UPDATE log2 SET userID=? WHERE userID=? AND q_paper=? AND started=?");
$result->bind_param('iiis', $_GET['userID'], $_GET['temp_userID'], $_GET['paperID'], $started);
$result->execute();
$result->close();


// Transfer records in log_metadata.
$result = $mysqli->prepare("UPDATE log_metadata SET userID=?, student_grade=?, year=? WHERE userID=? AND paperID=? AND started=?");
$result->bind_param('issiis', $_GET['userID'], $grade, $yearofstudy, $_GET['temp_userID'], $_GET['paperID'], $started);
$result->execute();
$result->close();

// Transfer textbox marking (just in case marking done before marks reasignment).
$result = $mysqli->prepare("UPDATE textbox_marking SET student_userID=? WHERE student_userID=? AND paperID=?");
$result->bind_param('iii', $_GET['userID'], $_GET['temp_userID'], $_GET['paperID']);
$result->execute();
$result->close();

// Transfer any student notes.
$result = $mysqli->prepare("UPDATE student_notes SET userID=? WHERE userID=? AND paper_id=?");
$result->bind_param('iii', $_GET['userID'], $_GET['temp_userID'], $_GET['paperID']);
$result->execute();
$result->close();

// Free up the temporary account.
$result = $mysqli->prepare("DELETE FROM temp_users WHERE assigned_account=?");
$result->bind_param('s', $_GET['assigned_account']);
$result->execute();
$result->close();

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