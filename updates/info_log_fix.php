<?php
set_time_limit(0);
require '../include/sysadmin_auth.inc';

for ($i=0; $i<3; $i++) {
	$update = $mysqli->prepare("ALTER TABLE log$i ADD INDEX q_id_idx(q_id)");
	$update->execute();
	$update->close();
}
	
$stmt = $mysqli->prepare("SELECT q_id FROM questions WHERE q_type = 'info'");
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($q_id);
while ($stmt->fetch()) {
	
	for ($i=0; $i<3; $i++) {
		$update = $mysqli->prepare("DELETE FROM log$i WHERE q_id = ?");
		$update->bind_param('i', $q_id);
		$update->execute();
		$update->close();
	}
  echo "$q_id<br />\n";
}
$stmt->close();

for ($i=0; $i<3; $i++) {
	$update = $mysqli->prepare("ALTER TABLE log$i DROP INDEX q_id_idx");
	$update->execute();
	$update->close();
}
?>