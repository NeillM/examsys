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
 * Data retention script - Deletes data no longer required.
 * @author Dr Joseph baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2021 The University of Nottingham
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
$options = 'h';
$longoptions = array(
    'help',
);

$optionslist = getopt($options, $longoptions);
$help = 'Rogo data retention script options. Deletes all data no longer required as specified in the system configuration'
    . PHP_EOL . PHP_EOL . "-h, --help \t\tDisplay help";

if ((isset($optionslist['h']) or isset($optionslist['help']))) {
    // Display some help information.
    cli_utils::prompt($help);
    exit(0);
}

\DBUtils::cliDbConnect();

$tables = Retention::getRetentionPolicesTables();
foreach ($tables as $table) {
    if (!Retention::deleteDataByRetentionPolicy($table)) {
        cli_utils::prompt($table . ' rentention policy set to retain all data.');
    } else {
        cli_utils::prompt($table . ' rentention policy applied');
    }
}
