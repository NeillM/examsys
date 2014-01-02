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
<script language="JavaScript">
  function displayDetails() {
    details=window.open("/admin/sys_error_details.php?errorID=" + $('#lineID').val() + "","properties","width=900,height=800,left="+(screen.width/2-450)+",top="+(screen.height/2-400)+",scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    if (window.focus) {
      details.focus();
    }
  }
</script>

<div id="left-sidebar" class="sidebar">

<br />

<div style="font-size:90%" id="menu1a">
	<div class="grey menuitem"><img class="sidebar_icon" src="../artwork/information_icon_grey.gif" alt="<?php echo $string['displaydetails']; ?>" /><?php echo $string['displaydetails']; ?></div>
</div>

<div style="font-size:90%; display:none" id="menu1b">
	<div class="menuitem" onclick="displayDetails()"><img class="sidebar_icon" src="../artwork/information_icon.gif" alt="<?php echo $string['displaydetails']; ?>" /><a onclick="return false"><?php echo $string['displaydetails']; ?></a></div>
</div>

<form name="errorsMenu">
<input type="hidden" name="lineID" id="lineID" value="" />
</form>

</div>