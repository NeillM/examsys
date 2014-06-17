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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/
?>
<script>
  function editAnnouncement() {
    window.location.href='./edit_announcement.php?announcementid=' + $('#lineID').val();
  }
  
  function deleteAnnouncement() {
    notice=window.open("../delete/check_delete_announcement.php?announcementID=" + $('#lineID').val() + "","announcements","width=420,height=170,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-210,screen.height/2-85);
    if (window.focus) {
      notice.focus();
    }
  }

</script>

<div id="left-sidebar" class="sidebar">
<form name="myform">
<br />

<div style="font-size:90%" id="menu1a">
	<div class="menuitem"><a href="add_announcement.php"><img class="sidebar_icon" src="../artwork/megaphone_16.png" alt="<?php echo $string['createannouncement']; ?>" /><?php echo $string['createannouncement']; ?></a></div>
	<div class="grey menuitem"><img class="sidebar_icon" src="../artwork/edit_grey.png" alt="<?php echo $string['editannouncement']; ?>" /><?php echo $string['editannouncement']; ?></div>
	<div class="grey menuitem"><img class="sidebar_icon" src="../artwork/red_cross_grey.png" alt="<?php echo $string['deleteannouncement']; ?>" /><?php echo $string['deleteannouncement']; ?></div>
</div>

<div style="font-size:90%; display:none" id="menu1b">
	<div class="menuitem"><a href="add_announcement.php"><img class="sidebar_icon" src="../artwork/megaphone_16.png" alt="<?php echo $string['createannouncement']; ?>" /><?php echo $string['createannouncement']; ?></a></div>
	<div class="menuitem" onclick="editAnnouncement()"><img class="sidebar_icon" src="../artwork/edit.png" alt="<?php echo $string['editannouncement']; ?>" /><a href="" onclick="return false;"><?php echo $string['editannouncement']; ?></a></div>
	<div class="menuitem" onclick="deleteAnnouncement()"><img class="sidebar_icon" src="../artwork/red_cross.png" alt="<?php echo $string['deleteannouncement']; ?>" /><a href="" onclick="return false;"><?php echo $string['deleteannouncement']; ?></a></div>
</div>

<input type="hidden" name="lineID" id="lineID" value="" />
</form>
</div>
