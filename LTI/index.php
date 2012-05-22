<?php
/**
 * Created by JetBrains PhpStorm.
 * User: cczsa1
 * Date: 30/04/12
 * Time: 13:02
 * To change this template use File | Settings | File Templates.
 */


require '../include/staff_student_auth.inc';
require '../include/sidebar_menu.inc';
require '../config/index.inc';
require_once '../classes/searchutils.class.php';
require_once  $cfg_web_root . 'include/lti_func.php';


global $cfg_long_date_time;



if(!$lti->valid) {
    $tempvar=$lti->message;
    $message = $string['LTIFAILURE'] . "</p>\n<p>$string[$tempvar]</p>\n";



    access_denied($message, true);
}

if (isset($_REQUEST['paperlinkID'])) {
  //  print_r($_SESSION);
  list($retlookup,$retlookup2) = $_SESSION['postlookup'][$_REQUEST['paperlinkID']];
  unset($_SESSION['postlookup']);

  if ($retlookup > 0) {
      $info = $lti->getResourceKey(1);
      addltiresource($mysqli, $info[0], $info[1], $retlookup, 'paper');
      if($retlookup2!=0)
      {
          addlticontext($mysqli, $info[0], $info[1], $retlookup1);
      }
  }

}


// jump check


//print_r($info);

$info = $lti->getResourceKey(1);

$returned = lookupltiresource($mysqli, $info[0], $info[1]);

//print_r($returned);
if ($returned === false AND !((strpos($userroles, 'SysAdmin') !== false) OR (strpos($userroles, 'Staff') !== false))) {
  echo "<html>\n<head>\n<title>" . $string['unavailablepaper'] . "</title>\n<style>\nbody {font-size:90%; font-family:Arial,sans-serif;background-color:#FCFCFC;color:#575757}\nh1 {font-weight:normal;color:#BF0000;font-size:140%}\n</style>\n</head>\n<body>\n";
  echo "<div style=\"position:absolute; left:10px; top:10px\"><img src=\"{$cfg_root_path}/artwork/access_denied.png\" width=\"48\" height=\"48\" /></div>\n";
  echo "<h1 style=\"margin-left:60px\">" . $string['unavailablepaper'] . "</h1>\n";
  //echo "<hr size=\"1\" align=\"left\" width=\"500\" style=\"margin-left:60px; color:#C0C0C0;  background-color:#C0C0C0; height:1px; border:0px\" />\n<p style=\"margin-left:60px\">". $string['ltifirstlogindesc']. "</p>\n</body>\n</html>";
  exit();
}
elseif ($returned === false) {
  //paper choice display
  $icons = array('formative', 'progress', 'summative', 'survey', 'osce', 'offline', 'peer_review');

  print <<<END
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=$cfg_page_charset" />
  <title>Rogō $cfg_install_type</title>
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <style type="text/css">
  .divider {padding-left:16px; padding-bottom:2px; font-weight:bold}
  .sch {padding-left:32px; text-indent:-20px}
  .greysch {padding-left:12px; color:#808080}
  .mod {padding-left:60px; text-indent:-20px}
  </style>


   $cfg_js_root

  <script language="JavaScript">


    function showHide(sectionID) {
      sectionID = 'block' + sectionID;
      current = (document.getElementById(sectionID).style.display == 'block') ? 'none' : 'block';
      document.getElementById(sectionID).style.display = current;
    }
  </script>
</head>
<body>
<div id="content" class="content" style="font-size:80%">


END;
class personal_folders {
    private $folderlst;
    private $folderlst2;
    private $mysqli;

    function __construct($mysqli) {
    $this->mysqli=$mysqli;
}
    function loadpersonalfolders($userID) {
        // -- Display personal folders --------------------------------------
        if (!isset($teams)){
            $teams = getUserTeams($userID, $this->mysqli);
        }
        $module_sql = '';
        foreach ($teams as $individual_team){
            if (trim($individual_team) != '') $module_sql .= " OR team_name LIKE '%$individual_team%'";
        }

        $resulta = $this->mysqli->prepare("SELECT id, name, team_name, color FROM folders WHERE (ownerID=$userID $module_sql)  AND deleted IS NULL ORDER BY name, id"); //AND name NOT LIKE '%;%'
        $resulta->execute();
        $resulta->bind_result($id, $name, $team_name, $color);
        $resulta->store_result();
        while ($resulta->fetch()) {
            $count=substr_count($name,';');
            $folderlst[]=array($id, $name, $team_name, $color,$count);
        }
        $resulta->close();
$this->folderlst=$folderlst;
    }

    function process() {
    $folderlst=$this->folderlst;
        $parent[0]=0;
        foreach($folderlst as $v) {
            list($id, $name, $team_name, $color,$count)=$v;

 //           print "$name::$id::$count<br>";
            $count1=$count+1;


            $folderlst2[$id]=array($id,$name,$team_name,$color,$count,$parent[$count]);
            $parent[$count1]=$id;
        }
    $this->folderlst2=$folderlst2;
    }

    function dump() {

        print "FOLDERLST<pre>";
        print_r($this->folderlst);
        print "</pre><br>FOLDERLST2<pre>";
        print_r($this->folderlst2);
        print "</pre>";

    }

    function getfolders($folder) {
        $retlst=array();
        foreach($this->folderlst2 as $v) {
            list($id,$name,$team_name,$color,$count,$parent)=$v;
            if($parent==$folder)
            {
                $retlst[]=array($id,$name,$team_name,$color,$count,$parent);
            }
        }
        return($retlst);
    }

    function countfolders($folder) {
        $lst=$this->getfolders($folder);
        return count($lst);
    }

    function gettests($folder)
    {
        $tests = array();

        if ($folder != 0) {


            $mysqli = $this->mysqli;
            $results = $mysqli->prepare("SELECT property_id,paper_title,start_date,end_date,paper_type,paper_ownerID,deleted,crypt_name FROM properties WHERE folder=?");
            $results->bind_param('i', $folder);
            $results->execute();
            $results->bind_result($property_id, $paper_title, $start_date, $end_date, $paper_type, $paper_ownerID, $deleted,$crypt_name);
            $results->store_result();
            if ($results->num_rows() > 0) {


                while ($results->fetch())
                {
                    $tests[] = array($property_id, $paper_title, $start_date, $end_date, $paper_type, $paper_ownerID, $deleted,$crypt_name);

                }
            }
            $results->close();


        }
        return $tests;
    }


    function counttests($folder) {
        $lst=$this->gettests($folder);
        return count($lst);
    }

    function listtree($folder, $block_id, $plk,$level)
    {
global $icons;
        $lst = $this->getfolders($folder);
        foreach ($lst as $v) {
            list($id, $name, $team_name, $color, $count, $parent) = $v;
            $cntfold = $this->countfolders($id);
            $cnttest = $this->counttests($id);
            if (($cnttest + $cntfold) > 0) {
                //subfolder or test
                //              $block_id=$cntshow;
                echo "<div class=\"mod\"><img src=\"../artwork/folder_16.png\" width=\"16\" height=\"16\" alt=\"folder\"border=\"0\" onclick=\"showHide($block_id)\"  /><a href=\"\" style=\"color:blue\" onclick=\"showHide($block_id); return false;\">&nbsp;$name</a></div>\n";



                echo "<div id=\"block$block_id\" style=\"display:none; padding-left:52px\">";

                @ob_flush();
                @flush();

                if ($cntfold > 0) {
                                     list($block_id,$plk)=$this->listtree($id,$block_id+1,$plk,0);
                    //print "folder $id,$block_id,$plk";
                }
                if ($cnttest > 0) {
                    $lst2 = $this->gettests($id);
                    foreach ($lst2 as $v2) {
                        list($property_id, $paper_title, $start_date, $end_date, $paper_type, $paper_ownerID, $deleted,$crypt_name) = $v2;

                        echo "<div style=\"padding-left:52px\"><a href=\"?paperlinkID=" . $plk . "\"><img src=\"../artwork/" . $icons[$paper_type] . "_16.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $paper_type . "\" /></a>&nbsp;<a class=\"recent\"";
                        if (strpos($paper_title, '[deleted') !== false) {
                            echo ' style="color:#808080"';
                        }
                        echo "href=\"?paperlinkID=" . $plk . "\">" . $paper_title . "</a></div>\n";

                        @ob_flush();
                        @flush();


                        // $paper_title ." [" . $start_date_disp . " - " . $end_date_display . "]</a></div>\n";
                        $_SESSION['postlookup'][$plk] = array($crypt_name,0);

                        $plk++;

                    }

                }
                $block_id++;
                echo "</div>";
            } else {
                //no subfolders or tests
                echo "<div class=\"mod\"><img src=\"../artwork/folder_16.png\" width=\"16\" height=\"16\" alt=\"folder\"border=\"0\"   />&nbsp;$name</div>\n";

            }

        }
        @ob_flush();
        @flush();

        return (array($block_id, $plk));

    }


}


    function listtreemodules($mysqli,$moduleid,$block_id,$plk,$flat=false) {
        global $cfg_long_date_time,$icons;
        $query_string = "SELECT DISTINCT crypt_name, paper_type, 'f', paper_title, DATE_FORMAT(start_date,'%Y%m%d%H%i%s') AS start_date, DATE_FORMAT(start_date,'$cfg_long_date_time') AS display_start_date, DATE_FORMAT(end_date,'$cfg_long_date_time') AS display_end_date, title, initials, surname, retired, moduleID  FROM (properties, users) LEFT JOIN papers ON properties.property_id=papers.paper WHERE properties.paper_ownerID=users.id AND (moduleID = '" . $moduleid . "' OR moduleID LIKE '%," . $moduleid . ",%' OR moduleID LIKE '" . $moduleid . ",%' OR moduleID LIKE '%," . $moduleid . "') AND deleted IS NULL AND paper_type IN (0,1,3)  GROUP BY moduleID,paper_title ORDER BY paper_type, paper_title";

        $results2 = $mysqli->prepare($query_string);

        $results2->execute();
        $results2->bind_result($crypt_name, $paper_type, $screens, $paper_title, $start_date, $start_date_disp, $end_date_display, $title, $initials, $surname, $retired, $moduleID);

        $results2->store_result();

        if ($results2->num_rows() > 0) {

            @ob_flush();
            @flush();

            $rt = $results2->num_rows();
            if(!$flat) {
            echo "<div class=\"mod\"><img src=\"../artwork/folder_16.png\" width=\"16\" height=\"16\" alt=\"folder\"border=\"0\" onclick=\"showHide($block_id)\"  /><a href=\"\" style=\"color:blue\" onclick=\"showHide($block_id); return false;\">&nbsp;$moduleid: $paper_title ($rt)</a></div>\n";
            echo "<div id=\"block$block_id\" style=\"display:none\">";
            } else {
                echo "<div>";
            }
            while ($results2->fetch()) {
                echo "<div style=\"padding-left:52px\"><a href=\"?paperlinkID=" . $plk . "\"><img src=\"../artwork/" . $icons[$paper_type] . "_16.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $paper_type . "\" /></a>&nbsp;<a class=\"recent\"";
                if (strpos($paper_title, '[deleted') !== false) {
                    echo ' style="color:#808080"';
                }
                echo "href=\"?paperlinkID=" . $plk . "\">" . $paper_title . "</a></div>\n";
                // $paper_title ." [" . $start_date_disp . " - " . $end_date_display . "]</a></div>\n";
                $_SESSION['postlookup'][$plk] = array($crypt_name,$moduleid);

                $plk++;
            }
            echo "</div>";
            $block_id++;
        }
        else {
            //        echo "<div class=\"mod\"><img src=\"../artwork/folder_16.png\" width=\"16\" height=\"16\" alt=\"folder\" border=\"0\" />&nbsp;$moduleid: $fullname</div>\n";
        }
        $results2->close();

        return (array($block_id, $plk));
    }


    $plk=0;
    $block_id=0;

$personalfolders = new personal_folders($mysqli);
    $personalfolders->loadpersonalfolders($userID);
    $personalfolders->process();

//$personalfolders->dump();

    echo $string['describemodulechoice'];

        $info=$lti->getCourseKey(1);

        $stmt = $mysqli->prepare("SELECT c_internal_id FROM lti_context WHERE  oauth_consumer_key=? AND lti_context_id=?");

        $stmt->bind_param('ss', $info[0], $info[1]);

        $stmt->execute();

        $stmt->store_result();

        $rows = $stmt->num_rows;
    $stmt->bind_result($c_internal_id);

        if($rows>0)
        {
            $stmt->fetch();

    echo "<table border=\"0\" style=\"padding-bottom:5px; width:100%; color:#1E3287\"><tr><td><nobr>" . $string['papersoncurrentmodule'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";


    //if there is a context and therefore a course already selected display that
$moduleid=$c_internal_id;

        list($block_id,$plk)=listtreemodules($mysqli,$moduleid,$block_id,$plk,true);


    }
    $stmt->close();


    echo "<table border=\"0\" style=\"padding-bottom:5px; width:100%; color:#1E3287\"><tr><td><nobr>" . $string['myfolders'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";



list($block_id,$plk)=$personalfolders->listtree(0,0,$plk,0);


  echo "<table border=\"0\" style=\"padding-top:10px; padding-bottom:5px; width:100%; color:#1E3287\"><tr><td><nobr>" . $string['bymodulecode'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";

  $old_faculty = '';
  $old_letter = '';
  $module_block = false;

  $teams = getUserTeams($userID, $mysqli);
  $modlist = SearchUtils::getTeams($teams, $userroles, $userID, $mysqli);

  foreach ($modlist as $value) {
    $moduleid = $value['id'];
    if ($moduleid !== '') {


list($block_id,$plk)=listtreemodules($mysqli,$moduleid,$block_id,$plk);





    }


  }
//  $results->close();

  echo "</div>\n"; // -- End of 'content' div ------------------


  echo "</td></tr></table>";


  exit();
}
elseif ($returned[1] == 'paper') {
  header("location: ../user_index.php?id=" . $returned[0]);
}


exit();
