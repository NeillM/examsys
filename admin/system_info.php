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
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require_once '../include/sysadmin_auth.inc';
require_once '../include/sidebar_menu.inc';

/**
 * Formats space in human-readable format.
 * @param int $space - Raw bytes to be converted.
 * @return string space in human readable format.
 */
function format_space($space)
{
    $units = ['KB', 'MB', 'GB', 'TB'];
    $i = -1;
    do {
        $i++;
        $space = $space / 1024;
        $correct_units = $units[$i];
    } while ($space > 1024);

    return round($space, 1) . ' ' . $correct_units;
}

$render = new render($configObject);
$lang = [
    'title' => $string['systeminformation'],
];
$additionaljs = '';
$addtionalcss = '<link rel="stylesheet" type="text/css" href="../css/system_info.css"/>';
$render->render_admin_header($lang, $additionaljs, $addtionalcss);

require '../include/toprightmenu.inc';
$toprightmenu = draw_toprightmenu();
$render->render_admin_options('', '', $lang, $toprightmenu, 'admin/no_sidebar.html');

$breadcrumb = [
    $string['home'] => '../index.php',
    $string['administrativetools'] => 'index.php',
];
$render->render_admin_content($breadcrumb, $lang);

// Get information about important database tables.
$sub_result = $mysqli->prepare('SELECT COUNT(id) FROM log_late');   // Query to get an accurate figure for log_late.
$sub_result->execute();
$sub_result->store_result();
$sub_result->bind_result($latelogs);
$sub_result->fetch();
$sub_result->close();

$sub_result = $mysqli->prepare('SELECT COUNT(id) FROM temp_users');   // Query to get an accurate figure for temp_users.
$sub_result->execute();
$sub_result->store_result();
$sub_result->bind_result($tempusers);
$sub_result->fetch();
$sub_result->close();

// Get the MySQL stats.
$mysql_stats = [];

$status = explode('  ', (string) $mysqli->stat());
for ($i = 0; $i <= 7; $i++) {
    $parts = explode(': ', $status[$i]);
    if ($i == 0) {
        $hours = ($parts[1] / 60 / 60);
        if ($hours < 1) {
            $hours = ($parts[1] / 60);
            $units = 'minutes';
        } elseif ($hours < 24) {
            $units = 'hours';
        } else {
            $hours = ($hours / 24);
            $units = 'days';
        }
        $value = number_format($hours) . ' ' . $string[$units];
    } elseif ($i < 7) {
        $value = number_format($parts[1]);
    } else {
        $value = $parts[1];
    }
    $mysql_stats[] = [
        'title' => $string[mb_strtolower($parts[0])],
        'value' => $value,
    ];
}

// Get some details about the application.
$yearutils = new yearutils($mysqli);

if ($configObject->get_setting('core', 'system_hostname_lookup')) {
    $hostname_lookup = $string['hostname'];
} else {
    $hostname_lookup = $string['ipaddress'];
}

$application = [
    ['title' => $string['version'], 'value' => $configObject->get_setting('core', 'rogo_version')],
    ['title' => $string['webroot'], 'value' => $configObject->get('cfg_web_root')],
    ['title' => $string['database'], 'value' => $configObject->get('cfg_db_database')],
    ['title' => $string['company'], 'value' => $configObject->get_setting('core', 'misc_company')],
    ['title' => $string['lookups'], 'value' => $hostname_lookup],
    ['title' => $string['Session'], 'value' => $yearutils->get_academic_session($yearutils->get_current_session())],
];

$build = $configObject->getxml('build');
if (!is_null($build)) {
    $start = array_slice($application, 0, 1);
    $end = array_slice($application, 1);
    $application = array_merge($start, [['title' => $string['build'], 'value' => $build]], $end);
}

// Error reporting details.
$contexthandling = $configObject->get('errorcontexthandling');
$varcapture = $contexthandling ? $string[$contexthandling] : $string['none'];

$error_reporting = [
    ['title' => $string['authdebug'], 'value' => $configObject->get('display_auth_debug'), 'type' => 'onoff'],
    ['title' => $string['errorsonscreen'], 'value' => $configObject->get('displayerrors'), 'type' => 'onoff'],
    ['title' => $string['errorslogged'], 'value' => $configObject->get('logerrors'), 'type' => 'onoff'],
    ['title' => $string['phpnotices'], 'value' => $configObject->get('displayallerrors'), 'type' => 'onoff'],
    ['title' => $string['errorshutdown'], 'value' => $configObject->get('errorshutdownhandling'), 'type' => 'onoff'],
    ['title' => $string['varcapturemethod'], 'value' => $varcapture  , 'type' => 'string'],
];

// Information about plugins

// Get info on authentication stack
ob_start();
$render->render($authentication->version_info(), $string, 'admin/system_info_auth.html');
$authinfo = ob_get_clean();

// Get info about enhanced calculation plugin
$calc_data = [
    'type' => $configObject->get_setting('core', 'cfg_calc_type'),
    'settings' => $configObject->get_setting('core', 'cfg_calc_settings'),
];
ob_start();
$render->render($calc_data, $string, 'admin/system_info_calc.html');
$enhancedPlugininfo = ob_get_clean();

$plugins = [
    ['title' => $string['authentication'], 'value' => $authinfo],
    ['title' => $string['EnhancedCalcPlugin'], 'value' => $enhancedPlugininfo],
];

// Get server details.
$processorinfo = [];
if (php_uname('s') != 'Windows NT') {
    // Try Linux command first
    $results = shell_exec('cat /proc/cpuinfo');
    if ($results != '') {
        $lines = explode('<br />', nl2br($results));
        $core_no = 0;
        $processor = '';
        foreach ($lines as $individual_line) {
            $components = explode(':', $individual_line);
            if (trim($components[0]) == 'model name') {
                $core_no++;
                $processor = trim($components[1]);
            }
        }

        $processorinfo = [
            ['title' => $string['processor'], 'value' => $processor],
            ['title' => $string['cores'], 'value' => $core_no],
        ];
    } else {
        // Try Solaris command
        $results = shell_exec('psrinfo -pv');
        $lines = explode('<br />', nl2br($results));
        $physical = 0;
        $virtual = 0;
        $processor = '';
        foreach ($lines as $individual_line) {
            if (mb_strpos($individual_line, 'The physical processor') !== false) {
                $tmp_line = str_replace('The physical processor has ', '', trim($individual_line));
                $physical++;
                $virtual += mb_substr($tmp_line, 0, 1);
            }
            if (mb_strpos($individual_line, 'clock') !== false) {
                $processor = trim($individual_line);
                $processor_parts = explode('\(', $processor);
                $speed_parts = explode('clock ', $processor_parts[1]);
                $speed = str_replace(')', '', $speed_parts[1]);
            }
        }

        if (isset($processor_parts[0])) {
            $processorinfo = [
                ['title' => $string['processor'], 'value' => $processor_parts[0] . "($speed)"],
                ['title' => $string['cpus'], 'value' => "$physical ($virtual virtual)"],
            ];
        }
    }
} else {
    $results = shell_exec('wmic cpu get name');
    $lines = explode('<br />', nl2br($results));
    $processorinfo = [
        ['title' => $string['processor'], 'value' => $lines[1]],
    ];
}

$serverinfo = [
    ['title' => $string['servername'], 'value' => gethostbyaddr(gethostbyname($_SERVER['SERVER_NAME']))],
    ['title' => $string['hostname'] , 'value' => $_SERVER['HTTP_HOST']],
    ['title' => $string['ipaddress'], 'value' => NetworkUtils::get_server_address()],
    ['title' => $string['clock'], 'value' => date($configObject->get('cfg_long_datetime_php'))],
    ['title' => $string['os'], 'value' => php_uname('s')],
    ['title' => $string['webserver'], 'value' => $_SERVER['SERVER_SOFTWARE']],
    ['title' => $string['php'], 'value' => phpversion()],
    ['title' => $string['mysql'], 'value' => $mysqli->server_info],
];

// Partition information.
if (php_uname('s') == 'Windows NT') {
    $disks = ['A:\\', 'B:\\', 'C:\\', 'D:\\', 'E:\\', 'F:\\', 'G:\\', 'H:\\', 'I:\\',
        'J:\\', 'K:\\', 'L:\\', 'M:\\', 'N:\\', 'O:\\', 'P:\\', 'Q:\\', 'R:\\', 'S:\\', 'T:\\',
        'U:\\', 'V:\\', 'W:\\', 'X:\\', 'Y:\\', 'Z:\\'];
    $i = 1;
    foreach ($disks as $disk) {
        if (file_exists($disk)) {
            $master_array[$i][3] = @disk_free_space($disk);
            $master_array[$i][1] = @disk_total_space($disk);
            $master_array[$i][5] = $disk;
            $master_array[$i]['totalspace'] = format_space($master_array[$i][1]);
            $master_array[$i]['freespace'] = format_space($master_array[$i][3]);
            if ($master_array[$i][1] > 0) {
                $master_array[$i]['percentage'] = 100 - (($master_array[$i][3] / $master_array[$i][1]) * 100);
            } else {
                $master_array[$i]['percentage'] = 0;
            }
            $i++;
        }
    }
    $row_no = $i + 1;
} else {
    $master_array = [];
    // List free disk space, ensuring one file system per line.
    // df -P flag not used as not supported by Solaris.
    $results = shell_exec("df -k | awk 'NF == 1 {printf($1); next}; {print}'");
    $lines = explode('<br />', nl2br($results));
    $row_no = 0;
    foreach ($lines as $individual_line) {
        if ($row_no > 0) {
            $cols = explode(' ', $individual_line);
            foreach ($cols as $individual_col) {
                if ($individual_col != '') {
                    $master_array[$row_no][] = $individual_col;
                }
            }
            if (isset($master_array[$row_no][1]) and isset($master_array[$row_no][3])) {
                $master_array[$row_no]['totalspace'] = format_space($master_array[$row_no][1] * 1024);
                $master_array[$row_no]['freespace'] = format_space($master_array[$row_no][3] * 1024);
                if ($master_array[$row_no][1] > 0) {
                    $master_array[$row_no]['percentage'] = 100 - (($master_array[$row_no][3] / $master_array[$row_no][1]) * 100);
                } else {
                    $master_array[$row_no]['percentage'] = 0;
                }
            }
        }
        $row_no++;
    }
}

// Get information about the directories.

$mediadirectory = rogo_directory::get_directory('media');
$themedirectory = rogo_directory::get_directory('theme');
$studenthelp = rogo_directory::get_directory('help_student');
$staffhelp = rogo_directory::get_directory('help_staff');
$photodirectory = rogo_directory::get_directory('user_photo');
$qtiimportdirectory = rogo_directory::get_directory('qti_import');
$qtiexportdirectory = rogo_directory::get_directory('qti_export');
$emailtemplatesdirectory = rogo_directory::get_directory('email_templates');

$directories = [
    [
        'name' => $string['dir_media'],
        'permissions' => $mediadirectory->check_permissions(),
        'location' => $mediadirectory->location(),
    ],
    [
        'name' => $string['dir_theme'],
        'permissions' => $themedirectory->check_permissions(),
        'location' => $themedirectory->location(),
    ],
    [
        'name' => $string['dir_student_help'],
        'permissions' => $studenthelp->check_permissions(),
        'location' => $studenthelp->location(),
    ],
    [
        'name' => $string['dir_staff_help'],
        'permissions' => $staffhelp->check_permissions(),
        'location' => $staffhelp->location(),
    ],
    [
        'name' => $string['dir_photo'],
        'permissions' => $photodirectory->check_permissions(),
        'location' => $photodirectory->location(),
    ],
    [
        'name' => $string['dir_qti_import'],
        'permissions' => $qtiimportdirectory->check_permissions(),
        'location' => $qtiimportdirectory->location(),
    ],
    [
        'name' => $string['dir_qti_export'],
        'permissions' => $qtiexportdirectory->check_permissions(),
        'location' => $qtiexportdirectory->location(),
    ],
    [
        'name' => $string['dir_email'],
        'permissions' => $emailtemplatesdirectory->check_permissions(),
        'location' => $emailtemplatesdirectory->location(),
    ],
];

$pagedata = [
    'application' => $application,
    'directories' => $directories,
    'error_reporting' => $error_reporting,
    'late_log_count' => $latelogs,
    'late_log_warning' => ($latelogs > 0),
    'mysql_stats' => $mysql_stats,
    'partitions' => $master_array,
    'plugins' => $plugins,
    'serverinfo' => array_merge($processorinfo, $serverinfo),
    'temp_user_count' => $tempusers,
    'temp_user_warning' => ($tempusers > 0),
];

$render->render($pagedata, $string, 'admin/system_info.html');
$render->render_admin_footer();
