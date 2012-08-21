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
	foreach($files as $ff)
	{
	//if ($lang!='en') $ff=preg_replace('/\/en\//','/'.$lang.'/',$ff);
	$ffs = preg_split('/\/en\//',$ff);
	$ff = $ffs[0].'/'.$lang.'/'.$ffs[1];
	
	if (file_exists($ff))
		{
		$file_contents = file_get_contents($ff);
		$file_contents = preg_replace('/</','&lt;',$file_contents);

		//split the lines
		$file_lines = preg_split('/\n/',$file_contents);
		foreach ($file_lines as $fl)
		{
			$fl = trim ($fl);
			//split the string array element line
			if (substr($fl,0,7)=='$string') 
			{
				$flt = preg_split('/=/',$fl);
				$line_string = $flt[0];
				$line_text = trim(substr($fl,strlen($line_string),-1));
				$line_text = preg_replace('/^[\s=\s\"\']+/','',$line_text);
				$line_text = preg_replace('/[\'\";\s]+$/','',$line_text);
				$line_string = substr($line_string,7,-1);
				$line_string = preg_replace('/^[\[][\']/','',$line_string);
				$line_string = preg_replace('/[\'][\]]$/','',$line_string);
				if (!isset($strings[$line_string]))
					{
					$strings[$line_string] = Array($ffs[1],$line_string,$line_text,1);
					}
				else
					{
					$strings[$line_string][0] .= '|'.$ffs[1];
					//$strings[$line_string][1] .= '|'.$line_string;
					$strings[$line_string][2] .= '|'.$line_text;
					$strings[$line_string][3] += 1;
					}
			}
		}	
	}
	else
		{
		//array_push($strings,Array($ff,'***','***',0));
		$strings[$ffs[1]] = Array($ffs[1],'','',1);
		}
	}
	return $strings;
}
 
 
function display_this($sen,$sin)
{
	$dt0= explode('|',$sen[0]);
	$dt1= $sen[1];
	$dt2= explode('|',$sen[2]);
	//echo 'xxx'.$sin.'xxx<br>';
	if ($dt1!='')
	{
		if ($sin=='') 
		{
		foreach ($dt0 as $di => $de)
			{
			echo '<tr><td>';
			if (isset($dt0[$di])) echo '<em>'.$dt0[$di].'</em>';
			echo '</td><td>';
			if (isset($dt1[$di])) echo '<strong>'.$dt1.'</strong>';
			echo '</td><td>';
			if (isset($dt2[$di])) echo $dt2[$di];
			echo '</td></tr>';
			}
		}
		else
		{
			echo '<tr><td><em>'.$dt0[$sin].'</em></td><td><strong>'.$dt1.'</strong></td><td>'.$dt2[$sin].'</td></tr>';
		}
	}
}
//----------------------------------------------------------------------------------------------
//list of lang folders
$lang_array=Array('pl');
$dira=getcwd().'/en/';

//exclusion list
$exclude = explode("|", ".|..|.ds_store|.svn");

//searching for files
$files = Array();
$dirs = Array();
$dirs = file_array($dira,$exclude );

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
	foreach ($strings_pl as $sid => $sen)
		if ($sen[0]==$sid) echo '<em>'.$sen[0].'</em><br />';
	echo '</table>';
	
	echo '<h2>Strings in missing files?:</h2>';
	echo '<table>';
	foreach ($strings_en as $sid => $sen)
	{
		$sen0 = explode("|",$sen[0]);
		foreach ($sen0 as $sen0e => $sen0i)
			if (isset($strings_pl[$sen0i])) display_this($sen,$sen0e);
	}
	echo '</table>';

	echo '<h2>Missing strings?:</h2>';
	echo '<table>';
	foreach ($strings_en as $sid => $sen)
		if (!isset($strings_pl[$sid]) && (!isset($strings_pl[$sen[0]]))) display_this($sen,'');
	echo '</table>';

	echo '<h2>Missing strings from file?:</h2>';
	echo '<table>';
	foreach ($strings_en as $sid => $sen)
		if (isset($strings_pl[$sid]) && ($strings_pl[$sid][0]!=$sen[0])) 
			{
			//echo $strings_pl[$sid][0].':'.$sen[0].'<br>';
			$sen1 = explode("|",$sen[0]);
			$sen2 = explode("|",$strings_pl[$sid][0]);
			$sen3 = array_diff($sen1,$sen2);
			display_this(Array(implode(", ",$sen3),$sen[1],$sen[2],$sen[3]),'');
			}
	echo '</table>';

	echo '<h2>Files with empty keys for the \'$string\' array?:</h2>';
	echo '<table>';
	foreach ($strings_pl as $sid => $sen)
	{
		if ($sen[1]=='')
		{
		$sen1 = explode("|",$sen[0]);
		foreach ($sen1 as $sen1e => $sen1i)
			echo '<em>'.$sen1i.'</em><br />';
		}
	}

	echo '</table>';

	echo '<h2>Duplicate strings in files?:</h2>';
	echo '<table>';
	foreach ($strings_pl as $sid => $sen)
		{
		if ($strings_pl[$sid][3]>1)	
			{
				$sen1 = explode("|",$sen[0]);
				$sen2 = array_unique($sen1);
				$sen3 = array_count_values($sen1);
				//var_dump($sen2);
				//if (count($sen2)!=count($sen1)) display_this(Array(implode(", ",$sen3),$sen[1],$sen[2],$sen[3]),'');
				if (count($sen2)!=count($sen1)) 
					foreach ($sen3 as $sen3i => $sen3e)
						if ($sen3e>1)  display_this(Array($sen3i,$sen[1],$sen[2],$sen[3]),'');
			}
		}
echo '</table>';

	echo '<h2>Identical texts?:</h2>';
	echo '<table>';
	foreach ($strings_en as $sid => $sen)
		if (isset($strings_pl[$sid]) && ($strings_pl[$sid]==$strings_en[$sid]))  display_this($sen,'');		
	echo '</table>';
}

//---------------------------------------------------------------------------

echo '<hr>';
echo '<h1>Analysis for: en</h1>';
echo '<h2>Files with empty keys for the \'$string\' array?:</h2>';
echo '<table>';
foreach ($strings_en as $sid => $sen)
{
	if ($sen[1]=='')
	{
	$sen1 = explode("|",$sen[0]);
	foreach ($sen1 as $sen1e => $sen1i)
		echo '<em>'.$sen1i.'</em><br />';
	}
}

echo '</table>';

echo '<h2>Duplicate strings in files?:</h2>';
echo '<table>';
foreach ($strings_en as $sid => $sen)
	{
	if ($strings_en[$sid][3]>1)	
		{
			$sen1 = explode("|",$sen[0]);
			$sen2 = array_unique($sen1);
			$sen3 = array_count_values($sen1);
			//var_dump($sen2);
			//if (count($sen2)!=count($sen1)) display_this(Array(implode(", ",$sen3),$sen[1],$sen[2],$sen[3]),'');
			if (count($sen2)!=count($sen1)) 
				foreach ($sen3 as $sen3i => $sen3e)
					if ($sen3e>1)  display_this(Array($sen3i,$sen[1],$sen[2],$sen[3]),'');
		}
	}
echo '</table>';

echo '<h2>Duplicate strings ?:</h2>';
echo '<table>';
foreach ($strings_en as $sid => $sen)
	{
	if ($strings_en[$sid][3]>1)	
		{
		display_this($sen,'');
		}
	}
echo '</table>';

?>
</body>

