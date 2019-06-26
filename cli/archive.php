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

/**
 * Archive data
 * @author Simon Wilkinson
 * @author Dr Joseph baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2019 The University of Nottingham
 */

// Only run from the command line!
if (PHP_SAPI != 'cli') {
    die("Please run this script from the CLI!\n");
}

set_time_limit(0);

$rogo_path = dirname(__DIR__);
if (!file_exists($rogo_path . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.inc.php')) {
    echo 'Rogo is not installed.';
    exit(0);
}

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'load_config.php';

// Lets look to see what arguments have been passed.
$options = 'hu:p:a:l';
$longoptions = array(
    'help',
    'user:',
    'passwd:',
    'account:',
    'ldap',
);

$optionslist = getopt($options, $longoptions);
$help = 'Rogo archive script options'
    . PHP_EOL . PHP_EOL . "-h, --help \t\tDisplay help"
    . PHP_EOL . PHP_EOL . "-u, --user, \t\tDatabase username"
    . PHP_EOL . PHP_EOL . "-p, --passwd, \t\tDatabase password"
    . PHP_EOL . PHP_EOL . "-a, --account, \t\tRogo account to log process against"
    . PHP_EOL . PHP_EOL . "-l, --ldap, \t\tRogo is using ldap accounts";

if (isset($optionslist['h']) or isset($optionslist['help'])) {
    // Display some help information.
    cli_utils::prompt($help);
    exit(0);
}

if (isset($optionslist['u'])) {
    $cfg_db_username = $optionslist['u'];
} elseif (isset($optionslist['user'])) {
    $cfg_db_username = $optionslist['user'];
}

if (isset($optionslist['p'])) {
    $databasepassword = $optionslist['p'];
} elseif (isset($optionslist['passwd'])) {
    $databasepassword = $optionslist['passwd'];
}

if (isset($optionslist['a'])) {
    $my_id = $optionslist['a'];
} elseif (isset($optionslist['account'])) {
    $my_id = $optionslist['account'];
}

if (isset($optionslist['l']) or isset($optionslist['ldap'])) {
    $ldap = 1;
} else {
    $ldap = 0;
}

$cfg_db_host = $configObject->get('cfg_db_host');
$databaseport = $configObject->get('cfg_db_port');
$cfg_db_database = $configObject->get('cfg_db_database');
$databasecharset = $configObject->get('cfg_db_charset');

@$mysqli = new mysqli($cfg_db_host, $cfg_db_username, $databasepassword, $cfg_db_database, $databaseport);
if ($mysqli->connect_error == '') {
    $mysqli->set_charset($databasecharset);
} else {
    cli_utils::prompt('Unable to connect to database - ' . $mysqli->connect_error);
    exit(0);
}

$logger = new Logger($mysqli);

$stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param('s', $my_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($userid);
if ($stmt->num_rows() !== 1) {
    cli_utils::prompt('User `' . $my_id . '` does not exist');
    $stmt->close();
    exit(0);
}
$stmt->fetch();
$stmt->close();

cli_utils::prompt("Start Archive Process " . date("Y-m-d H:i:s"));

$log0_deleted_overall = 0;
$log1_deleted_overall = 0;
$lti_user_deleted_overall = 0;

$stmt = $mysqli->prepare("SELECT id FROM users WHERE roles='left' OR roles='graduate'");
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($user_to_delete);
$numusers = $stmt->num_rows();
cli_utils::prompt($numusers . ' users to archive');
$usercount = 0;
while ($stmt->fetch()) {
    $log0_deleted = 0;
    $log1_deleted = 0;
    $lti_user_deleted = 0;

    $lm_check = $mysqli->prepare("SELECT count(lm.id) FROM log0 l INNER JOIN log_metadata lm ON l.metadataID = lm.id WHERE lm.userID = ?");
    $lm_check->bind_param('i', $user_to_delete);
    $lm_check->execute();
    $lm_check->bind_result($lm_count);
    $lm_check->fetch();
    $lm_check->close();

    cli_utils::prompt('Archiving user ' . $usercount . ' / ' . $numusers);

    if (isset($lm_count) and $lm_count > 0) {
        cli_utils::prompt($lm_count . ' Log0 rows to archive');
        $logquery = $mysqli->prepare("INSERT INTO log0_deleted SELECT l.* FROM log0 l INNER JOIN log_metadata lm ON l.metadataID = lm.id WHERE lm.userID = ?");
        $logquery->bind_param('i', $user_to_delete);
        $logquery->execute();
        $logquery->close();

        $logquery = $mysqli->prepare("INSERT INTO log_metadata_deleted SELECT DISTINCT lm.* FROM log0 l INNER JOIN log_metadata lm ON l.metadataID = lm.id WHERE lm.userID = ?");
        $logquery->bind_param('i', $user_to_delete);
        $logquery->execute();
        $logquery->close();

        // Delete from formative log.
        $deletequery = $mysqli->prepare("DELETE l, lm FROM log0 l INNER JOIN log_metadata lm ON l.metadataID = lm.id WHERE lm.userID = ?");
        $deletequery->bind_param('i', $user_to_delete);
        $deletequery->execute();
        $log0_deleted = $deletequery->affected_rows;
        $log0_deleted_overall += $log0_deleted;
        $deletequery->close();

        // Record the delete in audit trail
        $logger->track_change('Deleted records from log0', $user_to_delete, $my_id, $log0_deleted, 0, 'Clear old logs');
    }

    $lm_check = $mysqli->prepare("SELECT count(lm.id) FROM log1 l INNER JOIN log_metadata lm ON l.metadataID = lm.id WHERE lm.userID = ?");
    $lm_check->bind_param('i', $user_to_delete);
    $lm_check->execute();
    $lm_check->bind_result($lm_count);
    $lm_check->fetch();
    $lm_check->close();

    if (isset($lm_count) and $lm_count > 0) {
        cli_utils::prompt($lm_count . ' Log1 rows to archive');
        $logquery = $mysqli->prepare("INSERT INTO log1_deleted SELECT l.* FROM log1 l INNER JOIN log_metadata lm ON l.metadataID = lm.id WHERE lm.userID = ?");
        $logquery->bind_param('i', $user_to_delete);
        $logquery->execute();
        $logquery->close();

        $logquery = $mysqli->prepare("INSERT INTO log_metadata_deleted SELECT DISTINCT lm.* FROM log1 l INNER JOIN log_metadata lm ON l.metadataID = lm.id WHERE lm.userID = ?");
        $logquery->bind_param('i', $user_to_delete);
        $logquery->execute();
        $logquery->close();

        // Delete from formative log.
        $deletequery = $mysqli->prepare("DELETE l, lm FROM log1 l INNER JOIN log_metadata lm ON l.metadataID = lm.id WHERE lm.userID = ?");
        $deletequery->bind_param('i', $user_to_delete);
        $deletequery->execute();
        $log1_deleted = $deletequery->affected_rows;
        $log1_deleted_overall += $log1_deleted;
        $deletequery->close();

        // Record the delete in audit trail
        $logger->track_change('Deleted records from log1', $user_to_delete, $my_id, $log1_deleted, 0, 'Clear old logs');
    }
    
    // Delete from lti_user table.
    $deletequery = $mysqli->prepare("DELETE FROM lti_user WHERE lti_user_equ = ?");
    $deletequery->bind_param('i', $user_to_delete);
    $deletequery->execute();
    $lti_user_deleted = $deletequery->affected_rows;
    $lti_user_deleted_overall += $lti_user_deleted;
    $deletequery->close();

    if ($lti_user_deleted > 0) {
        cli_utils::prompt($lti_user_deleted . ' LTI users to delete');
        $logger->track_change('Delete LTI user', $user_to_delete, $my_id, 1, 0, 'Clear old logs');
    }
    $usercount++;
}
$stmt->close();

// Reset passwords
if ($ldap) {
    cli_utils::prompt('LDAP enabled - Resetting passwords');
    $updatequery = $mysqli->prepare("UPDATE users SET password='' WHERE roles IN('Student', 'graduate', 'left')");
    $roles_string = 'Student, graduate and left';
} else {
    cli_utils::prompt('LDAP disabled - Resetting passwords');
    $updatequery = $mysqli->prepare("UPDATE users SET password='' WHERE roles IN('graduate', 'left')");
    $roles_string = 'graduate and left';
}
$updatequery->execute();
if ($updatequery->affected_rows > 0) {
    $logger->track_change('Reset passwords for roles ' . $roles_string, $my_id, $my_id, 1, 0, 'Clear old logs');
}
$updatequery->close();

cli_utils::prompt("Log0 records archived: " . $log0_deleted_overall);
cli_utils::prompt("Log1 records archived: " . $log1_deleted_overall);
cli_utils::prompt("End Archive Process " . date("Y-m-d H:i:s"));
