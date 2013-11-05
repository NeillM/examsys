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
require_once '../classes/question_status.class.php';

$state = $stateutil->getState($userObject->get_user_ID(), $mysqli);

// Get question statuses
$status_array = QuestionStatus::get_all_statuses($mysqli, $string, true);

$typeSQL = '';
$type = '';
if (isset($_GET['type'])) {
  $type = $_GET['type'];
  if ($_GET['type'] != '%') {
    $typeSQL = " AND q_type = '" . $_GET['type'] . "'";
  }
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
  $module_code = module_utils::get_moduleid_from_id($_GET['module'], $mysqli);
  if (!$module_code) {
    $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
  }
} else {
  $module = '';
}

if (isset($_GET['checked'])) {
  if ($_GET['checked'] == 'true') {
    $state_checked = true;
  } else {
    $state_checked = false;
  }
} elseif (isset($state['myquestions']) and $state['myquestions'] == 'true') {
  $state_checked = true;
} else {
  $state_checked = false;
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
    .q:hover {background-color:#eee}
    .q.highlight {background-color:#B3C8E8}
    .nobr {white-space:nowrap}
<?php echo QuestionStatus::generate_status_css($status_array); ?>
  </style>

  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript" src="../tools/mee/mee/js/mee_src.js"></script>
  <script type="text/javascript" src="../js/state.js"></script>
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
?>

<div id="content" class="content" onclick="hideMenus(event)">
<table class="header">
<?php
  $question_no = 0;
  $display_no = 0;
  $bank_type = '';
  $module_sql = '';

  if ($keyword != '%' and $keyword != '' and $type == '%') {
    $parts = explode(';',$keyword);
    $bank_type = ": '" . $parts[1] . "'";
  }
  if ($module != '') {
    $bank_type = ": $module_code";
  }
  if ($_GET['type'] != '%') {
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
    $keyword = ' AND keywordID=' . $parts[0];
  } else {
    $keyword = '';
  }

  $hits = 0;
  $display_no = 0;

  $retired_in = '-1,' . implode(',', QuestionStatus::get_retired_status_ids($status_array));
	
	$questions = array();
	
  $query_string = "SELECT DISTINCT questions.q_id, title, initials, surname, leadin_plain AS leadin, q_type, last_edited, DATE_FORMAT(last_edited,' {$configObject->get('cfg_short_date')}') AS modified, locked, status FROM (users, questions, questions_modules)";
  if ($keyword != '%' and $keyword != '') {
  	$query_string .= " LEFT JOIN keywords_question ON questions.q_id = keywords_question.q_id";
  }
  if ($state_checked == 'true') {
    $query_string .= " WHERE questions.q_id = questions_modules.q_id $module_sql $staff_modules_sql AND users.id = questions.ownerID AND ownerID = " . $userObject->get_user_ID() . " $typeSQL $keyword AND status NOT IN ($retired_in) AND deleted IS NULL GROUP BY q_id ORDER BY leadin_plain, q_id";
  } else {
    $query_string .= " WHERE questions.q_id = questions_modules.q_id AND users.id = questions.ownerID $module_sql $staff_modules_sql $typeSQL $keyword AND status NOT IN ($retired_in) AND deleted IS NULL";
  }
  $search_results = $mysqli->prepare($query_string);
  $search_results->execute();
  $search_results->bind_result($q_id, $title, $initials, $surname, $leadin, $q_type, $last_edited, $modified, $locked, $status);
  while ($search_results->fetch()) {
	  $questions[] = array('q_id'=>$q_id, 'owner'=>$title . ' ' . $initials . ' ' . $surname, 'leadin'=>$leadin, 'q_type'=>$q_type, 'last_edited'=>$last_edited, 'modified'=>$modified, 'locked'=>$locked, 'status'=>$status_array[$status]->get_name());
	}
  $search_results->close();
	
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
	$questions = array_csort($questions, $tmp_sortby, $ordering);

  echo "<tr onclick=\"qOff();\"><th colspan=\"4\"><div class=\"breadcrumb\"><a href=\"../staff/index.php\">" . $string['home'] . "</a></div><div style=\"font-size:200%; margin-left:10px\"><strong>" . $string['questionbank'] . "&nbsp;(" . number_format(count($questions)) . ")</strong>$bank_type</div></th>";
  echo "<th colspan=\"2\" style=\"text-align:right\" nowrap><input class=\"chk\" type=\"checkbox\" onclick=\"myQuestions(this);\" name=\"myquestions\" id=\"myquestions\" value=\"on\"";
  if ($state_checked == 'true') echo ' checked="checked"';
  echo " />&nbsp;<nobr>" . $string['myquestionsonly'] . "</nobr>&nbsp;</th></tr>\n";

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
  echo "<tr><th class=\"bevel\" colspan=\"6\"></th></tr>\n";
	
	foreach ($questions as $question) {
    $status_class = ' status' . $status;
    echo '<tr class="q' . $status_class . '"';
    if ($question['locked'] != '') {
      echo " id=\"link_$display_no\" onclick=\"selQ($question[q_id],$display_no,'$question[q_type]','2c',event)\" ondblclick=\"editQ(); return false;\">";
      echo "<td><img src=\"../artwork/small_padlock.png\" width=\"16\" height=\"16\" alt=\"Padlock\" /></td>";
    } else {
      echo " id=\"link_$display_no\" onclick=\"selQ($question[q_id],$display_no,'$question[q_type]','2b',event)\" ondblclick=\"editQ(); return false;\">";
      echo "<td></td>";
    }
    $tmp_leadin = $question['leadin'];

    if (strpos($tmp_leadin,'class="mee"') === false) {
      $tmp_leadin = strip_tags($tmp_leadin);                                     // No equation, strip all tags
      if (strlen($tmp_leadin) > 160) {
        $tmp_leadin = substr($tmp_leadin,0,160) . '...';
      }
    } else {
      $tmp_leadin = trim(str_replace('&nbsp;',' ',(strip_tags($tmp_leadin,"<div>,<span>"))));
      $tmp_leadin = preg_replace('/ style="[\w-,:; \']*"/i', '', $tmp_leadin);   // Equation present, strip some formatting
    }

    if (trim($tmp_leadin) == '') $tmp_leadin = '<span style="color:#C00000">' . $string['noquestionleadin'] . '</span>';
    if ($userObject->has_role('Demo')) $question['owner'] = 'Dr J Bloggs';

    echo "<td class=\"d\">$tmp_leadin</td>";
    echo "<td class=\"d nobr\">" . $question['owner'] . "</td>";
    echo "<td class=\"d nobr\">" . $string[$question['q_type']] . "</td>";
    echo "<td class=\"d\">" . $question['modified'] . "</td>\n";
    echo "<td class=\"d\">" . $question['status'] . "</td></tr>\n";
    $display_no++;
  }
	
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
