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

require_once '../classes/dateutils.class.php';

// Calculate what the current academic session is.
$session = date_utils::get_current_academic_year();
?>
<script language="JavaScript">
  function editModule() {
    window.location.href='./edit_module.php?moduleid=' + $('#lineID').val();
  }

  function jumpToModule() {
    window.location = '<?php echo $configObject->get('cfg_root_path'); ?>/folder/details.php?module=' + $('#lineID').val();
  }

  function studentCohort() {
    window.location = '<?php echo $configObject->get('cfg_root_path') ?>/users/search.php?search_surname=&search_username=&student_id=&module=' + $('#lineID').val() + '&calendar_year=<?php echo $session; ?>&students=on&submit=Search&userID=&email=&oldUserID=&tmp_surname=&tmp_courseID=&tmp_yearID=';
  }

  function deleteModule() {
    notice=window.open("../delete/check_delete_module.php?idMod=" + $('#lineID').val() + "","notice","width=420,height=170,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-210,screen.height/2-85);
    if (window.focus) {
      notice.focus();
    }
  }
</script>

<div id="left-sidebar" class="sidebar">
<form name="myform">

<div class="submenuheading" id="currentmodule"><?php echo $string['currentmodule']; ?></div>
<div id="menu1a" style="font-size:90%">
	<div class="menuitem"><a href="add_module.php"><img class="sidebar_icon" src="../artwork/module_icon_16.png" alt="<?php echo $string['createmodule']; ?>" /><?php echo $string['createmodule']; ?></a></div>
	<div class="menuitem"><a href="bulk_import_modules.php"><img class="sidebar_icon" src="../artwork/import_16.gif" alt="" /><?php echo $string['bulkmoduleimport']; ?></a></div>
	<div class="grey menuitem"><img class="sidebar_icon" src="../artwork/edit_grey.png" alt="<?php echo $string['editmodule']; ?>" /><?php echo $string['editmodule']; ?></div>
	<div class="grey menuitem"><img class="sidebar_icon" src="../artwork/red_cross_grey.png" alt="<?php echo $string['deletemodule']; ?>" /><?php echo $string['deletemodule']; ?></div>
	<div class="grey menuitem"><img class="sidebar_icon" src="../artwork/shortcut_16_grey.png" alt="<?php echo $string['modulefolder']; ?>" /><?php echo $string['modulefolder']; ?></div>
	<div class="grey menuitem"><img class="sidebar_icon" src="../artwork/shortcut_16_grey.png" alt="<?php echo $string['studentcohort']; ?>" /><?php echo $string['studentcohort']; ?> (<?php echo $session; ?>)</div>
</div>

<div style="display:none; font-size:90%" id="menu1b">
	<div class="menuitem"><a href="add_module.php"><img class="sidebar_icon" src="../artwork/module_icon_16.png" alt="<?php echo $string['createmodule']; ?>" /><?php echo $string['createmodule']; ?></a></div>
	<div class="menuitem"><a href="bulk_import_modules.php"><img class="sidebar_icon" src="../artwork/import_16.gif" alt="" /><?php echo $string['bulkmoduleimport']; ?></a></div>
	<div class="menuitem" onclick="editModule()"><img class="sidebar_icon" src="../artwork/edit.png" alt="<?php echo $string['editmodule']; ?>" /><a href="" onclick="return false"><?php echo $string['editmodule']; ?></a></div>
	<div class="menuitem" onclick="deleteModule()"><img class="sidebar_icon" src="../artwork/red_cross.png" alt="<?php echo $string['deletemodule']; ?>" /><a href="" onclick="return false"><?php echo $string['deletemodule']; ?></a></div>
	<div class="menuitem" onclick="jumpToModule()"><img class="sidebar_icon" src="../artwork/shortcut_16.png" alt="<?php echo $string['modulefolder']; ?>" /><a href="" onclick="return false"><?php echo $string['modulefolder']; ?></a></div>
	<div class="menuitem" onclick="studentCohort()"><img class="sidebar_icon" src="../artwork/shortcut_16.png" alt="<?php echo $string['studentcohort']; ?>" /><a href="" onclick="return false"><?php echo $string['studentcohort']; ?> (<?php echo $session; ?>)</a></div>
</div>

<br />

<div class="submenuheading" id="currentmodule"><?php echo $string['moduleimports']; ?></div>
<div style="font-size:90%" id="menu2">
	<div class="menuitem"><a href="sms_import_summary.php"><img class="sidebar_icon" src="../artwork/shortcut_16.png" alt="<?php echo $string['importsummary']; ?>" /><?php echo $string['importsummary']; ?></a></div>
</div>

<input type="hidden" name="lineID" id="lineID" value="" />
</form>
</div>
