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
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */

require '../include/sysadmin_auth.inc';
require_once 'ims-lti/UoN_LTI.php';
$lti = new UoN_LTI();
$lti->init_lti0($mysqli);
if (isset($_POST['submit'])) {
  $ltiname = trim($_POST['ltiname']);
  $ltikey = trim($_POST['ltikey']);
  $ltisec = trim($_POST['ltisec']);
  $lticontext = trim($_POST['lticontext']);
  $insert_id = $lti->add_lti_key($ltiname, $ltikey, $ltisec, $lticontext);
  header("location: lti_keys_list.php");
	exit();
} else {
  ?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>"/>
  <title><?php echo $string['addltikeys'] . ' ' . $configObject->get('cfg_install_type'); ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <style type="text/css">
    td {
      text-align: left
    }
    .field {
      font-weight: bold;
      text-align: right;
      padding-right: 10px
    }
  </style>

  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery.validate.min.js"></script>
  <script type="text/javascript">
    $(function () {
      $('#theform').validate({
        errorClass: 'errfield',
        errorPlacement: function(error,element) {
          return true;
        }
      });
      $('form').removeAttr('novalidate');
    });
  </script>
</head>
<body>
<?php
  require '../include/lti_keys_options.inc';
  ?>
<div id="content" class="content" style="font-size:80%">
<table class="header">
  <tr>
    <th>
      <div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img
        src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-"/>&nbsp;&nbsp;<a
        href="../admin/index.php"><?php echo $string['administrativetools']; ?></a>&nbsp;&nbsp;<img
        src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-"/>&nbsp;&nbsp;<a
        href="lti_keys_list.php"><?php echo $string['ltikeys']; ?></a></div>
      <div style="margin-left:10px; font-size:200%; font-weight:bold"><?php echo $string['addltikeys']; ?></th>
    <th style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(233); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="<?php echo $string['help']; ?>" /></a></th>
  </tr>
  <tr>
    <th colspan="2" class="bevel"></th>
  </tr>
</table>
  <br/>
  <div align="center">
    <form id="theform" name="add_LTIkeys" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
      <table>
        <tr>
          <td class="field"><?php echo $string['name']; ?></td>
          <td><input type="text" size="70" maxlength="255" name="ltiname" id="ltiname" required /></td>
        </tr>
        <tr>
          <td class="field"><?php echo $string['oauth_consume_key']; ?></td>
          <td><input type="text" size="70" maxlength="255" name="ltikey" id="ltikey" required /></td>
        </tr>
        <tr>
          <td class="field"><?php echo $string['oauth_secret']; ?></td>
          <td><input type="text" size="70" maxlength="255" name="ltisec" id="ltisec" required /></td>
        </tr>
        <tr>
          <td class="field"><?php echo $string['oauth_context_id']; ?></td>
          <td><input type="text" size="70" maxlength="255" name="lticontext" id="lticontext" /></td>
        </tr>
      </table>
      <p><input type="submit" style="width:100px" name="submit" value="<?php echo $string['add']; ?>"/>&nbsp;&nbsp;<input style="width:100px" type="button" name="home" value="<?php echo $string['cancel']; ?>" onclick="javascript:history.back();"/></p>
    </form>
  </div>
  <?php
}
?>
</div>
</body>
</html>