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
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

?>
<script language="JavaScript">
  function editTemplate() {
    document.location.href='/admin/edit_ebel_grid.php?id=' + document.getElementById('divID').value;
  }
</script>

<div id="left-sidebar" class="sidebar">
<form name="myform">
<br />

<table cellpadding="4" cellspacing="0" border="0" style="font-size:90%">
<tr><td>

<table style="display:block" cellpadding="3" cellspacing="0" border="0" id="menu1a">
<tr><td><a href="" onclick="addFaculty(); return false;"><img src="../artwork/grid_16.gif" width="16" height="16" alt="" border="0" /></a></td><td class="menuitem"><a href="add_ebel_grid.php"><?php echo $string['createnewgrid']; ?></a></td></tr>
<tr><td><img src="../artwork/edit_grey.png" width="16" height="16" alt="" border="0" /></td><td class="greymenuitem"><?php echo $string['editgrid']; ?></td></tr>
</table>

<table style="display:none" cellpadding="3" cellspacing="0" border="0" id="menu1b">
<tr><td><a href="" onclick="addTemplate(); return false;"><img src="../artwork/grid_16.gif" width="16" height="16" alt="" border="0" /></a></td><td class="menuitem"><a href="add_ebel_grid.php"><?php echo $string['createnewgrid']; ?></a></td></tr>
<tr><td><img onclick="editTemplate()" src="../artwork/edit.png" width="16" height="16" alt="" border="0" /></td><td class="menuitem"><a href="" onclick="editTemplate(); return false;"><?php echo $string['editgrid']; ?></a></td></tr>
</table>
</td></tr>
</table>

</td></tr>
</table>

<input type="hidden" name="divID" id="divID" value="" />
</form>
</div>
