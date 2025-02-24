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
 * Copy Paper page - allows copying of exam papers
 *
 * @author Iyud Dissanayake
 * @copyright Copyright (c) 2025 The University of Nottingham
 */

require_once '../include/staff_auth.inc';
require_once '../include/errors.php';

// Get variables from paper_options.php
$paperID = check_var('paperID', 'GET', true, false, true, param::INT);
$module = param::optional('module', null, param::INT, param::FETCH_GET);
$folder = param::optional('folder', null, param::INT, param::FETCH_GET);

// Get paper properties
$properties = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);

// Check if user has permission to edit this paper
$userObject = UserObject::get_instance();
if (!$properties->can_user_edit_paper($userObject)) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit(
        $mysqli,
        $string['accessdenied'],
        $msg,
        $string['accessdenied'],
        '/artwork/page_not_found.png',
        '#C00000',
        true,
        true
    );
}

?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $string['copypaper']; ?></title>
    <link rel="stylesheet" type="text/css" href="../css/source/copy_paper.css">
    <script id="rogoconfig" data-root="<?php echo $configObject->get('cfg_root_path'); ?>" src="../js/rogoconfig.js"></script>
    <script src='../js/require.js'></script>
    <script src='../js/main.min.js'></script>
    <script src='../js/copypaperinit.min.js'></script>
</head>
<body>
    <div id="content">
        <div id="dataset" data-paperid="<?php echo $paperID; ?>"></div>
        <h1><?php echo $string['copypaper']; ?></h1>
        
        <?php
        if ($configObject->get_setting('core', 'cfg_summative_mgmt')) {
            echo '<form method="post" action="copy.php" id="checkcopy" class="copy-form" autocomplete="off">';
        } else {
            echo '<form method="post" action="copy.php" class="copy-form" autocomplete="off">';
        }
        ?>
        
        <div id="step1" class="form-step active">
            <div class="form-row">
                <label for="new_paper"><?php echo $string['copyname']; ?></label>
                <input type="text" 
                       id="new_paper" 
                       name="new_paper" 
                       maxlength="255" 
                       value="<?php echo $properties->get_paper_title(); ?>" 
                       required />
            </div>

            <div class="form-row">
                <label for="paper_type"><?php echo $string['type']; ?></label>
                <select name="paper_type" id="paper_type" class="changepapertype">
                    <?php
                    $currentType = $properties->get_paper_type();
                    
                    // Show paper types based on current type
                    switch ($currentType) {
                        case \assessment::TYPE_FORMATIVE:
                        case \assessment::TYPE_PROGRESS:
                        case \assessment::TYPE_SUMMATIVE:
                            echo '<option value="' . \assessment::TYPE_FORMATIVE . '"' . 
                                 ($currentType == \assessment::TYPE_FORMATIVE ? ' selected' : '') . 
                                 '>' . $string['formative self-assessment'] . '</option>';
                            echo '<option value="' . \assessment::TYPE_PROGRESS . '"' . 
                                 ($currentType == \assessment::TYPE_PROGRESS ? ' selected' : '') . 
                                 '>' . $string['progress test'] . '</option>';
                            echo '<option value="' . \assessment::TYPE_SUMMATIVE . '"' . 
                                 ($currentType == \assessment::TYPE_SUMMATIVE ? ' selected' : '') . 
                                 '>' . $string['summative exam'] . '</option>';
                            break;
                        case \assessment::TYPE_SURVEY:
                            echo '<option value="' . \assessment::TYPE_SURVEY . '" selected>' . 
                                 $string['survey'] . '</option>';
                            break;
                        case \assessment::TYPE_OSCE:
                            echo '<option value="' . \assessment::TYPE_OSCE . '" selected>' . 
                                 $string['osce station'] . '</option>';
                            break;
                        case \assessment::TYPE_OFFLINE:
                            echo '<option value="' . \assessment::TYPE_OFFLINE . '" selected>' . 
                                 $string['offline paper'] . '</option>';
                            break;
                        case \assessment::TYPE_PEERREVIEW:
                            echo '<option value="' . \assessment::TYPE_PEERREVIEW . '" selected>' . 
                                 $string['peer review'] . '</option>';
                            break;
                    }
                    ?>
                </select>
            </div>

            <div class="form-row">
                <label for="session"><?php echo $string['academicsession']; ?></label>
                <select name="session" id="session">
                    <?php
                    $yearutils = new yearutils($mysqli);
                    echo $yearutils->get_calendar_year_dropdown_options($properties->get_paper_type(), $properties->get_calendar_year(), $string);
                    ?>
                </select>
            </div>

            <div class="form-row">
                <label></label>
                <input type="checkbox" name="copy_std_setting" id="copy_std_setting" 
                       <?php echo $configObject->get_setting('core', 'stdset_copy_std_setting') ? 'checked' : ''; ?> />
                <label for="copy_std_setting" class="checkbox-label"><?php echo $string['copystdsetting']; ?></label>
            </div>

            <div class="form-row" id="copytype_selector">
                <label><?php echo $string['copytype']; ?></label>
                <div class="radio-group">
                    <div class="radio-option">
                        <input type="radio" name="copytype" id="copytype_paperonly" value="paperonly" />
                        <label for="copytype_paperonly">
                            <span><?php echo $string['paperonly']; ?></span>
                            <img src="../artwork/copy_paper_only.png" width="120" height="130" alt="" />
                        </label>
                    </div>
                    <div class="radio-option">
                        <input type="radio" name="copytype" id="copytype_paperquestions" value="paperquestions" checked />
                        <label for="copytype_paperquestions">
                            <span><?php echo $string['paperandquestions']; ?></span>
                            <img src="../artwork/copy_paper_questions.png" width="120" height="130" alt="" />
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div id="step2" class="form-step">
            <div class="step-header">
                <h2><?php echo $string['summativedetails']; ?></h2>
            </div>
            
            <!-- Summative exam fields -->
            <div id="summative_fields">
                <div class="form-row">
                    <label for="campus"><?php echo $string['campus']; ?></label>
                    <select name="campus" id="campus">
                        <?php
                        $campusobj = new campus($mysqli);
                        $campuses = $campusobj->get_all_campus_details();
                        foreach ($campuses as $key => $campusarray) {
                            echo '<option value="' . $campusarray['campusname'] . '"' . 
                                 ($campusarray['isdefault'] ? ' selected' : '') . '>' . 
                                 $campusarray['campusname'] . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-row">
                    <label></label>
                    <input type="checkbox" name="barriers_needed" id="barriers_needed" />
                    <label for="barriers_needed" class="checkbox-label"><?php echo $string['barriersneeded']; ?></label>
                </div>

                <div class="form-row">
                    <label for="duration"><?php echo $string['duration']; ?></label>
                    <div class="duration-inputs">
                        <input type="number" name="duration_hours" id="duration_hours" min="0" max="24" />
                        <label class="inline-label"><?php echo $string['hrs']; ?></label>
                        <input type="number" name="duration_mins" id="duration_mins" min="0" max="59" />
                        <label class="inline-label"><?php echo $string['mins']; ?></label>
                    </div>
                </div>

                <div class="form-row">
                    <label for="period"><?php echo $string['daterequired']; ?></label>
                    <select name="date_required" id="period">
                        <option value=""></option>
                        <?php
                        $months = array(
                            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                        );
                        foreach ($months as $num => $name) {
                            echo '<option value="' . $num . '">' . $name . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-row">
                    <label for="cohort_size"><?php echo $string['cohortsize']; ?></label>
                    <select name="cohort_size" id="cohort_size">
                        <option value=""></option>
                        <?php
                        $sizes = array('&lt;whole cohort&gt', '0-10', '11-20', '21-30', '31-40', '41-50', 
                                     '51-75', '76-100', '101-150', '151-200', '201-300', '301-400', '401-500');
                        foreach ($sizes as $size) {
                            echo '<option value="' . $size . '">' . $size . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-row">
                    <label for="sittings"><?php echo $string['sittings']; ?></label>
                    <select name="sittings" id="sittings">
                        <option value=""></option>
                        <?php
                        $maxSittings = $configObject->get_setting('core', 'summative_max_sittings');
                        for ($i = 1; $i <= $maxSittings; $i++) {
                            echo '<option value="' . $i . '">' . $i . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-row">
                    <label for="notes"><?php echo $string['notes']; ?></label>
                    <textarea name="notes" id="notes" rows="4"></textarea>
                </div>
            </div>
        </div>

        <input type="hidden" name="paperID" value="<?php echo $paperID; ?>" />
        <input type="hidden" name="module" value="<?php echo $module; ?>" />
        <input type="hidden" name="folder" value="<?php echo $folder; ?>" />
        
        <div class="form-buttons">
            <button type="button" id="next_button"><?php echo $string['next']; ?></button>
            <button type="submit" id="submit_button"><?php echo $string['copypaper']; ?></button>
            <button type="button" id="cancel"><?php echo $string['cancel']; ?></button>
            <button type="button" class="back-button" id="back_button"><?php echo $string['back']; ?></button>
        </div>
    </form>
</div>
</body>
</html>
