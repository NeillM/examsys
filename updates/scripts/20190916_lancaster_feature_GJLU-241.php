<?php

if (!$updater_utils->has_updated('lancaster/feature/GJLU-241')) {
    $configObject->set_setting('stdset_copy_std_setting', false, Config::BOOLEAN);

    $updater_utils->record_update('lancaster/feature/GJLU-241');
}
