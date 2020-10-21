<?php

if (!$updater_utils->has_updated('lancaster/feature/GJLU-245')) {
  $configObject->set_setting('rpt_fd_show_wordlist', 1, Config::BOOLEAN);
  $updater_utils->record_update('lancaster/feature/GJLU-245');
}
