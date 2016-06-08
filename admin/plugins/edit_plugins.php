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
* Admin screen to edit a plugin settings
* 
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2016 onwards The University of Nottingham
*/

require '../../include/sysadmin_auth.inc';
require_once '../../include/errors.inc';

$plugin = check_var('pid', 'REQUEST', true, false, true);
$installed = plugin_manager::plugin_installed($plugin);

if ($installed === false) {
    $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
    $title = $string['pagenotfound'];
    $notice->display_notice_and_exit($mysqli, $title, $msg, $title, '../artwork/page_not_found.png', '#C00000', true, true);
}

// Get all enabled plugins.
$enabledplugins = plugin_manager::get_all_enabled_plugins();
// Is plugin enabled.
$pluginenabled = false;
if (in_array($plugin, $enabledplugins)) {
    $pluginenabled = true;
}

// Get plugin namespace.
$pluginslist = plugin_manager::listplugins();
if (isset($pluginslist[$plugin])) {
    $pluginns = $pluginslist[$plugin];
}

require '../../include/toprightmenu.inc';

if (isset($_POST['submit'])) {
    $p = new $pluginns($mysqli);
    // Enable.
    $enable = check_var('enabledchk', 'POST', false, false, true);
    if (is_null($enable)) {
        $p->disable_plugin();
    } else {
        $p->enable_plugin();
    }
    // Configs.
    foreach ($configObject->get_setting($plugin) as $setting => $value) {
        if ($setting != "installed") {
            // Password settings are encrypted.
            if ($setting == "password") {
                $encryp = new encryp();
                $value = $encryp->mdecrypt_password($value);
            }
            if ($value != check_var($setting, 'POST', false, false, true)) {
                if ($setting == "password") {
                    $_POST[$setting] = $encryp->mcrypt_password($_POST[$setting]);
                }
                $configObject->set_setting($setting, $_POST[$setting], $plugin);
            }
        }
    }
    header("location: list_plugins.php", true, 303);
    exit();
}

$render = new render($configObject);
$toprightmenu = draw_toprightmenu();
$lang['title'] = $string['editplugins'];
$additionaljs = "<script type=\"text/javascript\" src=\"../../js/jquery.validate.min.js\"></script>
    <script type=\"text/javascript\" src=\"../../js/jquery-ui-1.10.4.min.js\"></script>
    <script type=\"text/javascript\" src=\"../../js/system_tooltips.js\"></script></script>
    <script type=\"text/javascript\" src=\"js/plugins_validate.min.js\"></script>";
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
$breadcrumb = array($string['home'] => "../../index.php", $string['administrativetools'] => "../index.php", $string['rogoplugins'] => "../plugins/list_plugins.php");
$render->render_admin_header($lang, $additionaljs, $addtionalcss);
$render->render_admin_options('', '', $lang, $toprightmenu, 'admin/options_empty.html');
$render->render_admin_content($breadcrumb, $lang);

?>

<br />
<div align="center">
    <form id="theform" name="add_session" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" autocomplete="off">
        <table cellpadding="0" cellspacing="2" border="0">
        <?php
            if ($pluginenabled) { 
                echo "<tr><td class=\"field\"><label for=\"enabledchk\">" . $string['enabled'] . "</label></td><td><input type=\"checkbox\" name=\"enabledchk\" id=\"enabledchk\" checked/></td></tr>";
            } else {
                echo "<tr><td class=\"field\"><label for=\"enabledchk\">" . $string['enabled'] . "</label></td><td><input type=\"checkbox\" name=\"enabledchk\"/></td></tr>";
            }
            echo "<input type=\"hidden\" name=\"pid\" id=\"pid\" value=\"" . $plugin. "\"/>";
            foreach ($configObject->get_setting($plugin) as $setting => $value) {
                if ($setting != "installed") {
                    // Password settings need to be decrypted.
                    if ($setting == "password") {
                        $encryp = new encryp();
                        $value = $encryp->mdecrypt_password($value);
                    }
                    echo "<tr><td class=\"field\"><label for=\"" . $setting . "\">" . $setting. "</label></td><td><input type=\"text\" size=\"20\" id=\"" . $setting . "\" name=\"" . $setting . "\" value=\"" . $value . "\" required /></td></tr>";
                }
            }
        ?>
        </table>
      <p><input type="submit" class="ok" name="submit" value="<?php echo $string['save'] ?>"><input class="cancel" id="cancel" type="button" name="home" value="<?php echo $string['cancel'] ?>" /></p>
    </form>
</div>

<?php
    $render->render_admin_footer();
?>
