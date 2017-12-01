<?php

if ($updater_utils->check_version("6.5.0")) {
  if (!$updater_utils->has_updated('rogo2263')) {
    $sql = "ALTER TABLE properties DROP COLUMN latex_needed";
    $updater_utils->execute_query($sql, false);
    $updater_utils->record_update('rogo2263');
  }
}