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

require '../include/sysadmin_auth.inc';
require_once '../include/errors.inc';
require_once '../classes/moduleutils.class.php';
require_once '../classes/logger.class.php';

check_var('moduleid', 'GET', true, false, false);

$module = module_utils::get_full_details_by_ID($_GET['moduleid'], $mysqli);

if ($module === false) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

$moduleid_in_use = false;
if (isset($_POST['submit']) and $_POST['modulecode'] != $_POST['old_modulecode']) {
  // Check for unique moduleid
  $new_modulecode = trim($_POST['modulecode']);
  $moduleid_in_use = module_utils::module_exists($new_modulecode, $mysqli);
}
if (isset($_POST['submit']) and $moduleid_in_use == false) {
  if (isset($_POST['active'])) {
    $module['active'] = 1;
  } else {
    $module['active'] = 0;
  }

  if (isset($_POST['selfenroll'])) {
    $module['selfenroll'] = 1;
  } else {
    $module['selfenroll'] = 0;
  }

  if (isset($_POST['neg_marking'])) {
    $module['neg_marking'] = 1;
  } else {
    $module['neg_marking'] = 0;
  }

  $module['checklist'] = '';
  if (isset($_POST['peer']))     $module['checklist'] .= ',peer';
  if (isset($_POST['external'])) $module['checklist'] .= ',external';
  if (isset($_POST['stdset']))   $module['checklist'] .= ',stdset';
  if (isset($_POST['mapping']))  $module['checklist'] .= ',mapping';
  $module['checklist'] = substr($module['checklist'], 1);

  // Update the properties of the module.
  $module['moduleid'] = trim($_POST['modulecode']);
  $module['fullname'] = trim($_POST['fullname']);


  if (isset($_POST['timed_exams'])) {
    $module['timed_exams'] = 1;
  } else {
    $module['timed_exams'] = 0;
  }
  if (isset($_POST['exam_q_feedback'])) {
    $module['exam_q_feedback'] = 1;
  } else {
    $module['exam_q_feedback'] = 0;
  }
  if (isset($_POST['add_team_members'])) {
    $module['add_team_members'] = 1;
  } else {
    $module['add_team_members'] = 0;
  }

  $vle_data = $_POST['vle_api'];
  if ($vle_data == '') {
    $module['map_level'] = 0;
    $module['vle_api'] = '';
  } else {
    $vle_parts = explode('~', $vle_data);
    $module['vle_api'] = $vle_parts[0];
    $module['map_level'] = $vle_parts[1];
  }

  $module['sms'] = $_POST['sms_api'];
  $module['schoolid'] = $_POST['schoolid'];
  $module['ebel_grid_template'] = $_POST['ebel_grid_template'];

  module_utils::update_module_by_code($_POST['old_modulecode'], $module, $mysqli);

  $mysqli->close();
  header("location: list_modules.php");
  exit;
} else {
  require_once '../classes/smsutils.class.php';

  $SMS = SMSutils::GetSmsUtils();
  $cfg_sms_sources = array();
  if (is_object($SMS)) {
    $cfg_sms_sources =  $SMS->getModuleSources();
  }
  $cfg_sms_sources = array($string['nolookup'] => '') + $cfg_sms_sources;
?>
  <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
  <html>
  <head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo $string['editmodule'] . ' ' . $configObject->get('cfg_install_type'); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style type="text/css">
    .field {font-weight:bold; text-align:right; padding-right:10px}
    .error {color:#800000}
    input.error, select.error {background-color:#FFD9D9; border:1px solid #800000}
  </style>

  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery.validate.min.js"></script>
  <script language="JavaScript">
<?php
  $vle_apis = $configObject->get('vle_apis');
  $mu = module_utils::get_instance();
  $vle_apis = $mu->get_vle_api_data($vle_apis);
  if(count($vle_apis) > 0) {
    $map_levels = array(iVLEAPI::LEVEL_SESSION => $string['session'], iVLEAPI::LEVEL_MODULE => $string['module']);
  } else {
    $map_levels = array();
  }
?>
    $(function () {
      $('#module_form').validate({
        messages: {
          modulecode: '<div><?php echo $string['entermoduleid']; ?></div>',
          fullname: '<div><?php echo $string['entermoduletitle']; ?></div>',
          schoolid: '<div><?php echo $string['selectschool']; ?></div>'
        }
      });
<?php
  if ($moduleid_in_use == true) {
?>
      $('#modulecode').addClass('error');
<?php
  }
?>
    });

    function showHideGrid() {
      if (document.getElementById('stdset').checked) {
        document.getElementById('ebelgrid').style.display = 'table-row';
      } else {
        document.getElementById('ebelgrid').style.display = 'none';
      }
    }

    function setSidebarMenu() {
      $('#menu1a').css('display','none');
      $('#menu1b').css('display','block');
      $('#lineID').val('<?php echo $_GET['moduleid']; ?>');
    }

    $(document).ready(setSidebarMenu);

  <?php
  if ($moduleid_in_use == true) {
  ?>
  function moduleWarning() {
    alert("<?php echo sprintf($string['moduleidinuse'], $new_modulecode); ?>");
  }
  <?php
  }
  ?>
  </script>
  </head>
  <?php
  if ($moduleid_in_use == true) {
    echo "<body onload=\"moduleWarning()\">\n";
  } else {
    echo "<body>\n";
  }
  ?>
  <?php
    require '../include/module_options.inc';
  ?>
  <div id="content" class="content">
  <table class="header">
  <tr><th><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php"><?php echo $string['administrativetools']; ?></a></div><div style="margin-left:10px; font-size:200%; font-weight:bold"><?php echo $string['editmodule']; ?></div></th></tr>
  <tr><th class="bevel"></th></tr>
  </table>
  <br />
  <div align="center">
  <form id="module_form" name="module_form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?moduleid=<?php echo $_GET['moduleid']; ?>">
    <table cellpadding="0" cellspacing="2" border="0" style="text-align:left">
    <tr><td class="field"><?php echo $string['moduleid'] ?></td><td><input type="text" size="10" id="modulecode" name="modulecode" value="<?php echo $module['moduleid'] ?>" class="required" /></td></tr>
    <tr><td class="field"><?php echo $string['name'] ?></td><td><input type="text" size="70" id="fullname" name="fullname" value="<?php echo $module['fullname'] ?>" class="required" /></td></tr>
  <?php
    $old_faculty = '';
    echo "<tr><td class=\"field\">" . $string['school'] . "</td><td><select id=\"schoolid\" name=\"schoolid\" class=\"required\">\n<option value=\"\"></option>\n";
    $result = $mysqli->prepare("SELECT schools.id, school, faculty.name FROM schools, faculty WHERE schools.facultyID=faculty.id AND schools.deleted IS NULL ORDER BY faculty.name, school");
    $result->execute();
    $result->bind_result($id, $list_school, $faculty);
    while ($result->fetch()) {
      if ($old_faculty != $faculty) {
        if ($old_faculty != '') echo "</optgroup>\n";
        echo "<optgroup label=\"$faculty\">\n";
      }
      if ($module['schoolid'] == $id) {
        echo "<option value=\"$id\" selected>$list_school</option>\n";
      } else {
        echo "<option value=\"$id\">$list_school</option>\n";
      }
      $old_faculty = $faculty;
    }
    $result->close();
    echo "</optgroup>\n</select></td></tr>\n";

    if (strpos($module['checklist'], 'peer') !== false) {
      $peer = 1;
    } else {
      $peer = 0;
    }
    if (strpos($module['checklist'], 'external') !== false) {
      $external = 1;
    } else {
      $external = 0;
    }
    if (strpos($module['checklist'], 'stdset') !== false) {
      $stdset = 1;
    } else {
      $stdset = 0;
    }
    if (strpos($module['checklist'], 'mapping') !== false) {
      $mapping = 1;
    } else {
      $mapping = 0;
    }

    echo '<tr><td class="field">' . $string['smsapi'] . '</td><td><select name="sms_api">';
    foreach ($cfg_sms_sources as $key=>$value) {
      if ($module['sms'] == $value) {
        echo "<option value=\"$value\" selected>$key</option>\n";
      } else {
        echo "<option value=\"$value\">$key</option>\n";
      }
    }
    echo '</select></td></tr>';
  ?>
        <tr><td class="field"><?php echo $string['objapi']; ?></td><td><select id="vle_api" name="vle_api"><option value=""><?php echo $string['nolookup']; ?></option>
  <?php
    foreach ($vle_apis as $vle_name => $vle_api_data) {
      foreach ($vle_api_data['levels'] as $api_level) {
        $selected = ($module['vle_api'] == $vle_name and $module['map_level'] == $api_level) ? ' selected="selected"' : '';

  ?>
        <option value="<?php echo $vle_name . '~' . $api_level; ?>"<?php echo $selected; ?>><?php echo $vle_api_data['name'] . ' (' . $vle_name . ') - ' . $map_levels[$api_level] . ' ' . $string['level']; ?></option>
  <?php
      }
    }
  ?>
    </select>
    </td></tr>
    <tr><td class="field"><?php echo $string['summativechecklist']; ?></td><td><input type="checkbox" name="peer"<?php if ($peer == 1) echo ' checked="checked"'; ?> /> <?php echo $string['peerreview']; ?>, <input type="checkbox" name="external"<?php if ($external == 1) echo ' checked'; ?> /> <?php echo $string['externalexaminers']; ?>, <input onclick="showHideGrid()" type="checkbox" id="stdset" name="stdset"<?php if ($stdset == 1) echo ' checked'; ?> /> <?php echo $string['standardssetting']; ?>, <input type="checkbox" name="mapping"<?php if ($mapping == 1) echo ' checked'; ?> /> <?php echo $string['mapping']; ?></td></tr>
    <tr><td class="field"><?php echo $string['active']; ?></td><td><input type="checkbox" name="active"<?php if ($module['active'] == 1) echo ' checked="checked"'; ?> /></td></tr>
    <tr><td class="field"><?php echo $string['allowselfenrol']; ?></td><td><input type="checkbox" name="selfenroll"<?php if ($module['selfenroll'] == 1) echo ' checked="checked"'; ?> /></td></tr>
    <tr><td class="field"><?php echo $string['negativemarking']; ?></td><td><input type="checkbox" name="neg_marking"<?php if ($module['neg_marking'] == 1) echo ' checked="checked"'; ?> /></td></tr>
    <tr><td class="field"><?php echo $string['timedexams']; ?></td><td><input type="checkbox" name="timed_exams"<?php if ($module['timed_exams'] == 1) echo ' checked="checked"'; ?> /></td></tr>
    <tr><td class="field"><?php echo $string['questionbasedfeedback']; ?></td><td><input type="checkbox" name="exam_q_feedback"<?php if ($module['exam_q_feedback'] == 1) echo ' checked="checked"'; ?> /></td></tr>
    <tr><td class="field"><?php echo $string['addteammembers']; ?></td><td><input type="checkbox" name="add_team_members"<?php if ($module['add_team_members'] == 1) echo ' checked="checked"'; ?> /></td></tr>
    <tr id="ebelgrid" style="display:<?php
    if ($stdset == 1) {
      echo 'table-row';
    } else {
      echo 'none';
    }
    ?>"><td class="field"><?php echo $string['ebelgrid']; ?></td><td><select name="ebel_grid_template"><option value=""></option><?php
    $result = $mysqli->prepare("SELECT id, name FROM ebel_grid_templates ORDER BY name");
    $result->execute();
    $result->bind_result($id, $name);
    while ($result->fetch()) {
      if ($id == $module['ebel_grid_template']) {
        echo "<option value=\"$id\" selected>$name</option>\n";
      } else {
        echo "<option value=\"$id\">$name</option>\n";
      }
    }
    $result->close();
    ?></select></td></tr>
  <?php
    echo "</table>\n";
    echo "<input type=\"hidden\" name=\"old_modulecode\" value=\"" . $module['moduleid'] . "\" />\n";
  ?>
    <p><input type="submit" style="width:100px" name="submit" value="<?php echo $string['save']; ?>">&nbsp;&nbsp;<input style="width:100px" type="button" name="home" value="<?php echo $string['cancel']; ?>" onclick="javascript:history.back();" /></p>
  </form>
  </div>
</div>
<?php
}
?>
</body>
</html>