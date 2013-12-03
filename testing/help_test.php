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
	foreach ($help_toc as $help_item) {
		$test = explode(' src=',$help_item['body']);
		if (count($test)>1) {
			for ($i=1;$i<count($test);$i++) {
				$code = preg_split("/\'|\"/",$test[$i]);
				$w=-1;$h=-1;
				foreach($code as $ci => $cv) {
					if (trim($cv)=='width=' && $w==-1) $w=$code[$ci+1];
					if (trim($cv)=='height=' && $h==-1) $h=$code[$ci+1];
				}
				if (!isset($help_img[$code[1]])) $help_img[$code[1]] = Array();
				if (count($code)>=2) {
					array_push($help_img[$code[1]],Array($help_item['id'],$w,$h));
				}
			}
		}
	}
	$result1 = '';
	$result2 = '';
	$result3 = '';
	foreach ($help_img as $img_item => $img_ids) {
		$img_size = @getimagesize("../help/staff/".$img_item);
		if (!$img_size) $result1 .= 'image "'.$img_item.'" is missing from: ';
		foreach ($img_ids as $item_id => $item_val) {
			if (!$img_size && $item_val!='') $result1 .= '"<strong><a href="/help/staff/index.php?id='.$item_val[0].'">'.$help_toc[$item_val[0]]['title'].'</a></strong>" (id=<strong><a href="/help/staff/index.php?id='.$item_val[0].'">'.$item_val[0].'</a></strong>)';
			if ($img_size) {
				array_push($help_img[$img_item][$item_id],$img_size[0],$img_size[1]);
				if (($help_img[$img_item][$item_id][1]*1!=$help_img[$img_item][$item_id][3]) || ($help_img[$img_item][$item_id][2]*1!=$help_img[$img_item][$item_id][4])) 
				{
					if ($help_img[$img_item][$item_id][1]=='-1') {
						$result3 .= 'Dimensions ('.$help_img[$img_item][$item_id][3].':'.$help_img[$img_item][$item_id][4].') for image "'.$img_item.'" are ';
						$result3 .= 'not set ';
						$result3 .= 'in: "<strong><a href="/help/staff/index.php?id='.$item_val[0].'">'.$help_toc[$item_val[0]]['title'].'</a></strong>" (id=<strong><a href="/help/staff/index.php?id='.$item_val[0].'">'.$item_val[0].'</a></strong>)<br />';
					}else{
						$result2 .= 'Dimensions ('.$help_img[$img_item][$item_id][3].':'.$help_img[$img_item][$item_id][4].') for image "'.$img_item.'" are ';
						$result2 .= 'set to ('.$help_img[$img_item][$item_id][1].':'.$help_img[$img_item][$item_id][2].') ';
						$result2 .= 'in: "<strong><a href="/help/staff/index.php?id='.$item_val[0].'">'.$help_toc[$item_val[0]]['title'].'</a></strong>" (id=<strong><a href="/help/staff/index.php?id='.$item_val[0].'">'.$item_val[0].'</a></strong>)<br />';
					}
				}
			}
		}
		if (!$img_size) $result1 .= '<br />';
	}
	echo $result1;
	if ($result1=='') echo 'Missing images - not detected.<br />';
	echo '<hr>';
	echo $result2;
	echo $result3;
	if ($result3=='' && $result2=='') echo 'dimensions inconsitencies - not detected.<br />';
	
	
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