<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require_once $cfg_web_root . 'classes/generalmenuitemdata.class.php';

?>
<div id="left-sidebar" class="sidebar">
<?php
  $folder_ownerID = folder_utils::get_ownerID($folder, $mysqli);
  $isOwner = ($folder_ownerID == $userObject->get_user_ID());
  $render = new render($configObject);
  $menuItemData = new GeneralMenuItemData($string);
  $folderPropertiesItem = $menuItemData->getFolderPropertiesItem($isOwner);
  $render->render($folderPropertiesItem, $string, 'sidebar/menuitem.html');
  $makeSubfolderItem = $menuItemData->getMakeSubfolderItem($isOwner, $module, $folder);
  $render->render($makeSubfolderItem, $string, 'sidebar/menuitem.html');
  $deleteFolderItem = $menuItemData->getDeleteFolderItem($isOwner);
  $render->render($deleteFolderItem, $string, 'sidebar/menuitem.html');

  $searchMenuItem = $menuItemData->getSearchMenuItem();
  $render->render($searchMenuItem, $string, 'sidebar/menuitem.html');
?>
</div>
<?php
  makeMenu([$string['questions'] => "{$configObject->get('cfg_root_path')}/question/search.php",$string['papers'] => "{$configObject->get('cfg_root_path')}/paper/search.php",$string['people'] => "{$configObject->get('cfg_root_path')}/users/search.php"]);
