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
* Confirm that it is OK to proceed deleting a status.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/sysadmin_auth.inc';
require_once '../include/errors.inc';
require_once '../classes/question_status.class.php';
require_once '../classes/questionutils.class.php';

$status_id = check_var('id', 'GET', true, false, true);

try {
  $status = new QuestionStatus($mysqli, $string, $status_id);
} catch (DatabaseException $ex) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

$q_count = QuestionUtils::get_question_count_by_status($status_id, $mysqli);
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['confirmstatusdelete']; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/check_delete.css" />
</head>

<body>

<table>
<?php
if ($q_count == 0) {
?>
<tr>
  <td class="icon"><img src="../artwork/delete_warning.png" width="48" height="48" alt="<?php echo $string['recyclebin']; ?>" /></td>
  <td>
    <p><strong><?php echo $string['msg']; ?></strong><p>
    <br />
    <br />
    <div style="text-align:right">
    <form action="do_delete_status.php" method="post">
      <input type="hidden" name="status_id" value="<?php echo $_GET['id']; ?>" />
      <input style="width:140px" type="submit" name="submit" value="<?php echo $string['delete']; ?>" />&nbsp;
      <input style="width:80px" type="button" name="cancel" value="<?php echo $string['cancel']; ?>" onclick="javascript:window.close();" />
    </form>
    </div>
  </td>
</tr>
<?php
} else {
?>
<tr>
  <td class="icon"><img src="../artwork/delete_warning.png" width="48" height="48" alt="<?php echo $string['recyclebin']; ?>" /></td>
  <td>
    <p><strong><?php echo $string['questionassigned']; ?></strong><p>
    <br />
    <br />
    <div style="text-align:right">
    <form action="do_delete_status.php" method="post">
      <input style="width:80px" type="button" name="cancel" value="<?php echo $string['cancel']; ?>" onclick="javascript:window.close();" />
    </form>
    </div>
  </td>
</tr>
<?php
}
?>

</table>

</body>
</html>