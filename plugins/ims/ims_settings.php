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
 * IMS Settings page
 * @author Barry Oosthuizen <barry.oosthuizen@nottingham.ac.uk>
 * @copyright Copyright (c) 2015 The University of Nottingham
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use plugins\ims\ims_enterprise_settings;

require_once '../../include/sysadmin_auth.inc';
require_once '../../include/errors.inc';

$settings = new ims_enterprise_settings();

if (isset($_POST['submit'])) {
  $settings->save_ims_settings();
}

$ims = $settings->get_ims_settings($mysqli);

if (!$ims) {
  $ims = $settings->get_default_settings();
}
$rolemappings = plugins\ims\ims_enterprise_roles::get_role_mappings();
$coursetags = $settings->get_course_tags();
$hierarchy_creation_options = $settings->get_hierarchy_creation_options();
$render = new \html_renderer();
?>
<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
    <title>Rog&#333;: <?php echo "IMS Settings " . $configObject->get('cfg_install_type') ?>
    </title>
    <link rel="stylesheet" type="text/css" href="../../css/body.css" />
    <link rel="stylesheet" type="text/css" href="../../css/header.css" />
    <link rel="stylesheet" type="text/css" href="../../css/submenu.css" />
    <style type="text/css">
      #content {
        padding-bottom: 100px;
      }

      td {text-align:left}
      .field {text-align:right; padding-right:30px}
      .form-error {
        width: 468px;
        margin: 38px auto;
        padding: 36px;
        background-color: #FFD9D9;
        color: #800000;
        border: 2px solid #800000;
      }

      div.form-defaultinfo {
        margin-bottom: 30px;
        color: #777;
        font-size: 0.85em;
        position: relative;
        left: 50px;
        max-width: 900px;
      }

      #theform > div > div > div.label {
        float: left;
        width: 350px;
        padding: 5px;
        -webkit-box-shadow: 2px 2px 5px 0px rgba(0,0,0,0.35);
        -moz-box-shadow: 2px 2px 5px 0px rgba(0,0,0,0.35);
        box-shadow: 2px 2px 5px 0px rgba(0,0,0,0.35);
      }

      .form_buttons {
        margin-top: 50px;
      }

      #theform > div > div > div > div > select,
      #theform > div > div > div > div > input {
        float: left;
        width: 300px;
        position: relative;
        left: 30px;
        height: 22px;;
      }

      #theform > div > div, h3, h2, h3 {
        padding: 5px;
      }

      div.tooltip {
        padding: 5px;
      }

      #theform > div > div.form_buttons > input[type="submit"] {
        padding-left: 50px;
        padding-right: 50px;
        padding-top: 10px;
        padding-bottom: 10px;
        font-weight: bold;
      }

      #theform > div > div.form_buttons > a {
        padding-left: 50px;
        padding-right: 50px;
        padding-top: 10px;
        padding-bottom: 10px;
        font-weight: bold;
        background-color: #FFD9D9;
        position: relative;
        left: 50px;
        border: solid 1px #000;
      }

      #theform > div > div.form_buttons > a:hover {
        background-color: #fcc4c4;
        color: #000;
        text-decoration: none;
      }
    </style>
    <?php echo $configObject->get('cfg_js_root') ?>
    <script type="text/javascript" src="../../js/jquery-1.11.1.min.js"></script>
    <script type="text/javascript" src="../../js/jquery-ui-1.10.4.min.js"></script>
    <script type="text/javascript" src="../../js/system_tooltips.js"></script>
    <script type="text/javascript" src="../../js/jquery.validate.min.js"></script>
    <script type="text/javascript" src="../../js/staff_help.js"></script>
    <script type="text/javascript" src="../../js/toprightmenu.js"></script>
    <script>
      $(function () {
        $('#theform').validate({
          errorClass: 'errfield',
          errorPlacement: function (error, element) {
            return true;
          }
        });
        $('form').removeAttr('novalidate');
        $('#cancel').click(function () {
          history.back();
        });
      });
    </script>
  </head>
  <body>
    <div id="left-sidebar" class="sidebar">
      <div class="breadcrumb">
        <a href="../../index.php"><?php echo $string['home'] ?>
        </a>
        <img src="../../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-"/>
        <a href="../../admin/index.php"><?php echo $string['administrativetools']; ?>
        </a>
      </div>
    </div>
    <?php
    require '../../include/toprightmenu.inc';
    echo draw_toprightmenu(742);
    ?>
    <div id="content">
      <div class="head_title">
        <div>
          <img alt="menu icon" src="../../artwork/toprightmenu.gif" id="toprightmenu_icon" />
        </div>
        <div class="page_title"><?php echo $string['imssettings'] ?></div>
      </div>
      <br />
      <div class="ims_settings">
        <form id="theform" name="ims_settings" method="post">
          <div>
            <?php
            $render->heading('h2', $string['imstitle'], $string['pluginname_desc']);
            $render->heading('h3', $string['basicsettings']);
            $render->text_input('filelocation', 'filelocation', $string['location'], $ims->filelocation, $string['default'] . $string['empty']);
            $render->checkbox_input('validatexml', 'validatexml', $string['validatexml'], $ims->validatexml, $string['default'] . $string['yes'], $string['validatexml_desc']);
            $render->heading('h3', $string['usersettings']);
            $render->checkbox_input('createusers', 'createusers', $string['createusers'], $ims->createusers, $string['default'] . $string['no'], $string['createusers_desc']);
            $render->checkbox_input('deleteusers', 'deleteusers', $string['deleteusers'], $ims->deleteusers, $string['default'] . $string['no'], $string['deleteusers_desc']);
            $render->checkbox_input('fixcaseusernames', 'fixcaseusernames', $string['fixcaseusernames'], $ims->fixcaseusernames, $string['default'] . $string['no']);
            $render->checkbox_input('fixcasenames', 'fixcasenames', $string['fixcasenames'], $ims->fixcasenames, $string['default'] . $string['no'], $string['fixcasenames_desc']);
            $render->checkbox_input('sourcedidfailback', 'sourcedidfailback', $string['sourcedidfailback'], $ims->sourcedidfailback, $string['default'] . $string['no'], $string['sourcedidfailback_desc']);
            $render->heading('h3', $string['roles'], $string['imsrolesdescription']);
            $render->select($rolemappings, 'rolemap01', 'rolemap01', $ims->rolemap01, $string['role_learner'], $string['default'] . $string['student']);
            $render->select($rolemappings, 'rolemap02', 'rolemap02', $ims->rolemap02, $string['role_instructor'], $string['default'] . $string['staff']);
            $render->select($rolemappings, 'rolemap03', 'rolemap03', $ims->rolemap03, $string['role_contentdeveloper'], $string['default'] . $string['staff']);
            $render->select($rolemappings, 'rolemap04', 'rolemap04', $ims->rolemap04, $string['role_member'], $string['default'] . $string['student']);
            $render->select($rolemappings, 'rolemap05', 'rolemap05', $ims->rolemap05, $string['role_manager'], $string['default'] . $string['staff']);
            $render->select($rolemappings, 'rolemap06', 'rolemap06', $ims->rolemap06, $string['role_mentor'], $string['default'] . $string['staff']);
            $render->select($rolemappings, 'rolemap07', 'rolemap07', $ims->rolemap07, $string['role_administrator'], $string['default'] . $string['staff']);
            $render->select($rolemappings, 'rolemap08', 'rolemap08', $ims->rolemap08, $string['role_teachingassistant'], $string['default'] . $string['staff']);
            $render->heading('h3', $string['coursesettings']);
            $render->text_input('truncatemodulecodes', 'truncatemodulecodes', $string['truncatemodulecodes'], $ims->truncatemodulecodes, $string['default'] . $string['zero'], $string['truncatemodulecodes_desc']);
            $render->checkbox_input('createmodules', 'createmodules', $string['createmodules'], $ims->createmodules, $string['default'] . $string['no'], $string['createmodules_desc']);
            $render->checkbox_input('createschools', 'createschools', $string['createschools'], $ims->createschools, $string['default'] . $string['no'], $string['createschools_desc']);
            $render->select($hierarchy_creation_options, 'schoolsource', 'schoolsource', $ims->schoolsource, $string['schoolsource'], $string['default'] . $string['orgname']);
            $render->checkbox_input('createfaculties', 'createfaculties', $string['createfaculties'], $ims->createfaculties, $string['default'] . $string['no'], $string['createfaculties_desc']);
            $render->select($hierarchy_creation_options, 'facultysource', 'facultysource', $ims->facultysource, $string['facultysource'], $string['default'] . $string['orgname']);
            $render->checkbox_input('createprogrammes', 'createprogrammes', $string['createprogrammes'], $ims->createprogrammes, $string['default'] . $string['no'], $string['createprogrammes_desc']);
            $render->select($hierarchy_creation_options, 'programmesource', 'programmesource', $ims->programmesource, $string['programmesource'], $string['default'] . $string['orgname']);
            $render->checkbox_input('unenrol', 'unenrol', $string['allowunenrol'], $ims->unenrol, $string['default'] . $string['no'], $string['allowunenrol_desc']);
            $render->select($coursetags, 'mapmoduleid', 'mapmoduleid', $ims->mapmoduleid, $string['settingmoduleid'], $string['default'] . $string['coursecode'], $string['settingmoduleiddescription']);
            $render->select($coursetags, 'mapfulltname', 'mapfullname', $ims->mapfullname, $string['settingfullname'], $string['default'] . $string['short'], $string['settingfullnamedescription']);
            $render->heading('h3', $string['miscsettings']);
            $render->text_input('restricttarget', 'restricttarget', $string['restricttarget'], $ims->restricttarget, $string['default'] . $string['empty'], $string['restricttarget_desc']);
            $render->checkbox_input('capitafix', 'capitafix', $string['usecapitafix'], $ims->capitafix, $string['default'] . $string['no'], $string['usecapitafix_desc']);
            ?>
            <div class="form_buttons">
              <input type="submit" name="submit" value="<?php echo $string['save'] ?>">
              <a href="../admin/index.php"><?php echo $string['cancel']; ?></a>
            </div>
          </div>
        </form>
      </div>
    </div>
  </body>
</html>
