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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require_once '../classes/searchutils.class.php';

if (isset($_POST['submit'])) {
  // Write the reference material
  $result = $mysqli->prepare("INSERT INTO reference_material VALUES (NULL, ?, ?, NOW(), NULL)");
  $result->bind_param('ss', $_POST['title'], $_POST['ref_content']);
  $result->execute();
  
  $refID = $mysqli->insert_id;
  
  // Add it to the modules
  for ($i=0; $i<$_POST['module_no']; $i++) {
    if (isset($_POST['module' . $i])) {
      $result = $mysqli->prepare("INSERT INTO reference_modules VALUES (NULL, ?, ?)");
      $result->bind_param('ii', $refID, $_POST['module' . $i]);
      $result->execute();
    }
  }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
<title>New Reference Material</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<link rel="stylesheet" type="text/css" href="../css/header.css" />
<style type="text/css">
body {font-size:100%; font-family:Arial,sans-serif; margin:0px}
table {font-size:100%}
input, textarea {font-family:Arial,sans-serif; line-height:140%}
.r1 {text-indent:-23px; padding-left:23px; background-color:white}
.r2 {text-indent:-23px; padding-left:23px; background-color:#B3C8E8}
</style>
<?php echo $cfg_js_root ?>
<script type="text/javascript" src="../tools/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript" src="../tools/tinymce/jscripts/tiny_mce/tiny_config.js"></script>
<script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
<script language="JavaScript">
  function toggle(objectID) {
    if (document.getElementById(objectID).className == 'r2') {
      document.getElementById(objectID).className = 'r1';
    } else {
      document.getElementById(objectID).className = 'r2';
    }
  }
</script>
</head>

<body>

<table class="header" cellspacing="0" cellpadding="0" border="0" style="font-size:80%">
<tr><th>
  <div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="/admin/">Admin</a></div>
  <div style="font-size:220%; font-weight:bold; margin-left:10px">Reference Material</div>
</th></tr>
<tr><th class="bevel"></th></tr>
</table>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" charset="UTF-8">
<br />
<table border="0" style="text-align:left; margin-left:auto; margin-right:auto; font-size:80%">
<tr><td>Name <input type="text" name="title" size="40" /></td><td>Modules</td></tr>
<tr><td><textarea name="ref_content" id="ref_content" rows="40" cols="100" style="height:600px" class="mceEditor"></textarea></td><td style="vertical-align:top">
<?php
  echo "<div style=\"margin-top:1px; display:block; width:400px; height:604px; overflow-y:scroll; border:1px solid #7F9DB9; font-size:90%\">";
  $modules_array = array();
  $total_modules = array_merge($teams, $modules_array);
    
  $module_array = SearchUtils::getTeams($teams, $userroles, $userID, $mysqli);
  $module_no = 0;
  $old_school = '';
  foreach ($module_array as $module) {
    if ($module['school'] != $old_school) {
      echo "<div style=\"padding-top:2px\"><strong>" . $module['school'] . "</strong></div>";
    }
    $match = false;
    foreach ($modules_array as $separate_module) {
      if ($separate_module == $module['id']) $match = true;
    }
    if ($match == true) {
      if (in_array($module['id'],$teams) or strpos($userroles,'SysAdmin') !== false) {
        echo "<div class=\"r2\" id=\"divmod$module_no\"><input type=\"checkbox\" onclick=\"toggle('divmod$module_no');\" name=\"module$module_no\" id=\"module$module_no\" value=\"" . $module['recordid'] . "\" checked>&nbsp;" . $module['id'] . ": " . substr($module['fullname'],0,60) . "</div>\n";
      } else {
        echo "<div class=\"r2\" id=\"divmod$module_no\"><input type=\"checkbox\" name=\"dummymod$module_no\" value=\"" . $module['id'] . "\" checked disabled><input type=\"checkbox\" name=\"module$module_no\" id=\"module$module_no\" style=\"display:none\" value=\"" . $module['recordid'] . "\" checked>&nbsp;" . $module['id'] . ": " . substr($module['fullname'],0,60) . "</div>\n";
      }
    } else {
      echo "<div class=\"r1\" id=\"divmod$module_no\"><input type=\"checkbox\" onclick=\"toggle('divmod$module_no');\" name=\"module$module_no\" id=\"module$module_no\" value=\"" . $module['recordid'] . "\">&nbsp;" . $module['id'] . ": " . substr($module['fullname'],0,60) . "</div>\n";
    }
    $module_no++;  
    $old_school = $module['school'];        
  }
  echo "<input type=\"hidden\" name=\"module_no\" id=\"module_no\" value=\"$module_no\" /></div>\n";
?>
</td>
</tr>
</table>
<div style="text-align:center"><input type="submit" name="submit" value="OK" style="width:100px" />&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" style="width:100px" /></div>

</form>

</body>
</html>
