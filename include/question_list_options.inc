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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require_once $cfg_web_root . 'include/sidebar_menu.inc';
require_once $cfg_web_root . 'include/sidebar_functions.inc';
require_once $cfg_web_root . 'include/errors.inc';
require_once $cfg_web_root . 'include/mapping.inc';

$module = check_var('module', 'GET', true, false, true);

$add_member = false;

if (!isset($module_details) && $_GET['module'] != '0') {
  $module_details = module_utils::get_full_details_by_ID($module, $mysqli);
}
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
  
  function selQ(questionID, lineID, evt) {
    $('#menu2a').hide();
    $('#menu2b').hide();
    $('#menu2c').show();

    highlight_line(questionID, lineID, evt);
  }
  
  function selL(questionID, lineID, evt) {
    $('#menu2a').hide();
    $('#menu2c').hide();
    $('#menu2b').show();

    highlight_line(questionID, lineID, evt);
  }
  
  function highlight_line(questionID, lineID, evt) {
    if (evt.ctrlKey == false) {
      clearAll();
      $('#l' + lineID).addClass('highlight');
      addQID(questionID, true);
    } else {
      if ($('#l' + lineID).hasClass('highlight')) {
        $('#l' + lineID).removeClass('highlight');
        subQID(questionID);
      } else {
        $('#l' + lineID).addClass('highlight');
        addQID(questionID, false);
      }
    }
    
  }
  
  function previewQ() {
    notice=window.open("../question/view_question.php?q_id=" + getLastID($('#questionID').val()) + "","preview","left=10,top=10,width=800,height=600,scrollbars=yes,toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable");
	}
	
  function edit() {
    var url = '<?php echo $configObject->get('cfg_root_path') ?>/question/edit/index.php?q_id=' + getLastID($('#questionID').val()) + '&calling=list';
    <?php 
      if (isset($_GET['team']) and $_GET['team'] != '') {
          echo "url = url + '&team=" . $_GET['team'] . "';\n";
      }
      if(isset($_GET['keyword']) and $_GET['keyword'] != '') {
          echo "url = url + '&keyword=" . $_GET['keyword'] . "';\n";
      }
    ?>
    window.location.href = url;
    return false;
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
    <?php
    $url = $configObject->get('cfg_root_path') . '/question/copy_onto_paper.php?q_id=" + $(\'#questionID\').val()';
    if (isset($_GET['type']) and $_GET['type'] != '') {
      $url .= ' + "&type=' . $_GET['type'];
    }
    ?>
    notice=window.open("<?php echo $url ?>","notice","width=600,height=" + (screen.height-100) + ",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-300, 20);

    if (window.focus) {
      notice.focus();
    }
  }

  function getSelectedOutcomes() {
    var outcomes = {};
    $('.check_type:checked').each(function() {
      var ids = $(this).data('ids');
      if (ids != '') {
        ids = ids.toString().split(',');
        for (var i = 0; i < ids.length; i++) {
          outcomes[ids[i].toString()] = $(this).val();
        }
      }
    });
    return JSON.stringify(outcomes);
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
  
  function check_checkboxes() {
    $(".q").hide();
    $('input[type=checkbox].check_type:checked').each(function () {
      q_type = (typeof $(this).data('ids') == 'undefined') ? $(this).val() : $(this).data('ids').toString().replace(',', ',.');
      $("." + q_type).show();
      if (!$('#check_locked').attr('checked')) {
        $("." + q_type + '.locked').hide();
      }
    });

    var n = $(".q:visible").length;
    $("#q_count").text(n.toLocaleString());
  }
  
  function resizeList() {
    var offset = $('#list').offset();
    winH = ($(window).height() - offset.top) - 2;

    $('#list').css('height', winH + 'px');
  }

  var hide_unmapped_obs = function() {
    $('.check_type').parent().hide();
    $('.check_type').each(function() {
      var ids = $(this).data('ids');
      ids = ids.toString().split(',');
      for (var i = 0; i < ids.length; i++) {
        if ($('.q.' + ids[i]).length > 0) {
          $(this).parent().show();
          break;
        }
      };
    });
  }

  $(document).ready(function() {
    $(".check_type").click(function() {
      check_checkboxes();
    });
    
    $("#check_locked").click(function() {
      check_checkboxes();
    });

<?php
if ($_GET['type'] == 'outcome') {
?>
    hide_unmapped_obs();
<?php
}
?> 
    check_checkboxes();
    resizeList();
      
    $(window).resize(function(){
      resizeList();
    });
  });
</script>

<div id="left-sidebar" class="sidebar">
<form name="PapersMenu" action="">

<br />

<div id="menu2a" style="font-size:90%">
	<div class="grey menuitem"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/small_play_grey.png" alt="<?php echo $string['quickview']; ?>" /><?php echo $string['quickview']; ?></div>
	<div class="grey menuitem"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/edit_grey.png" alt="<?php echo $string['editquestion']; ?>" /><?php echo $string['editquestion']; ?></div>
	<div class="grey menuitem"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/information_icon_grey.gif" alt="<?php echo $string['information']; ?>" /><?php echo $string['information']; ?></div>
	<div class="grey menuitem"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/copy_icon_grey.gif" alt="<?php echo $string['copyontopaperx']; ?>" /><?php echo $string['copyontopaperx']; ?></div>
	<div class="grey menuitem"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/link_grey.png" alt="<?php echo $string['linktopaperx']; ?>" /><?php echo $string['linktopaperx']; ?></div>
	<div class="grey menuitem"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/red_cross_grey.png" alt="<?php echo $string['deletequestion']; ?>" /><?php echo $string['deletequestion']; ?></div>
</div>

<div id="menu2b" style="font-size:90%">
	<div class="menuitem"><a id="preview" onclick="previewQ(); return false;" href="#"><img class="sidebar_icon" src="../artwork/small_play.png" alt="<?php echo $string['quickview']; ?>" /><?php echo $string['quickview']; ?></a></div>
	<div class="menuitem"><a id="edit" onclick="editQ(); return false;" href="#"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/edit.png" alt="<?php echo $string['editquestion']; ?>" /><?php echo $string['editquestion']; ?></a></div>
	<div class="menuitem"><a id="information" href="#" onclick="questionInfo(); return false;"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/information_icon.gif" alt="<?php echo $string['information']; ?>" /><?php echo $string['information']; ?></a></div>
	<div class="menuitem"><a id="copy" href="#" onclick="copyToPaper(); return false;"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/copy_icon.gif" alt="<?php echo $string['copyontopaperx']; ?>" /><?php echo $string['copyontopaperx']; ?></a></div>
	<div class="menuitem"><a id="link" href="#" onclick="linkToPaper(); return false;"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/link.png" alt="<?php echo $string['linktopaperx']; ?>" /><?php echo $string['linktopaperx']; ?></a></div>
	<div class="menuitem"><a id="delete" href="#" onclick="deleteQuestion(); return false;"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/red_cross.png" alt="<?php echo $string['deletequestion']; ?>" /><?php echo $string['deletequestion']; ?></a></div>
</div>

<div id="menu2c" style="font-size:90%">
	<div class="menuitem"><a id="preview" onclick="previewQ(); return false;" href="#"><img class="sidebar_icon" src="../artwork/small_play.png" alt="<?php echo $string['quickview']; ?>" /><?php echo $string['quickview']; ?></a></div>
	<div class="menuitem"><a id="edit" onclick="editQ(); return false;" href="#"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/edit.png" alt="<?php echo $string['editquestion']; ?>" /><?php echo $string['editquestion']; ?></a></div>
	<div class="menuitem"><a id="information" href="#" onclick="questionInfo(); return false;"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/information_icon.gif" alt="<?php echo $string['information']; ?>" /><?php echo $string['information']; ?></a></div>
	<div class="menuitem"><a id="copy" href="#" onclick="copyToPaper(); return false;"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/copy_icon.gif" alt="<?php echo $string['copyontopaperx']; ?>" /><?php echo $string['copyontopaperx']; ?></a></div>
	<div class="menuitem"><a id="link" href="#" onclick="linkToPaper(); return false;"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/link.png" alt="<?php echo $string['linktopaperx']; ?>" /><?php echo $string['linktopaperx']; ?></a></div>
	<div class="menuitem"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/red_cross_grey.png" alt="<?php echo $string['deletequestion']; ?>" /><?php echo $string['deletequestion']; ?></div>
</div>

<div style="font-size:90%">
<div class="menuitem" id="search"><a href="search.php"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/xmag.png" alt="Search" /><?php echo $string['search']; ?></a></div>
<div class="menuitem cascade" id="newquestion" onclick="showMenu('popup0','banktasks','newquestion',myOptions0,myURLs0,event); return false;"><a href="#"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/new_question_menu_icon.gif" alt="newquestion" /><?php echo $string['createnewquestion']; ?></a></div>
</div>

<?php
switch ($_GET['type']) {
  case 'all':
    $display_type = 'All Questions';
    break;    
  case 'type':
    $display_type = 'Types';
    break;
  case 'status':
    $display_type = 'Status';
    break;
  case 'bloom':
    $display_type = 'Bloom\'s Taxonomy';
    break;
  case 'keyword':
    $display_type = 'Keywords';
    break;
  case 'performance':
    $display_type = 'Performance';
    break;
  case 'outcome':
    $display_type = 'Learning Outcome';
    break;
}
echo "<div style=\"padding-top:30px; font-size:170%\">$display_type</div>\n";
?>
<div style="padding-left:12px; height:78%; font-size:90%; overflow:auto" id="list">
<?php
  echo "<div style=\"margin-bottom:15px\"><input type=\"checkbox\" id=\"check_locked\" value=\"locked\" checked=\"checked\" /><label style=\"background-color:#F0C01E\">&nbsp;show Locked questions&nbsp;</label></div>\n";
  if (isset($_GET['type'])) {
    $subtypes = $qbank->get_categories($type, $module_details['moduleid']);
    
    foreach($subtypes as $id=>$subtype) {
      $data = '';

      if ($_GET['type'] == 'all') {
        $checked = ' checked';
      } elseif (isset($_GET['subtype']) and $_GET['subtype'] == $id) {
        $checked = ' checked';
      } else {
        $checked = '';
      }

      if ($_GET['type'] == 'outcome') {
        $ids = $subtype['ids'];
        $data = ' data-ids="' . implode(',', $ids) . '"';

        $subtype = $subtype['label'];
      }
      
      echo "<div><input type=\"checkbox\" class=\"check_type\" id=\"check_$id\" value=\"$id\" $checked $data/><label for=\"check_$id\">$subtype</lable></div>\n";
    }
  }
?>
</div>

<input type="hidden" name="questionID" id="questionID" value="" />
<input type="hidden" name="qType" id="qType" value="" />
<input type="hidden" name="oldQuestionID" id="oldQuestionID" value="" />
<input type="hidden" name="team" id="team" value="<?php if(isset($_GET['team'])) echo $_GET['team'];?>" />

</form>
</div>
<?php

  makeMenu(array($string['info']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=info",$string['keyword_based']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=keyword_based",$string['random']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=random","-"=>"-",$string['area']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=area",$string['calculation']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=enhancedcalc",$string['dichotomous']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=dichotomous",$string['extmatch']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=extmatch",$string['blank']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=blank",$string['hotspot']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=hotspot",$string['labelling']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=labelling",$string['likert']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=likert",$string['matrix']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=matrix",$string['mcq']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=mcq",$string['mrq']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=mrq",$string['rank']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=rank",$string['sct']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=sct",$string['textbox']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=textbox",$string['true_false']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=true_false"));

  hideMenuScript($menuNo);
?>