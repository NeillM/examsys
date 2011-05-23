<?php
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