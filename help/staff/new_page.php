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

require '../../include/staff_auth.inc';    // Only let staff create pages.
require '../../include/errors.inc';
require '../../include/help.inc';

header('Content-Type: text/html; charset=' . $configObject->get('cfg_page_charset'));

if (isset($_POST['save_changes'])) {
	$tmp_title = $_POST['title'];
		
	// Update help file record
	$tmp_body = $_POST['edit1'];
	$tmp_body_plain = strip_tags($tmp_body);

	$result = $mysqli->prepare("INSERT INTO staff_help VALUES (NULL, ?, ?, ?, 'page', NULL, NULL, ?, NULL)");
	$result->bind_param('ssss', $tmp_title, $tmp_body, $tmp_body_plain, $_POST['page_roles']);
	$result->execute();  
	$result->close();

	$page_id = $mysqli->insert_id;
	$mysqli->close();
	?>
	<html>
	<head>
	<title>Rogo</title>
	<script language="JavaScript">
		function reloadHelp() {
			window.top.location='<?php echo $configObject->get('cfg_root_path') ?>/help/staff/index.php?id=<?php echo $page_id; ?>';
		}
	</script>
	</head>
	<body onload="reloadHelp()">
	</body>
	</html>
	
	<?php
} else {
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $configObject->get('cfg_page_charset') ?>">
  
  <title>New Help Page</title>
  
  <link rel="stylesheet" type="text/css" href="../../css/body.css" />
  <style type="text/css">
    html {height:100%}
    body {font-size:85%; line-height:150%; color:#484848}
    p, div, td {text-align:justify}
    li {text-align:justify; list-style:square inside; color:#FF9900}
    td {font-size:85%}
    h1 {font-size:150%; color:black; font-family:Verdana,sans-serif}
    h2 {font-size:140%; color:#EEA752; font-family:Verdana,sans-serif}
    .subheading {font-weight:bold; font-style:italic}
  </style>
  
  <?php echo $configObject->get('cfg_js_root') ?>
  <script type="text/javascript" src="../../tools/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
  <script type="text/javascript" src="../../tools/tinymce/jscripts/tiny_mce/tiny_config_help_staff.js"></script>
  <script type="text/javascript" src="../../js/jquery-1.6.1.min.js"></script>
  <script language="JavaScript">
    $(document).ready(function() {
		  var docHeight = $(document).height();
			docHeight = docHeight - 100;
		  $('#edit1').css('height', docHeight + 'px');
		});
  </script>
</head>

<body>

<form name="add_form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
  <table cellpadding="0" cellspacing="0" border="0" style="width:100%">
  <tr>
  <td style="padding-left:20px"><input type="text" style="font-family:Verdana,sans-serif; color:#295AAD; font-size:160%; border:1px solid #C0C0C0; font-weight:bold" size="50" name="title" value="" placeholder="<?php echo $string['pagetitle']; ?>" required /></td>
  <td style="text-align:right"><select name="page_roles"><option value="Staff">Staff</option><option value="Admin">Admin</option><option value="SysAdmin">SysAdmin</option></select></td>
  </tr>
  </table>
  <br />

  <textarea class="mceEditor" id="edit1" name="edit1" style="width:100%; height:500px"></textarea>

  <div style="text-align:center; padding-top:8px""><input style="width:120px" type="submit" name="save_changes" value="<?php echo $string['save']; ?>" />&nbsp;&nbsp;<input style="width:120px" type="button" name="cancel" value="<?php echo $string['cancel']; ?>" onclick="history.back();" /></div>
</form>
</body>
</html>
<?php
  }
?>