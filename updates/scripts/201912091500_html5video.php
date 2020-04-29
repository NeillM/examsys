<?php
if ($updater_utils->check_version('7.2.0')) {
    if (!$updater_utils->has_updated('rogo2673')) {
        $mediatypes = $configObject->get_setting('core', 'system_mediatypes');
        // Add mp4 as a media type.
        $mediatypes['mp4'] = 1;
        $configObject->set_setting('system_mediatypes', $mediatypes, Config::ASSOC);
        $updater_utils->record_update('rogo2673');
    }
}
