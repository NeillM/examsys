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
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require '../include/sysadmin_auth.inc';
require_once '../classes/question_status.class.php';

// Check if we have any faculties
$statuses = QuestionStatus::get_all_statuses($mysqli, $string);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['questionstatuses'] . ' ' . $configObject->get('cfg_install_type'); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu_qstatus.css" />
  <link rel="stylesheet" type="text/css" href="../css/list.css" />

  <script src="../js/jquery-1.6.1.min.js" type="text/javascript"></script>
  <script src="../js/staff_help.js" type="text/javascript"></script>
  <script src="../js/list_ul.js" type="text/javascript"></script>
</head>

<body>
<?php
  require '../include/status_options.inc.php';
?>
  <div id="content" class="content">
    <table class="header">
      <tr>
        <th colspan="2">
          <div class="breadcrumb">
            <a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php"><?php echo $string['administrativetools']; ?></a>
          </div>
          <div style="margin-left:10px; font-size:200%; font-weight:bold"><?php echo $string['questionstatuses']; ?></div>
        </th>
        <th style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(233); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="<?php echo $string['help']; ?>" border="0" /></a></th>
      </tr>
      <tr><th colspan="3" class="bevel"></th></tr>
      </table>

      <ul id="statuses" class="selectlist">
<?php
$i = 1;
foreach ($statuses as $status) {
?>
        <li id="status_<?php echo $i ?>" class="selectable" data-id="<?php echo $status->id ?>"><?php echo $status->get_name() ?></li>
<?php
  $i++;
}
?>
      </ul>
  </div>
</body>
</html>