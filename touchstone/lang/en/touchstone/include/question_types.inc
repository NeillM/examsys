<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  function fullQuestionType($abreviation) {
    switch ($abreviation) {
      case 'blank':
        $fullname = '1Fill-in-the-Blank';
        break;
      case 'branching':
        $fullname = '1Branching';
        break;
      case 'calculation':
        $fullname = '1Calculation';
        break;
      case 'dichotomous':
        $fullname = '1Dichotomous';
        break;
      case 'extmatch':
        $fullname = '1Extended Matching';
        break;
      case 'flash':
        $fullname = '1Flash&nbsp;Interface';
        break;
      case 'hotspot':
        $fullname = '1Image Hotspot';
        break;
      case 'info':
        $fullname = '1Information Block';
        break;
      case 'keyword_based':
        $fullname = '1Keyword-based';
        break;
      case 'labelling':
        $fullname = '1Labelling';
        break;
      case 'likert':
        $fullname = '1Likert Scale';
        break;
      case 'matrix':
        $fullname = '1Matrix';
        break;
      case 'mcq':
        $fullname = '1Multiple Choice';
        break;
      case 'mrq':
        $fullname = '1Multiple Response';
        break;
      case 'rank':
        $fullname = '1Ranking';
        break;
      case 'random':
        $fullname = '1Random';
        break;
      case 'sct';
        $fullname = '1Script Concordance';
        break;
      case 'textbox':
        $fullname = '1Text Box';
        break;
      case 'timedate':
        $fullname = '1Time/Date';
        break;
      case '%':
        $fullname = '1All Types';
        break;
    }
    return $fullname;
  }
?>