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
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/
  if (strpos($_SERVER['PHP_SELF'],'student_help') !== false) {
    $help_type = 'student';
    $require_file = '../include/staff_student_auth.inc';
  } else {
    $help_type = 'staff';
    $require_file = '../include/staff_auth.inc';
  }
  require $require_file;
?>
<html>
<head>
<title>TouchStone Tutorial</title>
</head>
<body>
<?php
   
   echo "<embed width=\"100%\" height=\"100%\" src='./images/" . $_GET['tutorial'] . "' />";
  
   if (strpos($userroles,'SysAdmin') === false) {   // Don't record the homepage or SysAdmin activities.
    $result = $mysqli->prepare("INSERT INTO help_tutorial_log VALUES (NULL,?,?,NOW(),?)");
    $result->bind_param('sis', $help_type, $userID, $_GET['tutorial']);
    $result->execute();  
    $result->close();
  }
?>
</body>