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
* Options menu bar for adacemic session admin screen.
* 
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2015 The University of Nottingham
*/

?>
<script>
    function editSession() {
        window.location.href='./edit_academic_session.php?year=' + $('#lineID').val();
    }

    function deleteSession() {
        notice=window.open("../delete/check_delete_academic_session.php?year=" + $('#lineID').val() + "","sessions","width=450,height=180,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
        notice.moveTo(screen.width/2-225,screen.height/2-90);
        if (window.focus) {
          notice.focus();
        }
    }
</script>

<div id="left-sidebar" class="sidebar">
<form name="myform" autocomplete="off">
<div id="menu1a">

	<div class="menuitem"><a href="add_academic_session.php"><img class="sidebar_icon" src="../artwork/add_sessions_16.png" alt="<?php echo $string['createsession'] ?>" /><?php echo $string['createsession'] ?></a></div>
	<div class="grey menuitem"><img class="sidebar_icon" src="../artwork/edit_grey.png" width="16" height="16" alt="<?php echo $string['editsession']; ?>" /><?php echo $string['editsession'] ?></div>
        <div class="grey menuitem"><img class="sidebar_icon" src="../artwork/red_cross_grey.png" width="16" height="16" alt="" /><?php echo $string['deletesession'] ?></div>

</div>

<div style="display:none" id="menu1b">
	<div class="menuitem"><a href="add_academic_session.php"><img class="sidebar_icon" src="../artwork/add_sessions_16.png" alt="<?php echo $string['createsession']; ?>" /><?php echo $string['createsession'] ?></a></div>
	<div class="menuitem"><a href="#" onclick="editSession(); return false;"><img class="sidebar_icon" src="../artwork/edit.png" alt="<?php echo $string['editsession'] ?>" /><?php echo $string['editsession'] ?></a></div>
        <div class="menuitem"><a href="#" onclick="deleteSession(); return false;"><img class="sidebar_icon" src="../artwork/red_cross.png" alt="" /><?php echo $string['deletesession'] ?></a></div>
</div>

<input type="hidden" id="lineID" name="lineID" value="" />
</form>
</div>


