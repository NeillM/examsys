<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title>functions and variables usage</title>
</head>

<?php
$thispath=getcwd();
if (isset($_GET['path'])) $thispath=$_GET['path'];
$excluded_files='';
if (isset($_GET['excl'])) {
  $excluded_files=$_GET['excl'];
  echo '<strong>Excluding: </strong>'. $excluded_files . '<br>';
  }
$no_display_files='';
if (isset($_GET['disp'])) {
  $no_display_files=$_GET['disp'];
  echo '<strong>Not displaying: </strong>'. $no_display_files . '<br>';
  }

$excluded = explode(',',$excluded_files);
$nodisplay = explode(',',$no_display_files);
$file_table = Array();
$func_table = Array();
$var_table = Array();

//read the filenames and content int an array
echo "<table border=1 cellpadding=5 cellspacing=1><tr><td>";
echo "<a href='?path=".dirname($thispath)."'>..</a><br />";
if ($handle = opendir($thispath)) {
	while (false !== ($filename = readdir($handle))) {
    if ($filename != "." && $filename != ".." && $filename != ".svn" && is_dir($thispath.'/'.$filename)) {
      echo "<a href='?path=".$thispath."/".$filename."'>".$filename."</a><br />";
    }
    if ($filename != "." && $filename != ".." && strpos($filename,'.js',0)>0 && strpos($filename,'q',0)===0) {
      if (!in_array($filename,$excluded)) 
        {
        $file_table[$filename] = $filename;
        echo '... '.$filename.'<br>';
      }
    }    
  }
closedir($handle);
}  
echo "</td><td>";
echo "reads all functions and variables declared in q*.js files in that folder<br />";
echo "displays filename and line number where it was used (round brackets) or declared (sqare brackets)<br />";
echo "if the line is commented out the slash is used for brakets<br />";
echo "if declared function or variable is never used in tested files - red stars are dislpayed<br />";
echo "</td></tr></table>";
       
foreach ($file_table as $filename) {
  $file_point=fopen($thispath.'/'.$filename,"r");
  $file_content=fread($file_point, filesize($thispath.'/'.$filename));
  $file_table[$filename] = $file_content;
  fclose($file_point);
  
  //searching for declared functions
  $pos1 = strpos($file_content,'function ',0);
  while ($pos1!==false) {
    $post = strpos($file_content,'(',$pos1+8);
    if (substr($file_content,$pos1+9,1)!=')') {
      $func_table[trim(substr($file_content,$pos1+9,$post-$pos1-9))] = 0;
      }
    $pos1 = strpos($file_content,'function ',$pos1+1);
    }

  //searching for declared variables
  $pos1 = strpos($file_content,'var ',0);
  while ($pos1!==false) {
    $post = strpos($file_content,';',$pos1+3);
    $post1 = strpos($file_content,'=',$pos1+3);
    if ($post1<$post) $post=$post1;
    if (substr($file_content,$pos1+4,1)!=')') {
      $vart = explode(',',trim(substr($file_content,$pos1+4,$post-$pos1-4)));
      foreach($vart as $v) {
        $var_table[trim($v)] = 0;
      }
      }
    $pos1 = strpos($file_content,'var ',$pos1+1);
    }
} 
 
function searchfor($tabl,$str) {
  global $file_table,$nodisplay;
  echo '<ol>';
  foreach ($tabl as $fui => $fut) {
    echo '<li>'.$str.': <strong>' . $fui . '</strong></li><ul>';
    $defuse = true;
    foreach ($file_table as $fii => $fit) {
      $result ='';
      $vlastp = 0;
      $vcount = 1;
      $fup = strpos($fit,$fui,0);
      while ($fup !==false) {
        $test = true;
        //test left boundary
        if ($fup>=0) {
          $tl1 = substr($fit,$fup-1,1);
          if (preg_match('/[a-zA-Z0-9_\']/',$tl1)) $test=false;         
        }
        //test right boundary
        if (($fup+strlen($fut))<strlen($fit)) {
          $tl2 = substr($fit,$fup+strlen($fui),1);
          if (preg_match('/[a-zA-Z0-9_\']/',$tl2)) $test=false;         
        }
        //$test = true;
        if ($test) {
          $part = substr($fit,0,$fup);
          $lines = explode(PHP_EOL,$part);
          $cline=count($lines);
          $lastl = $lines[($cline-1)];
          if ($vlastp != $cline) {
            if ($vcount>1 && $vlastp!=0) $result .=  ' x'.$vcount.', ';
            if ($vcount==1 && $vlastp!=0) $result .=  ', ';
            if ($vcount>0 && $vlastp!=0) $vcount = 1;
            $resultb =  '['. $cline . ']';                                                                     //asumme definition
            if (strpos($lastl,$str,0)===false) $resultb =  '('. $cline . ')';                        //if not 'var'              
            if ((strpos($lastl,'=',0)!==false && strpos($lastl,'=',0)>strpos($lastl,$str,0)))  // if '='
              $resultb =  '('. $cline . ')';
            if (strpos($lastl,'//',0)!==false) $resultb =  '/'. $cline . '/';                           //comments
            $result .= $resultb;
          } else {
          $vcount ++;
          }
          $vlastp = $cline;
        }
        $fup = strpos($fit,$fui,($fup+1));
      }
      if ($vcount>1 && $vlastp!=0) $result .= ' x'.$vcount.', ';
      if ($vcount==1 && $vlastp!=0) $result .=  ', ';
      if ($result!='' && (!in_array($fii,$nodisplay))) echo '<li>'.$fii.' '.$result .'</li>';
      if (strpos($result,'(',0)!==false) $defuse = false;
      if (strpos($result,'(',0)===false && strpos($result,'[',0)===false && strpos($result,'/',0)!==false) $defuse = false;
      if ($result!='' && (in_array($fii,$nodisplay)))  $defuse = false;
    }
    if ($defuse) echo '<li style="color:red">*****</li>';
    echo '</ul></li>';
  }
  echo '</ol>';
}

//search for functions
searchfor($func_table,'function');
echo '<hr>';
//search for vars
searchfor($var_table,'var');
  
?>