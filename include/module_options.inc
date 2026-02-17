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

?>
<div id="left-sidebar" style="overflow:visible" class="sidebar">
<?php

if (!$userObject->has_role('Standards Setter')) {
    $render = new render($configObject);
    $menuItemData = new GeneralMenuItemData($string);
    $add_member = false;
    if ($_GET['module'] != '0') {
        if (mb_strpos((string) $module_details['checklist'], 'mapping') !== false) {
            // Curriculum mapping switched on for the module
            $manageObjectivesItem = $menuItemData->getManageObjectivesItem($module);
            $render->render($manageObjectivesItem, $string, 'sidebar/menuitem.html');
        }
        $manageKeywordsItem = $menuItemData->getManageKeywordsItem($module);
        $render->render($manageKeywordsItem, $string, 'sidebar/menuitem.html');
        $referenceMaterialItem = $menuItemData->getReferenceMaterialItem($module);
        $render->render($referenceMaterialItem, $string, 'sidebar/menuitem.html');
        $searchMenuItem = $menuItemData->getSearchMenuItem();
        $render->render($searchMenuItem, $string, 'sidebar/menuitem.html');
        // Get members of current team.
        if (!isset($_GET['type']) and $_GET['module'] != '0') {
            $team_members = module_utils::get_staff_members($module, $mysqli);
            $tmp_html = '';
            if ($userObject->has_role('Demo')) {
                $i = 0;
            }
            if (count($team_members) == 0) {
                $tmp_html = '<br /><div>' . $string['noteammembers'] . '</div>';
            } else {
                $tmp_html = '<ul type="square" style="line-height:155%; margin-top:4px; margin-bottom:4px; margin-left:26px; padding-left:0">';
            }
            foreach ($team_members as $team_member) {
                if ($userObject->has_role('Demo')) {
                    $tmp_html .= '<li><span style="color:black">' . \demo::demo_replace_name($i) . "</span></li>\n";
                    $i++;
                } elseif ($userObject->has_role(['SysAdmin', 'Admin'])) {
                    $tmp_html .= '<li><a style="color:black" href="../users/details.php?userID=' . $team_member['userID'] . "&module=$module\">" . $team_member['surname'] . ', ' . $team_member['initials'] . '. ' . $team_member['title'] . "</a></li>\n";
                } else {
                    $tmp_html .= '<li><span style="color:black">' . $team_member['surname'] . ', ' . $team_member['initials'] . '. ' . $team_member['title'] . "</span></li>\n";
                }
                if ($team_member['userID'] == $userObject->get_user_ID() and $module_details['add_team_members'] == 1) {
                    $add_member = true;
                }
            }
            if (count($team_members) > 0) {
                $tmp_html .= '</ul>';
            }

            echo '<br /><br />';
            if ($add_member == true or $userObject->has_role(['SysAdmin', 'Admin'])) {
                echo '<div style="float:left; width:225px; font-size:170%"><div style="float:left">' . $string['teammembers'] . '</div><div style="float:right"><img src="../artwork/pencil_16.png" width="16" height="16" alt="' . $string['edit'] . '" id="addteammember" /></div></div>';
            } else {
                echo '<div style="float:left; width:225px; font-size:170%">' . $string['teammembers'] . '</div>';
            }
            echo '<div style="height:78%; overflow:auto; clear:left" id="list">';
            echo "$tmp_html</div>\n";
        }
    }
}
?>

</div>


<?php

if (!$userObject->has_role('Standards Setter')) {
    makeMenu([$string['questions'] => "{$configObject->get('cfg_root_path')}/question/search.php?module=$module", $string['papers'] => "{$configObject->get('cfg_root_path')}/paper/search.php?module=$module", $string['people'] => "{$configObject->get('cfg_root_path')}/users/search.php?module=$module"]);
}
