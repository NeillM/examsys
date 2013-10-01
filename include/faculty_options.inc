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

?>
<script language="JavaScript">
  function addFaculty() {
    facultywin=window.open("/admin/add_faculty.php","faculties","width=350,height=120,left="+(screen.width/2-175)+",top="+(screen.height/2-60)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    if (window.focus) {
      facultywin.focus();
    }
  }

  function editFaculty() {
    facultywin=window.open("/admin/edit_faculty.php?facultyID=" + $('#lineID').val() + "","faculties","width=350,height=120,left="+(screen.width/2-175)+",top="+(screen.height/2-60)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    if (window.focus) {
      facultywin.focus();
    }
  }
  
  function deleteFaculty() {
    notice=window.open("../delete/check_delete_faculty.php?facultyID=" + $('#lineID').val() + "","faculties","width=420,height=170,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-210,screen.height/2-85);
    if (window.focus) {
      notice.focus();
    }
  }
</script>

<div id="left-sidebar" class="sidebar">
<form name="myform">
<br />

<div id="menu1a" style="font-size:90%">
	<div class="menuitem" onclick="addFaculty(); return false;"><img class="sidebar_icon" src="../artwork/faculty_16.png" alt="" /><a href="" onclick="return false"><?php echo $string['createfaculty']; ?></a></div>
	<div class="grey menuitem"><img class="sidebar_icon" src="../artwork/edit_grey.png" alt="" /><?php echo $string['editfaculty']; ?></div>
	<div class="grey menuitem"><img class="sidebar_icon" src="../artwork/red_cross_grey.png" alt="" /><?php echo $string['deletefaculty']; ?></div>
</div>

<div style="display:none; font-size:90%" id="menu1b">
	<div class="menuitem" onclick="addFaculty()"><img class="sidebar_icon" src="../artwork/faculty_16.png" alt="" /><a href="" onclick="return false"><?php echo $string['createfaculty']; ?></a></div>
	<div class="menuitem" onclick="editFaculty()"><img class="sidebar_icon" onclick="editFaculty()" src="../artwork/edit.png" alt="" /><a href="" onclick="return false"><?php echo $string['editfaculty']; ?></a></div>
	<div class="menuitem" onclick="deleteFaculty()"><img class="sidebar_icon" onclick="deleteCourse()" src="../artwork/red_cross.png" alt="" /><a href="" onclick="return false"><?php echo $string['deletefaculty']; ?></a></div>
</div>

<input type="hidden" id="lineID" name="lineID" value="" />
</form>
</div>
