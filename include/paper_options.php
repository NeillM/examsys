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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See theZX
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * Sidebar menu for papers.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require_once $cfg_web_root . 'include/sidebar_menu.inc';
require_once $cfg_web_root . 'include/sidebar_functions.inc';
require_once $cfg_web_root . 'include/mapping.inc';
require_once $cfg_web_root . 'classes/papermenuitemdata.class.php';

$userObject = UserObject::get_instance();
$menuItemData = new MenuItemData($configObject, $string);


$clarif_types = $configObject->get_setting('core', 'summative_midexam_clarification');

if (!isset($properties)) {
    $properties = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);
}

if ($properties->get_paper_type() == '2' and $userObject->has_role(array('SysAdmin', 'Admin')) and $properties->is_live() and $properties->get_bidirectional() == '1' and count($clarif_types) > 0) {
    $exam_clarifications = true;
} else {
    $exam_clarifications = false;
}

if (!isset($module)) {
    $module = param::optional('module', '', param::INT, param::FETCH_GET);
}

if (!isset($folder)) {
    $folder = param::optional('folder', '', param::INT, param::FETCH_GET);
}

$moduleIDs = $properties->get_modules();
$checklist = '';
if (count($moduleIDs) > 0) {
    $moduleIDs = array_keys($moduleIDs);
    $stmt = $mysqli->prepare('SELECT checklist FROM modules WHERE id IN (' . implode(',', $moduleIDs) . ')');
    $stmt->execute();
    $stmt->bind_result($tmp_checklist);
    $check = array();
    while ($stmt->fetch()) {
        if ($tmp_checklist != '') {
            $tmp = explode(',', $tmp_checklist);
            foreach ($tmp as $c => $type) {
                $check[] = $type;
            }
        }
    }
    $checklist = implode(',', $check);
    $stmt->close();
}
?>

<div id="left-sidebar" class="sidebar">

<form name="PapersMenu" action="" autocomplete="off">
<input type="hidden" id="questionNo" name="questionNo" value="" />
<input type="hidden" id="questionID" name="questionID" value="" />
<input type="hidden" id="pID" name="pID" value="" />
<input type="hidden" id="qType" name="qType" value="" />
<input type="hidden" id="screenNo" name="screenNo" value="" />
<input type="hidden" id="scrOfY" name="scrOfY" value="0" />
<input type="hidden" id="current_pos" name="current_pos" value="" />

<div class="submenuheading" id="papertasks"><?php echo $string['papertasks'] ?></div>
<?php
// - Paper Tasks ----------------------------------------------------------
echo "<div id=\"menu1\" role=\"menu\">\n";

// Test & Preview
$render = new render($configObject);
$testPreviewItem = $menuItemData->getTestPreviewItem($properties);
$render->render($testPreviewItem, $string,  'sidebar/menuitem.html');

// Add Questions to Paper
$addQuestionsItem = $menuItemData->getAddQuestionsItem($properties);
$render->render($addQuestionsItem, $string, 'sidebar/menuitem.html');
// if ($properties->get_summative_lock() == 1) {
//     echo "<div class=\"grey menuitem\" role=\"menuitem\" aria-disabled=\"true\"><img class=\"sidebar_icon\" src=\"{$configObject->get('cfg_root_path')}/artwork/add_questions_grey.gif\" alt=\"\" />" . $string['addquestionspaper'] . "</div>\n";
// } else {
//     $max_screen = ($properties->get_max_screen() != '') ? $properties->get_max_screen() : 0;
//     echo '<div class="menuitem addquestions" data-dispno="' . ($properties->get_max_display_pos() + 1) . '" data-screen="' . $max_screen . '" role="menuitem"><a href="#" tabindex="0"><img class="sidebar_icon" src="' . $configObject->get('cfg_root_path') . '/artwork/add_questions_16.gif" alt="" />' . $string['addquestionspaper'] . "</a></div>\n";
// }

// Edit Properties
$editPropertiesItem = $menuItemData->getEditPropertiesItem($paperID, $module, $folder);
$render->render($editPropertiesItem, $string, 'sidebar/menuitem.html');
// echo "<div class=\"menuitem properties\" role=\"menuitem\"><a href=\"{$configObject->get('cfg_root_path')}/paper/properties.php?paperID={$paperID}&caller=details&module={$module}&folder={$folder}\" tabindex=\"0\"><img class=\"sidebar_icon\" src=\"{$configObject->get('cfg_root_path')}/artwork/properties_icon.gif\" alt=\"\" />" . $string['editproperties'] . "</a></div>\n";

// Email Externals
$emailExternalsItem = $menuItemData->getEmailExternalsItem($properties);
if ($emailExternalsItem) {
    $render->render($emailExternalsItem, $string, 'sidebar/menuitem.html');
}
// if ($properties->get_paper_type() == '2') {
//     if (is_null($properties->get_external_review_deadline())) {
//         echo "<div class=\"grey menuitem\" id=\"emailexternalsgrey\" role=\"menuitem\" aria-disabled=\"true\"><img class=\"sidebar_icon\" src=\"{$configObject->get('cfg_root_path')}/artwork/small_email_grey.png\" alt=\"\" />" . $string['emailexternals'] . "</div>\n";
//     } else {
//         echo "<div class=\"menuitem cascade showmenu\" id=\"emailexternals\" data-popupid=\"1\" data-popuptype=\"papertasks\" data-popupname=\"emailexternals\" role=\"menuitem\" aria-haspopup=\"true\" tabindex=\"0\"><a href=\"#\" tabindex=\"-1\"><img class=\"sidebar_icon\" src=\"{$configObject->get('cfg_root_path')}/artwork/small_email.png\" alt=\"\" />" . $string['emailexternals'] . "</a></div>\n";
//     }
// }

// Reports
$reportsItem = $menuItemData->getReportsItem($properties, $paperID, $module, $folder, $checklist, isset($graded) ? $graded : false);
if ($reportsItem) {
    $render->render($reportsItem, $string, 'sidebar/menuitem.html');
}

// Import OSCE marks
if ($properties->get_paper_type() == '4') {
    $gradebook = new gradebook($mysqli);
    $graded = $gradebook->paper_graded($paperID);
    if (!$graded) {
        $importOsceMarksItem = $menuItemData->getImportOsceMarksItem($paperID, $module, $folder);
        $render->render($importOsceMarksItem, $string, 'sidebar/menuitem.html');
    }
}

// Mapped Objectives Item (for paper types 0,1,2,5)
if (in_array($properties->get_paper_type(), ['0', '1', '2', '4','5']) && 
    mb_strpos($checklist, 'mapping') !== false && 
    $properties->get_paper_type() != '6') {
    $mappedObjectivesItem = $menuItemData->getMappedObjectivesItem($properties, $paperID, $module, $folder);
    $render->render($mappedObjectivesItem, $string, 'sidebar/menuitem.html');
}

// Add Standard Settings item if applicable
if ($properties->get_paper_type() != '6' && mb_strpos($checklist, 'stdset') !== false) {
    $standardSettingsItem = [
        'classes' => 'menuitem',
        'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/std_set_icon_16.gif',
        'text' => $string['standardssetting'],
        'href' => Config::get_instance()->get('cfg_root_path') . 
                "/std_setting/index.php?paperID=$paperID&module=$module&folder=$folder",
        'tabindex' => 0
    ];
    $render->render($standardSettingsItem, $string, 'sidebar/menuitem.html');
}

// Copy Paper
$copyPaperItem = $menuItemData->getCopyPaperItem();
$render->render($copyPaperItem, $string, 'sidebar/menuitem.html');
// echo "<div class=\"menuitem cascade\" id=\"copypaper\" role=\"menuitem\" aria-haspopup=\"true\"><a href=\"#\" tabindex=\"0\"><img class=\"sidebar_icon\" src=\"{$configObject->get('cfg_root_path')}/artwork/copy_icon.gif\" alt=\"\" />" . $string['copypaper'] . "</a></div>\n";

// Copy from Paper
$copyFromPaperItem = $menuItemData->getCopyFromPaperItem($properties);
$render->render($copyFromPaperItem, $string, 'sidebar/menuitem.html');
// if ($properties->get_summative_lock() == 1) {
//     echo '<div class="grey menuitem" role="menuitem" aria-disabled="true"><img class="sidebar_icon" src="' . $configObject->get('cfg_root_path') . '/artwork/copy_icon_grey.gif" alt="" />' . $string['copyfrompaper'] . '</div>';
// } else {
//     echo '<div class="menuitem cascade" id="copyfrompaper" role="menuitem" aria-haspopup="true"><a href="#" tabindex="0"><img class="sidebar_icon" src="' . $configObject->get('cfg_root_path') . '/artwork/copy_icon.gif" alt="" />' . $string['copyfrompaper'] . '</a></div>';
// }

// Delete Paper
$deletePaperItem = $menuItemData->getDeletePaperItem($properties, $userObject, $configObject);
$render->render($deletePaperItem, $string, 'sidebar/menuitem.html');
// if ($properties->get_summative_lock() == 1 or ($configObject->get_setting('core', 'cfg_summative_mgmt') and $properties->get_paper_type() == '2' and !$userObject->has_role(array('Admin', 'SysAdmin')))) {
//     echo '<div class="grey menuitem" role="menuitem" aria-disabled="true"><img class="sidebar_icon" src="' . $configObject->get('cfg_root_path') . '/artwork/delete_paper_grey_16.gif" alt="" />' . $string['deletepaper'] . '</div>';
// } else {
//     echo '<div class="menuitem deletepaper" role="menuitem"><a href="#" tabindex="0"><img class="sidebar_icon" src="' . $configObject->get('cfg_root_path') . '/artwork/delete_paper_16.gif" alt="" />' . $string['deletepaper'] . '</a></div>';
// }

// Retire Paper
$retirePaperItem = $menuItemData->getRetirePaperItem();
$render->render($retirePaperItem, $string, 'sidebar/menuitem.html');
// echo '<div class="menuitem retirepaper" role="menuitem"><a href="#" tabindex="0"><img class="sidebar_icon" src="' . $configObject->get('cfg_root_path') . '/artwork/retire_16.png" alt="" />' . $string['retirepaper'] . '</a></div>';

// Print Hardcopy
$printHardcopyItem = $menuItemData->getPrintHardcopyItem($properties, $paperID);
$render->render($printHardcopyItem, $string, 'sidebar/menuitem.html');
// if ($properties->get_item_no() == 0) {
//     echo "<div class=\"grey menuitem\" role=\"menuitem\" aria-disabled=\"true\"><img class=\"sidebar_icon\" src=\"{$configObject->get('cfg_root_path')}/artwork/print_icon_16_disabled.png\" alt=\"\" />" . $string['printhardcopy'] . "</div>\n";
// } else {
//     if ($properties->get_paper_type() == '4') {
//         echo "<div class=\"menuitem\" role=\"menuitem\"><a href=\"{$configObject->get('cfg_root_path')}/osce/print.php?paperID=$paperID\" tabindex=\"0\"><img class=\"sidebar_icon\" src=\"{$configObject->get('cfg_root_path')}/artwork/print_icon_16.png\" alt=\"\" />" . $string['printhardcopy'] . "</a></div>\n";
//     } else {
//         echo "<div class=\"menuitem cascade showmenu\" id=\"hardcopy\" data-popupid=\"3\" data-popuptype=\"papertasks\" data-popupname=\"hardcopy\" role=\"menuitem\" aria-haspopup=\"true\" tabindex=\"0\"><a href=\"#\" tabindex=\"-1\"><img class=\"sidebar_icon\" src=\"{$configObject->get('cfg_root_path')}/artwork/print_icon_16.png\" alt=\"\" />" . $string['printhardcopy'] . "</a></div>\n";
//     }
// }

// Import/Export
$importExportItem = $menuItemData->getImportExportItem($properties, $paperID, $module);
$render->render($importExportItem, $string, 'sidebar/menuitem.html');
// echo "<div class=\"menuitem cascade showmenu\" id=\"qti\" data-popupid=\"2\" data-popuptype=\"papertasks\" data-popupname=\"qti\" role=\"menuitem\" aria-haspopup=\"true\" tabindex=\"0\"><a href=\"#\" tabindex=\"-1\"><img class=\"sidebar_icon\" src=\"{$configObject->get('cfg_root_path')}/artwork/ims_16.png\" alt=\"\" />" . $string['importexport'] . "</a></div>\n";

// Student Cohort
$studentCohortItem = $menuItemData->getStudentCohortItem($properties);
if ($studentCohortItem) {
    $render->render($studentCohortItem, $string, 'sidebar/menuitem.html');
}
// if ($properties->get_calendar_year()) {
//     echo "<div class=\"menuitem studentcohort\" role=\"menuitem\"><a href=\"#\" tabindex=\"0\"><img class=\"sidebar_icon\" src=\"{$configObject->get('cfg_root_path')}/artwork/small_user_icon.gif\" alt=\"\" />" . $string['studentcohort'] . "</a></div>\n";
// }

echo "</div>\n";
?>

<br />

<?php
// - Current Question Tasks ---------------------------------------------------
?>
<div class="submenuheading" id="currentquestion"><?php echo $string['currentquestiontasks'] ?></div>
<!-- Grey menu items -->
<div id="menu2a">
    <?php
    $greyItems = $menuItemData->getCurrentQuestionItemsGrey($exam_clarifications, $properties->get_paper_type());
    foreach ($greyItems as $item) {
        $render->render($item, $string, 'sidebar/menuitem.html');
    }
    
    // Add killer question if paper type is 4
    if ($properties->get_paper_type() == '4') {
        $killerItem = $menuItemData->getKillerQuestionItem(true);
        $render->render($killerItem, $string, 'sidebar/killerquestion.html');
    }
    ?>
</div>
<!-- Active menu items -->
<div id="menu2b">
    <?php
    $activeItems = $menuItemData->getCurrentQuestionItemsActive($exam_clarifications, $properties->get_paper_type());
    foreach ($activeItems as $item) {
        $render->render($item, $string, 'sidebar/menuitem.html');
    }
    
    // Add killer question if paper type is 4
    if ($properties->get_paper_type() == '4') {
        $killerItem = $menuItemData->getKillerQuestionItem(false);
        $render->render($killerItem, $string, 'sidebar/killerquestion.html');
    }
    ?>
</div>

<div id="menu2c">
    <?php
    $activeItems = $menuItemData->getCurrentQuestionItemsActive($exam_clarifications, $properties->get_paper_type());
    foreach ($activeItems as $item) {
        $render->render($item, $string, 'sidebar/menuitem.html');
    }
    
    // Add killer question if paper type is 4
    if ($properties->get_paper_type() == '4') {
        $killerItem = $menuItemData->getKillerQuestionItem(false);
        $render->render($killerItem, $string, 'sidebar/killerquestion.html');
    }
    ?>
</div>

<!--[if lt IE 9]>
<div class="iefixdiv"></div><div class="iefixdiv"></div>
<![endif]-->

<?php
if ($properties->get_summative_lock() == true) {
    ?>
<ul id="break_controls" class="menu_list">
    <?php
    if ($properties->get_paper_type() != '4') {
        ?>
  <li id="add_break" class="break greymenuitem"><?php echo $string['addscreenbreak'] ?></li>
  <li id="delete_break" class="greymenuitem"><?php echo $string['deletescreenbreak'] ?></li>
        <?php
    }
    echo '</ul>';
} else {
    ?>
<ul id="break_controls" class="menu_list">
    <?php
    if ($properties->get_paper_type() != '4') {
        ?>
  <li id="add_break" class="break greymenuitem"><a href="#"><?php echo $string['addscreenbreak'] ?></a></li>
  <li id="delete_break" class="greymenuitem"><a href="#"><?php echo $string['deletescreenbreak'] ?></a></li>
        <?php
    }
    echo '</ul>';
}
?>
<div id="menu2a">
    <!-- Create new question -->
<?php
    $extra_url = '';
    $module = param::optional('module', null, param::INT, param::FETCH_GET);
    if (!is_null($module)) {
        $extra_url .= '&module=' . $module;
    }
    if ($extra_url != '') {
        $extra_url = '?' . $extra_url;
    }
    $newQuestionItem = $menuItemData->getNewQuestionItem();
    $render->render($newQuestionItem, $string, 'sidebar/menuitem.html');
?>
  <!-- <div class="menuitem cascade showmenu" id="newquestion" data-popupid="0" data-popuptype="banktasks" data-popupname="newquestion"><a href="#"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/new_question_menu_icon.gif" alt="<?php echo $string['createnewquestion'] ?>" /><?php echo $string['createnewquestion'] ?></a></div> -->
</div>

<?php
if ($properties->get_paper_type() == '2') {
    ?>
<br />

<table style="width:210px; background-color: #2B569A; color: white !important; margin-bottom:16px">
  <tr>
    <td style="font-size:120%; font-weight:bold; padding-bottom:6px" colspan="3"><?php echo $string['summativechecklist'] ?></td>
  </tr>
    <?php
    // Session
    if ($sessionItem = $menuItemData->getSessionCheckItem($properties)) {
        $render->render($sessionItem, $string, 'sidebar/tablemenuitem.html');
    }
    // $tmp_match = Paper_utils::academic_year_from_title($properties->get_paper_title());

    // if ($tmp_match !== false and $tmp_match != $properties->get_calendar_year()) {
    //     // echo "<tr><td><img src=\"{$configObject->get('cfg_root_path')}/artwork/checklist_exclamation.png\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . '" /></td><td><a href="" class="checklist properties">' . $string['session'] . '</a></td><td>' . $string['mismatch'] . "</td></tr>\n";
    // }

    // Times
    if ($timesItem = $menuItemData->getTimesCheckItem($properties)) {
        $render->render($timesItem, $string, 'sidebar/checklist_item.html');
    }
    // if ($properties->get_start_date() == $properties->get_end_date() or is_null($properties->get_start_date()) or is_null($properties->get_end_date())) {
    //     echo "<tr><td><img src=\"{$configObject->get('cfg_root_path')}/artwork/checklist_exclamation.png\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . '" /></td><td><a href="" class="checklist properties">' . $string['examtime'] . '</a></td><td>' . $string['incorrect'] . "</td></tr>\n";
    // }

    // Duration
    if ($durationItem = $menuItemData->getDurationCheckItem($properties)) {
        $render->render($durationItem, $string, 'sidebar/tablemenuitem.html');
    }
    // if ($properties->get_exam_duration() == '') {
    //     echo "<tr><td><img src=\"{$configObject->get('cfg_root_path')}/artwork/checklist_exclamation.png\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . '" /></td><td><a href="" class="checklist properties">' . $string['duration'] . '</a></td><td>' . $string['unset'] . "</td></tr>\n";
    // }

    // Computer labs
    if ($labsItem = $menuItemData->getLabsCheckItem($properties)) {
        $render->render($labsItem, $string, 'sidebar/tablemenuitem.html');
    }
    // if ($properties->get_labs() == '') {
    //     echo "<tr><td><img src=\"{$configObject->get('cfg_root_path')}/artwork/checklist_exclamation.png\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . '" /></td><td><a href="" class="checklist properties">' . $string['computerlabs'] . '</a></td><td>' . $string['unset'] . "</td></tr>\n";
    // }

    // Internal Peer review
    if (mb_strpos($checklist, 'peer') !== false) {
        if (count($properties->get_internal_reviewers()) == 0) {
            $item = $menuItemData->getUnsetInternalReviewItem(
            $string['peerreviewes'], 
            $string['unset']
            );
            $render->render($item, $string, 'sidebar/tablemenuitem.html');
        } else {
            $tmp_array = $properties->get_internal_reviewers();
            $internal_array = array();
            foreach ($tmp_array as $reviewerID => $reviewer_name) {
                $internal_array[$reviewerID] = 0;
            }

            $stmt = $mysqli->prepare("SELECT DISTINCT reviewerID FROM review_metadata WHERE paperID = ? AND review_type = 'internal' AND complete IS NOT NULL");
            $stmt->bind_param('i', $paperID);
            $stmt->execute();
            $stmt->bind_result($reviewer);
            while ($stmt->fetch()) {
                $internal_array[$reviewer] = 1;
            }
            $stmt->close();
            $reviews_complete = 0;
            foreach ($tmp_array as $reviewerID => $reviewer_name) {
                if ($internal_array[$reviewerID] == 1) {
                    $reviews_complete++;
                }
            }

            if ($reviews_complete < count($internal_array)) {
                if ($reviews_complete == 0) {
                    $tmp_color = '#C00000';
                } else {
                    $tmp_color = '#F27000';
                }
                $item = $menuItemData->getInternalReviewCheckItem(
                    $string['peerreviewes'],
                    $reviews_complete . "/" . count($internal_array),
                    false,
                    $paperID,
                    $module,
                    $folder
                );
                $render->render($item, $string, 'sidebar/tablemenuitem.html');
                // echo "<tr style=\"height:16px\"><td style=\"width:18px\"><img src=\"{$configObject->get('cfg_root_path')}/artwork/checklist_exclamation.png\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" /></td><td><a class=\"checklist\" href=\"{$configObject->get('cfg_root_path')}/reports/review_comments.php?type=internal&paperID=" . $paperID . '&startdate=&enddate=&repcourse=%&repyear=%&sortby=name&module=' . $module . '&folder=' . $folder . '&percent=100&absent=0&direction=asc">' . $string['peerreviewes'] . "</a></td><td>$reviews_complete/" . count($internal_array) . "</td></tr>\n";
            } else {
                $item = $menuItemData->getInternalReviewCheckItem(
                    $string['peerreviewes'],
                    $string['ok'],
                    true,
                    $paperID,
                    $module,
                    $folder
                );
                $render->render($item, $string, 'sidebar/tablemenuitem.html');
                // echo "<tr><td style=\"width:18px\"><img src=\"{$configObject->get('cfg_root_path')}/artwork/checklist_tick.png\" width=\"16\" height=\"16\" alt=\".\" /></td><td><a class=\"checklist\" href=\"{$configObject->get('cfg_root_path')}/reports/review_comments.php?type=internal&paperID=" . $paperID . '&startdate=&enddate=&repcourse=%&repyear=%&sortby=name&module=' . $module . '&folder=' . $folder . '&percent=100&absent=0&direction=asc">' . $string['peerreviewes'] . '</a></td><td>' . $string['ok'] . "</td></tr>\n";
            }
        }
    }

    // External examiners
    if (mb_strpos($checklist, 'external') !== false) {
        if (count($properties->get_externals()) == 0) {
            $item = $menuItemData->getUnsetExternalReviewItem(
                $string['externalreviews'],
                $string['unset']
            );
            $render->render($item, $string, 'sidebar/tablemenuitem.html');
            // echo "<tr><td style=\"height:16px\"><img src=\"{$configObject->get('cfg_root_path')}/artwork/checklist_exclamation.png\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . '" /></td><td><a href="" class="checklist properties">' . $string['externalreviews'] . '</td><td>' . $string['unset'] . "</td></tr>\n";
        } else {
            $tmp_array = $properties->get_externals();
            $external_array = array();
            foreach ($tmp_array as $reviewerID => $reviewer_name) {
                $external_array[$reviewerID] = 0;
            }

            $reviews_complete = 0;
            $stmt = $mysqli->prepare("SELECT DISTINCT reviewerID FROM review_metadata WHERE paperID = ? AND review_type = 'external' AND complete IS NOT NULL");
            $stmt->bind_param('i', $paperID);
            $stmt->execute();
            $stmt->bind_result($reviewer);
            while ($stmt->fetch()) {
                if (isset($external_array[$reviewer]) and $external_array[$reviewer] === 0) {
                    $reviews_complete++;
                }
            }
            $stmt->close();
            $paperID = param::required('paperID', param::INT, param::FETCH_GET);
            if ($reviews_complete < count($external_array)) {
                    $item = $menuItemData->getExternalReviewCheckItem(
                    $string['externalreviews'],
                    $reviews_complete . "/" . count($external_array),
                    false,
                    $paperID,
                    $module,
                    $folder
                );
                $render->render($item, $string, 'sidebar/tablemenuitem.html');
                // echo "<tr style=\"height:16px\"><td><img src=\"{$configObject->get('cfg_root_path')}/artwork/checklist_exclamation.png\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" /></td><td><a class=\"checklist\" href=\"{$configObject->get('cfg_root_path')}/reports/review_comments.php?type=external&paperID=" . $paperID . '&startdate=&enddate=&repcourse=%&repyear=%&sortby=name&module=' . $module . '&folder=' . $folder . '&percent=100&absent=0&direction=asc">' . $string['externalreviews'] . "</a></td><td>$reviews_complete/" . count($external_array) . "</td></tr>\n";
            } else {
                $item = $menuItemData->getExternalReviewCheckItem(
                    $string['externalreviews'],
                    $string['ok'],
                    true,
                    $paperID,
                    $module,
                    $folder
                );
                $render->render($item, $string, 'sidebar/checklist_item.html');
                // echo "<tr><td><img src=\"{$configObject->get('cfg_root_path')}/artwork/checklist_tick.png\" width=\"16\" height=\"16\" alt=\".\" /></td><td><a class=\"checklist\" href=\"{$configObject->get('cfg_root_path')}/reports/review_comments.php?type=external&paperID=" . $paperID . '&startdate=&enddate=&repcourse=%&repyear=%&sortby=name&module=' . $module . '&folder=' . $folder . '&percent=100&absent=0&direction=asc">' . $string['externalreviews'] . '</a></td><td>' . $string['ok'] . "</td></tr>\n";
            }
        }
    }

    // Standards Set
    $standard_set = 0;
    $standards_set = 0;
    if (mb_strpos($checklist, 'stdset') !== false) {
        $stmt = $mysqli->prepare('SELECT COUNT(std_set.id), setterID FROM std_set_questions, std_set WHERE std_set_questions.std_setID = std_set.id AND paperID = ? GROUP BY setterID');
        $stmt->bind_param('i', $paperID);
        $stmt->execute();
        $stmt->bind_result($set_set_records, $setterID);
        while ($stmt->fetch()) {
            if ($set_set_records >= $properties->get_question_no() and $standard_set == 0) {
                $standards_set = 1;
            } elseif ($set_set_records < $properties->get_question_no() and ($standard_set == 0 or $standard_set == 1)) {
                $standards_set = 0.5;
            }
        }
        $stmt->close();
        $paperID = param::required('paperID', param::INT, param::FETCH_GET);
        if ($standards_set == 1) {
            echo "<tr><td><img src=\"{$configObject->get('cfg_root_path')}/artwork/checklist_tick.png\" width=\"16\" height=\"16\" alt=\".\" /></td><td><a class=\"checklist\" href=\"{$configObject->get('cfg_root_path')}/std_setting/index.php?paperID=" . $paperID . '&module=' . $module . '&folder=' . $folder . '">' . $string['standardsset'] . '</a></td>';
        } else {
            echo "<tr style=\"height:16px\"><td><img src=\"{$configObject->get('cfg_root_path')}/artwork/checklist_exclamation.png\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" /></td><td><a class=\"checklist\" href=\"{$configObject->get('cfg_root_path')}/std_setting/index.php?paperID=" . $paperID . '&module=' . $module . '&folder=' . $folder . '">' . $string['standardsset'] . '</a></td>';
        }

        if ($standards_set == 1) {
            echo '<td>' . $string['ok'] . "</td></tr>\n";
        } elseif ($standards_set == 0.5) {
            echo '<td>' . $string['incomplete'] . "</td></tr>\n";
        } else {
            echo '<td>' . $string['unset'] . "</td></tr>\n";
        }
    }

    // Mapped
    if (mb_strpos($checklist, 'mapping') !== false) {
        $mappings_complete = 0;
        $tmp_session = $properties->get_calendar_year();

        $question_list = array();
        $stmt = $mysqli->prepare("SELECT question FROM papers, questions WHERE paper = ? AND papers.question = questions.q_id AND q_type != 'info'");
        $stmt->bind_param('i', $paperID);
        $stmt->execute();
        $stmt->bind_result($questionID);
        while ($stmt->fetch()) {
            $question_list[$questionID] = $questionID;
        }
        $stmt->close();
        $tmp_question_list = implode(',', array_keys($question_list));

        $objIDs = array();

        $moduleIDs = Paper_utils::get_modules($paperID, $mysqli);
        $objsBySession = getObjectives($moduleIDs, $tmp_session, $paperID, $tmp_question_list, $mysqli);
        if ($objsBySession !== 'error') {
            foreach ($objsBySession as $moduleCode) {
                foreach ($moduleCode as $sessionID) {
                    if (isset($sessionID['objectives'])) {
                        foreach ($sessionID['objectives'] as $objective) {
                              $ID = $objective['id'];
                              $objIDs[$ID] = $ID;
                        }
                    }
                }
            }
        }

        $mappings = array();
        $rels = Relationship::find($mysqli, '', $tmp_session, $paperID);
        if ($rels !== false and is_array($rels)) {
            foreach ($rels as $rel) {
                if (isset($question_list[$rel->get_question_id()]) and isset($objIDs[$rel->get_objective_id()])) {
                    $mappings[$rel->get_question_id()] = $rel->get_question_id();
                }
            }
        }

        $mappings_complete = count($mappings);
        $paperID = param::required('paperID', param::INT, param::FETCH_GET);
        if ($objsBySession == 'error') {
            echo "<tr><td><img src=\"{$configObject->get('cfg_root_path')}/artwork/checklist_exclamation.png\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" /></td><td><a class=\"checklist\" href=\"{$configObject->get('cfg_root_path')}/mapping/paper_by_question.php?paperID=" . $paperID . '&folder=' . $folder . '&module=' . $module . '">' . $string['mapping'] . "</a></td><td>Error</td></tr>\n";
        } elseif ($mappings_complete < $properties->get_question_no()) {
            echo "<tr style=\"height:16px\"><td><img src=\"{$configObject->get('cfg_root_path')}/artwork/checklist_exclamation.png\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" /></td><td><a class=\"checklist\" href=\"{$configObject->get('cfg_root_path')}/mapping/paper_by_question.php?paperID=" . $paperID . '&folder=' . $folder . '&module=' . $module . '">' . $string['mapping'] . "</a></td><td>$mappings_complete/" . $properties->get_question_no() . "</td></tr>\n";
        } else {
            echo "<tr><td><img src=\"{$configObject->get('cfg_root_path')}/artwork/checklist_tick.png\" width=\"16\" height=\"16\" alt=\".\" /></td><td><a class=\"checklist\" href=\"{$configObject->get('cfg_root_path')}/mapping/paper_by_question.php?paperID=" . $paperID . '&folder=' . $folder . '&module=' . $module . '">' . $string['mapping'] . '</a></td><td>' . $string['ok'] . "</td></tr>\n";
        }
    }
    echo "</table>\n";
}
?>

</form>
</div>
<?php
if ($properties->get_summative_lock()) {
    $params = '';
} else {
    $params = "&paperID=$paperID&folder=$folder&module=" . $module . '&calling=paper';
}
// Initialising the "Create new Question" submenu 
if ($properties->get_paper_type() == '6') {
    makeMenu(array(
    $string['likert'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=likert$params",
    $string['mcq'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=mcq$params"));
} else {
    makeMenu(array(
    $string['info'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=info$params",
    $string['keyword_based'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=keyword_based$params",
    $string['random'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=random$params",
    '-' => '-',
    $string['area'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=area$params",
    $string['calculation'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=enhancedcalc$params",
    $string['dichotomous'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=dichotomous$params",
    $string['extmatch'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=extmatch$params",
    $string['blank'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=blank$params",
    $string['hotspot'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=hotspot$params",
    $string['labelling'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=labelling$params",
    $string['likert'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=likert$params",
    $string['matrix'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=matrix$params",
    $string['mcq'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=mcq$params",
    $string['mrq'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=mrq$params",
    $string['rank'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=rank$params",
    $string['sct'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=sct$params",
    $string['textbox'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=textbox$params",
    $string['true_false'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=true_false$params"));
}
// Initialising the "Import/Export" submenu
    $importexport_menu = array();
if (!$properties->get_summative_lock()) {
    $importexport_menu[$string['import']] = $configObject->get('cfg_root_path') . "/qti/import.php?paperID=$paperID&module=$module";
    $importexport_menu[$string['importraf']] = $configObject->get('cfg_root_path') . "/import/rogo_assessment_format.php?paperID=$paperID&module=$module";
    if ($properties->get_question_no() > 0) {
        $importexport_menu['-'] = '-';
    }
}
if ($properties->get_question_no() > 0) {
    $importexport_menu[$string['export12']] = $configObject->get('cfg_root_path') . "/qti/export.php?dest=qti12&paperID=$paperID&module=$module";
    $importexport_menu[$string['exportraf']] = $configObject->get('cfg_root_path') . "/export/rogo_assessment_format.php?paperID=$paperID";
}
// Initialising the external submenu
    $external_menu[$string['initialinvitation']] = $configObject->get('cfg_root_path') . "/reviews/pick_external.php?paperID=$paperID&module=$module&mode=0";
    $external_menu[$string['reminder']] = $configObject->get('cfg_root_path') . "/reviews/pick_external.php?paperID=$paperID&module=$module&mode=1";
    $external_menu[$string['viewcomments']] = $configObject->get('cfg_root_path') . "/reviews/pick_external.php?paperID=$paperID&module=$module&mode=2";

  makeMenu($external_menu);

    makeMenu($importexport_menu);

  makeMenu(array(
    $string['Continuous'] => $configObject->get('cfg_root_path') . '/paper/print.php?id=' . $properties->get_crypt_name(),
    $string['Continuous - hide notes'] => $configObject->get('cfg_root_path') . '/paper/print.php?id=' . $properties->get_crypt_name() . '&hidenotes=1',
    $string['Page-break per question'] => $configObject->get('cfg_root_path') . '/paper/print.php?id=' . $properties->get_crypt_name() . '&break=1',
    $string['Page-break per question - hide notes'] => $configObject->get('cfg_root_path') . '/paper/print.php?id=' . $properties->get_crypt_name() . '&break=1&hidenotes=1'));

  require_once $cfg_web_root . 'include/reports_submenu.inc';
  require_once $cfg_web_root . 'include/paper_copy_submenu.inc';
  $render = new render($configObject);
  $lang['papers'] = $string['copyfrompaper'];
  $lang['cancel'] = $string['cancel'];
  $lang['ok'] = $string['ok'];
  $lang['paperslinkquestions'] = $string['paperslinkquestions'];
  $lang['papercopyquestions'] = $string['papercopyquestions'];
  $lang['copyquestionsblurb'] = $string['copyquestionsblurb'];
  $dataarray['action'] = '../paper/copy.php';
  $dataarray['papertype'] = $properties->get_paper_type();
  $dataarray['paperid'] = param::required('paperID', param::INT, param::FETCH_GET);
  $order = 'property_id';
  $direction = 'desc';
  $teamid = param::optional('teamID', null, param::INT, param::FETCH_GET);
  $dataarray['papers'] = PaperUtils::get_available_papers($userObject, $order, $direction, $properties->get_paper_type(), $module);
  $render->render($dataarray, $lang, 'paper/copy_from_paper_menu.html')
    ?>
    