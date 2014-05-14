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
* Rogō hompage. Uses ../include/options_menu.inc for the sidebar menu.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require_once '../include/staff_student_auth.inc';
require_once '../include/errors.inc';
require_once '../include/sidebar_menu.inc';
require_once '../classes/recyclebin.class.php';
require_once '../config/index.inc';
require_once '../classes/paperutils.class.php';
require_once '../classes/folderutils.class.php';

$userObject = UserObject::get_instance();

// Redirect Students (if not also staff), External Examiners and Invigilators to their own areas.
if ($userObject->has_role('Student') and !($userObject->has_role(array('Staff', 'Admin', 'SysAdmin')))) {
  header("location: ../students/");
  exit();
} elseif ($userObject->has_role('External Examiner')) {
  header("location: ../reviews/");
  exit();
} elseif ($userObject->has_role('Invigilator')) {
  header("location: ../invigilator/");
  exit();
}

// If we're still here we should be staff
require_once '../include/staff_auth.inc';
?><!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html; charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Rog&#333;<?php echo ' ' . $configObject->get('cfg_install_type'); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/rogo_logo.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  
  <style>
    h1 {font-size:160%}
  </style>

  <script src="../js/staff_help.js" type="text/javascript"></script>
  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery.validate.min.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <?php echo $configObject->get('cfg_js_root') ?>
  <script src="../js/sidebar.js" type="text/javascript"></script>
</head>

<body>

<?php
  require '../include/toprightmenu.inc';
	
	echo draw_toprightmenu();
?>

<div id="content" class="content">

<table cellpadding="0" cellspacing="0" border="0" class="header">
  <tr>
    <th style="padding-left:16px; padding-top:5px">

    <img src="../artwork/r_logo.gif" alt="logo" class="logo_img" />
    <div class="logo_lrg_txt">Rog&#333;</div>
    <div class="logo_small_txt"><?php echo $string['eassessmentmanagementsystem']; ?></div>

    </th>
    <th style="text-align:right; vertical-align:top"><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon"></th>
  </tr>
</table>
<br />
  <blockquote>
    <h1>Old Page</h1>
  
    <p>Please update your bookmarks accordingly:</p>

    <ul>
      <?php
        $staff_homepage = 'https://' . $_SERVER['HTTP_HOST'] . $cfg_root_path  . '/';
        $summative_homepage = 'https://' . $_SERVER['HTTP_HOST'] . $cfg_root_path  . '/paper/';
      ?>
      <li>New staff homepage: <a href="<?php echo $staff_homepage ?>"><?php echo $staff_homepage ?></a></li>
      <li>Summative exam homepage: <a href="<?php echo $summative_homepage ?>"><?php echo $summative_homepage ?></a></li>
    </ul>
  </blockquote>
</div>
</body>
</html>
