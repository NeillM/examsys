<?php
// This file is part of Rogo
//
// Rogo is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogo is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogo.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* Sidebar menu of the Rogō homepage
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require_once 'sidebar_functions.inc';

$modules_array = array();
//$staff_modules is populated in staff_auth.inc
$my_folders_array = get_folders($staff_modules, $userObject->get_user_ID(), $mysqli);
$full_modules_array = get_modules($staff_modules, $my_folders_array, $modules_array, $mysqli);
$staff_modules_array = get_staff_module_links($staff_modules, $mysqli);
$keywords_array = get_keywords($staff_modules, $userObject->get_user_ID(), $mysqli);

?><div id="left-sidebar" class="sidebar">
<br />

<div id="general" style="font-size:90%; margin-left:-8px">
	<div class="menuitem" onclick="launchHelp(1)"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/small_help_icon.gif" alt="Help" /><a href="#" onclick="launchHelp(1); return false;"><?php echo $string['helpsupport']; ?></a></div>
	<?php
		if ($userObject->has_role('SysAdmin')) {
			echo '<div class="menuitem"><a href="' . $configObject->get('cfg_root_path') . '/admin/index.php"><img class="sidebar_icon" src="' . $configObject->get('cfg_root_path') . '/artwork/admin_icon_16.gif" alt="Admin" />' . $string['admintools'] . '</a></div>';
		} elseif ($userObject->has_role('Admin')) {
			echo '<div class="menuitem"><a href="' . $configObject->get('cfg_root_path') . '/admin/calendar.php?calyear=' . date("Y") . '#' . date("n") . '"><img class="sidebar_icon" src="' . $configObject->get('cfg_root_path') . '/artwork/shortcut_calendar_icon.png" alt="Admin" />' . $string['calendar'] . '</a></div>';
		}
	?>
	<div class="menuitem"><a href="<?php echo $configObject->get('cfg_root_path') ?>/users/search.php"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/small_user_icon.gif" alt="Users" /></a></td><td class="menuitem"><a href="<?php echo $configObject->get('cfg_root_path') ?>/users/search.php"><?php echo $string['usermanagement']; ?></a></div>
	<div class="menuitem"><a href="<?php echo $configObject->get('cfg_root_path') ?>/staff/index.php?newfolder=y"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/folder_16.png" alt="New Folder" /></a></td><td class="menuitem"><a href="<?php echo $configObject->get('cfg_root_path') ?>/staff/index.php?newfolder=y"><?php echo $string['makeafolder']; ?></a></div>
	<div class="menuitem"><a href="<?php echo $configObject->get('cfg_root_path') ?>/folder/list_keywords.php"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/key.png" alt="key" /></a></td><td class="menuitem"><a href="<?php echo $configObject->get('cfg_root_path') ?>/folder/list_keywords.php"><?php echo $string['mypersonalkeywords']; ?></a></div>
</div>

<br />

<div class="submenuheading" id="papertasks"><?php echo $string['papertasks']; ?></div>
	<div style="font-size:90%">
	<?php
		if (count($staff_modules) > 0 or $userObject->has_role(array('Admin','SysAdmin'))) {    // Allow SysAdmin and Admin users not on any team to still create papers.
			echo "<div class=\"menuitem\" onclick=\"newPaper()\"><img class=\"sidebar_icon\" src=\"{$configObject->get('cfg_root_path')}/artwork/new_paper_icon_16.gif\" alt=\"New Paper\" /><a href=\"#\" onclick=\"newPaper(); return false;\">" . $string['createnewpaper'] . "</a></div>\n";
		} else {
			echo "<div class=\"grey menuitem greycascade\"><img class=\"sidebar_icon\" src=\"{$configObject->get('cfg_root_path')}/artwork/new_paper_icon_16.gif\" alt=\"New Paper\" />" . $string['createnewpaper'] . "</div>\n";
		}
	?>
	<div class="menuitem cascade" id="listpapers" onclick="showMenu('popup0','papertasks','listpapers',myOptions0,myURLs0,event); return false;"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/list_papers_icon_16.gif" alt="List Papers" /><a href="#" onclick="return false;"><?php echo $string['listpapers']; ?></a></div>
</div>

<br />

<div class="submenuheading" id="banktasks"><?php echo $string['questionbanktasks']; ?></div>
<div style="font-size:90%">
	<div class="menuitem cascade" id="type" onclick="showMenu('popup1','banktasks','type',myOptions1,myURLs1,event)"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/question_list_icon.gif" alt="List Papers" /><a href="#" onclick="return false"><?php echo $string['questionsbytype']; ?></div>
	<?php
		if (count($staff_modules) == 0) {
			echo "<div class=\"grey menuitem greycascade\"><img class=\"sidebar_icon\" src=\"" . $configObject->get('cfg_root_path') . "/artwork/question_list_icon_grey.gif\" alt=\"List Teams\" />" . $string['questionsbyteam'] . "</div>\n";
		} else {
			echo "<div class=\"menuitem cascade\" id=\"team\" onclick=\"showMenu('popup2','banktasks','team',myOptions2,myURLs2,event)\"><a href=\"#\"><img class=\"sidebar_icon\" src=\"" . $configObject->get('cfg_root_path') . "/artwork/question_list_icon.gif\" alt=\"List Teams\" /><a href=\"#\" onclick=\"return false\">" . $string['questionsbyteam'] . "</a></div>\n";
		}
		if (count($keywords_array) == 0) {
			echo "<div class=\"grey menuitem greycascade\" id=\"keyword\"><img class=\"sidebar_icon\" src=\"{$configObject->get('cfg_root_path')}/artwork/question_list_icon_grey.gif\" alt=\"List Keywords\" />" . $string['questionsbykeyword'] . "</div>\n";
		} else {
			echo "<div class=\"menuitem cascade\" id=\"keyword\" onclick=\"showMenu('popup3','banktasks','keyword',myOptions3,myURLs3,event)\"><img class=\"sidebar_icon\" src=\"{$configObject->get('cfg_root_path')}/artwork/question_list_icon.gif\" alt=\"List Keywords\" /><a href=\"#\" onclick=\"return false\">" . $string['questionsbykeyword'] . "</a></div>\n";
		}
	?>
	<div class="menuitem cascade" id="search" onclick="showMenu('popup4','banktasks','search',myOptions4,myURLs4,event)"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/xmag.png" alt="Search" /><a href="#" onclick="return false"><?php echo $string['search']; ?></a></div>
	<div class="menuitem cascade" id="newquestion" onclick="showMenu('popup5','banktasks','newquestion',myOptions5,myURLs5,event)"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/new_question_menu_icon.gif" alt="newquestion" /><a href="#" onclick="return false"><?php echo $string['createnewquestion']; ?></a></div>
</div>

<div style="position:absolute; bottom:15px; text-align:center; width:90%; cursor:pointer; font-size:90%"><a style="color:blue" href="#" onclick="displayCredits(); return false;">Rog&#333; <?php echo $configObject->get('rogo_version'); ?> - &copy; 2013</a></div>
</div>

<?php
  // Create the sub-menus.
  makeMenu($full_modules_array);
  makeMenu(array($string['info']=>$configObject->get('cfg_root_path') . '/question/list.php?type=info',$string['keyword_based']=>$configObject->get('cfg_root_path') . '/question/list.php?type=keyword',$string['random']=>$configObject->get('cfg_root_path') . '/question/list.php?type=random','-'=>'-',$string['area']=>$configObject->get('cfg_root_path') . '/question/list.php?type=area',$string['calculation']=>$configObject->get('cfg_root_path') . '/question/list.php?type=enhancedcalc',$string['dichotomous']=>$configObject->get('cfg_root_path') . '/question/list.php?type=dichotomous',$string['extmatch']=>$configObject->get('cfg_root_path') . '/question/list.php?type=extmatch',$string['blank']=>$configObject->get('cfg_root_path') . '/question/list.php?type=blank',$string['hotspot']=>$configObject->get('cfg_root_path') . '/question/list.php?type=hotspot',$string['labelling']=>$configObject->get('cfg_root_path') . '/question/list.php?type=labelling',$string['likert']=>$configObject->get('cfg_root_path') . '/question/list.php?type=likert',$string['matrix']=>$configObject->get('cfg_root_path') . '/question/list.php?type=matrix',$string['mcq']=>$configObject->get('cfg_root_path') . '/question/list.php?type=mcq',$string['mrq']=>$configObject->get('cfg_root_path') . '/question/list.php?type=mrq',$string['rank']=>$configObject->get('cfg_root_path') . '/question/list.php?type=rank',$string['sct']=>$configObject->get('cfg_root_path') . '/question/list.php?type=sct',$string['textbox']=>$configObject->get('cfg_root_path') . '/question/list.php?type=textbox', $string['true_false']=>$configObject->get('cfg_root_path') . '/question/list.php?type=true_false'));
  makeMenu($staff_modules_array);
  makeMenu($keywords_array);
  makeMenu(array($string['questions']=>$configObject->get('cfg_root_path') . '/question/search.php',$string['papers']=>$configObject->get('cfg_root_path') . '/paper/search.php',$string['people']=>$configObject->get('cfg_root_path') . '/users/search.php'));
  makeMenu(array($string['info']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=info",$string['keyword_based']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=keyword_based",$string['random']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=random","-"=>"-",$string['area']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=area",$string['calculation']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=enhancedcalc",$string['dichotomous']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=dichotomous",$string['extmatch']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=extmatch",$string['blank']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=blank",$string['hotspot']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=hotspot",$string['labelling']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=labelling",$string['likert']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=likert",$string['matrix']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=matrix",$string['mcq']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=mcq",$string['mrq']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=mrq",$string['rank']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=rank",$string['sct']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=sct",$string['textbox']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=textbox",$string['true_false']=>"{$configObject->get('cfg_root_path')}/question/edit/index.php?type=true_false"));

  hideMenuScript($menuNo);
?>