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
require_once '../include/errors.inc';

function get_special_needs($db) {
  $needs_array = array();
  $result = $db->prepare("SELECT userID FROM special_needs");
  $result->execute();
  $result->bind_result($tmp_userID);
  while ($result->fetch()) {
    $needs_array[$tmp_userID] = '1';
  }
  $result->close();
  
  return $needs_array;
}

if ($userObject->has_role('Demo')) {
  $demo = true;
} else {
  $demo = false;
}
$sortby = 'surname';
$ordering = 'asc';
$moduleID = check_var('module', $_GET, false, true, true);
$calendar_year = check_var('calendar_year', $_GET, false, true, true);

if (is_null($calendar_year) or $calendar_year === '%') {
  $calendar_year_sql = '';
  $calendar_year_param_types = '';
  $calendar_year_params = array();
} else {
  $calendar_year_sql = " AND calendar_year = ?";
  $calendar_year_param_types = 'i';
  $calendar_year_params = array($calendar_year);
}

$needs_array = get_special_needs($mysqli);

// We should only display the first 10,000 rows to avoid browser issues.
$limit = 10000;

if (isset($_GET['submit'])) {
  $username_sql = '';
  $username_param_types = '';
  $username_params = array();
  $title_sql = '';
  $title_param_types = '';
  $title_params = array();
  $surname_sql = '';
  $surname_param_types = '';
  $surname_params = array();
  $initials_sql = '';
  $initials_param_types = '';
  $initials_params = array();
  $student_id_sql = '';
  $student_id_param_types = '';
  $student_id_params = array();
  $param_types = '';
  $params = array();

  $tmp_surname = check_var('search_surname', $_GET, false, true, true);
  if (!is_null($tmp_surname)) {
    $tmp_surname = str_replace("*", "%", trim($tmp_surname));

    $tmp_titles = explode(',', $string['title_types']);
    foreach ($tmp_titles as $tmp_title) {
      if (substr_count(strtolower($tmp_surname), strtolower($tmp_title . ' ')) > 0) {
        $title_sql = " AND title=?";
        $title_param_types = 's';
        $title_params = array($tmp_title);
      }
      $tmp_surname = preg_replace("/(" . $tmp_title . " )/i","",$tmp_surname);
    }

    $sections = preg_split('[,.]',$tmp_surname);
    if (count($sections) > 1) {    // Search for initials.
      if (strlen($sections[0]) < strlen($sections[1])) {
        $tmp_initials = $mysqli->real_escape_string(trim($sections[0]));
        $tmp_surname = trim($sections[1]);
      } else {
        $tmp_initials = $mysqli->real_escape_string(trim($sections[1]));
        $tmp_surname = trim($sections[0]);
      }
      $initials_sql = " AND initials LIKE ?";
      $initials_param_types = 's';
      $initials_params = array($tmp_initials . '%');
    }
    $tmp_surname = $mysqli->real_escape_string(str_replace('*', '%', $tmp_surname));
    $surname_sql = " AND surname LIKE ?";
    $surname_param_types = 's';
    $surname_params = array($tmp_surname);
  }

  $tmp_username = check_var('search_username', $_GET, false, true, true);
  if (!is_null($tmp_username) and $tmp_username !== '') {
    $tmp_username = $mysqli->real_escape_string(str_replace('*', '%', trim($tmp_username)));
    $username_sql = " AND users.username LIKE ?";
    $username_param_types = 's';
    $username_params[] = $tmp_username;
  }

  $tmp_studentid = check_var('student_id', $_GET, false, true, true);
  if (!is_null($tmp_studentid) and $tmp_studentid !== '') {
    $tmp_studentid = $mysqli->real_escape_string(trim($_GET['student_id']));
    $student_id_sql = " AND student_id = ?";
    $student_id_param_types = 'i';
    $student_id_params[] = $tmp_studentid;
  }

  $roles_sql = '';
  if ((isset($_GET['students']) and $_GET['students'] != '') or (isset($_GET['student_id']) and $_GET['student_id'] != '') ) $roles_sql .= " OR roles LIKE '%Student'";
  if (isset($_GET['staff']) and $_GET['staff'] != '') $roles_sql .= " OR roles LIKE '%Staff%'";
  if (isset($_GET['adminstaff']) and $_GET['adminstaff'] != '') $roles_sql .= " OR roles LIKE '%,Admin%'";
  if (isset($_GET['sysadminstaff']) and $_GET['sysadminstaff'] != '') $roles_sql .= " OR roles LIKE '%,SysAdmin%'";
  if (isset($_GET['standardsstaff']) and $_GET['standardsstaff'] != '') $roles_sql .= " OR roles LIKE '%,Standards Setter%'";
  if (isset($_GET['inactive']) and $_GET['inactive'] != '') $roles_sql .= " OR roles LIKE '%inactive%'";
  if (isset($_GET['externals']) and $_GET['externals'] != '') $roles_sql .= " OR (roles = 'External Examiner' AND grade != 'left')";
  if (isset($_GET['internals']) and $_GET['internals'] != '') $roles_sql .= " OR (roles = 'Internal Reviewer' AND grade != 'left')";
  if (isset($_GET['invigilators']) and $_GET['invigilators'] != '') $roles_sql .= " OR roles = 'Invigilator'";
  if (isset($_GET['graduates']) and $_GET['graduates'] != '') $roles_sql .= " OR roles = 'Graduate'";
  if (isset($_GET['leavers']) and $_GET['leavers'] != '') $roles_sql .= " OR roles = 'left'";
  if (isset($_GET['suspended']) and $_GET['suspended'] != '') $roles_sql .= " OR roles = 'suspended'";
  if (isset($_GET['locked']) and $_GET['locked'] != '') $roles_sql .= " OR roles = 'locked'";
  if ($roles_sql != '') $roles_sql = '(' . substr($roles_sql,4) . ')';
  if (isset($_GET['leavers']) and $_GET['leavers'] == '' and isset($_GET['staff']) and  $_GET['staff'] != '') $roles_sql .= " AND grade != 'left'";

	$user_no = 0;
  if ($roles_sql != '') {
    $get_staff = (isset($_GET['staff']) and $_GET['staff'] != '');
    $get_inactive = (isset($_GET['inactive']) and $_GET['inactive'] != '');
    $get_sysadmin = (isset($_GET['sysadminstaff']) and $_GET['sysadminstaff'] != '');
    $get_admin = (isset($_GET['adminstaff']) and $_GET['adminstaff'] != '');
    $get_invigilators = (isset($_GET['invigilators']) and $_GET['invigilators'] != '');
    $get_standardstaff = (isset($_GET['standardsstaff']) and $_GET['standardsstaff'] != '');
    $seach_for_staff = ($get_staff or $get_inactive or $get_sysadmin or $get_admin or $get_invigilators or $get_standardstaff);

    $get_external = (isset($_GET['externals']) and $_GET['externals'] != '');
    $get_internal = (isset($_GET['internals']) and $_GET['internals'] != '');
    $search_for_reviewers = ($get_external or $get_internal);

    if ($seach_for_staff and !is_null($moduleID)) {
      $query_string = "(SELECT DISTINCT users.id, roles, student_id, surname, initials, first_names, title, users.username, grade, yearofstudy, email
      FROM (users, modules_student, modules)
      LEFT JOIN sid ON users.id = sid.userID
      WHERE modules_student.idMod = modules.id
      AND users.id = modules_student.userID
      AND modules_student.idMod = ?
      AND $roles_sql$surname_sql$title_sql$username_sql$initials_sql$calendar_year_sql
      AND user_deleted IS NULL)
      UNION
      (SELECT DISTINCT users.id, roles, student_id, surname, initials, first_names, title, users.username, grade, yearofstudy, email
      FROM (users, modules_staff, modules)
      LEFT JOIN sid ON users.id = sid.userID
      WHERE modules_staff.idMod = modules.id
      AND users.id = modules_staff.memberID
      AND modules_staff.idMod = ?
      AND $roles_sql$surname_sql$title_sql$username_sql$initials_sql
      AND user_deleted IS NULL LIMIT $limit)";
      $sql_params = array($moduleID);
      $param_types = 's' . $surname_param_types . $title_param_types . $username_param_types . $initials_param_types .
          $calendar_year_param_types . 's' . $surname_param_types . $title_param_types . $username_param_types .
          $initials_param_types;
      $params = array_merge($sql_params, $surname_params, $title_params, $username_params, $initials_params,
          $calendar_year_params, $sql_params, $surname_params, $title_params, $username_params, $initials_params);
    } elseif ($seach_for_staff or $search_for_reviewers) {
      $query_string = "SELECT DISTINCT users.id, roles, student_id, surname, initials, first_names, title, users.username, grade, yearofstudy, email
        FROM users
        LEFT JOIN sid ON users.id = sid.userID
        WHERE $roles_sql$surname_sql$title_sql$username_sql$initials_sql
        AND user_deleted IS NULL LIMIT $limit";
      $param_types = $surname_param_types . $title_param_types . $username_param_types . $initials_param_types;
      $params = array_merge($surname_params, $title_params, $username_params, $initials_params);
    } elseif (is_null($moduleID)) {
      // Students no module link.
      $query_string = "SELECT DISTINCT users.id, roles, student_id, surname, initials, first_names, title, users.username, grade, yearofstudy, email
        FROM users
        LEFT JOIN sid ON users.id = sid.userID
        WHERE $roles_sql$surname_sql$title_sql$username_sql$student_id_sql$initials_sql
        AND user_deleted IS NULL LIMIT $limit";
      $param_types = $surname_param_types . $title_param_types . $username_param_types . $student_id_param_types .
          $initials_param_types;
      $params = array_merge($surname_params, $title_params, $username_params, $student_id_params, $initials_params);
    } else {
      // Students on a particular module.
      $roles_sql = ' AND ' . $roles_sql;
      $module_sql = " AND idMod LIKE ? ";
      $module_params = array($moduleID);
      $query_string = "SELECT DISTINCT users.id, roles, student_id, surname, initials, first_names, title, users.username, grade, yearofstudy, email
        FROM (users, modules_student)
        LEFT JOIN sid ON users.id = sid.userID
        WHERE users.id = modules_student.userID $module_sql$calendar_year_sql$roles_sql$surname_sql$title_sql$username_sql$student_id_sql$initials_sql
        AND user_deleted IS NULL LIMIT $limit";
      $param_types = 's' . $calendar_year_param_types . $surname_param_types . $title_param_types . $username_param_types .
          $student_id_param_types . $initials_param_types;
      $params = array_merge($module_params, $calendar_year_params, $surname_params, $title_params, $username_params,
          $student_id_params, $initials_params);
    }

    // Create an array of references to the parameter values.
    $ref_params = array();
    foreach ($params as &$param) {
      $ref_params[] = &$param;
    }

    $user_data = $mysqli->prepare($query_string);
    if (count($params) > 0) {
      // Only call if the query has parameters.
      call_user_func_array(array($user_data, "bind_param"), array_merge(array($param_types), $ref_params));
    }
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
  <link rel="stylesheet" type="text/css" href="../css/list.css" />
  <link rel="stylesheet" type="text/css" href="../css/warnings.css" />
  <style type="text/css">
    a {color:black}
    .coltitle {cursor:hand; background-color:#F1F5FB; color:black}
    #usertable td {padding-left:6px}
    .fn {color:#A5A5A5}
    .uline {line-height: 150%}
    .uline:hover {background-color:#FFE7A2}
    .uline.highlight {background-color:#FFBD69}
    td {padding-left: 0 !important}
    .l {line-height: 160%}
  </style>

  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery_tablesorter/jquery.tablesorter.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <script>
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

      if (evt.ctrlKey == false && evt.metaKey == false) {
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
      
      evt.stopPropagation();
    }

    function userOff() {
      $('#menu2a').show();
      $('#menu2b').hide();
      $('#menu2c').hide();

      clearAll();
    }

    function profile(userID) {
      document.location.href='details.php?search_surname=<?php if (isset($_GET['search_surname'])) echo $_GET['search_surname'] ?>&search_username=<?php if (isset($_GET['search_username'])) echo $_GET['search_username'] ?>&student_id=<?php if (isset($_GET['student_id'])) echo $_GET['student_id'] ?>&moduleID=<?php if (isset($_GET['team'])) echo $_GET['team']; if (isset($_GET['module'])) echo '&module=' . $_GET['module'] ?>&calendar_year=<?php if (isset($_GET['calendar_year'])) echo $_GET['calendar_year'] ?>&students=<?php if (isset($_GET['students'])) echo $_GET['students']; ?>&submit=Search&userID=' + userID + '&email=<?php if (isset($_GET['email'])) echo $_GET['email']; ?>&tmp_surname=<?php if (isset($_GET['tmp_surname'])) echo $_GET['tmp_surname']; ?>&tmp_courseID=<?php if (isset($_GET['tmp_courseID'])) echo $_GET['tmp_courseID']; ?>&tmp_yearID=<?php if (isset($_GET['tmp_yearID'])) echo $_GET['tmp_yearID']; ?>';
    }
    
    $(function () {
      if ($("#maindata").find("tr").size() > 1) {
        $("#maindata").tablesorter({ 
          // sort on the third column, order asc 
          sortList: [[3,0]] 
        });
      }

      $(document).click(function() {
        $('#menudiv').hide();
      });
    });
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
    echo "<body>\n";

    include '../include/user_search_options.inc';

    echo "<div id=\"content\" class=\"content\">\n";
    echo "<div class=\"head_title\">\n";
    echo "<div><img src=\"../artwork/toprightmenu.gif\" id=\"toprightmenu_icon\" /></div>";
    echo "<div class=\"breadcrumb\"><a href=\"../index.php\">" . $string['home'] . "</a>";
    if (isset($_REQUEST['module'])) {
      echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $_REQUEST['module'] . '">' . module_utils::get_moduleid_from_id($_REQUEST['module'], $mysqli) . '</a>';
    }
    echo "</div><div class=\"page_title\">" . $string['usersearch'] . "</div>";
    echo "</div>\n</div>\n</body></html>\n";
    exit();     // There is no search submit so just exit.
  }
?>

<form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>?sortby=<?php echo $sortby; ?>&order=<?php echo $ordering; ?>" autocomplete="off">

<div class="head_title">
<div style="float:right; vertical-align:top"><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
<?php
echo "<div class=\"breadcrumb\"><a href=\"../index.php\">" . $string['home'] . "</a>";
if (isset($_GET['module']) and $_GET['module'] != '') {
  echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $_GET['module'] . '">' . module_utils::get_moduleid_from_id($_GET['module'], $mysqli) . '</a>';
}
echo "</div><div class=\"page_title\">" . $string['usersearch'] . " ($user_no): <span style=\"font-weight: normal\">";
if (isset($_GET['paperID'])) {
  echo implode(', ', array_values($paper_modules)) . ' (' . $paper_calendar_year . ')';
} elseif (isset($_GET['search_surname']) and $_GET['search_surname'] != '') {
  echo "'" . $_GET['search_surname'] . "'";
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
echo "</span></div>\n";
echo "</div>\n";

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

if (isset($_GET['student_id'])) {
  $tmp_student_id = $_GET['student_id'];
} else {
  $tmp_student_id = '';
}

if ($roles_sql == '') {
  echo "<div>" . $notice->info_strip($string['msg1'], 100) . "</div>";
  exit();
}

if ($user_data->num_rows == $limit) {
  echo " <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"width:100%\"><tr><td class=\"redwarn\" style=\"width:40px; line-height:0; padding-left:0\"><img src=\"../artwork/exclamation_red_bg.png\" width=\"32\" height=\"32\" alt=\""
    . $string['warning'] . "\" /></td>" . "<td class=\"redwarn\">" . $string['largeresult'] . "</td></tr></table>";
}

$table_order = array('#1', '#2', $string['title'], 'Surname', 'First Names', $string['username'], $string['studentid'], $string['year'], $string['course']);
echo "<table id=\"maindata\" class=\"header tablesorter\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"width:100%\">\n";
echo "<thead>\n";
echo "<tr>\n";
foreach ($table_order as $display) {
  if ($display{0} == '#') {
    echo "<th>&nbsp;</th>";
  } else {
    echo "<th class=\"col\">$display</th>\n";
  }    
}
?>
</tr>
</thead>

<tbody>
<?php

if ($user_data->num_rows == 0) {
  echo "</table>" . $notice->info_strip($string['msg2'], 100) . "</div>\n</body>\n</html>\n";
  exit();
}

$x = 0;
$photodirectory = rogo_directory::get_directory('user_photo');
while ($user_data->fetch()) {
  if ($userObject->has_role('SysAdmin')) {
    echo "<tr class=\"l\" id=\"$x\" onclick=\"selUser('$tmp_id',$x,'2c','" . $tmp_roles . "',event); return false;\" ondblclick=\"profile('$tmp_id'); return false;\">";
  } else {
    echo "<tr class=\"l\" id=\"$x\" onclick=\"selUser('$tmp_id',$x,'2b','" . $tmp_roles . "',event); return false;\" ondblclick=\"profile('$tmp_id'); return false;\">";
  }
  $photoname = UserUtils::student_photo_exist($tmp_username);
  if ($photoname) {
    echo '<td><img src="../artwork/photo.png" width="16" height="16" alt="Photo" /></td>';
  } else {
    echo '<td></td>';
  }
  if (array_key_exists($tmp_id, $needs_array)) {
    echo '<td><img src="../artwork/accessibility_16.png" width="16" height="16" /></td>';
  } else {
    echo '<td></td>';
  }

  if ($tmp_title != null) {
    $lowertitle = mb_strtolower($tmp_title);
    if (array_key_exists($lowertitle, $string)) {
      echo '<td>' . $string[$lowertitle] . '</td>';
    } else {
      echo '<td></td>';
    }
  } else {
    echo '<td></td>';
  }
  
  if ($tmp_first_names == '') $tmp_first_names = ' ';
  if ($tmp_surname == '') $tmp_surname = ' ';
  echo '<td>' . demo_replace($tmp_surname, $demo, true, $tmp_surname{0}) . '</td>';
  echo '<td>' . demo_replace($tmp_first_names, $demo, true, $tmp_first_names{0}) . '</td>';
  echo '<td>' . demo_replace($tmp_username, $demo, false) . '</td>';
      
  if (strpos($tmp_roles, 'Student') !== false) {
    if ($tmp_student_id == NULL) {
      echo '<td class="fn">' . $string['unknown'] . '</td>';
    } else {
      echo '<td>' . demo_replace_number($tmp_student_id, $demo) . '</td>';
    }
  } elseif (strpos($tmp_roles, 'Staff') !== false) {
    echo "<td>Staff</td>";
  } else {
    echo "<td class=\"fn\">" . $string['na'] . "</td>";
  }
  echo "<td>$tmp_yearofstudy</td>";
  echo "<td>$tmp_grade</td></tr>\n";
  
  $x++;
}

$user_data->close();
$mysqli->close();
?>
</tbody>
</table>
</div>

</body>
</html>