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
* Admin screen to add a campus
* 
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2016 onwards The University of Nottingham
*/

require '../../include/sysadmin_auth.inc';
require_once '../../include/errors.inc';
require '../../include/campus_options.inc';
require '../../include/toprightmenu.inc';

$configObject->load_settings('core');
$settings = (object) $configObject->get_setting('core');
$cfg_campus_list = json_decode($settings->campuses, true);
	
if (isset($_POST['submit'])) {
	$name = check_var('name', 'POST', true, false, true);
	$updatearray = array();
	$duplicate = false;
    foreach ($cfg_campus_list as $campusarray) {
		$campusid = $campusarray['id'];
		if ($name == $campusarray['name']) {
			$duplicate = true;
			break;
		} else {
			if (isset($_POST['default'])) {
				$default = false;
			} else {
				$default = $campusarray['default'];
			}
			$updatearray[] = array('id' => $campusid, 'name' => $campusarray['name'], 'default' => $default);
		}
	}
	if (!$duplicate) {
		if (isset($_POST['default'])) {
			$updatearray[] = array('id' => $campusid + 1, 'name' => $name, 'default' => true);
		} else {
			$updatearray[] = array('id' => $campusid + 1, 'name' => $name, 'default' => false);
		}
		if (count($updatearray) > 0) {
			$encoded_campuses = json_encode($updatearray);
			$configObject->set_setting('campuses', $encoded_campuses);
			header("location: list_campuses.php", true, 303);
			exit();
		}
	}
}

$render = new render($configObject);
$toprightmenu = draw_toprightmenu();
$config['cfg_page_charset'] = $configObject->get('cfg_page_charset');
$config['cfg_install_type'] = $configObject->get('cfg_install_type');
$lang['title'] = $string['addycampus'];
$additionaljs = "
    <script type=\"text/javascript\" src=\"../../js/jquery.validate.min.js\"></script>
    <script type=\"text/javascript\" src=\"../../js/jquery-ui-1.10.4.min.js\"></script>
    <script type=\"text/javascript\" src=\"../../js/system_tooltips.js\"></script>
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
    </script>";
$addtionalcss = "<style type=\"text/css\">
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
        </style>";
$breadcrumb = array($string['home'] => "../../index.php", $string['administrativetools'] => "../index.php", $string['computerlabs'] => "../list_labs.php", $string['campuses'] => "list_campuses.php" );
$render->render_admin_header($lang, $config, $breadcrumb, $toprightmenu, $additionaljs, $addtionalcss);

?>

<br />
<?php
	if ($duplicate and isset($_POST['submit'])) {
		echo $notice->info_strip($string['duplicate'], 100);
	}
?>
<div align="center">
    <form id="theform" name="add_session" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
        <table cellpadding="0" cellspacing="2" border="0">
        <?php 
			echo "<tr><td class=\"field\">" . $string['name'] . "</td><td><input type=\"text\" size=\"80\" maxlength=\"80\" id=\"name\" name=\"name\" value=\"" . $campusname . "\" required /></td></tr>";
			if ($campusdefault) {
				echo "<tr><td class=\"field\">" . $string['default'] . "</td><td><input type=\"checkbox\" name=\"default\" checked /></td></tr>";
			} else {
				echo "<tr><td class=\"field\">" . $string['default'] . "</td><td><input type=\"checkbox\" name=\"default\"/></td></tr>";
			}
        ?>
        </table>
      <p><input type="submit" class="ok" name="submit" value="<?php echo $string['save'] ?>"><input class="cancel" id="cancel" type="button" name="home" value="<?php echo $string['cancel'] ?>" /></p>
    </form>
</div>

<?php
    $render->render_admin_footer();
?>
