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
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2015 The University of Nottingham
*/

require '../include/sysadmin_auth.inc';

$yearutils = new yearutils($mysqli);

if (isset($_POST['submit'])) {
    $calendar_year = trim($_POST['calendar_year']);

    if ($yearutils->check_calendar_year($calendar_year)) {
        $error = 'duplicate';
    } else {

        $academic_year = trim( $_POST['academic_year']);
        (isset($_POST['cal_status'])) ? $cal_status = 1: $cal_status = 0;
        (isset($_POST['stat_status'])) ? $stat_status = 1 : $stat_status = 0;

        $result = $mysqli->prepare("INSERT INTO academic_year (calendar_year, academic_year, cal_status, stat_status) values(?, ?, ?, ?)");
        $result->bind_param('isii', $calendar_year, $academic_year, $cal_status, $stat_status);
        $result->execute();
        $result->close();

        header("location: academic_sessions.php");
        exit();
    }
}

?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
        <title>Rog&#333;: <?php echo $string['addacademicsession'] . " " . $configObject->get('cfg_install_type') ?></title>
        <link rel="stylesheet" type="text/css" href="../css/body.css" />
        <link rel="stylesheet" type="text/css" href="../css/header.css" />
        <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
        <style type="text/css">
          td {text-align:left}
          .field {text-align:right; padding-right:10px}
          .form-error {
            width: 468px;
            margin: 18px auto;
            padding: 16px;
            background-color: #FFD9D9;
            color: #800000;
            border: 2px solid #800000;
          }
        </style>

        <?php echo $configObject->get('cfg_js_root') ?>
        <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
        <script type="text/javascript" src="../js/jquery.validate.min.js"></script>
        <script type="text/javascript" src="../js/toprightmenu.js"></script>
        <script type="text/javascript" src="../js/jquery-ui-1.10.4.min.js"></script>
        <script type="text/javascript" src="../js/staff_help.js"></script>
        <script type="text/javascript" src="../js/system_tooltips.js"></script>
        <script>
          $(function () {
            $('#theform').validate({
              errorClass: 'errfield',
              errorPlacement: function(error,element) {
                return true;
              }
            });
            $('form').removeAttr('novalidate');
            $('#cancel').click(function() {
              history.back();
            });
          });
          
          function checkForm() {
            var year = $('#calendar_year').val();
            var regexp = /^[1-9][0-9][0-9][0-9]$/; 
            if (regexp.exec(year) === null) {
               alert('<?php echo $string['invalidcalendaryear'] ?>');
               return false;
            }
          }
        </script>
    </head>
    <body>
<?php
    require '../include/academic_session_options.inc';
    require '../include/toprightmenu.inc';

    echo draw_toprightmenu(740);
?>
        <div id="content">

        <div class="head_title">
          <div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
          <div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="./index.php"><?php echo $string['administrativetools'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="academic_sessions.php"><?php echo $string['academicsessions'] ?></a></div>
          <div class="page_title"><?php echo $string['addacademicsession'] ?></div>
        </div>

        <br />
            <div align="center">
                <form id="theform" name="add_session" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" onsubmit="return checkForm()" autocomplete="off">
                    <?php
                        if (isset($error) and $error = 'duplicate') {
                    ?>
                        <div class="form-error"><?php echo $string['duplicateerror'] ?></div>
                    <?php
                        }
                    ?>
                    <table cellpadding="0" cellspacing="2" border="0">
                        <tr><td class="field"><?php echo $string['calendaryear'] ?> <img src="../artwork/tooltip_icon.gif" class="help_tip" title="<?php echo $string['calendaryear_tt'] ?>" /></td><td><input type="text" size="4" maxlength="4" id="calendar_year" name="calendar_year" value="" required /></td></tr>
                        <tr><td class="field"><?php echo $string['academicyear'] ?> <img src="../artwork/tooltip_icon.gif" class="help_tip" title="<?php echo $string['academicyear_tt'] ?>" /></td><td><input type="text" size="30" maxlength="30" id="academic_year" name="academic_year" value="" required /></td></tr>
                        <tr><td class="field"><?php echo $string['calstatus'] ?> <img src="../artwork/tooltip_icon.gif" class="help_tip" title="<?php echo $string['calendarenabled_tt'] ?>" /></td><td><input type="checkbox" id="cal_status" name="cal_status" value="" /></td></tr>
                        <tr><td class="field"><?php echo $string['statstatus'] ?> <img src="../artwork/tooltip_icon.gif" class="help_tip" title="<?php echo $string['statenabled_tt'] ?>" /></td><td><input type="checkbox" id="stat_status" name="stat_status" value="" /></td></tr>
                    </table>
                  <p><input type="submit" class="ok" name="submit" value="<?php echo $string['save'] ?>"><input class="cancel" id="cancel" type="button" name="home" value="<?php echo $string['cancel'] ?>" /></p>
                </form>
            </div>
        </div>
    </body>
</html>
