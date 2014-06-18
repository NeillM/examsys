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
* Delete a question in the question bank.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/errors.inc';

check_var('q_id', 'POST', true, false, false);

$tmp_q_ids = explode(',', $_POST['q_id']);

for ($i=1; $i<count($tmp_q_ids); $i++) {
  $result = $mysqli->prepare("UPDATE questions SET deleted = NOW() WHERE q_id = ?");
  $result->bind_param('i', $tmp_q_ids[$i]);
  $result->execute();  
  $result->close();
}

$mysqli->close();
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title><?php echo $string['questiondeleted']; ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/check_delete.css" />

  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script>
		$(document).ready(function() {
      window.opener.location.href = window.opener.location.href;
      self.close();
    });
  </script>
</head>

<body>

<p><?php echo $string['msg']; ?><p>

<div style="text-align: center">
<form action="" method="get">
<input type="button" name="cancel" value="OK" class="ok" onclick="javascript:window.close();" />
</form>
</div>

</body>
</html>
