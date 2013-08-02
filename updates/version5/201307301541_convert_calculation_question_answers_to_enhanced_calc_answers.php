<?php

// Your code here

/*
 *****   NOW UPDATE THE INSTALLER SCRIPT   *****
 */

require_once $cfg_web_root . 'classes/stringutils.class.php';

echo "<li>Converting Calculation answers to enhanced calculation answers</li>";

set_time_limit(0);
ini_set('memory_limit', '0');

$LOG = '0';
$logarray = array('0', '1', '2', '3', '0_deleted', '1_deleted', '_late');

foreach ($logarray as $LOG) {
    $loop = 0;


    $sql = "select questions.q_id,id,user_answer,settings from questions,log$LOG where q_type='calculation' and questions.q_id=log$LOG.q_id;";
    $sql = "select questions.q_id,id,user_answer,settings from questions,log$LOG where q_type='enhancedcalc' and questions.q_id=log$LOG.q_id;";


    $result = $mysqli->prepare("$sql");
    $result->execute();
    $result->store_result();
    $result->bind_result($qid, $uid, $user_answer, $settings);


    while ($result->fetch()) {
        if (strpos($user_answer, '{') !== false) {
        } else {
            print '.';
            unset($statusdata);
            unset($ansdata);
            $settings = json_decode($settings, true);
            $userans[$qid][$uid] = $user_answer;

            $tmp_answer = explode('|', $user_answer);


            //[0] is user answer, [1] is correct answer, [2] is array variables
            //print $qid . '   ';
            if (!isset($settings['units'])) {
                $settings['units'] = '';
            }
            $new_user_answer['uans'] = $tmp_answer[0] . ' ' . $settings['units'];
            $new_user_answer['uansunit'] = $settings['units'];
            $new_user_answer['uansnumb'] = $tmp_answer[0];
            if (!isset($tmp_answer[1])) {
                $tmp_answer[1] = '';
            }
            $new_user_answer['cans'] = $tmp_answer[1];
            $ansdata['guessedunits'] = $settings['units'];

            $tolerance_full = $settings['tolerance_full'];
            if (StringUtils::ends_with($tolerance_full, '%')) {
                $tolerance_perc = rtrim($tolerance_full, '%');
                $tolerance_full = abs(round($tmp_answer[1] * ($tolerance_perc / 100), 12));
            }
            $tolerance_partial = $settings['tolerance_partial'];
            if (StringUtils::ends_with($tolerance_partial, '%')) {
                $tolerance_perc = rtrim($tolerance_partial, '%');
                $tolerance_partial = abs(round($tmp_answer[1] * ($tolerance_perc / 100), 12));
            }

            $ansdata['tolerance_full'] = $tolerance_full;
            $ansdata['tolerance_partial'] = $tolerance_partial;

            if ($tmp_answer[1] < 0) {
                $ansdata['tolerance_fullans'] = $tmp_answer[1] - $tolerance_full;
                $ansdata['tolerance_fullneg'] = $tmp_answer[1] + $tolerance_full;
                $ansdata['tolerance_partans'] = $tmp_answer[1] - $tolerance_partial;
                $ansdata['tolerance_partneg'] = $tmp_answer[1] + $tolerance_partial;

            } else {
                $ansdata['tolerance_fullans'] = $tmp_answer[1] + $tolerance_full;
                $ansdata['tolerance_fullneg'] = $tmp_answer[1] - $tolerance_full;
                $ansdata['tolerance_partans'] = $tmp_answer[1] + $tolerance_partial;
                $ansdata['tolerance_partneg'] = $tmp_answer[1] - $tolerance_partial;

            }

            $new_user_answer['ans'] = $ansdata;


            $statusdata['units'] = true;

            $saved_response = $tmp_answer[0];

            $saved_response_clean = preg_replace('([^0-9\.\-])', '', $saved_response);

            if ($tmp_answer[0] == '') {

                $new_user_answer['uansnumb'] = '';
                $new_user_answer['uans'] = '';
                $new_user_answer['uansunit'] = '';
                //echo "<td>" . display_response($tmp_display_students_response, 'blank') . "<input type=\"text\" style=\"color:#808080; text-align:right\" name=\"q' . $question . '\" size=\"10\" value=\"" . $string['unanswered'] . "\" />" . $settings['units'];
            } else {
                echo '<td>';


                $statusdata['overall'] = 1;
                if (isset($tmp_answer[1])) {
                    $difference = round(abs($saved_response_clean - $tmp_answer[1]), 12);

                    if ($saved_response_clean == $tmp_answer[1]) {
                        //$paper[$question]['mark'] = $paper[$question]['marks_correct'];
                        $statusdata['exact'] = true;
                    } elseif ($difference > 0 and $difference <= $tolerance_full and $tolerance_full > 0) {
                        //$paper[$question]['mark'] = $paper[$question]['marks_correct'];
                        $statusdata['exact'] = false;
                        $statusdata['tolerance_full'] = true;
                    } elseif ($difference > 0 and $difference <= $tolerance_partial and $tolerance_partial > 0) {
                        //$paper[$question]['mark'] = $paper[$question]['marks_partial'];
                        $statusdata['exact'] = false;
                        $statusdata['tolerance_full'] = false;
                        $statusdata['tolerance_partial'] = true;

                    } else {
                        //$paper[$question]['mark'] = $paper[$question]['marks_incorrect'];
                        $statusdata['exact'] = false;
                        $statusdata['tolerance_full'] = false;
                        $statusdata['tolerance_partial'] = false;
                        $statusdata['overall'] = 0;
                    }

                }
            }

            $new_user_answer['staus'] = $statusdata;


            $vars = array('$A', '$B', '$C', '$D', '$E', '$F', '$G', '$H', '$I', '$J', '$K', '$L');
            unset($variable_array);
            unset($varsdata);
            if (isset($tmp_answer[2])) {
                if ($tmp_answer[2] == '') {
                    $variable_array = array('error', 'error', 'error', 'error', 'error', 'error', 'error', 'error', 'error');
                } else {
                    $variable_array = explode(',', $tmp_answer[2]);
                }
            } else {
                $variable_array = array();
            }
            $varno = 0;
            //   var_dump($variable_array);
            foreach ($variable_array as $individual_variable) {
                if ($individual_variable != '') {
                    $varsdata[$vars[$varno]] = $individual_variable;
                }
                $varno++;
            }
            if (isset($vardata)) {
                $new_user_answer['vars'] = $varsdata;
            }
            $new_user_answer['original'] = $user_answer;
            //   print $user_answer;
            //    var_dump($new_user_answer);
            //  print "<br><br>";

            $jsoned = json_encode($new_user_answer);

            $sql = "UPDATE log$LOG set user_answer=? where id=?";
            $update = $mysqli->prepare("$sql");
            $update->bind_param('si', $jsoned, $uid);
            $update->execute();
            $update->store_result();
            $loop++;
            if ($loop % 200 == 0) {
                echo '<br>';
                @ob_flush();
            }
            $update->close();
        }


    }
    $result->close();
}

