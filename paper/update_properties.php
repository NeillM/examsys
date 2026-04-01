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
 * Allows the properties of a paper to be edited.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

define('AJAX_REQUEST', true);

require_once '../include/staff_auth.inc';
require_once '../include/errors.php';
require_once '../include/add_edit.inc';  // to clear MS Office tags
require_once '../include/load_config.php';
require_once '../include/timezones.php';

// Marking options
define('MARK_NO_ADJUSTMENT', '0');
define('MARK_RANDOM', '1');
define('MARK_STD_SET', '2');

$paperID = check_var('paperID', 'POST', true, false, true);

$exam_duration_hours = param::optional('exam_duration_hours', 0, param::INT, param::FETCH_POST);
$exam_duration_mins = param::optional('exam_duration_mins', 0, param::INT, param::FETCH_POST);
$ext_tyear = param::optional('ext_tyear', null, param::INT, param::FETCH_POST);
$int_tyear = param::optional('int_tyear', null, param::INT, param::FETCH_POST);
$texteditorplugin = \plugins\plugins_texteditor::get_editor();

$properties = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);
$papertype = $properties->get_paper_type();
$old_marking = $properties->get_marking();
$old_paper_title = $properties->get_paper_title();
$old_externals = $properties->get_externals();
$old_internals = $properties->get_internal_reviewers();
$papersettings = new PaperSettings($paperID, $properties->get_paper_type());
$logger = new Logger($mysqli);

if ($properties->get_summative_lock() and !$userObject->has_role('SysAdmin')) {
    $locked = true;
} else {
    $locked = false;
}

$modules_array = $properties->get_modules();

$q_feedback_enabled = Paper_utils::q_feedback_enabled(array_keys($modules_array), $mysqli);  // See if question-based feedback is enabled on all modules.

$paper_title = param::optional('paper_title', null, param::TEXT);
if ($paper_title !== null) {
    if ($old_paper_title == $paper_title) {
        $title_unique = true;
    } else {
        $title_unique = Paper_utils::is_paper_title_unique($paper_title, $mysqli);
    }
} else {
    $title_unique = true;
}

if (!$title_unique) {
    echo json_encode('DUPLICATE_TITLE');
    exit();
} else {
    if ($paper_title !== null) {  // Check is set, could be disabled.
        $properties->set_paper_title($paper_title);
    }

    // Save the paper type if it is one that can be switched.
    $paper_type = param::optional('paper_type', $papertype, param::INT, param::FETCH_POST);
    $switchable_types = [
        assessment::TYPE_FORMATIVE,
        assessment::TYPE_PROGRESS,
    ];
    if (in_array($papertype, $switchable_types) and in_array($paper_type, $switchable_types)) {
        $properties->set_paper_type($paper_type);
    }

    $bidirectional = param::optional('bidirectional', null, param::INT, param::FETCH_POST);
    if ($bidirectional !== null) {
        $properties->set_bidirectional($bidirectional);
    }

    // External system details;
    $extid = check_var('externalid', 'POST', false, false, true);
    $extsys = check_var('externalsys', 'POST', false, false, true);
    if (!is_null($extid)) {
        $properties->set_externalid($extid);
    }
    if (!is_null($extsys)) {
        $properties->set_externalsys($extsys);
    }

    if ($papertype == assessment::TYPE_PEERREVIEW) {
        $properties->set_display_correct_answer(
            (int) param::optional('display_photos', false, param::BOOLEAN, param::FETCH_POST)
        );
    } else {
        $properties->set_display_correct_answer(
            (int) param::optional('display_correct_answer', false, param::BOOLEAN, param::FETCH_POST)
        );
    }
    $properties->set_display_students_response(
        (int) param::optional('display_students_response', false, param::BOOLEAN, param::FETCH_POST)
    );
    if ($papertype == assessment::TYPE_PEERREVIEW) {
        $properties->set_display_question_mark(
            (int) param::optional('review', false, param::BOOLEAN, param::FETCH_POST)
        );
    } else {
        $properties->set_display_question_mark(
            (int) param::optional('display_question_mark', false, param::BOOLEAN, param::FETCH_POST)
        );
    }
    $properties->set_display_feedback(
        (int) param::optional('display_feedback', false, param::BOOLEAN, param::FETCH_POST)
    );
    $properties->set_hide_if_unanswered(
        (int) param::optional('hide_if_unanswered', false, param::BOOLEAN, param::FETCH_POST)
    );

    $timezone = param::optional('timezone', $properties->get_timezone(), param::TEXT, param::FETCH_POST);

    if ($properties->canEditSecurity()) {
        if (!$properties->isGraded()) {
            // Check if this is a remote summative paper.
            $remote_summative = param::optional('remote_summative', 0, param::INT, param::FETCH_POST);
            $is_remote_summative = ($papertype == '2' && $remote_summative == 1);
            // Date fields are mandatory unless it's a remote summative paper.
            if (!$is_remote_summative) {
                $fdate = check_var('fdate', 'POST', true, false, true, param::TEXT);
                $ftime = check_var('ftime', 'POST', true, false, true, param::TEXT);

                $tdate = check_var('tdate', 'POST', true, false, true, param::TEXT);
                $ttime = check_var('ttime', 'POST', true, false, true, param::TEXT);
            } else {
                // For remote summative papers, dates are optional
                $fdate = param::optional('fdate', '', param::TEXT, param::FETCH_POST);
                $ftime = param::optional('ftime', '', param::TEXT, param::FETCH_POST);

                $tdate = param::optional('tdate', '', param::TEXT, param::FETCH_POST);
                $ttime = param::optional('ttime', '', param::TEXT, param::FETCH_POST);
            }

            $null_start_date = false;
            if ($fdate == '' and $ftime == '') {
                $null_start_date = true;
                $tmp_start_date = null;
            } else {
                $start_date = date_utils::getDateTimeFromForm($fdate, $ftime, $timezone);
                $properties->set_start_date($start_date->getTimestamp());
                $properties->setRogoFormatStartDate();
            }

            $null_end_date = false;
            if ($tdate == '' and $ttime == '') {
                $null_end_date = true;
                $tmp_end_date = null;
            } else {
                $end_date = date_utils::getDateTimeFromForm($tdate, $ttime, $timezone);
                $properties->set_end_date($end_date->getTimestamp());
                $properties->setRogoFormatEndDate();
            }
            $properties->set_timezone($timezone);

            $calendar_year = param::optional('calendar_year', null, param::INT, param::FETCH_POST);
            $calendar_year = ($calendar_year == '') ? null : $calendar_year;
            $properties->set_calendar_year($calendar_year);

            // Set exam duration (in minutes).
            $exam_duration = $exam_duration_hours * 60;
            $exam_duration += $exam_duration_mins;

            if (!$locked) {
                $properties->set_exam_duration($exam_duration);
            }
        }

        $labs = param::optional('lab', [], param::INT, param::FETCH_POST);
        $lab_string = implode(',', $labs);
        $properties->set_labs($lab_string);

        if ($papertype == assessment::TYPE_SUMMATIVE) {
            $remote = check_var('remote_summative', 'POST', false, false, true);
            if (is_null($remote)) {
                $remote = 0;
            }
            $properties->updateSetting('remote_summative', $remote, $paperID);
        }
    }

    $external_deadline = param::optional('externaldeadline', null, param::TEXT, param::FETCH_POST);
    if (empty($external_deadline)) {
        $properties->set_external_review_deadline(null);
    } else {
        $properties->set_external_review_deadline($external_deadline);
    }

    $internal_deadline = param::optional('internaldeadline', null, param::TEXT, param::FETCH_POST);
    if (empty($internal_deadline)) {
        $properties->set_internal_review_deadline(null);
    } else {
        $properties->set_internal_review_deadline($internal_deadline);
    }

    $paper_modules = param::optional('mod', [], param::INT, param::FETCH_POST);
    $first_module_id = $paper_modules[0] ?? '';

    $new_externals = param::optional('examiner', [], param::INT, param::FETCH_POST);

    $new_internals = param::optional('internal', [], param::INT, param::FETCH_POST);

    if (!$locked) {
        $paper_prologue = param::optional('paper_prologue', '', param::RAW, param::FETCH_POST);
        $properties->set_paper_prologue(clearMSOtags($texteditorplugin->prepare_text_for_save($paper_prologue)));
    }

    if ($papertype == assessment::TYPE_OSCE) {
        $postscript = param::optional('osce_marking_guidance', '', param::RAW, param::FETCH_POST);
    } else {
        $postscript = param::optional('paper_postscript', '', param::RAW, param::FETCH_POST);
    }

    if (!$locked) {
        $properties->set_paper_postscript(clearMSOtags($texteditorplugin->prepare_text_for_save($postscript)));
    }

    if ($papertype == assessment::TYPE_PEERREVIEW) {
        // Reuse the 'rubric' field to store which field in the metadata to use for groups.
        $rubric = param::optional('type', '', param::TEXT, param::FETCH_POST);
    } else {
        $rubric_text = param::optional('rubric_text', '', param::RAW, param::FETCH_POST);
        $rubric = clearMSOtags($texteditorplugin->prepare_text_for_save($rubric_text));
    }

    if (!$locked) {
        $properties->set_rubric($rubric);
    }

    if (!$properties->isGraded()) {
        $marking = param::optional('marking', null, param::INT, param::FETCH_POST);
        if (!isset($marking) and $papertype == assessment::TYPE_OSCE) {
            // Do nothing, the marking method is locked.
        } elseif (empty($marking)) {
            $properties->set_marking(MARK_NO_ADJUSTMENT);
        } elseif ($marking == MARK_STD_SET) {
            $standard_setting = param::optional('std_set', null, param::TEXT, param::FETCH_POST);
            $properties->set_marking($standard_setting);
        } else {
            $properties->set_marking($marking);
        }

        $properties->set_pass_mark(
            param::optional('pass_mark', 0, param::INT, param::FETCH_POST) ?: 40
        );

        $properties->set_distinction_mark(
            param::optional('distinction_mark', 0, param::INT, param::FETCH_POST) ?: 70
        );
    }

    if (!$locked) {
        $tmp_calculator = param::optional('calculator', 0, param::INT, param::FETCH_POST);
        $properties->set_calculator($tmp_calculator);

        $properties->set_sound_demo(
            (int) param::optional('sound_demo', false, param::BOOLEAN, param::FETCH_POST)
        );

        $password = trim((string) param::optional('password', '', param::TEXT, param::FETCH_POST));
        if ($password != $properties->get_decrypted_password()) {
            $properties->set_password($password);
        }
        $properties->set_fullscreen(
            (int) param::optional('fullscreen', false, param::BOOLEAN, param::FETCH_POST)
        );

        $properties->set_bgcolor(param::optional('background', '', param::TEXT, param::FETCH_POST));
        $properties->set_fgcolor(param::optional('foreground', '', param::TEXT, param::FETCH_POST));
        $properties->set_themecolor(param::optional('themecolor', '', param::TEXT, param::FETCH_POST));
        $properties->set_labelcolor(param::optional('labelcolor', '', param::TEXT, param::FETCH_POST));
    }
    $properties->set_folder(param::optional('folderID', 0, param::INT, param::FETCH_POST) ?: '');

    if ($papertype == assessment::TYPE_SUMMATIVE and $old_marking != $properties->get_marking()) {
        $properties->set_recache_marks(1);
    }

    // Save any adjusted properties to the database.
    $properties->save();

    if (!$locked or $userObject->has_role(['SysAdmin', 'Admin'])) {
        $old_modules = $properties->get_modules(true);

        if (!$locked or $userObject->has_role(['SysAdmin'])) {
            // This method expects the database ids of the modules to be the array keys.
            Paper_utils::update_modules(array_flip($paper_modules), $paperID, $mysqli, $userObject);
        }

        $paper_modules = $properties->get_modules(true);

        $utils = new GeneralUtils();
        if (!$utils->arrays_are_equal($old_modules, $paper_modules)) {
            $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), implode(',', $old_modules), implode(',', $paper_modules), 'modules');
        }

        if (Paper_utils::update_reviewers($old_externals, $new_externals, 'external', $paperID, $mysqli)) {
            $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), implode(',', array_keys($old_externals)), implode(',', $new_externals), 'externals');
        }
        if (Paper_utils::update_reviewers($old_internals, $new_internals, 'internal', $paperID, $mysqli)) {
            $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), implode(',', array_keys($old_internals)), implode(',', $new_internals), 'internals');
        }
    }

    // Update Safe Exam Browser settings if enabled.
    if ($configObject->get_setting('core', 'paper_seb_enabled') and $papersettings->settingsCategoryEnabled('seb')) {
        $seb = (int) param::optional('seb_enabled', false, param::BOOLEAN, param::FETCH_POST);
        $properties->updateSetting('seb_enabled', $seb, $paperID);
        if ($papersettings->verifyValue(\Config::BOOLEAN, $seb)) {
            $seb_keys = param::optional('seb_keys_text', '', param::RAW, param::FETCH_POST);

            // Get existing keys to check for changes
            $seb_metadata = Paper_utils::get_metadata($mysqli, $paperID, 'seb_hash');
            $old_seb_key_array = $seb_metadata['seb_hash'] ?? [];

            if (empty(trim((string) $seb_keys))) {
                if (!empty($old_seb_key_array)) {
                    Paper_utils::delete_metadata(
                        $mysqli,
                        $paperID,
                        'seb_hash'
                    ); // Should this be PaperUtils::delete_metadata and declared static?
                    $logger->track_change(
                        'Paper',
                        $paperID,
                        $userObject->get_user_ID(),
                        'Safe Exam Browser keys removed',
                        '',
                        'SEB'
                    );
                }
            } else {
                $seb_key_array = explode("\n", (string) $seb_keys);
                $seb_key_array = array_map('trim', $seb_key_array);

                // Sort and compare key arrays
                sort($old_seb_key_array);
                sort($seb_key_array);

                if ($old_seb_key_array !== $seb_key_array) {
                    Paper_utils::set_metadata(
                        $mysqli,
                        $paperID,
                        ['seb_hash' => $seb_key_array],
                        true
                    ); // Delete old entries, replace with new
                    $logger->track_change(
                        'Paper',
                        $paperID,
                        $userObject->get_user_ID(),
                        'Safe Exam Browser keys added/updated',
                        '',
                        'SEB'
                    );
                }
            }
        }
    }


    $feedback = new Feedback($properties, $logger);
    if ($feedback->objectiveFeedbackPossible()) {
        $objectives_report = param::optional('objectives_report', false, param::BOOLEAN);
        $feedback->setObjectiveFeedback($objectives_report, $userObject->get_user_ID());
    }
    if ($feedback->questionFeedbackPossible()) {
        $questions_report = param::optional('questions_report', false, param::BOOLEAN);
        $feedback->setQuestionFeedback($questions_report, $userObject->get_user_ID());
    }
    if ($feedback->cohortPerformanceFeedbackPossible()) {
        $cohort_performance = param::optional('cohort_performance', false, param::BOOLEAN);
        $feedback->setCohortPerformanceFeedback($cohort_performance, $userObject->get_user_ID());
    }
    if ($feedback->externalExaminerFeedbackPossible()) {
        $external_examiner = param::optional('external_examiner', false, param::BOOLEAN);
        $feedback->setExternalExaminerFeedback($external_examiner, $userObject->get_user_ID());
    }

    if (!in_array($papertype, [assessment::TYPE_SUMMATIVE, assessment::TYPE_OSCE])) {
        // Update textual feedback if not a summative paper or OSCE station.
        // Get old settings
        $old_textual_feedback = Paper_utils::get_textual_feedback($paperID, $mysqli);
        for ($i = 1; $i <= 10; $i++) {
            if (!isset($old_textual_feedback[$i]['msg'])) {
                $old_textual_feedback[$i]['msg'] = '';
                $old_textual_feedback[$i]['boundary'] = '';
            }
        }

        $editProperties = $mysqli->prepare('DELETE FROM paper_feedback WHERE paperID = ?');
        $editProperties->bind_param('i', $paperID);
        $editProperties->execute();
        $editProperties->close();

        $editProperties = $mysqli->prepare('INSERT INTO paper_feedback VALUES (NULL, ?, ?, ?)');
        $editProperties->bind_param('iis', $paperID, $boundary, $message);

        // Get new settings
        for ($i = 1; $i <= 10; $i++) {
            $message = trim((string) param::optional("feedback_msg$i", '', param::TEXT, param::FETCH_POST));
            if ($message) {
                $boundary = param::optional("feedback_value$i", 0, param::INT, param::FETCH_POST);
                $editProperties->execute();
            } else {
                $boundary = '';
            }

            if ($old_textual_feedback[$i]['msg'] != $message or $old_textual_feedback[$i]['boundary'] != $boundary) {
                // log a change
                $logger->track_change(
                    'Paper',
                    $paperID,
                    $userObject->get_user_ID(),
                    $old_textual_feedback[$i]['boundary'] . '%&nbsp;' . $old_textual_feedback[$i]['msg'],
                    $boundary . '%&nbsp;' . $message,
                    'textualfeedback'
                );
            }
        }

        $editProperties->close();
    }

    // Get the current (old) metadata security settings from the database.
    $old_meta = '';
    $result = $mysqli->prepare('SELECT name, value FROM paper_metadata_security WHERE paperID = ? ORDER BY name');
    $result->bind_param('i', $paperID);
    $result->execute();
    $result->store_result();
    $result->bind_result($name, $value);
    while ($result->fetch()) {
        if ($old_meta == '') {
            $old_meta = $name . ':' . $value;
        } else {
            $old_meta .= ', ' . $name . ':' . $value;
        }
    }
    $result->close();

    // Loop around the POST fields to get the new metadata security settings.
    $new_meta = '';
    $meta_item_count = param::optional('meta_dropdown_no', 0, param::INT, param::FETCH_POST);
    for ($i = 0; $i < $meta_item_count; $i++) {
        $meta_type = param::optional('meta_type' . $i, '', param::TEXT, param::FETCH_POST);
        $meta_value = param::optional('meta_value' . $i, '', param::TEXT, param::FETCH_POST);

        if ($meta_value != '') {
            if ($new_meta == '') {
                $new_meta = $meta_type . ':' . $meta_value;
            } else {
                $new_meta .= ', ' . $meta_type . ':' . $meta_value;
            }
        }
    }

    if ($old_meta != $new_meta) {
        // The metadata security settings have changed - update the database.
        $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), $old_meta, $new_meta, 'restricttometadata');

        $editProperties = $mysqli->prepare('DELETE FROM paper_metadata_security WHERE paperID = ?');
        $editProperties->bind_param('i', $paperID);
        $editProperties->execute();
        $editProperties->close();

        $editProperties = $mysqli->prepare('INSERT INTO paper_metadata_security VALUES (NULL, ?, ?, ?)');
        $editProperties->bind_param('iss', $paperID, $meta_type, $meta_value);

        for ($i = 0; $i < $meta_item_count; $i++) {
            $meta_type = param::optional('meta_type' . $i, '', param::TEXT, param::FETCH_POST);
            $meta_value = param::optional('meta_value' . $i, '', param::TEXT, param::FETCH_POST);

            if ($meta_value != '') {
                $editProperties->execute();
            }
        }

        $editProperties->close();
    }

    // Get existing Reference Materials
    $existing_refs = [];
    $result = $mysqli->prepare('SELECT refID FROM reference_papers WHERE paperID = ?');
    $result->bind_param('i', $paperID);
    $result->execute();
    $result->store_result();
    $result->bind_result($refID);
    while ($result->fetch()) {
        $existing_refs[$refID] = $refID;
    }
    $result->close();

    $new_refs = [];
    $reference_count = param::optional('reference_no', 0, param::INT, param::FETCH_POST);
    for ($i = 0; $i < $reference_count; $i++) {
        $reference = param::optional("ref$i", 0, param::INT, param::FETCH_POST);
        if ($reference) {
            $new_refs[$reference] = $reference;
        }
    }

    foreach ($new_refs as $new_ref) {
        if (isset($existing_refs[$new_ref])) {
            // The reference material is already linked to the paper.
            unset($existing_refs[$new_ref]);
        } else {
            // A new reference material is being linked to the paper.
            $editProperties = $mysqli->prepare('INSERT INTO reference_papers VALUES (NULL, ?, ?)');
            $editProperties->bind_param('ii', $paperID, $new_ref);
            $editProperties->execute();
            $editProperties->close();

            $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), '', $new_ref, 'referencematerial');
        }
    }
    foreach ($existing_refs as $existing_ref) {
        // These reference materials are no longer linked to the paper.
        $editProperties = $mysqli->prepare('DELETE FROM reference_papers WHERE paperID = ? AND refID = ?');
        $editProperties->bind_param('ii', $paperID, $existing_ref);
        $editProperties->execute();
        $editProperties->close();

        $logger->track_change('Paper', $paperID, $userObject->get_user_ID(), $existing_ref, '', 'referencematerial');
    }
}
echo json_encode('SUCCESS');
