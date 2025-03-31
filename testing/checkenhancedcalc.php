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
 * Check enhanced calc setup is OK
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/sysadmin_auth.inc';

require_once $cfg_web_root . 'lang/' . $language . '/include/common.php'; // Include common language file that all scripts need
require_once $cfg_web_root . 'include/custom_error_handler.inc';
require $cfg_web_root . 'plugins/questions/enhancedcalc/enhancedcalc.class.php';

echo '<html>';
echo 'Starting<br><br>';

$enhancedcalcType = $configObject->get_setting('core', 'cfg_calc_type');
$enhancedcalcSettings = $configObject->get_setting('core', 'cfg_calc_settings');

$name = '\\plugins\\questions\\enhancedcalc\\engine\\' . mb_strtolower($enhancedcalcType) . '\\Engine';
if (empty($enhancedcalcType) or !class_exists($name)) {
    $name = '\\plugins\\questions\\enhancedcalc\\engine\\phpeval\\Engine';
    $enhancedcalcType = 'BLANK, MISSING or invalid setting that means it defaults to phpEval';
}
/** @var \plugins\questions\enhancedcalc\Engine $enhancedcalcObj1 */
$enhancedcalcObj1 = new $name($enhancedcalcSettings);

$sets = var_export($enhancedcalcSettings, true);
echo "<li>Enhanced Calc is set to <b>$enhancedcalcType</b></li>";
echo "<li>Settings are $sets</li>";

$data = [];
$data[] = [['$A' => 2, '$B' => 2],'$A+$B', '4'];
$data[] = [['$A' => 2, '$B' => 2],'$A*$B', '4'];
$data[] = [['$A' => 3, '$B' => 3],'$A+$B', '6'];
$data[] = [['$A' => 3, '$B' => 3],'$A*$B', '9'];

$data[] = [['$A' => 4, '$B' => 4],'$A+$B', '8'];
$data[] = [['$A' => 4, '$B' => 4],'$A*$B', '16'];

$data[] = [['$A' => 8, '$B' => 2],'$A/$B', '4'];
$data[] = [['$A' => 8, '$B' => 2],'$A-$B', '6'];

// Run test for each supported function.
$data[] = [['$A' => 2], 'abs($A)', '2'];
$data[] = [['$A' => -2], 'abs($A)', '2'];
$data[] = [['$A' => 0.1], 'acos($A) - acos($A)', '0'];
$data[] = [['$A' => 3], 'acosh($A) - acosh($A)', '0'];
$data[] = [['$A' => 0.1], 'asin($A) - asin($A)', '0'];
$data[] = [['$A' => 3], 'asinh($A) - asinh($A)', '0'];
$data[] = [['$A' => 3, '$B' => 5], 'atan2($A, $B) - atan2($A, $B)', '0'];
$data[] = [['$A' => 3], 'atan($A) - atan($A)', '0'];
$data[] = [['$A' => 0.1], 'atanh($A) - atanh($A)', '0'];
$data[] = [['$A' => 0.1], 'ceil($A)', '1'];
$data[] = [['$A' => 0.1], 'cos($A) - cos($A)', '0'];
$data[] = [['$A' => 5], 'cosh($A) - cosh($A)', '0'];
$data[] = [['$A' => 180], 'deg2rad($A) - deg2rad($A)', '0'];
$data[] = [['$A' => 2], 'exp($A) - exp($A)', '0'];
$data[] = [['$A' => 2], 'expm1($A) - expm1($A)', '0'];
$data[] = [['$A' => 2.9], 'floor($A)', '2'];
$data[] = [['$A' => 2, '$B' => 5], 'fmod($A, $B)', '2'];
$data[] = [['$A' => 10], 'log10($A)', '1'];
$data[] = [['$A' => 10], 'log1p($A) - log1p($A)', '0'];
$data[] = [['$A' => 10], 'log($A) - log($A)', '0'];
$data[] = [['$A' => 5, '$B' => 10], 'max($A, $B)', '10'];
$data[] = [['$A' => 5, '$B' => 10], 'min($A, $B)', '5'];
$data[] = [['$A' => 5], 'round(pi, $A)', '3.14159'];
$data[] = [['$A' => 10], 'sin($A) - sin($A)', '0'];
$data[] = [['$A' => 10], 'sinh($A) - sinh($A)', '0'];
$data[] = [['$A' => 10], 'sqrt($A) - sqrt($A)', '0'];
$data[] = [['$A' => 10], 'tan($A) - tan($A)', '0'];
$data[] = [['$A' => 10], 'tanh($A) - tanh($A)', '0'];

foreach ($data as $individual) {
    $vars = $individual[0];
    $formula = $individual[1];
    $cans = $individual[2];
    try {
        $ans = $enhancedcalcObj1->calculate_correct_ans($vars, $formula);
    } catch (Exception) {
        $ans = false;
    }
    $check = false;
    $correct = false;
    if (!is_null($cans)) {
        $check = true;
        if ($ans === $cans) {
            $correct = true;
        }
    }
    $varlist = 'Where ';
    foreach ($vars as $key => $value) {
        $varlist .= "$key=$value, ";
    }
    if ($ans === false) {
        echo '<li STYLE="background: #FF00FF;">Getting a failed status back from calculation plugin</li>';
    } else {
        if ($check === true) {
            if ($correct == true) {
                //correct
                echo "<li STYLE=\"background: #00FF00;\">$varlist $formula = $ans</li>";
            } else {
                //incorrect
                echo "<li STYLE=\"background: #FF0000;\">$varlist $formula = $ans  Correct Answer Listed as: $cans</li>";
            }
        } else {
            echo "<li>$varlist $formula = $ans</li>";
        }
    }
}
