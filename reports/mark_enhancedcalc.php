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

require '../include/staff_auth.inc';
require '../include/errors.inc';
require_once '../classes/paperutils.class.php';
require_once '../plugins/questions/enhancedcalc/enhancedcalc.class.php';
require_once '../plugins/questions/enhancedcalc/helpers/enhancedcalc_mark_helper.php';

set_time_limit(0);

$paperID = check_var('paperID', 'GET', true, false, true);

if (!Paper_utils::paper_exists($paperID, $mysqli)) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}


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


foreach ($q_ids as $q_id => $setting) {
  enhancedcalc_remark('2', $paperID, $q_id, $setting, $mysqli);
}
?>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title>Test</title>
</head>

<body>
Finished!

</body>
</html>