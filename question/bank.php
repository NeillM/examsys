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
* Displays a list of papers.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require_once '../include/staff_auth.inc';
require_once '../include/icon_display.inc';
require_once '../include/sidebar_menu.inc';
require_once '../include/errors.inc';

require_once '../classes/moduleutils.class.php';
require_once '../classes/folderutils.class.php';
require_once '../classes/keywordutils.class.php';
require_once '../classes/stateutils.class.php';
require_once '../classes/paperutils.class.php';
require_once '../classes/question_status.class.php';

$state = $stateutil->getState($userObject->get_user_ID(), $mysqli);

$type = check_var('type', 'GET', true, false, true);
$module = check_var('module', 'GET', true, false, true);

$module_details = module_utils::get_full_details_by_ID($module, $mysqli);

if (!$module_details) {
 $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
 $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
} elseif ($module_details['active'] == 0) {
 $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
 $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);	
}

$_SESSION['nav_page'] = $_SERVER['SCRIPT_NAME'];
$_SESSION['nav_query'] = $_SERVER['QUERY_STRING'];
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['questionbank'] . ' ' . $configObject->get('cfg_install_type'); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />

  <?php echo $configObject->get('cfg_js_root') ?>
  <script type="text/javascript" src="../js/sidebar.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript" src="../js/state.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
</head>

<body onclick="hideMenus()">
<?php
  require '../include/module_options.inc';
  require '../include/toprightmenu.inc';

	echo draw_toprightmenu();
?>
<div id="content" class="content">
<table class="header">
<?php
echo '<tr><th><div class="breadcrumb"><a href="../staff/index.php">' . $string['home'] . '</a>';
echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../module/index.php?module=' . $module . '">' . $module_details['moduleid'] . '</a>';
echo "</div></th><th style=\"text-align:right; vertical-align:top\"><img src=\"../artwork/toprightmenu.gif\" id=\"toprightmenu_icon\"></th></tr>\n";

echo '<tr><th><div style="margin-left:10px; font-size:200%; font-weight:bold">';
echo 'Question Bank: <span style="font-weight:normal">';
if ($type == 'type') {
  echo $string['bytype']; 
} elseif ($type == 'bloom') {
  echo $string['byblooms']; 
} elseif ($type == 'keyword') {
  echo $string['bykeyword']; 
} elseif ($type == 'status') {
  echo $string['bystatus'];
} elseif ($type == 'difficulty') {
  echo $string['bydifficulty'];
} elseif ($type == 'discrimination') {
  echo $string['bydiscrimination'];
}
echo '</span>';
echo "</div></th><th></th></tr>\n";

echo "</table>\n";

function get_stats($idMod, $type, $db) {
  $stats = array();
  
  if ($type == 'type') {
    $sql = 'SELECT COUNT(questions.q_id), q_type FROM questions, questions_modules WHERE questions.q_id = questions_modules.q_id AND idMod = ? AND deleted IS NULL GROUP BY q_type';
  } elseif ($type == 'status') {
    $sql = 'SELECT COUNT(questions.q_id), name FROM questions, questions_modules, question_statuses WHERE questions.status = question_statuses.id AND questions.q_id = questions_modules.q_id AND idMod = ? AND deleted IS NULL GROUP BY status';
  } elseif ($type == 'bloom') {
    $sql = 'SELECT COUNT(questions.q_id), bloom FROM questions, questions_modules WHERE questions.q_id = questions_modules.q_id AND idMod = ? AND deleted IS NULL GROUP BY bloom';
  } elseif ($type == 'keyword') {
    $sql = 'SELECT COUNT(questions.q_id), keyword FROM questions, questions_modules, keywords_question, keywords_user WHERE keywords_question.keywordID = keywords_user.id AND questions.q_id = questions_modules.q_id AND idMod = ? AND questions.q_id = keywords_question.q_id AND deleted IS NULL GROUP BY keyword';
  }
  
  $result = $db->prepare($sql);
  $result->bind_param('i', $idMod);
  $result->execute();
  $result->bind_result($number, $type);
  while ($result->fetch()) {
    $stats[$type] = $number;
  } 
  $result->close();
  
  return $stats;
}

function get_keywords($idMod, $db) {
  $keywords_array = array();
  
  $result = $db->prepare("SELECT keyword, keywords_user.id FROM keywords_user, modules WHERE keywords_user.userID = modules.id AND modules.id = $idMod ORDER BY keyword");
  $result->execute();
  $result->bind_result($keyword, $keywordID);
  while ($result->fetch()) {
    $keywords_array[$keywordID] = $keyword;
  }
  $result->close();
  
  return $keywords_array;
}

switch($type) {
  case 'keyword':
    $stats = get_stats($module, 'keyword', $mysqli);
    $keywords = get_keywords($module, $mysqli);
    if (count($keywords) == 0) {    // Stop we have no keywords.
      echo $notice->info_strip($string['nokeywords'], 100) . "</div>\n</body>\n</html>\n";
      exit;
    }
    foreach ($keywords as $keywordID=>$keyword) {
      $bank_types[$keyword] = 'list.php?keyword=' . $keywordID;
    }
    break;
  case 'type':
    $stats = get_stats($module, 'type', $mysqli);
    $bank_types = array(
        'area' => 'list.php?type=area',
        'calculation' => 'list.php?type=enhancedcalc',
        'dichotomous' => 'list.php?type=dichotomous',
        'extmatch' => 'list.php?type=extmatch',
        'blank' => 'list.php?type=blank',
        'hotspot' => 'list.php?type=hotspot',
        'info' => 'list.php?type=info',
        'keyword_based' => 'list.php?type=keyword',
        'labelling' => 'list.php?type=labelling',
        'likert' => 'list.php?type=likert',
        'matrix' => 'list.php?type=matrix',
        'mcq' => 'list.php?type=mcq',
        'mrq' => 'list.php?type=mrq',
        'random' => 'list.php?type=random',
        'rank' => 'list.php?type=rank',
        'sct' => 'list.php?type=sct',
        'textbox' => 'list.php?type=textbox',
        'true_false' => 'list.php?type=true_false'
      );
    break;
  case 'status':
    $statuses = QuestionStatus::get_all_statuses($mysqli, $string);
    $stats = get_stats($module, 'status', $mysqli);
    $bank_types = array();
    foreach ($statuses as $status) {
      $status_name = $status->get_name();
      $bank_types[$status_name] = 'list.php?status=' . $status->id;
    }
    break;
  case 'bloom':
    $stats = get_stats($module, 'bloom', $mysqli);
    $bank_types = array(
        'Knowledge' => 'list.php?bloom=knowledge',
        'Comprehension' => 'list.php?bloom=comprehension',
        'Application' => 'list.php?bloom=application',
        'Analysis' => 'list.php?bloom=analysis',
        'Synthesis' => 'list.php?bloom=synthesis',
        'Evaluation' => 'list.php?bloom=evaluation'
      );
    break;
  case 'difficulty':
    //$stats = get_stats($module, 'bloom', $mysqli);
    $bank_types = array(
        'Very Easy' => 'list.php?p=ve',
        'Easy' => 'list.php?p=e',
        'Moderate' => 'list.php?p=m',
        'Hard' => 'list.php?p=h',
        'Very Hard' => 'list.php?p=vh'
      );
    break;
  case 'discrimination':
    //$stats = get_stats($module, 'bloom', $mysqli);
    $bank_types = array(
        'Highest' => 'list.php?d=h2',
        'High' => 'list.php?d=h1',
        'Intermediate' => 'list.php?d=i',
        'Low' => 'list.php?d=l'
      );
    break;
}

if ($type != 'keyword') {
  echo "<br />\n";
}

$old_section = '';
foreach ($bank_types as $type_name=>$url) {
  $grey_text = '';
  $modified_url = $url . '&module=' . $module;
  if ($type == 'type') {
    $display_name = $string[$type_name];
  } elseif ($type == 'bloom') {
    $display_name = $string[strtolower($type_name)];
  } else {
    $display_name = $type_name;
  }
  if ($type == 'keyword') {
    if ($old_section != $type_name{0}) {
      echo "<br clear=\"left\" />\n";
      echo "<table border=\"0\" class=\"subsect\"><tr><td><nobr>" . $type_name{0} . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
    }
    $old_section = $type_name{0};
  }
  if (isset($stats[$type_name])) {
    $grey_text = '<br /><span class="grey">' . number_format($stats[$type_name]) . ' questions</span>';
  }
  echo "<div class=\"f2\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td class=\"f_icon\"><a href=\"$modified_url\"><img src=\"../artwork/yellow_folder.png\" alt=\"Folder\" /></a></td><td><a href=\"$modified_url\" class=\"blacklink\">" . $display_name . "</a>$grey_text</td></tr></table></div>\n";
}
$mysqli->close();
?>
</div>

</body>
</html>