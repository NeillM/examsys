<?php
if ($updater_utils->check_version("7.0.0")) {
  if (!$updater_utils->has_updated('rogo_1813')) {
    $search = '$cfg_db_collation';
    switch ($configObject->get('cfg_db_charset')) {
      case 'utf8mb4':
        $new_lines = '$cfg_db_collation = \'utf8mb4_unicode_ci\';' . PHP_EOL;
        break;
      case 'utf8':
        $new_lines = '$cfg_db_collation = \'utf8_general_ci\';' . PHP_EOL;
        break;
      default:
        $new_lines = '$cfg_db_collation = \'latin1_swedish_ci\';' . PHP_EOL;
    }
    $target_line = '$cfg_db_charset';
    $updater_utils->add_line($string, $search, $new_lines, $default_line, $cfg_web_root, $target_line);
    $replace = 'debug.inc';
    $updater_utils->replace_line($string, $replace, '', $cfg_web_root);
    $sqloauth_clients = "ALTER TABLE oauth_clients MODIFY COLUMN redirect_uri TEXT NOT NULL";
    $updater_utils->execute_query($sqloauth_clients, false);
    $sqloauth_access_tokens = "ALTER TABLE oauth_access_tokens MODIFY COLUMN scope TEXT";
    $updater_utils->execute_query($sqloauth_access_tokens, false);
    $sqloauth_authorization_codes = "ALTER TABLE oauth_authorization_codes MODIFY COLUMN scope TEXT";
    $updater_utils->execute_query($sqloauth_authorization_codes, false);
    $sqloauth_authorization_codes2 = "ALTER TABLE oauth_authorization_codes MODIFY COLUMN redirect_uri TEXT";
    $updater_utils->execute_query($sqloauth_authorization_codes2, false);
    $sqloauth_refresh_tokens = "ALTER TABLE oauth_refresh_tokens MODIFY COLUMN scope TEXT";
    $updater_utils->execute_query($sqloauth_refresh_tokens, false);
    $sqloauth_users = "ALTER TABLE oauth_users MODIFY COLUMN password TEXT";
    $updater_utils->execute_query($sqloauth_users, false);
    $sqloauth_jwt = "ALTER TABLE oauth_jwt MODIFY COLUMN public_key TEXT";
    $updater_utils->execute_query($sqloauth_jwt, false);
    $updater_utils->record_update('rogo_1813');
  }
}