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
* The results screen of a search for a user(s).
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require_once '../include/demo_replace.inc';
require_once '../classes/paperproperties.class.php';

if ($userObject->has_role('Demo')) {
  $demo = true;
} else {
  $demo = false;
}
$sortby = 'surname';
$ordering = 'asc';
$moduleID = '%';
$calendar_year =  (isset($_GET['calendar_year']) and $_GET['calendar_year'] != '') ? $_GET['calendar_year'] : '%';
$tmp_moduleid = '';

if ($calendar_year == '%') {
  $calendar_year_sql = '';
} else {
  $calendar_year_sql = " AND calendar_year = '$calendar_year'";
}

if (isset($_GET['paperID'])) {
  if (isset($_GET['sortby'])) {
		$sortby = $_GET['sortby'];
  } else {
	  $sortby = 'moduleid';
	}
	if (isset($_GET['ordering'])) {
		$ordering = $_GET['ordering'];
	} else {
	  $ordering = 'asc';
	}
	$tmp_sortby = $sortby;
	if ($tmp_sortby == 'moduleid') $tmp_sortby .= ', surname, first_names';

	$needs_array = array();
  $result = $mysqli->prepare("SELECT userID FROM special_needs");
  $result->execute();
  $result->bind_result($tmp_userID);
  while ($result->fetch()) {
    $needs_array[$tmp_userID] = '1';
  }
  $result->close();

  // Get the year and modules from the paper properties.
	$properties = PaperProperties::get_paper_properties_by_id($_GET['paperID'], $mysqli, $string);
	$paper_modules = $properties->get_modules();
	$paper_calendar_year = $properties->get_calendar_year();

  $module_list = implode(',', array_keys($paper_modules));
  $roles_sql = "AND roles LIKE '%Student' AND grade != 'left'";

  $query_string = "SELECT DISTINCT users.id, roles, student_id, surname, initials, first_names, title, users.username, grade, yearofstudy, email, m.moduleid FROM (users, modules_student) LEFT JOIN sid ON users.id = sid.userID INNER JOIN modules m ON modules_student.idMod=m.id WHERE users.id=modules_student.userID AND idMod IN ($module_list) AND calendar_year = '$paper_calendar_year' $roles_sql ORDER BY " . $tmp_sortby . " " . $ordering;
  $user_data = $mysqli->prepare($query_string);
  $user_data->execute();
  $user_data->bind_result($tmp_id, $tmp_roles, $tmp_student_id, $tmp_surname, $tmp_initials, $tmp_first_names, $tmp_title, $tmp_username, $tmp_grade, $tmp_yearofstudy, $tmp_email, $tmp_moduleid);
  $user_data->store_result();
  $user_no = number_format($user_data->num_rows);
} elseif (isset($_GET['team'])) {
  if (isset($_GET['sortby'])) $sortby = $_GET['sortby'];
  if (isset($_GET['ordering'])) $ordering = $_GET['ordering'];

  $needs_array = array();
  $result = $mysqli->prepare("SELECT userID FROM special_needs");
  $result->execute();
  $result->bind_result($tmp_userID);
  while ($result->fetch()) {
    $needs_array[$tmp_userID] = '1';
  }
  $result->close();

  // Sanitise module IDs from query string
  $tmp_modules = explode(',', $_GET['team']);
  $module_list_safe = '';
  foreach ($tmp_modules as $tmp_module) {
    if (ctype_digit($tmp_module)) {
      $module_list_safe .= $tmp_module . ',';
    }
  }

  if ($module_list_safe != '') {
		$tmp_sortby = $sortby;
		if ($tmp_sortby == 'moduleid') $tmp_sortby .= ', surname, first_names';

		$module_list_safe = rtrim($module_list_safe, ',');

    $roles_sql = "AND roles LIKE '%Student' AND grade != 'left'";

    $query_string = "SELECT DISTINCT users.id, roles, student_id, surname, initials, first_names, title, users.username, grade, yearofstudy, email, m.moduleid FROM (users, modules_student) LEFT JOIN sid ON users.id=sid.userID INNER JOIN modules m ON modules_student.idMod=m.id WHERE users.id=modules_student.userID AND idMod IN ($module_list_safe) $calendar_year_sql $roles_sql ORDER BY " . $tmp_sortby . " " . $ordering;
    $user_data = $mysqli->prepare($query_string);
    $user_data->execute();
    $user_data->bind_result($tmp_id, $tmp_roles, $tmp_student_id, $tmp_surname, $tmp_initials, $tmp_first_names, $tmp_title, $tmp_username, $tmp_grade, $tmp_yearofstudy, $tmp_email, $tmp_moduleid);
    $user_data->store_result();
    $user_no = number_format($user_data->num_rows);
  } else {
    $user_no = 0;
  }
} elseif (isset($_GET['submit'])) {
  $username_sql = '';
  $title_sql = '';
  $surname_sql = '';
  $initials_sql = '';
  $student_id_sql = '';
  $title = '';

  if (isset($_GET['sortby'])) $sortby = $_GET['sortby'];
  if (isset($_GET['ordering'])) $ordering = $_GET['ordering'];
  if (isset($_GET['module']) and $_GET['module'] != '') $moduleID = $_GET['module'];

  if (isset($_GET['search_surname']) and $_GET['search_surname'] != '') {
    $tmp_surname = str_replace("*","%",trim($_GET['search_surname']));

    $tmp_titles = explode(',', $string['title_types']);
    foreach ($tmp_titles as $tmp_title) {
      if (substr_count(strtolower($tmp_surname), strtolower($tmp_title . ' ')) > 0) $title_sql = " AND title='$tmp_title'";
      $tmp_surname = preg_replace("/(" . $tmp_title . " )/i","",$tmp_surname);
    }

    $sections = preg_split('[,.]',$tmp_surname);
    if (count($sections) > 1) {    // Search for initials.
      if (strlen($sections[0]) < strlen($sections[1])) {
        $initials_sql = " AND initials LIKE '" . trim($sections[0]) . "%'";
        $tmp_surname = trim($sections[1]);
      } else {
        $initials_sql = " AND initials LIKE '" . trim($sections[1]) . "%'";
        $tmp_surname = trim($sections[0]);
      }
    } else {
      $initials_sql = '';
    }
    $tmp_surname = str_replace('*','%',$tmp_surname);
    $surname_sql = " AND surname LIKE '$tmp_surname'";
  }
  if ($_GET['search_username'] != '') {
    $tmp_username = str_replace('*','%',trim($_GET['search_username']));
    $username_sql = " AND users.username LIKE '$tmp_username'";
  }

  if ($_GET['student_id'] != '') {
    $student_id_sql = " AND student_id ='" . trim($_GET['student_id']) . "'";
  }

  $roles_sql = '';
  if ((isset($_GET['students']) and $_GET['students'] != '') or (isset($_GET['student_id']) and $_GET['student_id'] != '') ) $roles_sql .= " OR roles LIKE '%Student'";
  if (isset($_GET['staff']) and $_GET['staff'] != '') $roles_sql .= " OR roles LIKE '%Staff%'";
  if (isset($_GET['adminstaff']) and $_GET['adminstaff'] != '') $roles_sql .= " OR roles LIKE '%,Admin%'";
  if (isset($_GET['sysadminstaff']) and $_GET['sysadminstaff'] != '') $roles_sql .= " OR roles LIKE '%,SysAdmin%'";
  if (isset($_GET['inactive']) and $_GET['inactive'] != '') $roles_sql .= " OR roles LIKE '%inactive%'";
  if (isset($_GET['externals']) and $_GET['externals'] != '') $roles_sql .= " OR (roles = 'External Examiner' AND grade != 'left')";
  if (isset($_GET['invigilators']) and $_GET['invigilators'] != '') $roles_sql .= " OR roles = 'Invigilator'";
  if (isset($_GET['graduates']) and $_GET['graduates'] != '') $roles_sql .= " OR roles = 'Graduate'";
  if (isset($_GET['leavers']) and $_GET['leavers'] != '') $roles_sql .= " OR roles = 'left'";
  if (isset($_GET['suspended']) and $_GET['suspended'] != '') $roles_sql .= " OR roles = 'suspended'";
  if ($roles_sql != '') $roles_sql = '(' . substr($roles_sql,4) . ')';
  if (isset($_GET['leavers']) and $_GET['leavers'] == '' and isset($_GET['staff']) and  $_GET['staff'] != '') $roles_sql .= " AND grade != 'left'";

  $needs_array = array();
  $result = $mysqli->prepare("SELECT userID FROM special_needs");
  $result->execute();
  $result->bind_result($tmp_userID);
  while ($result->fetch()) {
    $needs_array[$tmp_userID] = '1';
  }
  $result->close();

	$tmp_sortby = $sortby;
	if ($tmp_sortby == 'moduleid') $tmp_sortby .= ', surname, first_names';
	
  $user_no = 0;
  if ($roles_sql != '') {
    if ((isset($_GET['staff']) and $_GET['staff'] != '') or (isset($_GET['inactive']) and $_GET['inactive'] != '') or (isset($_GET['adminstaff']) and $_GET['adminstaff'] != '') or (isset($_GET['invigilators']) and $_GET['invigilators'] != '')) {
      if ($_GET['module'] != '') {
        $query_string = "SELECT DISTINCT users.id, roles, NULL AS student_id, surname, initials, first_names, title, users.username, grade, yearofstudy, email FROM users, modules_student, modules WHERE modules_student.idMod = modules.id AND users.id = modules_student.userID AND modules_student.idMod = '" . $_GET['module'] . "' AND $roles_sql$surname_sql$title_sql$username_sql$initials_sql$calendar_year_sql AND user_deleted IS NULL ORDER BY " . $tmp_sortby . " " . $ordering;
      } else {
        $query_string = "SELECT DISTINCT users.id, roles, NULL AS student_id, surname, initials, first_names, title, users.username, grade, yearofstudy, email FROM users WHERE $roles_sql$surname_sql$title_sql$username_sql$initials_sql AND user_deleted IS NULL ORDER BY " . $tmp_sortby . " " . $ordering;
      }
    } elseif (isset($_GET['externals']) and $_GET['externals'] != '') {
      $query_string = "SELECT DISTINCT users.id, roles, NULL AS student_id, surname, initials, first_names, title, users.username, grade, yearofstudy, email FROM users WHERE $roles_sql$surname_sql$title_sql$username_sql$initials_sql AND user_deleted IS NULL ORDER BY " . $tmp_sortby . " " . $ordering;
    } else {
      // Student search
      if ($moduleID == '%') {
        $query_string = "SELECT DISTINCT users.id, roles, student_id, surname, initials, first_names, title, users.username, grade, yearofstudy, email FROM users LEFT JOIN sid ON users.id = sid.userID WHERE $roles_sql$surname_sql$title_sql$username_sql$student_id_sql$initials_sql AND user_deleted IS NULL ORDER BY " . $tmp_sortby . " " . $ordering;
      } else {
        $roles_sql = 'AND ' . $roles_sql;
        if ($moduleID == '%') {
          $module_sql = '';
        } else {
          $module_sql = " AND idMod LIKE '{$moduleID}'";
        }
        $query_string = "SELECT DISTINCT users.id, roles, student_id, surname, initials, first_names, title, users.username, grade, yearofstudy, email FROM (users, modules_student) LEFT JOIN sid ON users.id = sid.userID WHERE users.id = modules_student.userID $module_sql$calendar_year_sql$roles_sql$surname_sql$title_sql$username_sql$student_id_sql$initials_sql AND user_deleted IS NULL ORDER BY " . $tmp_sortby . " " . $ordering;
      }
    }

    $user_data = $mysqli->prepare($query_string);
    $user_data->execute();
    $user_data->bind_result($tmp_id, $tmp_roles, $tmp_student_id, $tmp_surname, $tmp_initials, $tmp_first_names, $tmp_title, $tmp_username, $tmp_grade, $tmp_yearofstudy, $tmp_email);
    $user_data->store_result();
    $user_no = number_format($user_data->num_rows);
  }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title>Rog&#333;: <?php echo $string['usermanagement'] . ' ' . $configObject->get('cfg_install_type'); ?></title>
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/warnings.css" />
  <style type="text/css">
    a {color:black}
    .coltitle {cursor:hand; background-color:#F1F5FB; color:black}
    #usertable td {padding-left:6px}
    .fn {color:#A5A5A5}
    .uline:hover {background-color:#FFE7A2}
    .uline.highlight {background-color:#FFBD69}
  </style>

  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <script language="JavaScript">
    function addUserID(ID, clearall) {
      if (clearall) {
        $('#userID').val(',' + ID);
      } else {
        cur_value = $('#userID').val() + ',' + ID;
        $('#userID').val(cur_value);
      }
    }

    function subUserID(ID) {
      var tmpuserID = ',' + ID;
      new_value = $('#userID').val().replace(tmpuserID, '');
      $('#userID').val(new_value);
    }

    function clearAll() {
      $('.highlight').removeClass('highlight');
    }

    function selUser(userID, lineID, menuID, roles, evt) {
      $('#menu2a').hide();
      $('#menu' + menuID).show();

      if (evt.ctrlKey == false) {
        clearAll();
        $('#' + lineID).addClass('highlight');
        addUserID(userID, true);
      } else {
        if ($('#' + lineID).hasClass('highlight')) {
          $('#' + lineID).removeClass('highlight');
          subUserID(userID);
        } else {
          $('#' + lineID).addClass('highlight');
          addUserID(userID, false);
        }
      }
      $('#roles').val(roles);
      checkRoles();
    }

    function userOff() {
      $('#menu2a').show();
      $('#menu2b').hide();
      $('#menu2c').hide();

      clearAll();
    }

    function profile(userID) {
      document.location.href='details.php?search_surname=<?php if (isset($_GET['search_surname'])) echo $_GET['search_surname']; ?>&search_username=<?php if (isset($_GET['search_username'])) echo $_GET['search_username']; ?>&student_id=<?php if (isset($_GET['student_id'])) echo $_GET['student_id']; ?>&moduleID=<?php if (isset($_GET['team'])) echo $_GET['team']; if (isset($_GET['module'])) echo '&module=' . $_GET['module']; ?>&calendar_year=<?php if (isset($_GET['calendar_year'])) echo $_GET['calendar_year']; ?>&students=<?php if (isset($_GET['students'])) echo $_GET['students']; ?>&submit=Search&userID=' + userID + '&email=<?php if (isset($_GET['email'])) echo $_GET['email']; ?>&tmp_surname=<?php if (isset($_GET['tmp_surname'])) echo $_GET['tmp_surname']; ?>&tmp_courseID=<?php if (isset($_GET['tmp_courseID'])) echo $_GET['tmp_courseID']; ?>&tmp_yearID=<?php if (isset($_GET['tmp_yearID'])) echo $_GET['tmp_yearID']; ?>';
    }
  </script>
</head>

<?php
  require '../include/toprightmenu.inc';

	echo draw_toprightmenu(92);
	
  if (isset($_GET['submit']) or isset($_GET['paperID']) or isset($_GET['moduleID'])) {
    echo "<body>\n";

    include '../include/user_search_options.inc';

    echo "<div id=\"content\" class=\"content\">\n";
  } else {
    echo "<body style=\"margin:0px; background-color:white; color:black\">\n";

    include '../include/user_search_options.inc';

    echo "<div id=\"content\" class=\"content\">\n";
    echo "<table class=\"header\">\n";
    echo "<tr><th><div class=\"breadcrumb\"><a href=\"../staff/index.php\">" . $string['home'] . "</a></div><div onclick=\"qOff()\" style=\"font-size:200%; margin-left:10px\"><strong>" . $string['usersearch'] . "</div></th><th style=\"text-align:right; vertical-align:top\"><img src=\"../artwork/toprightmenu.gif\" id=\"toprightmenu_icon\"></th></tr>";
    echo "</table>\n</div>\n</body></html>\n";
    exit;
  }
?>

<form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>?sortby=<?php echo $sortby; ?>&order=<?php echo $ordering; ?>">
<table class="header" id="usertable">

<?php
echo "<tr><th style=\"padding-left:16px\" colspan=\"7\"><div class=\"breadcrumb\" style=\"margin-left:0px\"><a href=\"../staff/index.php\">" . $string['home'] . "</a></div><div onclick=\"qOff()\" style=\"font-size:200%\"><strong>Users ($user_no):&nbsp;</strong>";
if (isset($_GET['paperID'])) {
  echo implode(', ', array_values($paper_modules)) . ' (' . $paper_calendar_year . ')';
} elseif (isset($_GET['search_surname']) and $_GET['search_surname'] != '') {
  echo $_GET['search_surname'];
} elseif (isset($_GET['module']) and $_GET['module'] != '%') {
  echo module_utils::get_moduleid_from_id($_GET['module'], $mysqli);
  if (isset($_GET['calendar_year']) and $_GET['calendar_year'] != '' and isset($_GET['students']) and $_GET['students'] != '') {
    echo ' (' . $_GET['calendar_year'] . ')';
  }
} elseif (isset($_GET['search_username']) and $_GET['search_username'] != '') {
  echo $_GET['search_username'];
} elseif (isset($_GET['student_id']) and $_GET['student_id'] != '') {
  echo $_GET['student_id'];
} elseif (isset($_GET['calendar_year']) and $_GET['calendar_year'] != '%') {
  echo $_GET['calendar_year'];
}
echo "</div></th><th style=\"text-align:right; vertical-align:top\"><img src=\"../artwork/toprightmenu.gif\" id=\"toprightmenu_icon\"></th></tr>\n";

if ($ordering == 'asc') {
  $new_order = 'desc';
} else {
  $new_order = 'asc';
}

if (isset($_GET['search_surname'])) {
  $tmp_surname = $_GET['search_surname'];
} else {
  $tmp_surname = '';
}

if (isset($_GET['search_username'])) {
  $tmp_username = $_GET['search_username'];
} else {
  $tmp_username = '';
}

if (isset($_GET['students'])) {
  $tmp_students = $_GET['students'];
} else {
  $tmp_students = '';
}

if (isset($_GET['staff'])) {
  $tmp_staff = $_GET['staff'];
} else {
  $tmp_staff = '';
}

if (isset($_GET['student_id'])) {
  $tmp_student_id = $_GET['student_id'];
} else {
  $tmp_student_id = '';
}

if (isset($_GET['module'])) {
  $tmp_module = $_GET['module'];
} else {
  $tmp_module = '';
}

if (isset($_GET['paperID'])) {
  $additional_param = '&paperID=' . $_GET['paperID'];
} else {
  $additional_param = '&module=' . $tmp_module . '&search_surname=' . $tmp_surname . '&search_username=' . $tmp_username . '&student_id=' . $tmp_student_id . '&moduleID=' . $moduleID . '&calendar_year=' . $calendar_year . '&submit=Search&userID=';
}
$user_types = array('students', 'graduates', 'leavers', 'suspended', 'staff', 'adminstaff', 'inactive', 'externals', 'invigilators');
foreach ($user_types as $user_type) {
  if (isset($_GET[$user_type])) {
    $additional_param .= '&' .  $user_type . '=' . $_GET[$user_type];
  }
}

if (isset($_GET['paperID'])) {
  $string['course'] = $string['module'];   // Override course with module if called from a paper.
}

$table_order = array('#1'=>'', $string['title']=>'title', '#2'=>'', $string['name']=>'surname', $string['username']=>'username', $string['studentid']=>'student_id', $string['year']=>'yearofstudy', $string['course']=>'grade');
echo "<tr>\n";
foreach ($table_order as $display => $key) {
	if ($key == '') {
		echo "<th>&nbsp;";
	} else {
		echo "<th class=\"vert_div\">";
		if ($sortby == $key and $ordering == 'asc') {
			echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?sortby=$key&ordering=desc$additional_param\">$display</a><img src=\"../artwork/desc.gif\" width=\"9\" height=\"7\" style=\"padding-left:5px\" />";
		} elseif ($sortby == $key and $ordering == 'desc') {
			echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?sortby=$key&ordering=asc$additional_param\">$display</a><img src=\"../artwork/asc.gif\" width=\"9\" height=\"7\" style=\"padding-left:5px\" />";
		} else {
			echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?sortby=$key&ordering=asc$additional_param\">$display</a>";
		}
	}
	echo "</th>";
  }
  echo "</tr>\n";
  if ($roles_sql == '') {
		echo "</table>" . $notice->info_strip($string['msg1'], 100) . "</div>\n</body>\n</html>\n";
    exit;
  }

  if ($user_data->num_rows == 0) {
		echo "</table>" . $notice->info_strip($string['msg2'], 100) . "</div>\n</body>\n</html>\n";
		exit;
  }

  $old_letter		= '';
  $old_title		= '';
  $old_username = '';
  $old_grade		= '';
  $old_year			= '';
	$old_moduleid = '';
  $x = 0;
	
  while ($user_data->fetch()) {
    if ($old_letter != mb_strtoupper(mb_substr($tmp_surname, 0, 1)) and $sortby == 'surname') {
      echo "<tr><td colspan=\"8\" style=\"padding-left:0px\"><table border=\"0\" class=\"subsect\" style=\"width:99%\"><tr><td>" . mb_strtoupper(mb_substr($tmp_surname,0,1)) . "</td><td style=\"width:99%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#CCCCCC; background-color:#CCCCCC; width:100%\" /></td></tr></table>\n</td></tr>\n";
    } elseif ($old_title != $tmp_title and $sortby == 'title') {
      echo "<tr><td colspan=\"8\" style=\"padding-left:0px\"><table border=\"0\" class=\"subsect\" style=\"width:99%\"><tr><td>" . $string[mb_strtolower($tmp_title)] . "</td><td style=\"width:99%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#CCCCCC; background-color:#CCCCCC; width:100%\" /></td></tr></table>\n</td></tr>\n";
    } elseif ($old_username != mb_substr($tmp_username, 0, 4) and $sortby == 'username') {
      echo "<tr><td colspan=\"8\" style=\"padding-left:0px\"><table border=\"0\" class=\"subsect\" style=\"width:99%\"><tr><td>" . mb_substr($tmp_username,0,4) . "</td><td style=\"width:99%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#CCCCCC; background-color:#CCCCCC; width:100%\" /></td></tr></table>\n</td></tr>\n";
    } elseif ($old_grade != $tmp_grade and $sortby == 'grade') {
      echo "<tr><td colspan=\"8\" style=\"padding-left:0px\"><table border=\"0\" class=\"subsect\" style=\"width:99%\"><tr><td>" . $tmp_grade . "</td><td style=\"width:99%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#CCCCCC; background-color:#CCCCCC; width:100%\" /></td></tr></table>\n</td></tr>\n";
    } elseif ($old_year != $tmp_yearofstudy and $sortby == 'yearofstudy') {
      echo "<tr><td colspan=\"8\" style=\"padding-left:0px\"><table border=\"0\" class=\"subsect\" style=\"width:99%\"><tr><td><nobr>Year " . $tmp_yearofstudy . "</nobr></td><td style=\"width:99%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#CCCCCC; background-color:#CCCCCC; width:100%\" /></td></tr></table>\n</td></tr>\n";
    } elseif ($old_moduleid != $tmp_moduleid and $sortby == 'moduleid') {
      echo "<tr><td colspan=\"8\" style=\"padding-left:0px\"><table border=\"0\" class=\"subsect\" style=\"width:99%\"><tr><td><nobr>" . $tmp_moduleid . "</nobr></td><td style=\"width:99%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#CCCCCC; background-color:#CCCCCC; width:100%\" /></td></tr></table>\n</td></tr>\n";
    }

    if ($userObject->has_role('SysAdmin')) {
      echo "<tr class=\"uline\" id=\"$x\" onclick=\"selUser('$tmp_id',$x,'2c','" . $tmp_roles . "',event); return false;\" ondblclick=\"profile('$tmp_id'); return false;\">";
      if (file_exists($cfg_web_root . 'users/photos/' . $tmp_username . '.jpg')) {
        echo '<td><img src="../artwork/photo.png" width="16" height="16" alt="Photo" /></td>';
      } else {
        echo '<td></td>';
      }
      if (array_key_exists($tmp_id, $needs_array)) {
        echo "<td>" . $string[mb_strtolower($tmp_title)] . "</td><td style=\"width:20px\"><img src=\"../artwork/accessibility_16.png\" width=\"16\" height=\"16\" /></td><td>$tmp_surname, ";
        if ($tmp_first_names != '') {
          echo '<span class="fn">' . $tmp_first_names . '</span>';
        } else {
          echo $tmp_initials;
        }
        echo  "</td><td>$tmp_username</td>";
      } else {
        if (isset($tmp_title) and $tmp_title != '') {
          $tmp_title = $tmp_title;
        } else {
          $tmp_title = '';
        }
        echo "<td>$tmp_title</td><td></td><td>$tmp_surname, ";
        if ($tmp_first_names != '') {
          echo '<span class="fn">' . $tmp_first_names . '</span>';
        } else {
          echo $tmp_initials;
        }
        echo "</td><td>$tmp_username</td>";
      }
    } else {
      echo "<tr class=\"uline\" id=\"$x\" onclick=\"selUser('$tmp_id',$x,'2b','" . $tmp_roles . "',event); return false;\" ondblclick=\"profile('$tmp_id'); return false;\">";
      if (file_exists($cfg_web_root . '/users/photos/' . $tmp_username . '.jpg')) {
        echo '<td><img src="../artwork/photo.png" width="16" height="16" alt="Photo" /></td>';
      } else {
        echo '<td></td>';
      }
      if (array_key_exists($tmp_id, $needs_array)) {
        echo "<td>&nbsp;" . $tmp_title . "</td><td style=\"width:20px\"><img src=\"../artwork/accessibility_16.png\" width=\"16\" height=\"16\" /></td><td>$tmp_surname, ";
        if ($tmp_first_names != '') {
          echo '<span class="fn">' . $tmp_first_names . '</span>';
        } else {
          echo $tmp_initials;
        }
        echo '</a></td><td>' . demo_replace($tmp_username, $demo, false) . '</td>';
      } else {
        echo "<td>&nbsp;$tmp_title</td><td></td><td>$tmp_surname, ";
        if ($tmp_first_names != '') {
          echo '<span class="fn">' . $tmp_first_names . '</span>';
        } else {
          echo $tmp_initials;
        }
        echo '</a></td><td>' . demo_replace($tmp_username, $demo, false) . '</td>';
      }
    }
    if ($tmp_roles == 'Student') {
      if ($tmp_student_id == NULL) {
        echo '<td class="fn">' . $string['unknown'] . '</td>';
      } else {
        echo '<td>' . demo_replace_number($tmp_student_id, $demo) . '</td>';
      }
    } else {
      echo "<td class=\"fn\">" . $string['na'] . "</td>";
    }
    echo "<td>$tmp_yearofstudy</td><td>&nbsp;";
    if (isset($_GET['paperID'])) {
      echo $tmp_moduleid;
    } else {
      echo $tmp_grade;
    }
    echo "</td></tr>\n";
		
    $old_letter		= mb_strtoupper(mb_substr($tmp_surname, 0, 1));
    $old_title		= $tmp_title;
    $old_username = mb_substr($tmp_username, 0, 4);
    $old_grade		= $tmp_grade;
    $old_year			= $tmp_yearofstudy;
		$old_moduleid = $tmp_moduleid;
    $x++;
  }
	
  $user_data->close();
  $mysqli->close();
?>
</table>
</div>

</body>
</html>