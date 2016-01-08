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
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @version 1.0
* @copyright Copyright (c) 2016 The University of Nottingham
*/

?>
<script>
  function viewDetails() {
    document.location.href='./edit_campuses.php?campus=' + $('#lineID').val();
    $('#menu1a').show();
    $('#menu1b').hide();
  }
  
  function deleteCampus() {
    notice=window.open("../../delete/check_delete_campus.php?campus=" + $('#lineID').val() + "","notice","width=500,height=200,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-250,screen.height/2-100);
    if (window.focus) {
      notice.focus();
    }

  }
</script>

<div id="left-sidebar" class="sidebar">
<form name="campusform">
<div id="menu1a">
	<div class="menuitem"><a href="add_campuses.php"><img class="sidebar_icon" src="../../artwork/new_campus_16.png" alt="" /><?php echo $string['createnewcampus'] ?></a></div>
	<div class="grey menuitem"><img class="sidebar_icon" src="../../artwork/edit_grey.png" alt="" /><?php echo $string['viewcampus'] ?></div>
	<div class="grey menuitem"><img class="sidebar_icon" src="../../artwork/red_cross_grey.png" alt="" /><?php echo $string['deletecampus'] ?></div>
</div>

<div style="display:none" id="menu1b">
	<div class="menuitem"><a href="add_campuses.php"><img class="sidebar_icon" src="../../artwork/new_campus_16.png" alt="" /><?php echo $string['createnewcampus'] ?></a></div>
	<div class="menuitem" onclick="viewDetails()"><img class="sidebar_icon" src="../../artwork/edit.png" alt="" /><a href="" onclick="return false;"><?php echo $string['viewcampus'] ?></a></div>
	<div class="menuitem" onclick="deleteCampus()"><img class="sidebar_icon" src="../../artwork/red_cross.png" alt="" /><a href="" onclick="return false;"><?php echo $string['deletecampus'] ?></a></div>
</div>

<input type="hidden" id="lineID" name="lineID" value=""/>
</form>
</div>
