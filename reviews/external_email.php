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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require_once '../include/errors.inc';
require_once '../classes/paperproperties.class.php';

$paperID = check_var('paperID', 'GET', true, false, true);
$externalID = check_var('externalID', 'GET', true, false, true);
$properties = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Email External</title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <style>
    body {font-size: 90%}
    .email {width:300px; color:#316ac5}
  </style>
  
  <?php echo $configObject->get('cfg_js_root') ?>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <script type="text/javascript" src="../tools/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
  <script type="text/javascript" src="../tools/tinymce/jscripts/tiny_mce/tiny_config_externals_email.js"></script>
</head>

<body>
<?php
  $external_details = UserUtils::get_user_details($externalID, $mysqli);

  $url = 'https://' . $_SERVER['HTTP_HOST'] . $configObject->get('cfg_root_path');
  $support_email = $configObject->get('support_email');
  
  $to = $external_details['email'];
  $subject = $configObject->get('cfg_company') . ' e-assessment review';
  $message = "<div style=\"text-align:right\"><img src=\"http://" . $_SERVER['HTTP_HOST'] . $configObject->get('cfg_root_path') . "/config/black_uon_logo.png\" width=\"167\" height=\"70\" /></div><p>Dear " . $external_details['title'] . " " . $external_details['surname'] . ",</p>";
  $message .= "<p>The online assessment <strong>" . $properties->get_paper_title() . "</strong> is now available for you to log in and review. The exam will be delivered using our online assessment system Rog&#333;. To review the paper please log in at:<br />\n";
  $message .= "<a href=\"$url\">$url</a></p>\n";
  $message .= "<p>Any problems with accessing the paper please do not hesitate to contact me. Technical support for Rog&#333; is also available from: <a href=\"mailto:$support_email\">$support_email</a></p>\n";
  $message .= "<p>Kind regards</p>\n";
  $message .= "<p>" . $userObject->get_first_first_name() . "</p>\n";

  require '../include/toprightmenu.inc';
	echo draw_toprightmenu();
?>
  <div class="head_title" style="font-size:90%">
    <div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon"></div>
    <div class="breadcrumb"><a href="../reviews/index.php"><?php echo $string['home'] ?></a>
    <?php
    if (isset($_GET['module']) and $_GET['module'] != '') {
      echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $_GET['module'] . '">' . module_utils::get_moduleid_from_id($_GET['module'], $mysqli) . '</a>';
    }    
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../paper/details.php?paperID=' . $paperID . '&module=' . $_GET['module'] . '">' . $properties->get_paper_title() . '</a>';
    ?>
    </div>
    <div class="page_title">Email Template</div>
  </div>
  
  <br />
<?php
if (isset($_POST['submit'])) {
  $to = trim($_POST['toaddress']);
  $subject = trim($_POST['subject']);
  $message = "<html>\n<head><style>\nbody {margin:20px; font-family:Arial,sans-serif; line-height:140%; color:#3F3F3F; }\na {color:#316ac5}\n</style>\n</head>\n<body>\n" . $_POST['message'] . "</body></html>\n";

  $headers = "MIME-Version: 1.0" . "\r\n";
  $headers .= "Content-type:text/html;charset=" . $configObject->get('cfg_page_charset') . "\r\n";
  $headers .= 'From: ' . $userObject->get_email();
  if (trim($_POST['ccaddress']) != '') {
    $headers .= 'CC: ' . trim($_POST['ccaddress']);
  }
  if (trim($_POST['bccaddress']) != '') {
    $headers .= 'BCC: ' . trim($_POST['ccaddress']);
  }
  
  mail($to, $subject, $message, $headers);
  echo "Email sent, please check your inbox.";
} else {
?>
  <form name="templateform" method="post" action="<?php echo $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'] ?>">

    <table cellpadding="1" cellspacing="0" border="0" style="text-align:left; margin-left:auto; margin-right:auto">
    <tr>
    <td><?php echo $string['to'] ?></td>
    <td><input type="text" size="70" name="toaddress" value="<?php echo $to ?>" class="email" /></td>
    <td style="text-align:right" rowspan="4" valign="top"><img src="../artwork/stamp.png" width="89" height="93" alt="stamp" /></td>
    </tr>
    <tr>
    <td><?php echo $string['cc'] ?></td>
    <td><input type="text" size="70" name="ccaddress" value="<?php echo $userObject->get_email() ?>" class="email" /></td>
    </tr>
    <tr>
    <td><?php echo $string['bcc'] ?></td><td><input type="text" size="70" name="bccaddress" value="" class="email" /></td>
    </tr>
    <tr>
    <td><?php echo $string['subject'] ?></td><td><input type="text" size="70" name="subject" value="<?php echo $subject ?>" /></td>
    </tr>
    <tr>
    <td colspan="3"><textarea class="mceEditor" id="message" name="message" style="width:782px; height:350px"><?php echo htmlspecialchars($message, ENT_NOQUOTES); ?></textarea></p>
    </tr>

    <tr>
    <td colspan="3" style="text-align: center">
    <input type="submit" class="ok" name="submit" value="<?php echo $string['email'];?>" />&nbsp;<input type="button" name="cancel" class="cancel" value="<?php echo $string['cancel'];?>" onclick="window.close();" />
    <input type="hidden" name="from" value="<?php echo $userObject->get_email() ?>" /></td>
    </tr>
    </table>

  </form>
<?php
}
$mysqli->close();
?>
</body>
</html>