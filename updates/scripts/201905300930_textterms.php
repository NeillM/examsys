<?php
if ($updater_utils->check_version('7.1.0')) {
    if (!$updater_utils->has_updated('rogo2599')) {
        $selectsql = $mysqli->prepare("SELECT q_id, settings FROM questions where q_type = 'textbox'");
        $selectsql->execute();
        $selectsql->store_result();
        $selectsql->bind_result($q_id, $settings);
        $updatesql = $mysqli->prepare('UPDATE questions SET settings = ? WHERE q_id = ?');
        while ($selectsql->fetch()) {
            $oldsettings = json_decode($settings, true);
            if (isset($oldsettings['terms'])) {
                $oldterms = explode(';', $oldsettings['terms']);
                if (is_array($oldterms)) {
                  // JSON encode instead of ; seperated.
                    $oldsettings['terms'] = json_encode($oldterms);
                    $newsettings = json_encode($oldsettings, true);
                    $updatesql->bind_param('si', $newsettings, $q_id);
                    $updatesql->execute();
                }
            }
        }
        $selectsql->close();
        $updatesql->close();
        $updater_utils->record_update('rogo2599');
    }
}
