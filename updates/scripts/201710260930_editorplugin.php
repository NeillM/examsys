<?php
if ($updater_utils->check_version("7.0.0")) {
  if (!$updater_utils->has_updated('rogo2263')) {
    // Latex column no longer required.
    $sql = "ALTER TABLE properties DROP COLUMN latex_needed";
    $updater_utils->execute_query($sql, false);
    // Install tinymce plugin.
    $defaulttexteditorns = 'plugins\texteditor\plugin_tinymce3_texteditor\plugin_tinymce3_texteditor';
    $defaulttexteditor = new $defaulttexteditorns($mysqli);
    $defaulttexteditor->install($mysql_admin_user, $mysql_admin_pass);
    // Install plain plugin.
    $plaintexteditorns = 'plugins\texteditor\plugin_plain_texteditor\plugin_plain_texteditor';
    $plaintexteditor = new $plaintexteditorns($mysqli);
    $plaintexteditor->install($mysql_admin_user, $mysql_admin_pass);
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