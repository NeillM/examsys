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
* Marks all enhanced calculation questions for a summative paper.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require '../../include/staff_auth.inc';
require '../../include/errors.inc';
require_once '../../classes/paperproperties.class.php';
require_once '../../plugins/questions/enhancedcalc/enhancedcalc.class.php';
require_once '../../plugins/questions/enhancedcalc/helpers/enhancedcalc_helper.php';

set_time_limit(0);

$paperID = check_var('paperID', 'REQUEST', true, false, true);

$properties = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);

$questions = $properties->get_questions();

// Get the enhanced calculation questions on the paper.
$q_ids = array();
$result = $mysqli->prepare("SELECT question, settings FROM papers, questions WHERE papers.question = questions.q_id AND q_type = 'enhancedcalc' AND paper = ?");
$result->bind_param('i', $paperID);
$result->execute();
$result->bind_result($q_id, $settings);
while ($result->fetch()) {
  $q_ids[$q_id] = $settings;
}
$result->close();

$server_connection = true;

$statuses = array();
foreach ($q_ids as $q_id => $setting) {
  $data = enhancedcalc_remark('2', $paperID, $q_id, $setting, $mysqli, 'all');
	if ($data[-3] > 0) {
		$server_connection = false;
	}
  $statuses[$q_id] = $data;
}

$return_status = 'Complete';

foreach($statuses as $qid => $data) {
  if ($data[Q_MARKING_UNMARKED] > 0 or $data[Q_MARKING_ERROR] > 0) {
	  $return_status = 'Problems detected - please contact ' . $configObject->get('support_email');
	}
}
if ($return_status != 'Complete' and $userObject->has_role('SysAdmin')) {
  $return_status .= var_dump($statuses);
}

print $return_status;
?>

