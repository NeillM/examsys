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
* Task menu for question list.
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require_once $cfg_web_root . 'include/sidebar_menu.inc';
require_once $cfg_web_root . 'include/sidebar_functions.inc';

$modules_array = array();

$my_folders_array = get_folders($staff_modules, $userObject->get_user_ID(), $mysqli);
$full_modules_array = get_modules($staff_modules, $my_folders_array, $modules_array, $mysqli);
$staff_modules_array = get_staff_module_links($staff_modules, $mysqli);
$keywords_array = get_keywords($staff_modules, $userObject->get_user_ID(), $mysqli);
?>
<?php echo $configObject->get('cfg_js_root') ?>
<script src="<?php echo $configObject->get('cfg_root_path') ?>/js/sidebar.js" type="text/javascript"></script>
<script language="JavaScript">
  function getLastID(IDs) {
    var id_list = IDs.split(",");
    last_elm = id_list.length - 1;
    
    return id_list[last_elm];
  }

  function addQID(qID, clearall) {
    if (clearall) {
      $('#questionID').val(',' + qID);
    } else {
      new_value = $('#questionID').val() + ',' + qID;
      $('#questionID').val(new_value);
    }
  }

  function subQID(qID) {
    var tmpq = ',' + qID;
    new_value = $('#questionID').val().replace(tmpq, '');
    $('#questionID').val(new_value);
  }
  
  function clearAll() {
    $('.highlight').removeClass('highlight');
  }
  
  function selQ(questionID, lineID, qType, menuID, evt) {
    $('#menu2a').hide();
    $('#menu2b').hide();
    $('#menu2c').hide();
    $('#menu' + menuID).show();

    if (evt.ctrlKey == false) {
      clearAll();
      $('#link_' + lineID).addClass('highlight');
      addQID(questionID, true);
    } else {
      if ($('#link_' + lineID).hasClass('highlight')) {
        $('#link_' + lineID).removeClass('highlight');
        subQID(questionID);
      } else {
        $('#link_' + lineID).addClass('highlight');
        addQID(questionID, false);
      }
    }
    <?php 
    if (isset($_GET['type']) and $_GET['type'] == '%') {
      echo "$('#qType').val('%');\n";
    } else {
      echo "$('#qType').val(qType);\n";
    }
    ?>
  }
  
  function previewQ() {
    notice=window.open("../question/view_question.php?q_id=" + getLastID($('#questionID').val()) + "","preview","left=10,top=10,width=800,height=600,scrollbars=yes,toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable");
	}
	
  function editQ() {
    var url = '<?php echo $configObject->get('cfg_root_path') ?>/question/edit/index.php?q_id=' + getLastID($('#questionID').val()) + '&paperID=list:' + $('#qType').val() + '&calling=list';
    <?php 
      if (isset($_GET['team']) and $_GET['team'] != '') {
          echo "url = url + '&team=" . $_GET['team'] . "';\n";
      }
      if(isset($_GET['keyword']) and $_GET['keyword'] != '') {
          echo "url = url + '&keyword=" . $_GET['keyword'] . "';\n";
      }
    ?>
    window.location.href = url;
  }

  function deleteQuestion() {
    notice=window.open("<?php echo $configObject->get('cfg_root_path') ?>/delete/check_delete_q_original.php?q_id=" + $('#questionID').val() + "","notice","width=500,height=200,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-250, screen.height/2-100);
    if (window.focus) {
      notice.focus();
    }
  }

  function questionInfo() {
    notice=window.open("<?php echo $configObject->get('cfg_root_path') ?>/question/info.php?q_id=" + getLastID($('#questionID').val()) + "","question_info","width=700,height=600,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-250, screen.height/2-250);
    if (window.focus) {
      notice.focus();
    }
  }

  function copyToPaper() {
    notice=window.open("<?php echo $configObject->get('cfg_root_path') ?>/question/copy_onto_paper.php?q_id=" + $('#questionID').val() + "","notice","width=600,height=" + (screen.height-100) + ",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-300, 20);
    if (window.focus) {
      notice.focus();
    }
  }

  function linkToPaper() {
    notice=window.open("<?php echo $configObject->get('cfg_root_path') ?>/question/link_to_paper.php?q_id=" + $('#questionID').val() + "","properties","width=600,height=" + (screen.height-100) + ",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-300, 20);
    if (window.focus) {
      notice.focus();
    }
  }

  function qOff() {
    $('#menu2a').show();
    $('#menu2b').hide();
    $('#menu2c').hide();
    $('#questionID').val();
    clearAll();
  }
</script>

<div id="left-sidebar" class="sidebar">
<form name="PapersMenu" action="">

<div class="submenuheading" id="banktasks"><?php echo $string['questionbank']; ?></div>

<div id="menu1" style="font-size:90%">
<div class="menuitem cascade" id="type" onclick="showMenu('popup0','banktasks','type',myOptions0,myURLs0,event); return false;"><a href="#"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/question_list_icon.gif" alt="List Papers" /><?php echo $string['questionsbytype']; ?></a></div>
<?php
  if (count($staff_modules) == 0) {
    echo "<div class=\"menuitem greycascade\" id=\"team\"><img class=\"sidebar_icon\" src=\"" . $configObject->get('cfg_root_path') . "/artwork/question_list_icon_grey.gif\" alt=\"" . $string['questionsbyteam'] . "\" />" . $string['questionsbyteam'] . "</div>\n";
  } else {
    echo "<div class=\"menuitem cascade\" id=\"team\" onclick=\"showMenu('popup1','banktasks','team',myOptions1,myURLs1,event); return false;\"><a href=\"#\"><img class=\"sidebar_icon\" src=\"" . $configObject->get('cfg_root_path') . "/artwork/question_list_icon.gif\" alt=\"List Teams\" />" . $string['questionsbyteam'] . "</a></div>\n";
  }
  if (count($keywords_array) == 0) {
    echo "<div class=\"menuitem greycascade\" id=\"keyword\"><img class=\"sidebar_icon\" src=\"{$configObject->get('cfg_root_path')}/artwork/question_list_icon_grey.gif\" alt=\"List Keywords\" />" . $string['questionsbykeyword'] . "</div>\n";
  } else {
    echo "<div class=\"menuitem cascade\" id=\"keyword\" onclick=\"showMenu('popup2','banktasks','keyword',myOptions2,myURLs2,event); return false;\"><a href=\"#\"><img class=\"sidebar_icon\" src=\"{$configObject->get('cfg_root_path')}/artwork/question_list_icon.gif\" alt=\"List Keywords\" />" . $string['questionsbykeyword'] . "</a></div>\n";
  }
?>
<div class="menuitem cascade" id="search" onclick="showMenu('popup3','banktasks','search',myOptions3,myURLs3,event); return false;"><a href="#"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/xmag.png" alt="Search" /><?php echo $string['search']; ?></a></div>
<div class="menuitem cascade" id="newquestion" onclick="showMenu('popup4','banktasks','newquestion',myOptions4,myURLs4,event); return false;"><a href="#"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/new_question_menu_icon.gif" alt="newquestion" /><?php echo $string['createnewquestion']; ?></a></div>
</div>

<br />

<div class="submenuheading" id="banktasks"><?php echo $string['currentquestiontasks']; ?></div>

<div style="font-size:90%" id="menu2a">
	<div class="grey menuitem"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/small_play_grey.png" alt="<?php echo $string['quickview']; ?>" /><?php echo $string['quickview']; ?></div>
	<div class="grey menuitem"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/edit_grey.png" alt="<?php echo $string['editquestion']; ?>" /><?php echo $string['editquestion']; ?></div>
	<div class="grey menuitem"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/information_icon_grey.gif" alt="<?php echo $string['information']; ?>" /><?php echo $string['information']; ?></div>
	<div class="grey menuitem"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/copy_icon_grey.gif" alt="<?php echo $string['copyontopaperx']; ?>" /><?php echo $string['copyontopaperx']; ?></div>
	<div class="grey menuitem"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/link_grey.png" alt="<?php echo $string['linktopaperx']; ?>" /><?php echo $string['linktopaperx']; ?></div>
	<div class="grey menuitem"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/red_cross_grey.png" alt="<?php echo $string['deletequestion']; ?>" /><?php echo $string['deletequestion']; ?></div>
</div>

<div style="font-size:90%" id="menu2b">
	<div class="menuitem"><a id="preview" onclick="previewQ(); return false;" href="#"><img class="sidebar_icon" src="../artwork/small_play.png" alt="<?php echo $string['quickview']; ?>" /><?php echo $string['quickview']; ?></a></div>
	<div class="menuitem"><a id="edit" onclick="editQ(); return false;" href="#"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/edit.png" alt="<?php echo $string['editquestion']; ?>" /><?php echo $string['editquestion']; ?></a></div>
	<div class="menuitem"><a id="information" href="#" onclick="questionInfo(); return false;"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/information_icon.gif" alt="<?php echo $string['information']; ?>" /><?php echo $string['information']; ?></a></div>
	<div class="menuitem"><a id="copy" href="#" onclick="copyToPaper(); return false;"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/copy_icon.gif" alt="<?php echo $string['copyontopaperx']; ?>" /><?php echo $string['copyontopaperx']; ?></a></div>
	<div class="menuitem"><a id="link" href="#" onclick="linkToPaper(); return false;"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/link.png" alt="<?php echo $string['linktopaperx']; ?>" /><?php echo $string['linktopaperx']; ?></a></div>
	<div class="menuitem"><a id="delete" href="#" onclick="deleteQuestion(); return false;"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/red_cross.png" alt="<?php echo $string['deletequestion']; ?>" /><?php echo $string['deletequestion']; ?></a></div>
</div>

<div style="font-size:90%" id="menu2c">
	<div class="menuitem"><a id="preview" onclick="previewQ(); return false;" href="#"><img class="sidebar_icon" src="../artwork/small_play.png" alt="<?php echo $string['quickview']; ?>" /><?php echo $string['quickview']; ?></a></div>
	<div class="menuitem"><a id="edit" onclick="editQ(); return false;" href="#"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/edit.png" alt="<?php echo $string['editquestion']; ?>" /><?php echo $string['editquestion']; ?></a></div>
	<div class="menuitem"><a id="information" href="#" onclick="questionInfo(); return false;"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/information_icon.gif" alt="<?php echo $string['information']; ?>" /><?php echo $string['information']; ?></a></div>
	<div class="menuitem"><a id="copy" href="#" onclick="copyToPaper(); return false;"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/copy_icon.gif" alt="<?php echo $string['copyontopaperx']; ?>" /><?php echo $string['copyontopaperx']; ?></a></div>
	<div class="menuitem"><a id="link" href="#" onclick="linkToPaper(); return false;"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/link.png" alt="<?php echo $string['linktopaperx']; ?>" /><?php echo $string['linktopaperx']; ?></a></div>
	<div class="menuitem"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/red_cross_grey.png" alt="<?php echo $string['deletequestion']; ?>" /><?php echo $string['deletequestion']; ?></div>
</div>

<input type="hidden" name="questionID" id="questionID" value="" />
<input type="hidden" name="qType" id="qType" value="" />
<input type="hidden" name="oldQuestionID" id="oldQuestionID" value="" />
<input type="hidden" name="team" id="team" value="<?php if(isset($_GET['team'])) echo $_GET['team'];?>" />
</form>

</div>

<?php
  //makeMenu($full_modules_array);
  makeMenu(array($string['info']=>$configObject->get('cfg_root_path') . '/question/list.php?type=info',$string['keyword_based']=>$configObject->get('cfg_root_path') . '/question/list.php?type=keyword',$string['random']=>$configObject->get('cfg_root_path') . '/question/list.php?type=random','-'=>'-',$string['area']=>$configObject->get('cfg_root_path') . '/question/list.php?type=area',$string['calculation']=>$configObject->get('cfg_root_path') . '/question/list.php?type=enhancedcalc',$string['dichotomous']=>$configObject->get('cfg_root_path') . '/question/list.php?type=dichotomous',$string['extmatch']=>$configObject->get('cfg_root_path') . '/question/list.php?type=extmatch',$string['blank']=>$configObject->get('cfg_root_path') . '/question/list.php?type=blank',$string['hotspot']=>$configObject->get('cfg_root_path') . '/question/list.php?type=hotspot',$string['labelling']=>$configObject->get('cfg_root_path') . '/question/list.php?type=labelling',$string['likert']=>$configObject->get('cfg_root_path') . '/question/list.php?type=likert',$string['matrix']=>$configObject->get('cfg_root_path') . '/question/list.php?type=matrix',$string['mcq']=>$configObject->get('cfg_root_path') . '/question/list.php?type=mcq',$string['mrq']=>$configObject->get('cfg_root_path') . '/question/list.php?type=mrq',$string['rank']=>$configObject->get('cfg_root_path') . '/question/list.php?type=rank',$string['sct']=>$configObject->get('cfg_root_path') . '/question/list.php?type=sct',$string['textbox']=>$configObject->get('cfg_root_path') . '/question/list.php?type=textbox', $string['true_false']=>$configObject->get('cfg_root_path') . '/question/list.php?type=true_false'));
  makeMenu($staff_modules_array);
  makeMenu($keywords_array);
  makeMenu(array($string['questions']=>$configObject->get('cfg_root_path') . '/question/search.php',$string['papers']=>$configObject->get('cfg_root_path') . '/paper/search.php',$string['people']=>$configObject->get('cfg_root_path') . '/users/search.php'));
  makeMenu(array($string['info']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=info",$string['keyword_based']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=keyword_based",$string['random']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=random","-"=>"-",$string['calculation']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=enhancedcalc",$string['dichotomous']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=dichotomous",$string['extmatch']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=extmatch",$string['blank']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=blank",$string['hotspot']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=hotspot",$string['labelling']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=labelling",$string['likert']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=likert",$string['matrix']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=matrix",$string['mcq']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=mcq",$string['mrq']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=mrq",$string['rank']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=rank",$string['sct']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=sct",$string['textbox']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=textbox",$string['true_false']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=true_false"));

  hideMenuScript($menuNo);
?>