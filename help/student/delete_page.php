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

function check4Images($html,&$images) {
  if (stripos($html, '<img') !== false) {
    $img_parts = explode('src="',$html);
    for ($i=1; $i<count($img_parts); $i++) {
      $quote_parts = explode('"',$img_parts[$i]);
      $images[] = $quote_parts[0];
    }
  }
}

require '../../include/sysadmin_auth.inc';    // Only let staff delete pages.
require '../../include/errors.inc';

$path = $cfg_web_root . 'student_help';

header('Content-Type: text/html; charset=' . $configObject->get('cfg_page_charset'));
$image_list = array();

// Is the current page real or a pointer.
$result = $mysqli->prepare("SELECT type, body FROM student_help WHERE articleid = ? AND language = ?");
$result->bind_param('is', $_GET['id'], $_SESSION['ROGO_language']);
$result->execute();
$result->bind_result($type, $body);
$result->fetch();
$result->close();

if ($type == 'page') {
  // Search for any pointers to the current page.
  $result = $mysqli->prepare("SELECT id, body FROM student_help WHERE type = 'pointer' AND id != ? AND body = ? AND language = ?");
  $result->bind_param('iis', $_GET['id'], $_GET['id'], $_SESSION['ROGO_language']);
  $result->execute();
  $result->store_result();
  $result->bind_result($page_id, $body);
  while ($result->fetch()) {
    $deleteQuery = $mysqli->prepare("UPDATE student_help SET deleted = NOW() WHERE articleid = ? AND language = ?");
    $deleteQuery->bind_param('is', $page_id, $_SESSION['ROGO_language']);
    $deleteQuery->execute();
    $deleteQuery->close();
  }
  $result->close();
}

$deleteQuery = $mysqli->prepare("UPDATE student_help SET deleted = NOW() WHERE articleid = ? AND language = ?");
$deleteQuery->bind_param('is', $_GET['id'], $_SESSION['ROGO_language']);
$deleteQuery->execute();
$deleteQuery->close();

$mysqli->close();
header("location: index.php?id=1");
?>