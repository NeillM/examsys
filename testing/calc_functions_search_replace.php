<?php
	require '../include/sysadmin_auth.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html>
<head>
<title>Calculation functions search</title>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=UTF-8"/>
</head>

<body>
<?php

function dopowrep($correct) {

  unset($left);
  unset($right);
  unset($place);
  unset($convert);
  unset($base);
  unset($position);
  unset($newdatacontent);
  $base='';
  $end='';
  $newdatacontent[2]=0;
  $funcount[$correct]=1;
  $newdatacontent[0]=$correct;
  $newdatacontent[1]=$correct;

  $newdatacontent[3]='';
  $newdatacontent[4]='';
  $newdatacontent[5]='';
  $newdatacontent[6]='';
  $newdatacontent[7]='';
  $newdatacontent[8]='';
  $newdatacontent[9]='';
  $newdatacontent[10]='';

  if($correct!='') {
    $position=stripos($correct,'pow');
    if($position!==false) {
      $newdatacontent[2]=1;

      $left=substr($correct,0,$position);
      $right=substr($correct,$position+4);
      $place=0;
      $convert=false;
      if(substr($right,$place,1) ==' ') {
        $place++;
      }
      $newdatacontent[10]=substr($right,$place,1);
      if(substr($right,$place,1) =='$') {
        //variable
        $pos=strpos($right,',');
        $base=substr($right,0,$pos);
        $end=$pos+1;
        $convert=true;
        $newdatacontent[9]='$';
      }
      if(substr($right,$place,1) =='(') {
        //variable
        $pos=strpos($right,'),');
        $base=substr($right,0,$pos);
        $end=$pos+2;
        $convert=true;
        $newdatacontent[9]=')';
      }
      $pos=strpos($right,'),');
      if(is_numeric((substr($right,0,$pos)))) {
        $base=substr($right,0,$pos);
        $convert=true;
        $end=$pos+1;
        $newdatacontent[9]='numb';
      }
      $pos=strpos($right,',');
      if(is_numeric((substr($right,0,$pos)))) {
        $base=substr($right,0,$pos);
        $convert=true;
        $end=$pos+1;
        $newdatacontent[9]='numb2';
      }

      if($convert===true) {
        $newdatacontent[3]='Y';
      }
      else {
        $newdatacontent[3]='N';
      }
      $newdatacontent[4]=$left;
      $newdatacontent[5]=$right;
      $newdatacontent[6]=$base;
      $newdatacontent[7]=$end;
      $newdatacontent[8]=$pos;


      if($convert===true) {
        $newdatacontent[1]=$left . '(' . $base . ')^(' . substr($right,$end) .')';
      }

    }


    $return=$newdatacontent[1];
if(stripos($newdatacontent[1],'pow') !==false and $convert!==false) {
//  print $newdatacontent[1] . '  ';
  $return=dopowrep($newdatacontent[1]);
}


    $newdata[]=$newdatacontent;

    return $return;
  }
}
	$details = array();
	$funcount = array();
	$phpfunc = array('abs','acos','acosh','asin','asinh','atan2','atan','atanh','ceil','cos','cosh','deg2rad','exp','expm1','floor','fmod','log10','log1p','log','max','min','pi','pow','round','sin','sinh','sqrt','tan','tanh');
	
  $result = $mysqli->prepare("SELECT correct FROM options INNER JOIN questions ON options.o_id=questions.q_id WHERE questions.q_type = 'calculation';");
  $result->execute();
  $result->store_result();
  $result->bind_result($correct);
  while ($result->fetch()) {
    $details[$correct]=1;
  }
  $result->close();

$cnt=0;
	foreach($details as $correct=>$valuecor){
  //  $dones++;
$ret=0;
    if(stripos($correct,'pow')!==false) {
  $ret=1;
      $dones++;
}
    $ans=dopowrep($correct);
    $ret1=0;
    if(stripos($ans,'pow')!==false) {
      $failed++;
  $ret1=1;
}
    $newdata[]=array($correct,$ans,$ret,$ret1,'a','a','a','a','a','a','a');
	}

/*
print count($details) . '      ' . $dones . '   ' . $failed;
	echo '<table border="1"><tbody>';
	foreach ($newdata as $key => $val) {
		$col = '#A00'; if (in_array($key,$phpfunc)) $col = '#0A0';
		if ($key!='' and $val[2]==1) echo "<tr style='color:$col;'><td>$key</td><td>$val[0]</td><td>$val[1]</td><td>$val[2]</td><td>$val[3]</td><td>$val[4]</td><td>$val[5]</td><td>$val[6]</td><td>$val[7]</td><td>$val[8]</td><td>$val[9]</td><td>$val[10]</td></tr>";
	}
	echo '</tbody></table>';

print "<br><br><br>";
foreach ($newdata as $key => $val) {
  if ($key!='' and $val[2]==1) {
    print $val[0] . "<br>" . $val[1] . "<br><br>";

  }

}
*/

$result = $mysqli->prepare("SELECT q_id,settings from  questions WHERE questions.q_type = 'calculation';");
$result->execute();
$result->store_result();
$result->bind_result($qid,$settings);
while ($result->fetch()) {
 $qids[$qid]=$settings;
}

var_dump($qids);
$vars=array('$A','$B','$C','$D','$E','$F','$G','$H','$I','$J','$K','$L');

foreach($qids as $qid=>$settings) {
  $result = $mysqli->prepare("SELECT option_text,correct,id_num from  options WHERE o_id=? order by id_num;");
  $result->bind_param('i',$qid);
  $result->execute();
  $result->store_result();
  $result->bind_result($optiontext,$correct,$id_num);
  $settings=json_decode($settings,true);
  $changed=false;
  $loc=0;
  while ($result->fetch()) {
    $changed=true;
    $opts=explode(',',$optiontext);
$settings['vars'][$vars[$loc]]['min']=$opts[0];
$settings['vars'][$vars[$loc]]['max']=$opts[1];
$settings['vars'][$vars[$loc]]['inc']=$opts[2];
$settings['vars'][$vars[$loc]]['dec']=$opts[3];
$settings['answers']['formula']=$correct;
    $settings['answers']['units']=$settings['units'];

$loc++;

var_dump($optiontext);
  }
  unset($settings['units']);
  var_dump($settings);
  print "<BR>";
}


?>
</body>
</html>
