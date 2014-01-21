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
require_once '../include/media.inc';
require_once '../classes/paperproperties.class.php';
require_once '../classes/logger.class.php';

$paperID = check_var('paperID', 'GET', true, false, true);

if (isset($_POST['submit'])) {
	$userID = $userObject->get_user_ID();
	$filename = $userID . '_raf.zip';
	$tmp_path = $configObject->get('cfg_tmpdir');
	
  if (!move_uploaded_file($_FILES['raffile']['tmp_name'], $tmp_path . $filename))  {
    echo uploadError($_FILES['raffile']['error']);
    exit();
	}

	$dest_dir = $tmp_path . $userID;
	if (!file_exists($dest_dir)) {
		mkdir($dest_dir, 0700);
	}
		
	$zip = new ZipArchive;
	if ($zip->open($tmp_path . $filename) === TRUE) {
		$zip->extractTo($dest_dir);
		
		$data = file_get_contents($dest_dir . '/raf.json');

		copy_images($dest_dir, $tmp_path, $data);
		
		load_raf_data($data, $tmp_path, $paperID, $mysqli);
		
		unlink($dest_dir . '/raf.json');

		$zip->close();
	} else {
		echo 'failed';
		exit;
	}
	
	$mysqli->close();
} else {
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
	
  <title>Rog&#333;</title>
	
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/dialog.css" />
	
  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
</head>

<body>
<?php
  require '../include/paper_options.inc';
?>
<div id="content" class="content">
<br />
<br />
<form name="myform" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>" enctype="multipart/form-data">
<table cellspacing="0" cellpadding="0" border="0" style="width:500px; text-align:left" class="dialog_border"> 
	<tr> 
		<td class="inline_dialog_header" style="width:55px"><img src="../artwork/raf_file.png" width="48" height="48" alt="IMS Logo" /></td><td class="dialog_header" style="width:445px"><?php echo $string['importraf']; ?></td> 
	</tr> 
	<tr> 
		<td class="dialog_body" colspan="2">
				<table width="100%" cellspacing="0" cellpadding="10">
					<tr>
						<td>
							<strong>File</strong>&nbsp;<input type="file" size="40" name="raffile" id="raffile" class="required" />
						</td>
					</tr>
          <tr>
            <td>&nbsp;</td>
          </tr>
					<tr>
						<td style="text-align:center">
							<input type="submit" name="submit" value="Import File" style="width:100px" />&nbsp;<input type="button" name="cancel" value="Cancel" style="width:100px" onclick="javascript:history.back()" />
						</td>
					</tr>
          <tr>
            <td>&nbsp;</td>
          </tr>
				</table>
		
		</td>
	</tr>
</table>

</form>
</div>

</body>
</html>
<?php
}

function copy_images($dir, $tmp_path, &$data) {
  $userObj = UserObject::get_instance();
	$userID = $userObj->get_user_ID();
	
	$configObj = Config::get_instance();

	if ($handle = opendir($dir)) {
    while (false !== ($entry = readdir($handle))) {
      if ($entry != '.' and $entry != '..' and $entry != 'raf.json') {
				$new_media = unique_filename($entry);
				rename($tmp_path . $userID . '/' . $entry, $configObj->get('cfg_web_root') . 'media/' . $new_media);
				$data = str_replace($entry, $new_media, $data);
      }
    }
    closedir($handle);
	}
}

function load_raf_data($data, $tmp_path, $paperID, $db) {
  $data_array = json_decode($data, true);
  $display_pos = 1;
	foreach ($data_array['items'] as $item) {
		
		$q_id = write_question($item['question'], $db);
		
		foreach ($item['options'] as $options) {
			write_option($options, $q_id, $tmp_path, $db);
		}
		
		add_to_paper($item['question'], $q_id, $display_pos, $paperID, $db);
		$display_pos++;
	}
}

function add_to_paper($question, $q_id, $display_pos, $paperID, $db) {
	$result = $db->prepare("INSERT INTO papers VALUE (NULL, ?, ?, ?, ?)");
	$result->bind_param('iiii', $paperID, $q_id, $question['screen'], $display_pos);
	$result->execute();
	$result->close();
}

function write_question($q, $db) {
  $userObj = UserObject::get_instance();
	$userID = $userObj->get_user_ID();

	// Stop SQL errors with ENUM fields and old data which may be blank.
	if ($q['bloom'] == '') 					$q['bloom'] = null;  
	if ($q['q_option_order'] == '') $q['q_option_order'] = 'display order';
	if ($q['score_method'] == '') 	$q['score_method'] = 'Mark per Option';

  $guid = uniqid('', true);
	
	$result = $db->prepare("INSERT INTO questions VALUE (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), ?, ?, ?, NULL, NULL, NULL, NULL, ?, ?, ?, ?, ?, ?)");
	$result->bind_param('ssssssssisssssssissss', $q['q_type'], $q['theme'], $q['scenario'], $q['leadin'], $q['correct_fback'], $q['incorrect_fback'], $q['display_method'], $q['notes'], $userID, $q['q_media'], $q['q_media_width'], $q['q_media_height'], $q['bloom'], $q['scenario_plain'], $q['leadin_plain'], $q['std'], $q['status'], $q['q_option_order'], $q['score_method'], $q['settings'], $guid);
	$result->execute();
	$q_id =  $db->insert_id;
	$result->close();
	
	$logger = new Logger($db);
	$logger->track_change('New Question', $q_id, $userID, '', 'Imported from...', '');
	
	return $q_id;
}

function write_option($o, $q_id, $tmp_path, $db) {
	$result = $db->prepare("INSERT INTO options VALUE (?, ?, ?, ?, ?, ?, ?, ?, NULL, ?, ?, ?)");
	$result->bind_param('isssssssddd', $q_id, $o['option_text'], $o['o_media'], $o['o_media_width'], $o['o_media_height'], $o['feedback_right'], $o['feedback_wrong'], $o['correct'], $o['marks_correct'], $o['marks_incorrect'], $o['marks_partial']);
	$result->execute();
	$result->close();
}
?>