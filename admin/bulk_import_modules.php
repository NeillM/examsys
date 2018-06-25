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
* Bulk module creation
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/admin_auth.inc';
require '../include/toprightmenu.inc';

// Instantiate Twig renderer.
$render = new render($configObject);
$lang['title'] = $string['bulkmoduleimport'];
$additionaljs = "<script type=\"text/javascript\" src=\"../js/jquery.validate.min.js\"></script>
                <script>
                    $(function () {
                      $('#import_form').validate();
                      
                      $('#cancel').click(function() {
                        history.back();
                      });
                    });
                </script>";
$addtionalcss = "<link rel=\"stylesheet\" type=\"text/css\" href=\"../css/dialog.css\" />
                <link rel=\"stylesheet\" type=\"text/css\" href=\"../css/breadcrumb.css\" />
                <style type=\"text/css\">
                    p {margin:0; padding:0}
                    h1 {font-size:120%; font-weight:bold}
                    label.error {display:block; color:#f00}
                    li {list-style-type: none}
                    .existing {color:#808080; background-image: url('../artwork/arrow_circle_double.png'); background-repeat:no-repeat; line-height:20px; text-indent:20px}
                    .added {color:black; background-image: url('../artwork/green_plus_16.png'); background-repeat:no-repeat; line-height:20px; text-indent:20px}
                    .failed {color:#C00000; background-image: url('../artwork/red_cross_16.png'); background-repeat:no-repeat; line-height:20px; text-indent:20px}
                </style>";
$render->render_admin_header($lang, $additionaljs, $addtionalcss);
?>
<body>
<?php
  require '../include/admin_module_options.inc';  
  echo draw_toprightmenu();
?>
<div id="content" class="content">
<?php
  echo $render->render_admin_navigation(array(
    '/' => $string['home'],
    '/admin/index.php' => $string['admintools'],
    '/admin/list_modules.php' => $string['modules'],
    '/users/bulk_import_modules.php' => $string['bulkmoduleimport'],
  ));
?>
<br />
<br />
<?php
  if (isset($_POST['submit'])) {
    $default_academic_year_start = $configObject->get_setting('core', 'system_academic_year_start');
    $tmpfile = $configObject->get('cfg_tmpdir') . $userObject->get_user_ID() . "_module_create.csv";
    try {
      \csv\csv_handler::move_upload_to_temp($_FILES['csvfile'], $tmpfile);
    } catch (\csv\csv_load_exception $e) {
        echo $e->getMessage();
        exit;
    }
?>
<br /><br /><br />
<div align="center">
    <table border="0" cellpadding="4" cellspacing="0" style="border:1px solid #95AEC8; font-size:120%; width:600px">
        <tr>
            <td valign="middle" align="left" style="width:56px; background-color:white"><img src="../artwork/upload_48.png" width="48" height="48" alt="Icon" /><span style="font-size:140%; font-weight:bold" class="dialog_header"><?php echo $string['bulkmoduleimport']; ?></span></td>
        </tr>
        <tr>
            <td align="left" class="dialog_body">
                <ul>
<?php
  $modulesAdded = 0;
  try {
    $csv = new \csv\csv_handler($configObject->get('cfg_tmpdir') . $userObject->get_user_ID() . "_module_create.csv");
    $import = new \import\import_modules($csv);
    $import->execute();
    foreach ($import->get_exists() as $exists) {
      echo "<li class=\"existing\">$exists - " . $string['alreadyexists'] . "</li>\n";
    }
    foreach ($import->get_added() as $added) {
      echo "<li class=\"added\">$added - " . $string['added'] . "</li>\n";
    }
    foreach ($import->get_failed() as $failed) {
      echo "<li class=\"fail\">$failed - " . $string['failed'] . "</li>\n";
    }
  } catch (\csv\csv_load_exception $e) {
    echo "<li class=\"fail\">" . $e->getMessage() . "</li>\n";
  }

  $csv->delete( $configObject->get('cfg_tmpdir') . $userObject->get_user_ID() . "_module_create.csv");
  echo "</ul>";
  echo "<div style=\"text-align:center\"><input type=\"button\" name=\"ok\" value=\"" . $string['ok'] . "\" onclick=\"window.location='list_modules.php'\" class=\"ok\" /></div>\n";
?>
             </div>
            </td>
        </tr>
    </table>
</div>
    </td></tr>
    </table>
    <?php
  } else {
    $data['formaction'] = $_SERVER['PHP_SELF'];
    $data['required'] = \import\import_modules::REQUIRED;
    $data['optional'] = \import\import_modules::OPTIONAL;
    $render->render($data, $string, 'admin/upload.html');
  }
  $render->render_admin_footer();