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

function fullQuestionType($abreviation, $string)
{
    $fullname = match ($abreviation) {
        'area' => $string['area'],
        'blank' => $string['blank'],
        'branching' => $string['branching'],
        'enhancedcalc' => $string['calculation'],
        'dichotomous' => $string['dichotomous'],
        'extmatch' => $string['extmatch'],
        'flash' => $string['flash'],
        'hotspot' => $string['hotspot'],
        'info' => $string['info'],
        'keyword_based' => $string['keyword_based_short'],
        'labelling' => $string['labelling'],
        'likert' => $string['likert'],
        'matrix' => $string['matrix'],
        'mcq' => $string['mcq'],
        'mrq' => $string['mrq'],
        'rank' => $string['rank'],
        'random' => $string['random_short'],
        'sct' => $string['sct_short'],
        'textbox' => $string['textbox'],
        'true_false' => $string['true_false'],
        '%' => $string['alltypes'],
        default => $fullname,
    };
    return $fullname;
}
