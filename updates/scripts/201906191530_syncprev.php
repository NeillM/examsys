<?php
if ($updater_utils->check_version("7.1.0")) {
  if (!$updater_utils->has_updated('rogo_2615')) {
    $sql = "ALTER TABLE modules ADD COLUMN syncpreviousyear BOOLEAN NOT NULL default false";
    $updater_utils->execute_query($sql, false);
    $updater_utils->record_update('rogo_2615');
  }
}
