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

$properties = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);

$questions = $properties->get_questions();

$media = array();
$raf_data = create_array($questions, $media, $properties, $configObject, $mysqli);

write_file($raf_data, $configObject, $userObject);

write_zip_file($configObject, $userObject, $media);

unlink($configObject->get('cfg_tmpdir') . $userObject->get_user_ID(). '_raf.json');

$filename = $userObject->get_user_ID() . '_raf.zip';
$filepath = $configObject->get('cfg_tmpdir');

// http headers for zip downloads
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Content-Transfer-Encoding: binary");
header("Content-Length: ". filesize($filepath . $filename));
ob_end_flush();
@readfile($filepath . $filename);

unlink($filepath . $filename);

function getImages_from_html($html, &$media) {
  $parts = explode('<img',$html);
  if (count($parts) > 0) {
    // Got some images
    unset($parts[0]);
    foreach ($parts as $image_line) {
      $second_split = explode('src="',$image_line);
      $third_split = explode('"',$second_split[1]);
      $image_src = $third_split[0];
      $image_src = str_replace('./media/','',$image_src);
      $image_src = str_replace('/media/','',$image_src);
      
      $media[] = $image_src;
    }
  }
}

function create_array($questions, &$media, $properties, $configObj, $db) {
	$data = array();
	
	$data['metadata']['rogo_version'] = $configObj->get('rogo_version');
	$data['metadata']['export_date'] = date($configObj->get('cfg_long_date_php') . ' ' . $configObj->get('cfg_long_time_php'));
	$data['metadata']['company'] = $configObj->get('cfg_company');
	$data['items'] = array();
	
	$item_no = 0;

	foreach ($questions as $question) {
		$data['items'][$item_no]['question'] = get_question($question, $media, $db);
		
		getImages_from_html($data['items'][$item_no]['question']['scenario'], $media);   // Parse out any images in the question scenario.
		getImages_from_html($data['items'][$item_no]['question']['leadin'], $media);     // Parse out any images in the question leadin.
		
		$data['items'][$item_no]['options'] = get_options($question['q_id'], $media, $db);

		$item_no++;
	}
	
	return $data;
}

function write_file($xml_data, $configObj, $userObj) {
	$file_handle = fopen($configObj->get('cfg_tmpdir') . $userObj->get_user_ID(). '_raf.json', 'w');
	fwrite($file_handle, json_encode($xml_data));
	fclose($file_handle);
}

function write_zip_file($configObj, $userObj, $media) {
	$zip = new ZipArchive();
	$zip_filename = $configObj->get('cfg_tmpdir') . $userObj->get_user_ID() . '_raf.zip';

	if ($zip->open($zip_filename, ZipArchive::CREATE) !== TRUE) {
			exit("cannot open <$zip_filename>\n");
	}

	$xml_filename = $configObj->get('cfg_tmpdir') . $userObj->get_user_ID() . '_raf.json';

	$zip->addFile($xml_filename, 'raf.json');
	foreach ($media as $media_filename) {
	  if (file_exists($configObj->get('cfg_web_root') . 'media/' . $media_filename)) {
			$zip->addFile($configObj->get('cfg_web_root') . 'media/' . $media_filename, $media_filename);
		}
	}
	$zip->close();
}

function get_question($question, &$media, $db) {
	$result = $db->prepare("SELECT q_type, theme, scenario, leadin, correct_fback, incorrect_fback, display_method, notes, ownerID, q_media, q_media_width, q_media_height, creation_date, last_edited, bloom, scenario_plain, leadin_plain, checkout_time, checkout_authorID, deleted, locked, std, status, q_option_order, score_method, settings, guid FROM questions WHERE q_id = ?");
	$result->bind_param('i', $question['q_id']);
	$result->execute();
	$result->bind_result($q_type, $theme, $scenario, $leadin, $correct_fback, $incorrect_fback, $display_method, $notes, $ownerID, $q_media, $q_media_width, $q_media_height, $creation_date, $last_edited, $bloom, $scenario_plain, $leadin_plain, $checkout_time, $checkout_authorID, $deleted, $locked, $std, $status, $q_option_order, $score_method, $settings, $guid);
	$result->fetch();
	$result->close();
	
	check_media($media, $q_media);

	return array('screen'=>$question['screen'], 'q_id'=>$question['q_id'], 'q_type'=>$q_type, 'theme'=>$theme, 'scenario'=>$scenario, 'leadin'=>$leadin, 'correct_fback'=>$correct_fback, 'incorrect_fback'=>$incorrect_fback, 'display_method'=>$display_method, 'notes'=>$notes, 'ownerID'=>$ownerID, 'q_media'=>$q_media, 'q_media_width'=>$q_media_width, 'q_media_height'=>$q_media_height, 'creation_date'=>$creation_date, 'last_edited'=>$last_edited, 'bloom'=>$bloom, 'scenario_plain'=>$scenario_plain, 'leadin_plain'=>$leadin_plain, 'std'=>$std, 'status'=>$status, 'q_option_order'=>$q_option_order, 'score_method'=>$score_method, 'settings'=>$settings, 'guid'=>$guid);
}

function get_options($o_id, &$media, $db) {
  $options = array();

	$result = $db->prepare("SELECT option_text, o_media, o_media_width, o_media_height, feedback_right, feedback_wrong, correct, marks_correct, marks_incorrect, marks_partial FROM options WHERE o_id = ? ORDER BY id_num");
	$result->bind_param('i', $o_id);
	$result->execute();
	$result->bind_result($option_text, $o_media, $o_media_width, $o_media_height, $feedback_right, $feedback_wrong, $correct, $marks_correct, $marks_incorrect, $marks_partial);
	while ($result->fetch()) {
	  $options[] = array('option_text'=>$option_text, 'o_media'=>$o_media, 'o_media_width'=>$o_media_width, 'o_media_height'=>$o_media_height, 'feedback_right'=>$feedback_right, 'feedback_wrong'=>$feedback_wrong, 'correct'=>$correct, 'marks_correct'=>$marks_correct, 'marks_incorrect'=>$marks_incorrect, 'marks_partial'=>$marks_partial);

		check_media($media, $o_media);
	}
	$result->close();
	
	return $options;
}

function check_media(&$media, $media_string) {
	// Parse for any media files.
	if ($media_string != '') {
	  $media_parts = explode('|', $media_string);
		foreach ($media_parts as $media_part) {
		  if (trim($media_part) != '') {
			  $media[] = trim($media_part);
			}
		}	
	}
}
?>