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

if ($updater_utils->check_version('7.0.0')) {
    if (!$updater_utils->has_updated('rogo2263')) {
        // Latex column no longer required.
        $sql = 'ALTER TABLE properties DROP COLUMN latex_needed';
        $updater_utils->execute_query($sql, false);
        // Install tinymce plugin.
        $defaulttexteditorns = 'plugins\texteditor\plugin_tinymce3_texteditor\plugin_tinymce3_texteditor';
        $defaulttexteditor = new $defaulttexteditorns();
        $defaulttexteditor->update_plugin_version($defaulttexteditor->get_file_version());
        $configObject->set_setting('installed', 1, \Config::BOOLEAN, 'plugin_tinymce3_texteditor');
        // Install plain plugin.
        $plaintexteditorns = 'plugins\texteditor\plugin_plain_texteditor\plugin_plain_texteditor';
        $plaintexteditor = new $plaintexteditorns();
        $plaintexteditor->update_plugin_version($plaintexteditor->get_file_version());
        $configObject->set_setting('installed', 1, \Config::BOOLEAN, 'plugin_plain_texteditor');
        $settingssql = "INSERT IGNORE INTO config (component, setting, value, type) values ('plugin_plain_texteditor', 'supports_mathjax', 1, 'boolean')";
        $updater_utils->execute_query($settingssql, false);
        // Enable one.
        if ($configObject->get_setting('core', 'misc_editor_name') === 'tinymce') {
            $defaulttexteditor->enable_plugin();
        } else {
            $plaintexteditor->enable_plugin();
        }
        // Add mee setting.
        $configObject->set_setting('paper_mee', 1, Config::BOOLEAN);
        // Delete deprcated settings.
        $sql = "DELETE FROM config WHERE component = 'core' AND setting = 'misc_editor_name'";
        $updater_utils->execute_query($sql, false);
        $sql = "DELETE FROM config WHERE component = 'core' AND setting = 'paper_editor_supports_mathjax'";
        $updater_utils->execute_query($sql, false);
        $updater_utils->record_update('rogo2263');
    }
}
