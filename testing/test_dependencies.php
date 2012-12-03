<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title>dependendies test</title>
    
    <style type="text/css">
.vert {
	width: 14px;
  /*height: 200px;*/
  margin-top: 250px;
	writing-mode: lr-tb;
  float: left;
	-webkit-transform: rotate(-90deg);
	-moz-transform: rotate(-90deg);
	-ms-transform: rotate(-90deg);
	-o-transform: rotate(-90deg);
	transform: rotate(-90deg);
}
</style>
</head>

<?php
$thispath=getcwd();
if (isset($_GET['path'])) $thispath=$_GET['path'];
//echo $thispath;
$count_table = Array();
$conn_table = Array(Array());

echo "<a href='?path=".dirname($thispath)."'>..</a><br />";
if ($handle = opendir($thispath)) {
	while (false !== ($filename = readdir($handle))) {
    if ($filename != "." && $filename != ".." && $filename != ".svn" && is_dir($thispath.'/'.$filename)) {
      echo "<a href='?path=".$thispath."/".$filename."'>".$filename."</a><br />";
    }

    if ($filename != "." && $filename != ".." && strpos($filename,'.php',0)>0) {
      //echo $filename;
			$file_point=fopen($thispath.'/'.$filename,"r");
			$file_content=fread($file_point, filesize($thispath.'/'.$filename));
			$file_content=preg_replace('/\n/','',$file_content);
			$file_content=strrev($file_content);
			fclose($file_point);
			$conn_table[$filename][0]='';
      
      if (!isset($count_table[$filename])) $count_table[$filename]=0;
          
			$pos1 = mb_strpos($file_content,'php.',0,'UTF-8');
			while ($pos1>0) {
        $file_content = preg_replace('/[\'"=\/]/','*',$file_content); 
        $p2= mb_strpos($file_content,'*',$pos1,'UTF-8');
				$p3=strrev(mb_substr($file_content,$pos1,$p2-$pos1,'UTF-8'));
				$p3=preg_replace('/http:\/\/localhost\/um\//','',$p3);
				$conn_table[$filename][$p3]=$p3;
				$pos1 = mb_strpos($file_content,'php.',$pos1+1,'UTF-8');
			}
      foreach ($conn_table[$filename] as $ti => $tv) {
        if (isset($count_table[$ti]))
          $count_table[$ti]++;
        else
          $count_table[$ti]=1;
      }
    }
	}
	closedir($handle);
}

ksort($count_table);
$count_tableb = $count_table;
arsort($count_tableb);
//$i=0;foreach ($count_tableb as $ti => $tv) echo ++$i.'. '.$ti.':'.$tv.'<br />';
//var_dump($conn_table);

echo '<table border=0 cellspacing=0>';
echo '<tr height=150>';
echo '<th></th><th></th>';
foreach ($count_tableb as $ti => $tv) if ($ti!='' && $ti!='.php') echo '<th bgcolor="#CCCCCC"></th><th class="vert">'.$ti.'</th>';
echo '</tr>';
$line=0;
$count = 0;
foreach ($conn_table as $ti2 => $tv2) {
  if ($ti2!='0') {
    $line=1-$line;
    echo '<tr>';
    echo '<td align=right>'.++$count.'.</td>';
    echo '<td>'.$ti2.'</td>';
    foreach ($count_tableb as $ti => $tv) {
      if ($ti!='' && $ti!='.php') {
        echo '<td bgcolor="#CCCCCC"></td>';
        $col = 'bgcolor="#F0F0F0"';
        if ($line==1) $col = 'bgcolor="#F8F8F8"';
        if (in_array($ti,$tv2)) $col='bgcolor="#AAAAAA"';
        //if (count($tv2)==1) $col = 'bgcolor="#FFFFFF"';
        if ($tv==0 || count($tv2)==1) $col = 'bgcolor="#FFEFEF"';
        if (($tv==0 || count($tv2)==1) && $line==0) $col = 'bgcolor="#FFE0E0"';
        echo '<td '.$col.'>&nbsp;</td>';
        } 
      }
    echo '</tr>';
    }
  }
echo '</table>';

?>