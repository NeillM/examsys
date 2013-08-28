<?php
// This file is part of Rogo
//
// Rogo is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogo is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogo.  If not, see <http://www.gnu.org/licenses/>.

/**
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require '../include/sysadmin_auth.inc';
set_time_limit(0);
?>
<html>
<head>
<title>Fix</title>
</head>
<body>
<ul>
<?php

$stmt = $mysqli->prepare("SELECT id FROM users WHERE roles='left' OR roles='graduate'");
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($user_to_delete);
while ($stmt->fetch()) {
	
	$result = $mysqli->prepare("SELECT log_metadata.id, userID, paperID, started, ipaddress, student_grade, year, attempt, completed, lab_name FROM log_metadata, properties WHERE log_metadata.paperID = properties.property_id AND paper_type IN ('0', '1') AND userID = ?");
	$result->bind_param('i', $user_to_delete);
	$result->execute();
	$result->bind_result($metadata_id, $userID, $paperID, $started, $ipaddress, $student_grade, $year, $attempt, $completed, $lab_name);
	$result->store_result();
	while ($result->fetch()) {
		$found = false;
		
		$sub_query = $mysqli->prepare("SELECT id FROM log0 WHERE metadataID = ? LIMIT 1");
		$sub_query->bind_param('i', $metadata_id);
		$sub_query->execute();
		$sub_query->bind_result($metadata_id);
		$sub_query->store_result();
		if ($sub_query->num_rows > 0) $found = true;
		$sub_query->close();

		if (!$found) {
			$sub_query = $mysqli->prepare("SELECT id FROM log1 WHERE metadataID = ? LIMIT 1");
			$sub_query->bind_param('i', $metadata_id);
			$sub_query->execute();
			$sub_query->bind_result($metadata_id);
			$sub_query->store_result();
			if ($sub_query->num_rows > 0) $found = true;
			$sub_query->close();
		}
		
		if (!$found) {
		  echo "<li>$user_to_delete - $metadata_id</li>";
			
			// Insert into 'log_metadata_deleted'
			$sub_query = $mysqli->prepare("INSERT INTO log_metadata_deleted VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
			$sub_query->bind_param('iiisssiiss', $metadata_id, $userID, $paperID, $started, $ipaddress, $student_grade, $year, $attempt, $completed, $lab_name);
			$sub_query->execute();
			$sub_query->close();
			
			// Delete the original 'log_metadata' record
			$sub_query = $mysqli->prepare("DELETE FROM log_metadata WHERE id = ?");
			$sub_query->bind_param('i', $metadata_id);
			$sub_query->execute();
			$sub_query->close();
		}

	}
	$result->close();

}
$stmt->close();

$mysqli->close();

?>
</ul>
<div>Finished</div>
</body>
</html>
