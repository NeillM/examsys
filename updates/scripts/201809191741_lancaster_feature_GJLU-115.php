<?php

if (!$updater_utils->has_updated('lancaster/feature/GJLU-115')) {
  $configObject->set_setting('paper_textbox_editor_default', 'plain', Config::EDITOR);

  $updater_utils->record_update('lancaster/feature/GJLU-115');
}
