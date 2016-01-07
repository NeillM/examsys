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
* Confirm that it is OK to proceed deleting a campus.
*
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @version 1.0
* @copyright Copyright (c) 2016 onwards The University of Nottingham
* @package
*/

require '../include/sysadmin_auth.inc';
require_once '../include/errors.inc';

$campus = check_var('campus', 'GET', true, false, true);

$result = $mysqli->prepare("SELECT id, name, isdefault FROM campus WHERE id = ?");
$result->bind_param("i" , $campus);
$result->execute();
$result->bind_result($id, $name, $isdefault);
$result->fetch();
$result->close();
$found = true;
if ($db->errno != 0) {
	$found = false;
}
// Do not delete if default.
if ($isdefault) {
	$error = true;
	$msg = $string['campusisdefault'];
	$title = $string['cannotdelete'];
}
// Do not delete if in use.
$lab = new LabFactory($mysqli);
if ($lab->check_campus_in_use($name)) {
	$error = true;
	$msg = $string['campusisinuse'];
	$title = $string['cannotdelete'];
}
	
if (!$found) {
	$msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
	$title = $string['pagenotfound'];
	$notice->display_notice_and_exit($mysqli, $title, $msg, $title, '../artwork/page_not_found.png', '#C00000', true, true);
}
if ($error) {
	$notice->display_notice($title, $msg, '../artwork/page_not_found.png');
	exit;
}

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title><?php echo $string['confirmdelete']; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/check_delete.css" />
</head>

<body>
<p><strong><?php echo $string['msg']; ?></strong></p>

<div class="button_bar">
  <form action="do_delete_campus.php" method="post">
    <input type="hidden" name="campus" value="<?php echo $campus; ?>"/>
    <input class="delete" type="submit" name="submit" value="<?php echo $string['delete']; ?>"/><input class="cancel" type="button" name="cancel" value="<?php echo $string['cancel']; ?>" onclick="javascript:window.close();"/>
  </form>
</div>
    
</body>
</html>