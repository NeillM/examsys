<?php
set_time_limit(0);
require '../include/sysadmin_auth.inc';

// Gather all the Image Hotspot question IDs first.
$q_ids = array();

$stmt = $mysqli->prepare("SELECT property_id FROM properties WHERE start_date > 20131207180000 and start_date < now() and paper_type = '2'");
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($paperID);
while ($stmt->fetch()) {
	
		$stmt2 = $mysqli->prepare("SELECT q_id FROM questions, papers WHERE papers.question = questions.q_id AND q_type='hotspot' AND paper = ?");
		$stmt2->bind_param('i', $paperID);
		$stmt2->execute();
		$stmt2->bind_result($q_id);
		while ($stmt2->fetch()) {
			$q_ids[$q_id] = $q_id;
		}
		$stmt2->close();

}
$stmt->close();


// Look up user answers.
$updates = array();
foreach ($q_ids as $q_id) {
	$stmt = $mysqli->prepare("SELECT id, user_answer FROM log2 WHERE q_id = ? AND updated > 20131207180000");
	$stmt->bind_param('i', $q_id);
	$stmt->execute();
	$stmt->store_result();
	$stmt->bind_result($log_id, $user_answer);
	while ($stmt->fetch()) {
	  if ($user_answer != 'u') {
			$fixed = fix_coords($user_answer);
			
			$updates[$log_id] = $fixed;
		}
	}
	$stmt->close();
}

var_dump($updates);

$stmt = $mysqli->prepare("UPDATE log2 SET user_answer = ? WHERE id = ?");
foreach ($updates as $log_id => $user_answer) {
	$stmt->bind_param('si', $user_answer, $log_id);
	$stmt->execute();
}
$stmt->close();


function fix_coords($answer) {
	$new_answer = '';
	
	$parts = explode('|', $answer);
	foreach($parts as $part) {
	  $coords = explode(',', $part);
		if ($new_answer != '') $new_answer .= '|';
		if ($coords[1] == 'false') {
			$new_answer .= $coords[0] . ',false,false';
		} else {
			$new_answer .= $coords[0] . ',' . ($coords[1] + 4) . ',' . ($coords[2] - 2);
		}
	}
	
	return $new_answer;
}


?>