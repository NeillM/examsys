<?php

$mysqli->autocommit(false);

// Delete any keywords associate with keyword based questions - ROGO-649
if (!$updater_utils->has_updated('keyword_loop')) {

	// Get keyword based questions that have an associated keyword.
	$select = $mysqli->prepare("select keywords_question.q_id, keywordID from keywords_question, questions where q_type = 'keyword_based' and keywords_question.q_id = questions.q_id");
	$select->execute();
	$select->bind_result($qid, $keywordID);
	
	// Delete entries.
	$delete = $mysqli->prepare("delete from keywords_question where q_id = ? and keywordID = ?)";
	while ($select->fetch()) {
		$delete->bind_param('ii', $qid, $keywordID);
		$delete->execute();
	}
	
	// Commit all deletes.
	$mysqli->commit();
	$delete->close();
	$select->close();
	
	$updater_utils->record_update('keyword_loop');
}

?>
