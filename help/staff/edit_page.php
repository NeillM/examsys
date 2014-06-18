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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../../include/sysadmin_auth.inc';
require '../../include/errors.inc';
require '../../include/help.inc';

$pageid = check_var('id', 'REQUEST', true, false, true);

header('Content-Type: text/html; charset=' . $configObject->get('cfg_page_charset'));

$rows = 0;
$result = $mysqli->prepare("SELECT title, body, id, DATE_FORMAT(checkout_time,'%Y%m%d%H%i%S') AS checkout_time, checkout_authorID, type, roles FROM staff_help WHERE id = ? LIMIT 1");
$result->bind_param('i', $pageid);
$result->execute();
$result->store_result();
$result->bind_result($page_title, $body, $id, $page_checkout_time, $page_checkout_authorID, $type, $roles);
$rows = $result->num_rows;
$result->fetch();
$result->close();

if ($rows == 0) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../../artwork/page_not_found.png', '#C00000', true, true);
}

if (isset($_POST['save_changes'])) {
  // Update help file record
  $tmp_body = $_POST['edit1'];
  $tmp_body_plain = strip_tags($tmp_body);
  $order = array("\r\n", "\n", "\r", "\t");
  $tmp_body_plain = str_replace($order, ' ', $tmp_body_plain);
  $tmp_body_plain = str_replace('  ', ' ', $tmp_body_plain);
  $tmp_title = $_POST['page_title'];

  if ($_POST['edit_id'] == $pageid) {
    // Editing normal page.
    $result = $mysqli->prepare("UPDATE staff_help SET title = ?, body = ?, body_plain = ?, checkout_time = NULL, checkout_authorID = NULL, roles = ? WHERE id = ?");
    $result->bind_param('ssssi', $tmp_title, $tmp_body, $tmp_body_plain, $_POST['page_roles'], $_POST['edit_id']);
    $result->execute();
    $result->close();
  } else {
    // Editing a page pointed to.
    $result = $mysqli->prepare("UPDATE staff_help SET title = ? WHERE id = ?");
    $result->bind_param('si', $tmp_title, $pageid);
    $result->execute();
    $result->close();

    $result = $mysqli->prepare("UPDATE staff_help SET body = ?, body_plain = ?, checkout_time = NULL, checkout_authorID = NULL, roles = ? WHERE id = ?");
    $result->bind_param('sssi', $tmp_body, $tmp_body_plain, $_POST['page_roles'], $_POST['edit_id']);
    $result->execute();
    $result->close();
  }

  $mysqli->close();
  header("location: display_page.php?id=$pageid");
  exit;
} elseif (isset($_POST['cancel'])) {
  // Release authoring lock.
  if ($_POST['checkout_authorID'] == $userObject->get_user_ID()) {
    $result = $mysqli->prepare("UPDATE staff_help SET checkout_time = NULL, checkout_authorID = NULL WHERE id = ?");
    $result->bind_param('i', $_POST['edit_id']);
    $result->execute();
    $result->close();
  }
  $mysqli->close();
  header("location: display_page.php?id=$pageid");
  exit;
} else {
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=7,9,10" >
  <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $configObject->get('cfg_page_charset') ?>">

  <title>Rog&#333;: Edit Help File</title>

  <link rel="stylesheet" type="text/css" href="../../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../../css/staff_help.css" />

  <?php echo $configObject->get('cfg_js_root') ?>
  <script type="text/javascript" src="../../tools/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
  <script type="text/javascript" src="../../tools/tinymce/jscripts/tiny_mce/tiny_config_help_staff.js"></script>
  <script type="text/javascript" src="../../js/jquery-1.11.1.min.js"></script>
  <script>
    $(document).ready(function() {
		  var docHeight = $(document).height();
			docHeight = docHeight - 100;
		  $('#edit1').css('height', docHeight + 'px');
		});
  </script>
</head>

<body>

<form name="add_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] . "?id=$pageid"; ?>">
<?php
  if ($type == 'pointer') {
    $edit_id = $body;

    $result = $mysqli->prepare("SELECT body FROM staff_help WHERE id = ?");
    $result->bind_param('i', $body);
    $result->execute();
    $result->store_result();
    $result->bind_result($body);
    $result->fetch();
    $result->close();
  } else {
    $edit_id = $_GET['id'];
  }

  echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:100%\"><tr><td style=\"padding-left:20px\"><input type=\"text\" style=\"color:#295AAD; font-size:160%; border: 1px solid #C0C0C0; font-weight:bold\" size=\"50\" name=\"page_title\" value=\"$page_title\" required /></td><td style=\"text-align:right\"><select name=\"page_roles\">\n";
  $categories = array('Staff', 'Admin', 'SysAdmin');
  foreach ($categories as $category) {
    if ($category == $roles) {
      echo "<option value=\"$category\" selected>$category</option>\n";
    } else {
      echo "<option value=\"$category\">$category</option>\n";
    }
  }

  echo "</select>\n</td></tr></table>\n<br />\n";

  echo "<textarea class=\"mceEditor\" id=\"edit1\" name=\"edit1\" style=\"width:100%; height:500px\">" .  htmlspecialchars($body, ENT_NOQUOTES) . "</textarea>\n";

  // Check for lockout.
  $current_time = date('YmdHis');
  $disabled = '';
  if ($userObject->get_user_ID() != $page_checkout_authorID) {
    if ($page_checkout_time != '' and $current_time - $page_checkout_time < 10000) {
      $editor = $mysqli->prepare("SELECT title, initials, surname FROM users WHERE id = ?");
      $editor->bind_param('i', $page_checkout_authorID);
      $editor->execute();
      $editor->bind_result($title, $initials, $surname);
      $editor->fetch();
      $editor->close();
      echo "<script language=\"JavaScript\">\n";
      echo "  alert('" . $string['entertitle'] . " $title $initials $surname. " . $string['isinreadonly'] . "')";
      echo "</script>\n";
      $checkout_authorID = $page_checkout_authorID;
      $disabled = ' disabled';
    } else {
      // Set the lock to the current time/author.
      $result = $mysqli->prepare("UPDATE staff_help SET checkout_time = NOW(), checkout_authorID = ? WHERE id = ?");
      $result->bind_param('ii', $userObject->get_user_ID(), $edit_id);
      $result->execute();
      $result->close();
      $checkout_authorID = $userObject->get_user_ID();

    }
  } elseif ($disabled == '' and $userObject->get_user_ID() == $page_checkout_authorID) {
    $checkout_authorID = $userObject->get_user_ID();
  }
  ?>
  <input type="hidden" name="checkout_authorID" value="<?php echo $checkout_authorID; ?>" />
  <div style="text-align:center; padding-top:8px"><input class="ok" type="submit" name="save_changes" value="<?php echo $string['save']; ?>"<?php echo $disabled; ?> /><input class="cancel" type="submit" name="cancel" value="<?php echo $string['cancel']; ?>" /></div>
  <input type="hidden" name="edit_id" value="<?php echo $edit_id; ?>" />
</form>
</body>
</html>
<?php
  $mysqli->close();
  }
?>