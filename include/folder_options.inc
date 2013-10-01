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

include_once '../classes/dateutils.class.php';
include_once '../classes/moduleutils.class.php';
require_once '../include/sidebar_functions.inc';

$modules_array = array();
//$staff_modules is populated in staff_auth.inc
$my_folders_array = get_folders($staff_modules, $userObject->get_user_ID(), $mysqli);
$full_modules_array = get_modules($staff_modules, $my_folders_array, $modules_array, $mysqli);
$staff_modules_array = get_staff_module_links($staff_modules, $mysqli);
$keywords_array = get_keywords($staff_modules, $userObject->get_user_ID(), $mysqli);

?>
<div id="left-sidebar" class="sidebar">
<?php
  if ($folder != '') {       // Currently in a folder
?>
<div class="submenuheading"><?php echo $string['foldertasks']; ?></div>
<div style="font-size:90%">
<?php

  if ($folder_ownerID == $userObject->get_user_ID() ) {
    echo '<div class="menuitem" onclick="folderProperties()"><img class="sidebar_icon" src="' . $configObject->get('cfg_root_path') . '/artwork/properties_icon.gif" alt="Properties" /><a href="#" onclick="return false">' . $string['folderproperties'] . '</a></div>';
    echo '<div class="menuitem"><a href="' . $configObject->get('cfg_root_path') . '/folder/details.php?module=' . $module . '&folder=' . $folder . '&newfolder=y"><img class="sidebar_icon" src="' . $configObject->get('cfg_root_path') . '/artwork/folder_16.png" alt="New Folder" />' . $string['makesubfolder'] . '</a></div>';
    echo '<div class="menuitem" onclick="deleteFolder()"><img class="sidebar_icon" src="' . $configObject->get('cfg_root_path') . '/artwork/red_cross.png" alt="Delete Folder" /><a href="#" onclick="return false">' . $string['deletefolder'] . '</a></div>';
  } else {
    echo '<div class="grey menuitem"><img class="sidebar_icon" src="' . $configObject->get('cfg_root_path') . '/artwork/properties_icon_grey.gif" alt="Properties" />' . $string['folderproperties'] . '</div>';
    echo '<div class="grey menuitem"><img class="sidebar_icon" src="' . $configObject->get('cfg_root_path') . '/artwork/folder_16_grey.png" alt="New Folder" />' . $string['makesubfolder'] . '</div>';
    echo '<div class="grey menuitem"><img class="sidebar_icon" src="' . $configObject->get('cfg_root_path') . '/artwork/red_cross_grey.png" alt="Delete Folder" />' . $string['deletefolder'] . '</div>';
  }
?>
</div>

<br />

<?php
  } else {

    if ($module != '') {
    ?>
      <div class="submenuheading"><?php echo $string['module']; ?></div>
      <div style="font-size:90%">
    <?php

    if ($userObject->has_role('SysAdmin')) {
      echo "<div class=\"menuitem\"><a href=\"../admin/edit_module.php?moduleid=$module\"><img class=\"sidebar_icon\" src=\"{$configObject->get('cfg_root_path')}/artwork/admin_icon_16.gif\" alt=\"cog\" />" . $string['editpropertiessysadmin'] . "</a></div>";
    }
    if (strpos($module_details['checklist'],'mapping') !== false ) {     // Curriculum mapping switched on for the module
      echo "<div class=\"menuitem\"><a href=\"../mapping/sessions_list.php?module=" . $module . "\"><img class=\"sidebar_icon\" src=\"{$configObject->get('cfg_root_path')}/artwork/module_icon_16.png\" alt=\"modules\" />" . $string['manageobjectives'] . "</a></div>\n";
    }
    echo "<div class=\"menuitem\"><a href=\"../folder/list_keywords.php?module=$module\"><img class=\"sidebar_icon\" src=\"{$configObject->get('cfg_root_path')}/artwork/key.png\" alt=\"key\" />" . $string['managekeywords'] . "</a></div>";
    echo "<div class=\"menuitem\"><a href=\"../folder/list_ref_material.php?module=$module\"><img class=\"sidebar_icon\" src=\"{$configObject->get('cfg_root_path')}/artwork/ref_16.png\" alt=\"key\" />" . $string['referencematerial'] . "</a></div>";
    echo '<div class="menuitem"><a href="../users/load_users_metadata.php?module=' . $_GET['module'] . '"><img class="sidebar_icon" src="' . $configObject->get('cfg_root_path') . '/artwork/import_16.gif" alt="Import Metadata" />' . $string['importstudentmetadata'] . '</a></div>';
  ?>
  </div>

  <br />

<?php
    }
  }
?>

<div class="submenuheading" id="papertasks"><?php echo $string['papertasks']; ?></div>
<div style="font-size:90%">
<?php
  if (count($staff_modules) > 0) {
    echo "<div class=\"menuitem\" onclick=\"newPaper()\"><img class=\"sidebar_icon\" src=\"{$configObject->get('cfg_root_path')}/artwork/new_paper_icon_16.gif\" alt=\"New Paper\" /><a href=\"#\" onclick=\"return false\">" . $string['createnewpaper'] . "</a></div>\n";
  } else {
    echo "<div class=\"grey menuitem\"><img class=\"sidebar_icon\" src=\"{$configObject->get('cfg_root_path')}/artwork/new_paper_icon_16.gif\" alt=\"New Paper\" />" . $string['createnewpaper'] . "</div>\n";
  }

?>
<div class="menuitem cascade" id="listpapers" onclick="showMenu('popup0','papertasks','listpapers',myOptions0,myURLs0,event)"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/list_papers_icon_16.gif" alt="List Papers" /><a href="#" onclick="return false"><?php echo $string['listpapers']; ?></a></div>
</div>

<br />

<div class="submenuheading" id="banktasks"><?php echo $string['questionbank']; ?></div>
<div style="font-size:90%">
<div class="menuitem cascade" id="type" onclick="showMenu('popup1','banktasks','type',myOptions1,myURLs1,event)"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/question_list_icon.gif" alt="List Papers" /><a href="#" onclick="return false"><?php echo $string['questionsbytype']; ?></a></div>
<div class="menuitem cascade" id="team" onclick="showMenu('popup2','banktasks','team',myOptions2,myURLs2,event)"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/question_list_icon.gif" alt="List Teams" /><a href="#" onclick="return false"><?php echo $string['questionsbyteam']; ?></a></div>
<div class="menuitem cascade" id="keyword" onclick="showMenu('popup3','banktasks','keyword',myOptions3,myURLs3,event)"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/question_list_icon.gif" alt="List Keywords" /><a href="#" onclick="return false"><?php echo $string['questionsbykeyword']; ?></a></div>
<div class="menuitem cascade" id="search" onclick="showMenu('popup4','banktasks','search',myOptions4,myURLs4,event)"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/xmag.png" alt="Search" /><a href="#" onclick="return false"><?php echo $string['search']; ?></a></div>
<div class="menuitem cascade" id="newquestion" onclick="showMenu('popup5','banktasks','newquestion',myOptions5,myURLs5,event)"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/new_question_menu_icon.gif" alt="newquestion" /><a href="#" onclick="return false"><?php echo $string['createnewquestion']; ?></a></div>
</div>

<?php
if ($folder != '') {
?>
<br />
<br />
<?php
}
?>

</div>
<?php
  makeMenu($full_modules_array);
  makeMenu(array($string['info']=>$configObject->get('cfg_root_path') . '/question/list.php?type=info',$string['keyword_based']=>$configObject->get('cfg_root_path') . '/question/list.php?type=keyword',$string['random']=>$configObject->get('cfg_root_path') . '/question/list.php?type=random','-'=>'-',$string['calculation']=>$configObject->get('cfg_root_path') . '/question/list.php?type=calculation',$string['dichotomous']=>$configObject->get('cfg_root_path') . '/question/list.php?type=dichotomous',$string['extmatch']=>$configObject->get('cfg_root_path') . '/question/list.php?type=extmatch',$string['blank']=>$configObject->get('cfg_root_path') . '/question/list.php?type=blank',$string['flash']=>$configObject->get('cfg_root_path') . '/question/list.php?type=flash',$string['hotspot']=>$configObject->get('cfg_root_path') . '/question/list.php?type=hotspot',$string['labelling']=>$configObject->get('cfg_root_path') . '/question/list.php?type=labelling',$string['likert']=>$configObject->get('cfg_root_path') . '/question/list.php?type=likert',$string['matrix']=>$configObject->get('cfg_root_path') . '/question/list.php?type=matrix',$string['mcq']=>$configObject->get('cfg_root_path') . '/question/list.php?type=mcq',$string['mrq']=>$configObject->get('cfg_root_path') . '/question/list.php?type=mrq',$string['rank']=>$configObject->get('cfg_root_path') . '/question/list.php?type=rank',$string['sct']=>$configObject->get('cfg_root_path') . '/question/list.php?type=sct',$string['textbox']=>$configObject->get('cfg_root_path') . '/question/list.php?type=textbox', $string['true_false']=>$configObject->get('cfg_root_path') . '/question/list.php?type=true_false'));
  makeMenu($staff_modules_array);
  makeMenu($keywords_array);
  if (isset($module_details)) {
    makeMenu(array($string['questions']=>$configObject->get('cfg_root_path') . '/question/search.php', $string['papers']=>$configObject->get('cfg_root_path') . '/paper/search.php', $string['people']=>$configObject->get('cfg_root_path') . '/users/search.php', '-'=>'-', $string['sudentson'] . $module_details['moduleid'] . ' (' . date_utils::get_current_academic_year() . ')'=>$configObject->get('cfg_root_path') . '/users/search.php?submit=Search&team=' . $_GET['module'] . '&calendar_year=' . date_utils::get_current_academic_year() . '&students=on&search_username=&student_id='));
  } else {
    makeMenu(array($string['questions']=>$configObject->get('cfg_root_path') . '/question/search.php', $string['papers']=>$configObject->get('cfg_root_path') . '/paper/search.php', $string['people']=>$configObject->get('cfg_root_path') . '/users/search.php'));
  }
  makeMenu(array($string['info']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=info",$string['keyword_based']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=keyword_based",$string['random']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=random","-"=>"-",$string['calculation']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=enhancedcalc",$string['dichotomous']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=dichotomous",$string['extmatch']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=extmatch",$string['blank']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=blank",$string['flash']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=flash",$string['hotspot']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=hotspot",$string['labelling']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=labelling",$string['likert']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=likert",$string['matrix']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=matrix",$string['mcq']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=mcq",$string['mrq']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=mrq",$string['rank']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=rank",$string['sct']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=sct",$string['textbox']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=textbox",$string['true_false']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=true_false"));

  hideMenuScript($menuNo);
?>