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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

?><div id="left-sidebar" class="sidebar" style="overflow:hidden">
<br />

<div id="general" style="margin-left:-8px">
	<?php
		if ($userObject->has_role('SysAdmin')) {
			echo '<div class="menuitem"><a href="' . $configObject->get('cfg_root_path') . '/admin/index.php"><img class="sidebar_icon" src="' . $configObject->get('cfg_root_path') . '/artwork/admin_icon_16.gif" alt="Admin" />' . $string['admintools'] . '</a></div>';
		} elseif ($userObject->has_role('Admin')) {
			echo '<div class="menuitem"><a href="' . $configObject->get('cfg_root_path') . '/admin/calendar.php?calyear=' . date("Y") . '#' . date("n") . '"><img class="sidebar_icon" src="' . $configObject->get('cfg_root_path') . '/artwork/shortcut_calendar_icon.png" alt="Admin" />' . $string['calendar'] . '</a></div>';
		}
	?>
	<div class="menuitem"><a href="<?php echo $configObject->get('cfg_root_path') ?>/users/search.php"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/small_user_icon.gif" alt="Users" /><?php echo $string['usermanagement'] ?></a></div>
	<div class="menuitem"><a href="<?php echo $configObject->get('cfg_root_path') ?>/index.php?newfolder=y"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/folder_16.png" alt="New Folder" /><?php echo $string['makeafolder'] ?></a></div>
	<div class="menuitem"><a href="<?php echo $configObject->get('cfg_root_path') ?>/folder/list_keywords.php"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/key.png" alt="key" /><?php echo $string['mypersonalkeywords'] ?></a></div>
</div>

<div style="font-size:180%; padding-top:40px" id="papertasks"><?php echo $string['recent'] ?></div>

<?php
  $icons = array('formative', 'progress', 'summative', 'survey', 'osce', 'offline', 'peer_review');

  // -- Display top 10 recent papers ----------------------------------
  echo '<ul type="square">';
  $result = $mysqli->prepare("SELECT paperID, paper_title FROM (recent_papers, properties) WHERE userID = ? AND recent_papers.paperID = properties.property_id ORDER BY accessed DESC LIMIT 10");
  $result->bind_param('i', $userObject->get_user_ID());
  $result->execute();
  $result->bind_result($paperID, $paper_title);
  $result->store_result();
  while ($result->fetch()) {
    echo "<li class=\"recent\"><a ";
    if (strpos($paper_title,'[deleted') !== false) echo ' style="color:#808080"';
    echo "href=\"" . $configObject->get('cfg_root_path') . "/paper/details.php?paperID=$paperID\">$paper_title</a></li>\n";
  }
  $result->close();
  echo '</ul>';

?>
<div style="position:absolute; bottom:15px; text-align:center; width:90%; cursor:pointer; color:#295AAD; font-weight:bold" id="displaycredits">Rog&#333; <?php echo $configObject->get('rogo_version'); ?> - &copy; 2014</div>

</div>
