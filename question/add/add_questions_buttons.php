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

  require '../../include/staff_auth.inc';
?>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title>Buttons</title>

  <link rel="stylesheet" type="text/css" href="../../css/body.css" />
  <style type="text/css">
    html {height:100%}
    body {height:100%; background-color:#F0F0F0; margin-top:4px; margin-bottom:2px; margin-left:4px; margin-right:4px}
		.tab {height:25px; cursor:default}
  </style>

	<script type="text/javascript" src="../../js/jquery-1.6.1.min.js"></script>
  <script language="JavaScript">
    var selectedButton = 'unused';
  
    function buttonclick(sectionID, scriptName) {
      parent.qlist.iframeurl.location = scriptName;
      parent.qlist.previewurl.location = 'preview_default.php'
      
      $('#button_unused').css('backgroundColor','white');
      $('#button_alphabetic').css('backgroundColor','white');
      $('#button_keywords').css('backgroundColor','white');
      $('#button_status').css('backgroundColor','white');
      $('#button_papers').css('backgroundColor','white');
      $('#button_team').css('backgroundColor','white');
      $('#button_search').css('backgroundColor','white');

      $('#button_'+sectionID).css('backgroundColor','#FFBD69');
      selectedButton = sectionID;
    }

    function buttonover(buttonID) {
      if (buttonID != selectedButton) {
        $('#button_'+buttonID).css('backgroundColor','#FFE7A2');
      }
    }

    function buttonout(buttonID) {
      if (buttonID != selectedButton) {
        $('#button_'+buttonID).css('backgroundColor','white');
      }
    }
  </script>
</head>
<body>

<table cellspacing="0" cellpadding="0" style="font-size:90%; width:126px; height:99%; background-color:white; border:1px solid #828790">
<tr><td style="vertical-align:top; text-align:center" valign="top">

<table cellspacing="0" cellpadding="0" style="padding:2px; font-size:90%; width:144px; background-white; text-align:left">
<tr><td id="button_unused" class="tab" style="background-color:#FFBD69" onmouseover="buttonover('unused')" onmouseout="buttonout('unused')" onclick="buttonclick('unused','add_questions_list.php?type=unused')">&nbsp;<?php echo $string['myunused']; ?></td></tr>
<tr><td id="button_alphabetic" class="tab" onmouseover="buttonover('alphabetic')" onmouseout="buttonout('alphabetic')" onclick="buttonclick('alphabetic','add_questions_list.php?type=all')">&nbsp;<?php echo $string['allmyquestions']; ?></td></tr>
<tr><td id="button_keywords" class="tab" onmouseover="buttonover('keywords')" onmouseout="buttonout('keywords')" onclick="buttonclick('keywords','add_questions_keywords_frame.php')">&nbsp;<?php echo $string['bykeywords']; ?></td></tr>
<tr><td id="button_status" class="tab" onmouseover="buttonover('status')" onmouseout="buttonout('status')" onclick="buttonclick('status','add_questions_by_status.php')">&nbsp;<?php echo $string['bystatus']; ?></td></tr>
<tr><td id="button_papers" class="tab" onmouseover="buttonover('papers')" onmouseout="buttonout('papers')" onclick="buttonclick('papers','add_questions_paper_types.php')">&nbsp;<?php echo $string['bypaper']; ?></td></tr>
<tr><td id="button_team" class="tab" onmouseover="buttonover('team')" onmouseout="buttonout('team')" onclick="buttonclick('team','add_questions_team_list.php')">&nbsp;<?php echo $string['byteam']; ?></td></tr>
<tr><td id="button_search" class="tab" onmouseover="buttonover('search')" onmouseout="buttonout('search')" onclick="buttonclick('search','add_questions_list_search.php')">&nbsp;<?php echo $string['search']; ?></td></tr>
</table>

</td></tr>
</table>

</body>
</html>
