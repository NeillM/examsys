<?php
/**
 * Created by JetBrains PhpStorm.
 * User: cczsa1
 * Date: 11/12/12
 * Time: 11:51
 * To change this template use File | Settings | File Templates.
 */

global $string;

global $notice;
$mysqli =& $this->db;
$configObject =& $this->configObj;


$message = $string['authenticationfailed'] . "</p>\n<ul style=\"margin-left:80px\">\n<li>" . $string['usernamecasesensitive'] . "</li>\n";
if (isset($displayerrformobj->li)) {
  foreach ($displayerrformobj->li as $li) {
    $message .= '<li>' . $li . '</li>';
  }
}

$message .= '<li>' . $string['pressf5'] . '</li>';

$message .= "</ul>";
if ($configObject->get('cfg_use_ldap') != TRUE) $message .= $fp_link;
$notice->display_notice($string['accessdenied'], $message, '/artwork/access_denied.png', '#C00000', $title_color = 'black', $output_header = TRUE, $output_footer = TRUE);

if (isset($displayerrformobj->messages)) {
  foreach ($displayerrformobj->messages as $message1) {
    $message .= '<p>' . $message1 . '</p>';
  }
}


echo <<<END
</body>
</html>

END;
