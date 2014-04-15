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
require_once '../classes/questionbank.class.php';

$state = $stateutil->getState($userObject->get_user_ID(), $mysqli);

$_SESSION['nav_page'] = $_SERVER['SCRIPT_NAME'];
$_SESSION['nav_query'] = $_SERVER['QUERY_STRING'];

// Get question statuses
$status_array = QuestionStatus::get_all_statuses($mysqli, $string, true);

$statusSQL  = '';
$type       = $_GET['type'];


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

$qbank = new QuestionBank($module, $string, $notice, $mysqli);
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
    .theme {font-weight:bold; color:#316ac5}
    .d {padding-left:6px; padding-right:2px; padding-top:4px; padding-bottom:2px; vertical-align:top}
    .o {color:#A5A5A5}
    .q {line-height:150%;cursor:pointer;color:#000000;background-color:white; -webkit-user-select:none; -moz-user-select:none; display:none}
    .q:hover {background-color:#FFE7A2}
    .q.highlight {background-color:#FFBD69}
    .nobr {white-space:nowrap}
    .plock {width:16px; height:16px; border:1px solid white}
    input[type=checkbox] {margin-right:8px}
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
  } elseif ($_GET['type'] == 'performance') {
    $types = array('veryeasy' => 'Very Easy', 'easy' => 'Easy', 'moderate' => 'Moderate', 'hard' => 'Hard', 'veryhard' => 'Very Hard', 'highest' => 'Highest', 'high' => 'High', 'intermediate'  => 'Intermediate', 'low'  => 'Low');
    $bank_type = ': ' . $types[$_GET['subtype']];
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
    $sql = "SELECT DISTINCT NULL AS extra_field, NULL AS p, NULL AS d, questions.q_id, theme, leadin_plain AS leadin, q_type, last_edited, DATE_FORMAT(last_edited,' {$configObject->get('cfg_short_date')}') AS modified, locked, status, bloom FROM (users, questions) LEFT JOIN questions_modules ON questions.q_id = questions_modules.q_id WHERE users.id = questions.ownerID AND ownerID = " . $userObject->get_user_ID() . " AND idMod IS NULL GROUP BY q_id";
  } elseif ($_GET['type'] == 'performance') {
    $sql = "SELECT DISTINCT NULL AS extra_field, p, d, questions.q_id, theme, leadin_plain AS leadin, q_type, last_edited, DATE_FORMAT(last_edited,' {$configObject->get('cfg_short_date')}') AS modified, locked, status, bloom FROM (questions, performance_main, performance_details, questions_modules) WHERE questions.q_id = performance_main.q_id AND performance_main.id = performance_details.perform_id AND questions.q_id = questions_modules.q_id AND idMod = $module";
  } elseif ($_GET['type'] == 'keyword') {
    $sql = "SELECT DISTINCT keyword AS extra_field, keywordID AS p, NULL AS d, questions.q_id, theme, leadin_plain AS leadin, q_type, last_edited, DATE_FORMAT(last_edited,' {$configObject->get('cfg_short_date')}') AS modified, locked, status, bloom FROM (questions, questions_modules, keywords_question, keywords_user) WHERE questions.q_id = keywords_question.q_id AND keywords_question.keywordID = keywords_user.id AND questions.q_id = questions_modules.q_id AND idMod = $module AND deleted IS NULL AND status NOT IN ($retired_in)";
  } elseif ($_GET['type'] == 'bloom') {
    $sql = "SELECT DISTINCT bloom AS extra_field, NULL AS p, NULL AS d, questions.q_id, theme, leadin_plain AS leadin, q_type, last_edited, DATE_FORMAT(last_edited,' {$configObject->get('cfg_short_date')}') AS modified, locked, status, bloom FROM (questions, questions_modules) WHERE questions.q_id = questions_modules.q_id $module_sql $staff_modules_sql $statusSQL AND deleted IS NULL AND status NOT IN ($retired_in)";
  } else {
    $sql = "SELECT DISTINCT NULL AS extra_field, NULL AS p, NULL AS d, questions.q_id, theme, leadin_plain AS leadin, q_type, last_edited, DATE_FORMAT(last_edited,' {$configObject->get('cfg_short_date')}') AS modified, locked, status, bloom FROM (questions, questions_modules) WHERE questions.q_id = questions_modules.q_id $module_sql $staff_modules_sql $statusSQL $keyword AND deleted IS NULL";
    if ($_GET['type'] != 'status') {
      $sql .= " AND status NOT IN ($retired_in)";
    }
  }
  $sql .= ' ORDER BY ' . $tmp_sortby . ' ' . $ordering;
  
  //echo $sql . '<br />';
  
  $search_results = $mysqli->prepare($sql);
  $search_results->execute();
  $search_results->bind_result($extra_field, $p, $d, $q_id, $theme, $leadin, $q_type, $last_edited, $modified, $locked, $status, $bloom);
  $search_results->store_result();
  
  if ($type == 'keyword') {
    $table_order = array(''=>'', $string['question']=>'leadin', $string['type']=>'q_type', 'Keyword'=>'keyword', $string['modified']=>'modified', $string['status']=>'status');
  } elseif ($type == 'bloom') {
    $table_order = array(''=>'', $string['question']=>'leadin', $string['type']=>'q_type', 'Bloom\'s Taxonomy'=>'bloom', $string['modified']=>'modified', $string['status']=>'status');
  } elseif ($type == 'performance') {
    $table_order = array(''=>'', $string['question']=>'leadin', $string['type']=>'q_type', 'P'=>'p', 'D'=>'d', $string['modified']=>'modified', $string['status']=>'status');
  } else {
    $table_order = array(''=>'', $string['question']=>'leadin', $string['type']=>'q_type', $string['modified']=>'modified', $string['status']=>'status');
  }
  
  echo "<tr onclick=\"qOff();\"><th colspan=\"" . (count($table_order) - 1) . "\"><div class=\"breadcrumb\"><a href=\"../staff/index.php\">" . $string['home'] . "</a>";
  if (isset($_GET['module'])) {
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../module/index.php?module=' . $_GET['module'] . '">' . $module_code . '</a>';
    
    if ($_GET['type'] == 'type') {
      echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../question/bank.php?type=type&module=' . $_GET['module'] . '">Question Type</a>'; 
    } elseif ($_GET['type'] == 'bloom') {
      echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../question/bank.php?type=bloom&module=' . $_GET['module'] . '">Bloom\'s Taxonomy</a>'; 
    } elseif ($_GET['type'] == 'status') {
      echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../question/bank.php?type=status&module=' . $_GET['module'] . '">Status</a>'; 
    } elseif ($_GET['type'] == 'keyword') {
      echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../question/bank.php?type=keyword&module=' . $_GET['module'] . '">Keyword</a>'; 
    } elseif ($_GET['type'] == 'performance') {
      echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../question/bank.php?type=performance&module=' . $_GET['module'] . '">Performance</a>'; 
    }
  }
  echo "</div><div style=\"font-size:200%; margin-left:10px\"><strong>" . $string['questionbank'] . "&nbsp;(<span id=\"q_count\">" . number_format($search_results->num_rows) . "</span>)</strong>$bank_type</div></th>";
  echo "<th colspan=\"2\" style=\"text-align:right; vertical-align:top\"><img src=\"../artwork/toprightmenu.gif\" id=\"toprightmenu_icon\"></th></tr>\n";

	$params = '';
	if (isset($_GET['type'])) $params .= '&type=' . $_GET['type'];
	if (isset($_GET['module'])) $params .= '&module=' . $_GET['module'];
	if (isset($_GET['keyword'])) $params .= '&keyword=' . $_GET['keyword'];
	

  echo "<tr>\n";
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
    echo '<tr class="q';
    
    if ($_GET['type'] == 'type' or $_GET['type'] == 'all') {
      echo ' ' . $q_type;
    } elseif ($_GET['type'] == 'status') {
      echo ' ' . $status;
    } elseif ($_GET['type'] == 'keyword') {
      echo ' ' . $p;
    } elseif ($_GET['type'] == 'bloom' and $bloom != '') {
      echo ' ' . strtolower($bloom);
    } elseif ($_GET['type'] == 'performance') {
        if ($p >= 80 and $p <= 100) {
          echo ' veryeasy';
        } elseif ($p >= 60 and $p < 80) {
          echo ' easy';
        } elseif ($p >= 40 and $p < 60) {
          echo ' moderate';
        } elseif ($p >= 20 and $p < 40) {
          echo ' hard';
        } elseif ($p >= 0 and $p < 20) {
          echo ' veryhard';
        }

        if ($d >= 35) {
          echo ' highest';
        } elseif ($d >= 25 and $d < 35) {
          echo ' high';
        } elseif ($d >= 15 and $d < 25) {
          echo ' intermediate';
        } elseif ($d >= 0 and $d < 15) {
          echo ' low';
        }
    }
    if ($locked != '') {
      echo ' locked';
    }
    echo '"';
    if ($locked != '') {
      echo " id=\"link_$display_no\" onclick=\"selQ($q_id,$display_no,'$q_type','2c',event)\" ondblclick=\"editQ()\">";
      echo "<td><img src=\"../artwork/small_padlock.png\" class=\"plock\" alt=\"Padlock\" /></td>";
    } else {
      echo " id=\"link_$display_no\" onclick=\"selQ($q_id,$display_no,'$q_type','2b',event)\" ondblclick=\"editQ()\">";
      echo "<td></td>";
    }

    if (trim($leadin) == '') $leadin = '<span style="color:#C00000">' . $string['noquestionleadin'] . '</span>';

    echo "<td class=\"d\">";
    if (trim($theme) != '') {
      echo '<span class="theme">' . $theme . '</span><br />';
    }
    echo "$leadin</td>";
    echo "<td class=\"d nobr\">" . $string[$q_type] . "</td>";
    if ($type == 'keyword' or $type == 'bloom') {
      echo "<td class=\"d\">" . $extra_field . "</td>\n";    
    } elseif ($type == 'performance') {
      echo "<td class=\"d\">" . ($p / 100) . "</td>\n";    
      echo "<td class=\"d\">" . ($d / 100) . "</td>\n";    
    }
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
