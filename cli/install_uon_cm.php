<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file is used to install the settings for the UoN Curriculum mapping plugin.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 The University of Nottingham
 */

// Only run from the command line!
if (PHP_SAPI != 'cli') {
    die("Please run this script from the CLI!\n");
}

$rogo_path = dirname(__DIR__);
if (!file_exists($rogo_path . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.inc.php')) {
    echo 'ExamSys is not installed.';
    exit(0);
}

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'load_config.php';

$message = <<<MESSAGE
This will install the settings for the University of Nottingham Curriculum mapping system plugin.

Do you wish to proceed? (y/n)
MESSAGE;

$confirm = cli_utils::user_response($message, ['y', 'n']);

if ($confirm === 'n') {
    // The installation has been aborted.
    exit(0);
}

\DBUtils::cliDbConnect();
$configObject = Config::get_instance();
if ($configObject->get_setting('core', 'cfg_cmap_url') === null) {
    $configObject->set_setting('cfg_cmap_url', '', Config::URL);
}
if ($configObject->get_setting('core', 'cfg_moodle_base_url') === null) {
    $configObject->set_setting('cfg_moodle_base_url', '', Config::URL);
}
if ($configObject->get_setting('core', 'cfg_cmap_url_timeout') === null) {
    $configObject->set_setting('cfg_cmap_url_timeout', 20, Config::INTEGER);
}
