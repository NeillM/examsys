<?php

$sql = "GRANT SELECT ON " . $cfg_db_database . ".marking_override TO '" . $cfg_db_student_user . "'@'" . $cfg_db_host . "'";
$updater_utils->execute_query($sql, true);

/*
 *****   NOW UPDATE THE INSTALLER SCRIPT   *****
 */