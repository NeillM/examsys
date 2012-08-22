<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=utf-8" />
  <title>lang test</title>
</head>

<body>
<?php

//function for recursive files search
function file_array($path, $exclude) {
	GLOBAL $files;
	$path = rtrim($path, "/") . "/";
	$folder_handle = opendir($path);
	$result = array();
	while(false !== ($filename = readdir($folder_handle))) {
	if(!in_array(strtolower($filename), $exclude)) {
		if(is_dir($path . $filename . "/")) {
			$result[] = file_array($path . $filename . "/", $exclude);
			} else {
			array_push($files, $path.$filename);
			}
		}
	}
	return $result;
 }
 
//function for reading string into the array
function file_array_read($files,$lang)
{
	$strings = Array(Array());
	foreach($files as $filepath)
	{
	//if ($lang!='en') $filepath=preg_replace('/\/en\//','/'.$lang.'/',$filepath);
	$filepath_parts = preg_split('/\\\\en\\\\/',$filepath);
	$filepath = $filepath_parts[0].'\\'.$lang.'\\'.$filepath_parts[1];
	
	if (file_exists($filepath))
		{
		$file_contents = file_get_contents($filepath);
		$file_contents = preg_replace('/</','&lt;',$file_contents);

		//split the lines
		$file_lines = preg_split('/\n/',$file_contents);
		foreach ($file_lines as $file_line)
		{
			$file_line = trim ($file_line);
			//split the string array element line
			if (substr($file_line,0,7)=='$string') 
			{
				$file_line_parts = preg_split('/=/',$file_line);
				$line_string = $file_line_parts[0];
				$line_text = trim(substr($file_line,strlen($line_string)));
				//remove comments except for '//cognate'
				if ((strpos($line_text,'//')!==false) && (strpos($line_text,'//cognate')===false)) 
					$line_text = trim(substr($line_text,0,strpos($line_text,'//')));

					$line_text = preg_replace('/^[\s=\s\"\']+/','',$line_text);
				$line_text = preg_replace('/[\'\";\s]+$/','',$line_text);
				$line_string = substr($line_string,7,-1);
				$line_string = preg_replace('/^[\[][\']/','',$line_string);
				$line_string = preg_replace('/[\'][\]]$/','',$line_string);
				
				if (!isset($strings[$line_string]))
					{
					$strings[$line_string] = Array($filepath_parts[1],$line_string,$line_text,1);
					}
				else
					{
					$strings[$line_string][0] .= '|'.$filepath_parts[1];
					//$strings[$line_string][1] .= '|'.$line_string;
					$strings[$line_string][2] .= '|'.$line_text;
					$strings[$line_string][3] += 1;
					}
			}
		}	
	}
	else
		{
		//array_push($strings,Array($filepath,'***','***',0));
		$strings[$filepath_parts[1]] = Array($filepath_parts[1],'','',1);
		}
	}
	return $strings;
}
 
 
function display_this($data,$data_index)
{
	$data_part1= explode('|',$data[0]);
	$data_part2= $data[1];
	$data_part3= explode('|',$data[2]);
	if ($data_part2!='')
	{
		if ($data_index=='-1') 
		{
		foreach ($data_part1 as $data_key => $data_element)
			{
			echo '<tr><td>';
			if (isset($data_part1[$data_key])) echo '<em>'.$data_part1[$data_key].'</em>';
			echo '</td><td>';
			if (isset($data_part2)) echo '<strong>'.$data_part2.'</strong>';
			echo '</td><td>';
			if (isset($data_part3[$data_key])) echo $data_part3[$data_key];
			echo '</td></tr>';
			}
		}
		else
		{
			echo '<tr><td><em>'.$data_part1[$data_index].'</em></td><td><strong>'.$data_part2.'</strong></td><td>'.$data_part3[$data_index].'</td></tr>';
		}
	}
}
//----------------------------------------------------------------------------------------------
//list of lang folders
$lang_array=Array('pl');
$path=getcwd();
$path=preg_replace('/testing/','',$path).'lang\\en\\';

//exclusion list
$excluded = explode("|", ".|..|.ds_store|.svn");

//searching for files
$files = Array();
$paths = Array();
$paths = file_array($path,$excluded);

//test list of searched files
$strings_en = file_array_read($files,'en');
if (empty($strings_en[0])) unset($strings_en[0]);

foreach ($lang_array as $lang)
{
	$strings_pl = file_array_read($files,$lang);
	if (empty($strings_pl[0])) unset($strings_pl[0]);

	echo '<h1>Analysis for: '.$lang.'</h1>';
	echo '<h2>Missing files?:</h2>';
	echo '<table>';
	foreach ($strings_pl as $strings_key => $strings_data)
		if ($strings_data[0]==$strings_key) echo '<em>'.$strings_data[0].'</em><br />';
	echo '</table>';
	
	echo '<h2>Strings in missing files?:</h2>';
	echo '<table>';
	foreach ($strings_en as $strings_key => $strings_data)
	{
		$data_path = explode("|",$strings_data[0]);
		foreach ($data_path as $data_path_key => $data_path_elem)
			if (isset($strings_pl[$data_path_elem])) display_this($strings_data,$data_path_key);
	}
	echo '</table>';

	echo '<h2>Missing strings?:</h2>';
	echo '<table>';
	foreach ($strings_en as $strings_key => $strings_data)
		if (!isset($strings_pl[$strings_key]) && (!isset($strings_pl[$strings_data[0]]))) display_this($strings_data,-1);
	echo '</table>';

	echo '<h2>Missing strings from file?:</h2>';
	echo '<table>';
	foreach ($strings_en as $strings_key => $strings_data)
		if (isset($strings_pl[$strings_key]) && ($strings_pl[$strings_key][0]!=$strings_data[0])) 
			{
			$data_path1 = explode("|",$strings_data[0]);
			$data_path2 = explode("|",$strings_pl[$strings_key][0]);
			$data_path3 = array_diff($data_path1,$data_path2);
			display_this(Array(implode(", ",$data_path3),$strings_data[1],$strings_data[2],$strings_data[3]),-1);
			}
	echo '</table>';

	echo '<h2>Files with empty keys for the \'$string\' array?:</h2>';
	echo '<table>';
	foreach ($strings_pl as $strings_key => $strings_data)
	{
		if ($strings_data[1]=='')
		{
		$data_path1 = explode("|",$strings_data[0]);
		foreach ($data_path1 as $data_path1_key => $data_path1_elem)
			echo '<em>'.$data_path1_elem.'</em><br />';
		}
	}

	echo '</table>';

	echo '<h2>Duplicate strings in files?:</h2>';
	echo '<table>';
	foreach ($strings_pl as $strings_key => $strings_data)
		{
		if ($strings_pl[$strings_key][3]>1)	
			{
				$data_path1 = explode("|",$strings_data[0]);
				$data_path2 = array_unique($data_path1);
				$data_path3 = array_count_values($data_path1);
				if (count($data_path2)!=count($data_path1)) 
					foreach ($data_path3 as $data_path3_key => $data_path3_elem)
						if ($data_path3_elem>1)  display_this(Array($data_path3_key,$strings_data[1],$strings_data[2],$strings_data[3]),-1);
			}
		}
	echo '</table>';

	echo '<h2>Identical texts?:</h2>';
	echo '<table>';
	foreach ($strings_en as $strings_key => $strings_data)
		if (isset($strings_pl[$strings_key]) && ($strings_pl[$strings_key]==$strings_en[$strings_key]))  display_this($strings_data,-1);		
	echo '</table>';

	
	echo '<h2>Identical strings in files?:</h2>';
	echo '<table>';
	foreach ($strings_en as $strings_key => $strings_data)
		{
		if ($strings_en[$strings_key][3]>1)	
			{
				$data_path1 = explode("|",$strings_data[2]);
				$data_path2 = explode("|",$strings_pl[$strings_key][2]);
				if (count($data_path2)==count($data_path1)) 
					foreach ($data_path1 as $data_path1_key => $data_path1_elem)
						if (($data_path1[$data_path1_key]==$data_path2[$data_path1_key])) 
						{
						display_this($strings_pl[$strings_key],$data_path1_key);
						}
			}
		}
	echo '</table>';
}

//---------------------------------------------------------------------------

echo '<hr>';
echo '<h1>Analysis for: en</h1>';
echo '<h2>Files with empty keys for the \'$string\' array?:</h2>';
echo '<table>';
foreach ($strings_en as $strings_key => $strings_data)
{
	if ($strings_data[1]=='')
	{
	$data_path1 = explode("|",$strings_data[0]);
	foreach ($data_path1 as $data_path1_key => $data_path1_elem)
		echo '<em>'.$data_path1_elem.'</em><br />';
	}
}

echo '</table>';

echo '<h2>Duplicate strings in files?:</h2>';
echo '<table>';
foreach ($strings_en as $strings_key => $strings_data)
	{
	if ($strings_en[$strings_key][3]>1)	
		{
			$data_path1 = explode("|",$strings_data[0]);
			$data_path2 = array_unique($data_path1);
			$data_path3 = array_count_values($data_path1);
			if (count($data_path2)!=count($data_path1)) 
				foreach ($data_path3 as $data_path3_key => $data_path3_elem)
					if ($data_path3_elem>1)  display_this(Array($data_path3_key,$strings_data[1],$strings_data[2],$strings_data[3]),-1);
		}
	}
echo '</table>';


echo '<h2>Duplicate strings ?:</h2>';
echo '<table>';
foreach ($strings_en as $strings_key => $strings_data)
	{
	if ($strings_en[$strings_key][3]>1)	
		{
		display_this($strings_data,-1);
		}
	}
echo '</table>';

?>
</body>

