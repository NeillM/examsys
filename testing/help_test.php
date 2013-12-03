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
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title>Help pages internal consistency test</title>
  
</head>
<body>

<div id="main">
<?php


  $result = $mysqli->prepare("SELECT id, body, title, type FROM rogo.staff_help ORDER BY id;");
  $result->execute();  
	$help_toc = array();
	$help_img = array();

  $result->bind_result($id, $body, $title, $type);
  while ($result->fetch()) {
    $help_toc[$id]['id'] = $id;
    $help_toc[$id]['body'] = $body;
    $help_toc[$id]['type'] = $type;
    $help_toc[$id]['title'] = $title;
    $help_toc[$id]['links'] = '';
  }
  $result->close();
  $mysqli->close();
  echo '<h1>Help pages internal consistency test</h1>';
	
	//internal links
	$result = '';
	foreach ($help_toc as $help_item) {
		$test = explode('?id=',$help_item['body']);
		if (count($test)>1) {
			for ($i=1;$i<count($test);$i++) {
				$pos1 = strpos($test[$i],'"');
				$pos2 = strpos($test[$i],'>',$pos1)+1;
				$pos3 = strpos($test[$i],'</a>',$pos1);
				$link = substr($test[$i],0,$pos1);
				$text = substr($test[$i],$pos2,$pos3-$pos2);
				if (isset($help_toc[$link])) {
					$help_toc[$link]['links'] .= $help_item['id'].',';
				} else {
					$result .= 'link reference is missing in: "<strong><a href="/help/staff/index.php?id='.$help_item['id'].'">'.$help_item['title'].'</a></strong>" (id=<strong><a href="/help/staff/index.php?id='.$help_item['id'].'">'.$help_item['id'].'</a></strong>) to: "'.$text.'" (id='.$link.')<br />';
				}	
			}
		}
	}
	echo $result;
	if ($result=='') echo 'Missing internal links - not detected.';
	echo '<hr>';
	
	//incorporated images
	$result = '';
	foreach ($help_toc as $help_item) {
		$test = explode(' src=',$help_item['body']);
		if (count($test)>1) {
			for ($i=1;$i<count($test);$i++) {
				$code = preg_split("/\'|\"/",$test[$i]);
				if (!isset($help_img[$code[1]])) $help_img[$code[1]] = '';
				if (count($code)>=2) $help_img[$code[1]] .= $help_item['id'].',';
			}
		}
	}
	foreach ($help_img as $img_item => $img_ids) {
		if (!@getimagesize("../help/staff/".$img_item)) {
			$result .= 'image is missing for: "<strong><a href="/help/staff/index.php?id='.$help_item['id'].'">'.$help_item['title'].'</a></strong>" (id=<strong><a href="/help/staff/index.php?id='.$help_item['id'].'">'.$help_item['id'].'</a></strong>) to: "'.$img_item.'"<br />';	
		}
	}
	echo $result;
	if ($result=='') echo 'Missing images - not detected.<br />';
	
	
	echo '<hr><h2>Help pages ids:</h2>';
	$div_num = round(count($help_toc)/15);
	echo '<table><tr><td><ol>';
	$i=0;$j=1;
	foreach ($help_toc as $help_item) {
		$i++;
		if ($i>($div_num*$j)) {
			$j++;
			echo '</ol></td><td><ol start='.$i.'>';
		}
		echo '<li><a href="/help/staff/index.php?id='.$help_item['id'].'">'.$help_item['id'].'</a></li>';
	}
	echo '</ol></td></tr></table>';
?>
</div>
</body>
</html>