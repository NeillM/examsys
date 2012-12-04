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

function strpos_arr($string, $array,$strtpnt) {
    $pos=strlen($string);
    foreach($array as $search) {
      if(($temp = strpos($string, $search,$strtpnt))!==false && $temp<$pos && $temp!=0) $pos=$temp;
    }
    if ($pos==strlen($string)) $pos=false;
    return $pos;
 }


echo "<table border=1 cellpadding=5 cellspacing=1><tr><td><a href='?path=".dirname($thispath)."'>..</a><br />";
if ($handle = opendir($thispath)) {
	while (false !== ($filename = readdir($handle))) {
    if ($filename != "." && $filename != ".." && $filename != ".svn" && is_dir($thispath.'/'.$filename)) {
      echo "<a href='?path=".$thispath."/".$filename."'>".$filename."</a><br />";
    }

    if ($filename != "." && $filename != ".." && (strpos($filename,'.php',0)>0 || strpos($filename,'.inc',0)>0)) {
			$file_point=fopen($thispath.'/'.$filename,"r");
			$file_content=fread($file_point, filesize($thispath.'/'.$filename));
			$file_content=preg_replace('/\n/','',$file_content);
			$file_content=strrev($file_content);
			fclose($file_point);
			$conn_table[$filename][0]='';
      
      if (!isset($count_table[$filename])) $count_table[$filename]=0;
          
			$pos1 = mb_strpos($file_content,'php.',0,'UTF-8');
      $pos1 = strpos_arr($file_content,Array('php.','cni.'),0);
			while ($pos1!==false) {
        $p2 = strpos_arr($file_content,Array('\\','\'','"','=','/',')','(',' '),$pos1);
				$p3=strrev(mb_substr($file_content,$pos1,$p2-$pos1,'UTF-8'));
				$p3=preg_replace('/-/','_',$p3);
        //echo $p3.'<br>';
				$conn_table[$filename][$p3]=$p3;
        $pos1 = strpos_arr($file_content,Array('php.','cni.'),$pos1+1);
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
echo "</td></tr></table>";

ksort($count_table);
$count_tableb = $count_table;
arsort($count_tableb);

$desc = '<ul style="font-size: smaller;font-weight: normal;text-align: left;color: #00F;">';
$desc .= '<li>files are listed in rows</li>';
$desc .= '<li>referrences are in columns</li>';
$desc .= '<li>dark-gray cells represent connections</li>';
$desc .= '<li>red rows represent files with no connection to others</li>';
$desc .= '<li>blue columns represent files that no other is connected to</li>';
$desc .= '</ul>';

echo '<table border=0 cellspacing=0>';
echo '<tr height=150>';
echo '<th></th><th>'.$desc.'</th>';
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
        if ($tv==0 || count($tv2)==1) $col = 'bgcolor="#FFEFEF"';
        if (($tv==0 || count($tv2)==1) && $line==0) $col = 'bgcolor="#FFE0E0"';
        if (isset($conn_table[$ti]) && count($conn_table[$ti])==1) $col='bgcolor="#F0F0FF"';
        if (in_array($ti,$tv2)) $col='bgcolor="#AAAAAA"';
        echo '<td '.$col.'>&nbsp;</td>';
        } 
      }
    echo '</tr>';
    }
  }
echo '</table>';

?>