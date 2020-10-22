<?php

// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

if ($updater_utils->check_version('7.1.0')) {
    if (!$updater_utils->has_updated('rogo2599')) {
        $selectsql = $update_mysqli->prepare("SELECT q_id, settings FROM questions where q_type = 'textbox'");
        $selectsql->execute();
        $selectsql->store_result();
        $selectsql->bind_result($q_id, $settings);
        $updatesql = $update_mysqli->prepare('UPDATE questions SET settings = ? WHERE q_id = ?');
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
