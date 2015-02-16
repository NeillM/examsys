<?php

// Set deletions count to zero where null.
if ($updater_utils->count_rows("SELECT * from sms_imports where deletions is NULL") > 0) {
    $sql = "update sms_imports set deletions = 0 where deletions is NULL";
    $updater_utils->execute_query($sql, true);
}

// Set enrolements count to zero where null.
if ($updater_utils->count_rows("SELECT * from sms_imports where enrolements is NULL") > 0) {
    $sql = "update sms_imports set enrolements = 0 where enrolements is NULL";
    $updater_utils->execute_query($sql, true);
}
?>
