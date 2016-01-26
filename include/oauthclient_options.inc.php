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
 * @copyright Copyright (c) 2015 onwards The University of Nottingham
 * @package
 */


?>
<script>
  function editoauthkeys() {
    window.location.href = './edit_oauthclient.php?client=' + $('#lineID').val();
  }

  function deleteoauthkeys() {
    notice = window.open("../../delete/check_delete_oauthclient.php?client=" + $('#lineID').val() + "", "LTIkeyss", "width=520,height=170,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width / 2 - 270, screen.height / 2 - 85);
    if (window.focus) {
      notice.focus();
    }
  }
</script>

<div id="left-sidebar" class="sidebar">
  <form name="myform" autocomplete="off">
    <br/>

		<div id="menu1a">
			<div class="menuitem"><a href="add_oauthclient.php"><img class="sidebar_icon" src="../../artwork/lti_key_16.png" alt="<?php echo $string['addoauthclient'] ?>" /><?php echo $string['addoauthclient'] ?></a></div>
			<div class="grey menuitem"><img class="sidebar_icon" src="../../artwork/edit_grey.png" alt="<?php echo $string['editoauthclient'] ?>" /><?php echo $string['editoauthclient'] ?></div>
			<div class="grey menuitem"><img class="sidebar_icon" src="../../artwork/red_cross_grey.png" alt="<?php echo $string['deleteoauthclient'] ?>" /><?php echo $string['deleteoauthclient'] ?></div>
		</div>

		<div style="display:none" id="menu1b">
			<div class="menuitem"><a href="add_oauthclient.php"><img class="sidebar_icon" src="../../artwork/lti_key_16.png" alt="<?php echo $string['addoauthclient'] ?>" /><?php echo $string['addoauthclient'] ?></a></div>
			<div class="menuitem"><a href="#" onclick="editoauthkeys(); return false;"><img class="sidebar_icon" src="../../artwork/edit.png" alt="<?php echo $string['editoauthclient'] ?>" /><?php echo $string['editoauthclient'] ?></a></div>
			<div class="menuitem"><a href="#" onclick="deleteoauthkeys(); return false;"><img class="sidebar_icon" src="../../artwork/red_cross.png" alt="<?php echo $string['deleteoauthclient'] ?>" /><?php echo $string['deleteoauthclient'] ?></a></div>
		</div>

    <input type="hidden" id="lineID" name="lineID" value=""/>
  </form>
</div>
