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
 * Archive data for graduated and left users
 * Moves formative and progress user responses to archive tables, deletes LTI links of users and resets their password
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
$options = 'ha:lr';
$longoptions = array(
    'help',
    'account:',
    'ldap',
    'archive',
);

$optionslist = getopt($options, $longoptions);
$help = 'Rogo archive script options'
    . PHP_EOL . PHP_EOL . "-h, --help \t\tDisplay help"
    . PHP_EOL . PHP_EOL . "-a, --account, \t\tRogo account to log process against [Required]"
    . PHP_EOL . PHP_EOL . "-l, --ldap, \t\tRogo is using ldap accounts [Optional]"
    . PHP_EOL . PHP_EOL . "-r, --archive, \t\tArchive data to a seperate database [Optional]";

if ((isset($optionslist['h']) or isset($optionslist['help'])) or ((!isset($optionslist['a']) and !isset($optionslist['account'])))) {
    // Display some help information.
    cli_utils::prompt($help);
    exit(0);
}

if (isset($optionslist['a'])) {
    $account = $optionslist['a'];
} elseif (isset($optionslist['account'])) {
    $account = $optionslist['account'];
}

if (isset($optionslist['l']) or isset($optionslist['ldap'])) {
    $ldap = 1;
} else {
    $ldap = 0;
}

if (isset($optionslist['r']) or isset($optionslist['archive'])) {
    $archive = 1;
} else {
    $archive = 0;
}

$cfg_db_host = $configObject->get('cfg_db_host');
$cfg_db_port = $configObject->get('cfg_db_port');
$cfg_db_database = $configObject->get('cfg_db_database');
$cfg_db_charset = $configObject->get('cfg_db_charset');
$cfg_db_sysadmin_user = $configObject->get('cfg_db_sysadmin_user');
$cfg_db_sysadmin_passwd = $configObject->get('cfg_db_sysadmin_passwd');

@$mysqli = new mysqli($cfg_db_host, $cfg_db_sysadmin_user, $cfg_db_sysadmin_passwd, $cfg_db_database, $cfg_db_port);
if ($mysqli->connect_error == '') {
    $mysqli->set_charset($cfg_db_charset);
} else {
    cli_utils::prompt('Unable to connect to database - ' . $mysqli->connect_error);
    exit(0);
}

// Setup archive database connection.
if ($archive) {
    cli_utils::prompt('Connecting to archive database');
    $cfg_archivedb_host = $configObject->get('cfg_archivedb_host');
    $cfg_archivedb_port = $configObject->get('cfg_archivedb_port');
    $cfg_archivedb_database = $configObject->get('cfg_archivedb_database');
    $cfg_archivedb_charset = $configObject->get('cfg_archivedb_charset');
    $cfg_archivedb_sysadmin_user = $configObject->get('cfg_archivedb_username');
    $cfg_archivedb_sysadmin_passwd = $configObject->get('cfg_archivedb_passwd');
    @$mysqliarchive = new mysqli($cfg_archivedb_host, $cfg_archivedb_sysadmin_user, $cfg_archivedb_sysadmin_passwd, $cfg_archivedb_database, $cfg_archivedb_port);
    if ($mysqliarchive->connect_error == '') {
        $mysqliarchive->set_charset($cfg_archivedb_charset);
    } else {
        cli_utils::prompt('Unable to connect to archive database - ' . $mysqliarchive->connect_error);
        exit(0);
    }
} else {
    $mysqliarchive = $mysqli;
    $cfg_archivedb_database = $cfg_db_database;
}

$logger = new Logger($mysqli);

$stmt = $mysqli->prepare("SELECT id FROM " . $cfg_db_database . ".users WHERE username = ?");
$stmt->bind_param('s', $account);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($userid);
if ($stmt->num_rows() !== 1) {
    cli_utils::prompt('User `' . $account . '` does not exist');
    $stmt->close();
    exit(0);
}
$stmt->fetch();
$stmt->close();

cli_utils::prompt("Start Archive Process " . date("Y-m-d H:i:s"));

$log0_deleted_overall = 0;
$log1_deleted_overall = 0;
$lti_user_deleted_overall = 0;

$stmt = $mysqli->prepare("SELECT id FROM " . $cfg_db_database . ".users WHERE roles='left' OR roles='graduate'");
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($user_to_delete);
$numusers = $stmt->num_rows();
cli_utils::prompt($numusers . ' users to potentially archive');
$usercount = 1;
while ($stmt->fetch()) {
    $log0_deleted = 0;
    $log1_deleted = 0;
    $lti_user_deleted = 0;

    $lm_check = $mysqli->prepare("SELECT count(lm.id) FROM " . $cfg_db_database . ".log0 l INNER JOIN " . $cfg_db_database . ".log_metadata lm ON l.metadataID = lm.id WHERE lm.userID = ?");
    $lm_check->bind_param('i', $user_to_delete);
    $lm_check->execute();
    $lm_check->bind_result($lm_count);
    $lm_check->fetch();
    $lm_check->close();

    cli_utils::prompt('Checking user ' . $usercount . ' / ' . $numusers);

    if (isset($lm_count) and $lm_count > 0) {
        cli_utils::prompt($lm_count . ' Log0 rows to archive');
        $logquery = $mysqliarchive->prepare("INSERT INTO " . $cfg_archivedb_database . ".log0_deleted SELECT l.* FROM " . $cfg_db_database . ".log0 l INNER JOIN " . $cfg_db_database . ".log_metadata lm ON l.metadataID = lm.id WHERE lm.userID = ?");
        $logquery->bind_param('i', $user_to_delete);
        $logquery->execute();
        $logquery->close();

        $logquery = $mysqliarchive->prepare("INSERT INTO " . $cfg_archivedb_database . ".log_metadata_deleted SELECT DISTINCT lm.* FROM " . $cfg_db_database . ".log0 l INNER JOIN " . $cfg_db_database . ".log_metadata lm ON l.metadataID = lm.id WHERE lm.userID = ?");
        $logquery->bind_param('i', $user_to_delete);
        $logquery->execute();
        $logquery->close();

        // Delete from formative log.
        $deletequery = $mysqli->prepare("DELETE l, lm FROM " . $cfg_db_database . ".log0 l INNER JOIN " . $cfg_db_database . ".log_metadata lm ON l.metadataID = lm.id WHERE lm.userID = ?");
        $deletequery->bind_param('i', $user_to_delete);
        $deletequery->execute();
        $log0_deleted = $deletequery->affected_rows;
        $log0_deleted_overall += $log0_deleted;
        $deletequery->close();

        // Record the delete in audit trail
        $logger->track_change('Deleted records from log0', $user_to_delete, $account, $log0_deleted, 0, 'Clear old logs');
    }

    $lm_check = $mysqli->prepare("SELECT count(lm.id) FROM " . $cfg_db_database . ".log1 l INNER JOIN " . $cfg_db_database . ".log_metadata lm ON l.metadataID = lm.id WHERE lm.userID = ?");
    $lm_check->bind_param('i', $user_to_delete);
    $lm_check->execute();
    $lm_check->bind_result($lm_count);
    $lm_check->fetch();
    $lm_check->close();

    if (isset($lm_count) and $lm_count > 0) {
        cli_utils::prompt($lm_count . ' Log1 rows to archive');
        $logquery = $mysqliarchive->prepare("INSERT INTO " . $cfg_archivedb_database . ".log1_deleted SELECT l.* FROM " . $cfg_db_database . ".log1 l INNER JOIN " . $cfg_db_database . ".log_metadata lm ON l.metadataID = lm.id WHERE lm.userID = ?");
        $logquery->bind_param('i', $user_to_delete);
        $logquery->execute();
        $logquery->close();

        $logquery = $mysqliarchive->prepare("INSERT INTO " . $cfg_archivedb_database . ".log_metadata_deleted SELECT DISTINCT lm.* FROM " . $cfg_db_database . ".log1 l INNER JOIN " . $cfg_db_database . ".log_metadata lm ON l.metadataID = lm.id WHERE lm.userID = ?");
        $logquery->bind_param('i', $user_to_delete);
        $logquery->execute();
        $logquery->close();

        // Delete from formative log.
        $deletequery = $mysqli->prepare("DELETE l, lm FROM " . $cfg_db_database . ".log1 l INNER JOIN " . $cfg_db_database . ".log_metadata lm ON l.metadataID = lm.id WHERE lm.userID = ?");
        $deletequery->bind_param('i', $user_to_delete);
        $deletequery->execute();
        $log1_deleted = $deletequery->affected_rows;
        $log1_deleted_overall += $log1_deleted;
        $deletequery->close();

        // Record the delete in audit trail
        $logger->track_change('Deleted records from log1', $user_to_delete, $account, $log1_deleted, 0, 'Clear old logs');
    }

    // Delete from lti_user table.
    $deletequery = $mysqli->prepare("DELETE FROM " . $cfg_db_database . ".lti_user WHERE lti_user_equ = ?");
    $deletequery->bind_param('i', $user_to_delete);
    $deletequery->execute();
    $lti_user_deleted = $deletequery->affected_rows;
    $lti_user_deleted_overall += $lti_user_deleted;
    $deletequery->close();

    if ($lti_user_deleted > 0) {
        cli_utils::prompt($lti_user_deleted . ' LTI users to delete');
        $logger->track_change('Delete LTI user', $user_to_delete, $account, 1, 0, 'Clear old logs');
    }
    $usercount++;
}
$stmt->close();

// Reset passwords
if ($ldap) {
    cli_utils::prompt('LDAP enabled - Resetting passwords');
    $updatequery = $mysqli->prepare("UPDATE " . $cfg_db_database . ".users SET password='' WHERE roles IN('Student', 'graduate', 'left')");
    $roles_string = 'Student, graduate and left';
} else {
    cli_utils::prompt('LDAP disabled - Resetting passwords');
    $updatequery = $mysqli->prepare("UPDATE " . $cfg_db_database . ".users SET password='' WHERE roles IN('graduate', 'left')");
    $roles_string = 'graduate and left';
}
$updatequery->execute();
if ($updatequery->affected_rows > 0) {
    $logger->track_change('Reset passwords for roles ' . $roles_string, $account, $account, 1, 0, 'Clear old logs');
}
$updatequery->close();

cli_utils::prompt("Log0 records archived: " . $log0_deleted_overall);
cli_utils::prompt("Log1 records archived: " . $log1_deleted_overall);
cli_utils::prompt("End Archive Process " . date("Y-m-d H:i:s"));
