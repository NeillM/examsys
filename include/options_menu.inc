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
 * Sidebar menu of the ExamSys homepage
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require_once $cfg_web_root . 'classes/generalmenuitemdata.class.php';

?><div id="left-sidebar" class="sidebar" style="overflow:hidden">
<?php
if (!$userObject->has_role('Standards Setter')) {
    ?>
<div id="general">
    <?php
    $render = new render($configObject);
    $menuItemData = new GeneralMenuItemData($string);
    
    if ($userObject->has_role('SysAdmin')) {
        $adminToolsItem = $menuItemData->getAdminToolsItem();
        $render->render($adminToolsItem, $string, 'sidebar/menuitem.html');
    } elseif ($userObject->has_role('Admin')) {
        $calendarItem = $menuItemData->getCalendarItem();
        $render->render($calendarItem, $string, 'sidebar/menuitem.html');
    }
    
    $createFolderItem = $menuItemData->getCreateFolderItem();
    $render->render($createFolderItem, $string, 'sidebar/menuitem.html');
    
    $personalKeywordsItem = $menuItemData->getPersonalKeywordsItem();
    $render->render($personalKeywordsItem, $string, 'sidebar/menuitem.html');
    
    $searchMenuItem = $menuItemData->getSearchMenuItem();
    $render->render($searchMenuItem, $string, 'sidebar/menuitem.html');
    ?>
</div>
    <?php
    // -- Display top 10 recent papers ----------------------------------
    $recent_papers = Paper_utils::get_recent($userObject->get_user_ID(), $mysqli);
    if (count($recent_papers) > 0) {
        echo '<div style="font-size:180%; padding-top:40px" id="papertasks">' . $string['recent'] . '</div>';
    }

    echo '<ul type="square">';
    foreach ($recent_papers as $paperID => $paper_title) {
        echo '<li class="recent"><a ';
        if (mb_strpos($paper_title, '[deleted') !== false) {
            echo ' style="color:#808080"';
        }
        echo 'href="' . $configObject->get('cfg_root_path') . "/paper/details.php?paperID=$paperID\">$paper_title</a></li>\n";
    }
    echo '</ul>';
}
?>
<div id="displaycredits">ExamSys <?php echo $configObject->get_setting('core', 'rogo_version') ?> - &copy; 2018</div>

</div>

<?php
if (!$userObject->has_role('Standards Setter')) {
    makeMenu([$string['questions'] => "{$configObject->get('cfg_root_path')}/question/search.php",$string['papers'] => "{$configObject->get('cfg_root_path')}/paper/search.php",$string['people'] => "{$configObject->get('cfg_root_path')}/users/search.php"]);
}
