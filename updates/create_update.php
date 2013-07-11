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

require_once '../include/load_config.php';
require '../include/sysadmin_auth.inc';

$migration_path = 'version5';

set_time_limit(0);

$old_version = $configObject->get('rogo_version');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>"/>

    <title>Rog&#333; <?php echo $configObject->get('rogo_version') ?> update Script</title>

    <link rel="stylesheet" type="text/css" href="../css/body.css"/>
    <link rel="stylesheet" type="text/css" href="../css/header.css"/>
    <link rel="stylesheet" type="text/css" href="../css/updater.css"/>

    <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
    <script type="text/javascript" src="../js/jquery.validate.min.js"></script>
  </head>
  <body>
  <table class="header">
    <tr>
      <th style="padding-top:4px; padding-bottom:4px; padding-left:16px">
          <img src="../artwork/r_logo.gif" width="56" height="60" alt="logo" border="0" style="float:left; padding-right:8px"/>

          <div style="color:#1F497D; font-size:28pt; font-weight:bold">Rog&#333;</div>
          <div style="color:#1F497D; font-size:9pt">Update Creation Utility (<?php echo $migration_path; ?>)</div>
      </th>
      <th style="text-align:right; padding-right:10px"><img src="../artwork/software_64.png" width="64" height="64" alt="Upgrade Icon" border="0" /></th>
    </tr>
    <tr>
      <th colspan="2" class="bevel"></th>
    </tr>
  </table>
<?php
if (!isset($_POST['create'])) {
?>
<script type="text/javascript">
  $(document).ready(function () {
    $("#create_form").validate();
  });
</script>
  <form id="create_form" class="cmxform" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
      <div><?php echo $string['msg1']; ?></div>
      <table class="h">
          <tr>
              <td>
                  <nobr><?php echo $string['tag']; ?></nobr>
              </td>
              <td class="line">
                  <hr/>
              </td>
          </tr>
      </table>
      <br/>

      <div><label for="tag"><?php echo $string['tag']; ?></label> <input type="text" value="" name="tag" class="required" minlength="2" /></div>
      </div>

      <div class="submit"><input type="submit" name="create" value="<?php echo $string['create']; ?>"/></div>
  </form>
   </body>
   </html>
  <?php

} else {
  $template = <<<TEMPLATE
<?php
// Your code here

/*
 *****   NOW UPDATE THE INSTALLER SCRIPT   *****
 */
TEMPLATE;

  $tag = str_replace(' ', '_', strtolower(trim($_POST['tag'])));
  $datestamp = date('YmdHi');
  $filename = $migration_path . '/' . $datestamp . '_' . $tag . '.php';

  if (file_put_contents($filename, $template) !== false) {
    printf('<p>' . $string['success'] . '</p>', $filename);
    echo "<p><a href=\"version5.php\">{$string['runupdate']}</a></p>\n";
  } else {
    echo "<p>{$string['createerror']}</p>";
  }
}
?>
