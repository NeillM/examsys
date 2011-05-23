<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require '../include/staff_auth.inc';
  $mysqli->close();
?>
<!DOCTYPE html
   PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>TouchStone: Qualitative Analysis</title>
<style type="text/css">
body {font-family:Arial,sans-serif; font-size:90%; color:black; margin-top:0px; margin-left:0px; margin-right:0px}
h1 {margin-left:15px; font-family:Arial,sans-serif; font-size:18pt; color:#3A70A4}
.heading {background-color:#EBEADB; color:black; font-family:Arial,sans-serif}
</style>
</head>

<body>
<?php
  echo "<form name=\"analyse\" method=\"get\" action=\"qualitative_results.php\" target=\"results\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";
  echo "<tr><td class=\"heading\"><span style=\"font-size:200%; color:black; font-weight:bold\">Qualitative Analysis</span></td>";
  echo "<td class=\"heading\" valign=\"top\" width=\"250\"><input type=\"text\" name=\"keywords\" size=\"20\" value=\"";
  if (isset($_GET['keywords'])) echo $_GET['keywords']; 
  echo "\" /><input type=\"submit\" name=\"submit\" value=\"Highlight\" />";
  if (isset($_GET['collapse']) and $_GET['collapse'] == '1') {
    echo "<br /><input type=\"checkbox\" name=\"collapse\" value=\"1\" checked />&nbsp;Collapse";
  } else {
    echo "<br /><input type=\"checkbox\" name=\"collapse\" value=\"1\" />&nbsp;Collapse";
  }
  echo '&nbsp;&nbsp;&nbsp;&nbsp;';
  if (isset($_GET['casesensitive']) and $_GET['casesensitive'] == '1') {
    echo "<br /><input type=\"checkbox\" name=\"casesensitive\" value=\"1\" checked />&nbsp;Case-sensitive";
  } else {
    echo "<br /><input type=\"checkbox\" name=\"casesensitive\" value=\"1\" />&nbsp;Case-sensitive";
  }
  echo '<input type="hidden" name="paperID" value="' . $_GET['paperID'] . '" />';
  echo '<input type="hidden" name="startdate" value="' . $_GET['startdate'] . '" />';
  echo '<input type="hidden" name="enddate" value="' . $_GET['enddate'] . '" />';
  echo '<input type="hidden" name="module" value="' . $_GET['module'] . '" />';
  echo '<input type="hidden" name="repdegree" value="' . $_GET['repdegree'] . '" />';
  echo "</td></tr>";
  echo "<tr><td colspan=\"2\" style=\"height: 3px\"><img src=\"../artwork/header_horizontal_line.gif\" width=\"100%\" height=\"3\" alt=\"Line\" /></td></tr>\n";
?>
</table>
</form>
</body>
</html>