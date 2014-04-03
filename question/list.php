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

require '../include/staff_auth.inc';
require_once '../include/sort.inc';
require_once '../lang/' . $language . '/include/question_types.inc';
require_once '../classes/stateutils.class.php';
require_once '../classes/moduleutils.class.php';
require_once '../classes/keywordutils.class.php';
require_once '../classes/question_status.class.php';

$state = $stateutil->getState($userObject->get_user_ID(), $mysqli);

$_SESSION['nav_page'] = $_SERVER['SCRIPT_NAME'];
$_SESSION['nav_query'] = $_SERVER['QUERY_STRING'];

// Get question statuses
$status_array = QuestionStatus::get_all_statuses($mysqli, $string, true);

$typeSQL    = '';
$bloomSQL   = '';
$statusSQL  = '';
$type       = '';

if (isset($_GET['type'])) {
  $type = $_GET['type'];
  if ($_GET['type'] != '%') {
    $typeSQL = " AND q_type = '" . $_GET['type'] . "'";
  }
}
if (isset($_GET['bloom'])) {
  if ($_GET['bloom'] != '%') {
    $bloomSQL = " AND bloom = '" . $_GET['bloom'] . "'";
  }
}
if (isset($_GET['status'])) {
  $statusSQL = " AND status = " . $_GET['status'];
}
if (isset($_GET['userid'])) {
  $userid = $_GET['userid'];
} else {
  $userid = '';
}
if (isset($_GET['keyword'])) {
  $keyword = $_GET['keyword'];
} else {
  $keyword = '';
}
if (isset($_GET['module'])) {
  $module = $_GET['module'];
  if ($module != '0') {
    $module_code = module_utils::get_moduleid_from_id($module, $mysqli);
    if (!$module_code) {
      $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
      $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
    }
  } else {
    $module_code = 'Unassigned';
  }
} else {
  $module = '';
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Rog&#333;: <?php echo $string['questionbank'] . ' ' . $configObject->get('cfg_install_type'); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style type="text/css">
    .d {padding-left:6px; padding-right:2px; padding-top:4px; padding-bottom:2px; vertical-align:top}
    .o {color:#A5A5A5}
    .q {line-height:150%;cursor:pointer;color:#000000;background-color:white; -webkit-user-select:none; -moz-user-select:none;}
    .q:hover {background-color:#FFE7A2}
    .q.highlight {background-color:#FFBD69}
    .nobr {white-space:nowrap}
    .plock {width:16px; height:16px}
<?php echo QuestionStatus::generate_status_css($status_array); ?>
  </style>

  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript" src="../tools/mee/mee/js/mee_src.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <script language="JavaScript">
    function myQuestions(thisObj) {
      var content = $(thisObj).is(':checked');
      <?php
      $qs = '';
      if (isset($_GET['type']))     $qs .= "&type={$_GET['type']}";
      if (isset($_GET['keyword']))  $qs .= "&keyword={$_GET['keyword']}";
      if (isset($_GET['module']))   $qs .= "&module={$_GET['module']}";
      ?>
      window.location = 'list.php?type=<?php echo $qs; ?>&checked=' + content;
    }
  </script>
</head>

<body onclick="hideMenus(event)" onselectstart="return false">
<?php
  require '../include/question_list_options.inc';
  require '../include/toprightmenu.inc';

	echo draw_toprightmenu();
?>

<div id="content" class="content" onclick="hideMenus(event)">
<table class="header">
<?php
  $question_no = 0;
  $display_no = 0;
  $bank_type = '';
  $module_sql = '';

  if ($keyword != '%' and $keyword != '') {
    $bank_type = ": '" . keyword_utils::name_from_ID($keyword, $mysqli) . "'";
 } elseif (isset($_GET['bloom'])) {
    $bank_type = ': ' . $_GET['type'];
  } elseif (isset($_GET['p'])) {
    $types = array('ve' => 'Very Easy', 'e' => 'Easy', 'm' => 'Moderate', 'h' => 'Hard', 'vh' => 'Very Hard');
    $bank_type = ': ' . $types[$_GET['p']];
  } elseif (isset($_GET['d'])) {
    $types = array('h1' => 'Highest', 'h2' => 'High', 'i'  => 'Intermediate', 'l'  => 'Low');
    $bank_type = ': ' . $types[$_GET['d']];
  } elseif ($module != '') {
    $bank_type = ": $module_code";
  } elseif ($_GET['type'] != '%') {
    $bank_type = ': ' . $string[$_GET['type']];
  }
 
  $staff_modules_sql = '';
  if ($module != '') {
    $module_sql = "idMod = " . $_GET['module'];
  } else {
    if (count($staff_modules) > 0) {
      $staff_modules_sql = implode(',', array_keys($staff_modules));
      $staff_modules_sql = " AND (idMod IN ($staff_modules_sql)";
      $staff_modules_sql .= " OR users.id=" . $userObject->get_user_ID() . ") ";
    } else {
      // Reset to just look for current owners paper if not on any teams.
      $staff_modules_sql .= "AND users.id=" . $userObject->get_user_ID() . " ";
    }
  }

  if ($module_sql != '') {
    $module_sql = 'AND (' . $module_sql .')';
  }

  if ($keyword != '%' and $keyword != '') {
    $keyword = ' AND keywordID=' . $keyword;
  } else {
    $keyword = '';
  }

  $display_no = 0;

  $retired_in = '-1,' . implode(',', QuestionStatus::get_retired_status_ids($status_array));
	
	$questions = array();
		
	if (isset($_GET['sortby'])) {
		$sortby = $_GET['sortby'];
	} else {
	  if (isset($state['sortby'])) {
			$sortby = $state['sortby'];
		} else {
			$sortby = 'leadin';
		}
	}
	
	if (isset($_GET['ordering'])) {
		$ordering = $_GET['ordering'];
	} else {
	  if (isset($state['ordering'])) {
			$ordering = $state['ordering'];
		} else {
			$ordering = 'asc';
		}
	}
	
	if ($sortby == 'modified') {
		$tmp_sortby = 'last_edited';
	} else {
		$tmp_sortby = $sortby;
	}
	if ($tmp_sortby == 'q_type' and isset($_GET['type'])) {
	  $tmp_sortby = 'leadin';
	}

  if ($_GET['module'] == '0') {
    $sql = "SELECT DISTINCT questions.q_id, ownerID, title, initials, surname, leadin_plain AS leadin, q_type, last_edited, DATE_FORMAT(last_edited,' {$configObject->get('cfg_short_date')}') AS modified, locked, status FROM (users, questions) LEFT JOIN questions_modules ON questions.q_id = questions_modules.q_id WHERE users.id = questions.ownerID AND ownerID = " . $userObject->get_user_ID() . " AND idMod IS NULL GROUP BY q_id";
  } elseif (isset($_GET['p'])) {
    $range = array('ve' => 'p >= 80', 'e'  => 'p >= 60 AND p < 80', 'm'  => 'p >= 40 AND p < 60', 'h'  => 'p >= 20 AND p < 40', 'vh' => 'p < 20');
    $sql = "SELECT DISTINCT questions.q_id, ownerID, title, initials, surname, leadin_plain AS leadin, q_type, last_edited, DATE_FORMAT(last_edited,' {$configObject->get('cfg_short_date')}') AS modified, locked, status FROM (users, questions, performance_main, performance_details, questions_modules) WHERE questions.q_id = performance_main.q_id AND users.id = questions.ownerID AND performance_main.id = performance_details.perform_id AND " . $range[$_GET['p']] . " AND questions.q_id = questions_modules.q_id AND idMod = $module";
  } elseif (isset($_GET['d'])) {
    $range = array('h1' => 'd >= 35', 'h2' => 'd >= 25 and d < 35', 'i'  => 'd >= 15 and d < 25', 'l'  => 'd < 15');
    $sql = "SELECT DISTINCT questions.q_id, ownerID, title, initials, surname, leadin_plain AS leadin, q_type, last_edited, DATE_FORMAT(last_edited,' {$configObject->get('cfg_short_date')}') AS modified, locked, status FROM (users, questions, performance_main, performance_details, questions_modules) WHERE questions.q_id = performance_main.q_id AND users.id = questions.ownerID AND performance_main.id = performance_details.perform_id AND " . $range[$_GET['d']] . " AND questions.q_id = questions_modules.q_id AND idMod = $module";
  } else {
    $sql = "SELECT DISTINCT questions.q_id, ownerID, title, initials, surname, leadin_plain AS leadin, q_type, last_edited, DATE_FORMAT(last_edited,' {$configObject->get('cfg_short_date')}') AS modified, locked, status FROM (users, questions, questions_modules)";
    if ($keyword != '%' and $keyword != '') {
      $sql .= " LEFT JOIN keywords_question ON questions.q_id = keywords_question.q_id";
    }
    $sql .= " WHERE questions.q_id = questions_modules.q_id AND users.id = questions.ownerID $module_sql $staff_modules_sql $typeSQL $bloomSQL $statusSQL $keyword AND status NOT IN ($retired_in) AND deleted IS NULL";
  }
  $sql .= ' ORDER BY ' . $tmp_sortby . ' ' . $ordering;
  
  $search_results = $mysqli->prepare($sql);
  $search_results->execute();
  $search_results->bind_result($q_id, $ownerID, $title, $initials, $surname, $leadin, $q_type, $last_edited, $modified, $locked, $status);
  $search_results->store_result();
  
  echo "<tr onclick=\"qOff();\"><th colspan=\"4\"><div class=\"breadcrumb\"><a href=\"../staff/index.php\">" . $string['home'] . "</a>";
  if (isset($_GET['module'])) {
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../module/index.php?module=' . $_GET['module'] . '">' . $module_code . '</a>';
    
    if (isset($_GET['type'])) {
      echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../question/bank.php?type=type&module=' . $_GET['module'] . '">Question Type</a>'; 
    } elseif (isset($_GET['bloom'])) {
      echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../question/bank.php?type=bloom&module=' . $_GET['module'] . '">Bloom\'s Taxonomy</a>'; 
    } elseif (isset($_GET['status'])) {
      echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../question/bank.php?type=status&module=' . $_GET['module'] . '">Status</a>'; 
    } elseif (isset($_GET['keyword'])) {
      echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../question/bank.php?type=keyword&module=' . $_GET['module'] . '">Keyword</a>'; 
    } elseif (isset($_GET['p'])) {
      echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../question/bank.php?type=difficulty&module=' . $_GET['module'] . '">Difficulty</a>'; 
    } elseif (isset($_GET['d'])) {
      echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../question/bank.php?type=discrimination&module=' . $_GET['module'] . '">Discrimination</a>'; 
    }
  }
  echo "</div><div style=\"font-size:200%; margin-left:10px\"><strong>" . $string['questionbank'] . "&nbsp;(<span id=\"q_count\">" . number_format($search_results->num_rows) . "</span>)</strong>$bank_type</div></th>";
  echo "<th colspan=\"2\" style=\"text-align:right; vertical-align:top\"><img src=\"../artwork/toprightmenu.gif\" id=\"toprightmenu_icon\"></th></tr>\n";

	$params = '';
	if (isset($_GET['type'])) $params .= '&type=' . $_GET['type'];
	if (isset($_GET['module'])) $params .= '&module=' . $_GET['module'];
	if (isset($_GET['keyword'])) $params .= '&keyword=' . $_GET['keyword'];
	
  $table_order = array(''=>'', $string['question']=>'leadin', $string['owner']=>'owner', $string['type']=>'q_type', $string['modified']=>'modified', $string['status']=>'status');
	echo "<tr style=\"font-size:110%\">\n";
	foreach ($table_order as $display => $key) {
		if ($key == '') {
			echo "<th>";
		} else {
			echo "<th class=\"vert_div\">";
		}
	
		if ($sortby == $key and $ordering == 'asc') {
			echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?sortby=$key&ordering=desc$params\">$display</a><img src=\"../artwork/desc.gif\" width=\"9\" height=\"7\" style=\"padding-left:5px\" /></th>";
		} elseif ($sortby == $key and $ordering == 'desc') {
			echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?sortby=$key&ordering=asc$params\">$display</a><img src=\"../artwork/asc.gif\" width=\"9\" height=\"7\" style=\"padding-left:5px\" /></th>";
		} else {
			echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?sortby=$key&ordering=asc$params\">$display</a></th>";
		}
	}
	echo "</tr>\n";

  while ($search_results->fetch()) {
    $status_class = ' status' . $status;
    echo '<tr class="q' . $status_class;
    if ($ownerID != $userObject->get_user_ID()) {
      echo ' notmyq';
    }
    if ($locked != '') {
      echo ' lockedq';
    }
    echo '"';
    if ($locked != '') {
      echo " id=\"link_$display_no\" onclick=\"selQ($q_id,$display_no,'$q_type','2c',event)\" ondblclick=\"editQ()\">";
      echo "<td><img src=\"../artwork/small_padlock.png\" class=\"plock\" alt=\"Padlock\" style=\"border:1px solid white\" /></td>";
    } else {
      echo " id=\"link_$display_no\" onclick=\"selQ($q_id,$display_no,'$q_type','2b',event)\" ondblclick=\"editQ()\">";
      echo "<td></td>";
    }

    if (trim($leadin) == '') $leadin = '<span style="color:#C00000">' . $string['noquestionleadin'] . '</span>';
    if ($userObject->has_role('Demo')) $owner = 'Dr J Bloggs';

    echo "<td class=\"d\">$leadin</td>";
    echo "<td class=\"d nobr\">" . $title . ' ' . $initials . ' ' . $surname . "</td>";
    echo "<td class=\"d nobr\">" . $string[$q_type] . "</td>";
    echo "<td class=\"d\">" . $modified . "</td>\n";
    echo "<td class=\"d\">" . $status_array[$status]->get_name() . "</td></tr>\n";
    $display_no++;
  }
	$search_results->close();

	if (isset($_GET['sortby'])) {
		$stateutil->setState($userObject->get_user_ID(), 'sortby', $_GET['sortby'], $_SERVER['PHP_SELF'], $mysqli);
	}
	if (isset($_GET['ordering'])) {
		$stateutil->setState($userObject->get_user_ID(), 'ordering', $_GET['ordering'], $_SERVER['PHP_SELF'], $mysqli);
	}
  $mysqli->close();
?>
</table>
</div>

</body>
</html>
