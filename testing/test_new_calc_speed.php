<html>
<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once '../include/load_config.php';

require '../question/enhancedcalculation.class.php';

$settings = '{"strictdisplay":"on","strictzeros":true,"dp":"2","tolerance_full":"2","fulltoltyp":"%","tolerance_partial":"5","parttoltyp":"#","marks_partial":"0.5","marks_incorrect":"0","marks_correct":"1","marks_unit":0,"show_units":true,"answers":[{"formula":"$A - $B","units":"mm"},{"formula":"($A - $B)\/1000","units":"m"}],"vars":{"$A":{"min":"1000","max":"2000","inc":"5","dec":"0"},"$B":{"min":"1000","max":"100000","inc":"5","dec":"0"}}}';
$uans[1] = '{"vars":{"$A":"182.0","$B":"88.0"},"uans":"94.00","uansunit":"mm"}';
$uans[2] = '{"vars":{"$A":"182.0","$B":"88.0"},"uans":"92.00","uansunit":"mm"}';
$uans[3] = '{"vars":{"$A":"182.0","$B":"88.0"},"uans":"90.00","uansunit":"mm"}';
$uans[4] = '{"vars":{"$A":"182.0","$B":"88.0"},"uans":"70.00","uansunit":"mm"}';

//$configObj = new Config();
$q = new EnhancedCalculation($configObject);
$q->setsettings($settings);

$mtime = microtime(); 
$mtime = explode(' ', $mtime); 
$mtime = $mtime[1] + $mtime[0]; 
$starttime = $mtime; 

$i = 0;
while($i <= 1000 ) {
  $q->setuseranswer($uans[array_rand($uans)]);
  $q->caculateUserMark();
  echo "$i ::" . $q->qmark . "<br/>";
  $i++;
}

$mtime = microtime(); 
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0]; 
$endtime = $mtime; 
$totaltime = ($endtime - $starttime); 
echo '<h1>Script execution took ' .$totaltime. ' seconds</h1>';
?>
</html>