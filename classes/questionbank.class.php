<?php
// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

/**
*
* Question bank class.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require_once '../classes/question_status.class.php';

class QuestionBank {
  
  private $db;
  private $idMod;
  private $string;
  private $notice;
  private $bank_types = null;
  private $stats = null;

  public function __construct($idMod, $string, $notice, $db) {
    $this->db     = $db;
    $this->string = $string;
    $this->idMod  = $idMod;
    $this->notice = $notice;
  }
  
  public function get_categories($type) {
    if ($this->bank_types == null) {
      $this->load_categories($type);
    }
    
    return $this->bank_types;
  }
  
  public function get_stats($type) {
    return $this->stats;
  }
  
  private function get_keywords() {
    $keywords_array = array();

    $result = $this->db->prepare("SELECT keyword, keywords_user.id FROM keywords_user, modules WHERE keywords_user.userID = modules.id AND modules.id = $this->idMod ORDER BY keyword");
    $result->execute();
    $result->bind_result($keyword, $keywordID);
    while ($result->fetch()) {
      $keywords_array[$keywordID] = $keyword;
    }
    $result->close();

    return $keywords_array;
  }

  private function load_categories($type) {
    switch($type) {
      case 'keyword':
        $this->load_stats($type);
        $keywords = $this->get_keywords();
        if (count($keywords) == 0) {    // Stop we have no keywords.
          echo $this->notice->info_strip($this->string['nokeywords'], 100) . "</div>\n</body>\n</html>\n";
          exit;
        }
        foreach ($keywords as $keywordID=>$keyword) {
          //$this->bank_types[$keyword] = 'list.php?keyword=' . $keywordID;
          $this->bank_types[$keywordID] = $keyword;
        }
        break;
      case 'type':
        $this->load_stats($type);
        $this->bank_types = array(
          'area' => $this->string['area'],
          'enhancedcalc' => $this->string['enhancedcalc'],
          'dichotomous' => $this->string['dichotomous'],
          'extmatch' => $this->string['extmatch'],
          'blank' => $this->string['blank'],
          'hotspot' => $this->string['hotspot'],
          'info' => $this->string['info'],
          'keyword_based' => $this->string['keyword_based'],
          'labelling' => $this->string['labelling'],
          'likert' => $this->string['likert'],
          'matrix' => $this->string['matrix'],
          'mcq'=> $this->string['mcq'],
          'mrq' => $this->string['mrq'],
          'random' => $this->string['random'],
          'rank' => $this->string['rank'],
          'sct' => $this->string['sct'],
          'textbox' => $this->string['textbox'],
          'true_false' => $this->string['true_false']
        );
        break;
      case 'status':
        $statuses = QuestionStatus::get_all_statuses($this->db, $this->string);
        $this->load_stats($type);
        $this->bank_types = array();
        foreach ($statuses as $status) {
          $status_name = $status->get_name();
          $this->bank_types[$status->id] = $status_name;
        }
        break;
      case 'bloom':
        $this->load_stats($type);
        $this->bank_types = array(
          'knowledge' => $this->string['knowledge'],
          'comprehension' => $this->string['comprehension'],
          'application' => $this->string['application'],
          'analysis' => $this->string['analysis'],
          'synthesis' => $this->string['synthesis'],
          'evaluation' => $this->string['evaluation']
        );
        break;
      case 'performance':
        //$this->load_stats($type);
        $this->bank_types = array(
            'veryeasy' => $this->string['veryeasy'],
            'easy' => $this->string['easy'],
            'moderate' => $this->string['moderate'],
            'hard' => $this->string['hard'],
            'veryhard' => $this->string['veryhard'],
            'highest' => $this->string['highest'],
            'high' => $this->string['high'],
            'intermediate' => $this->string['intermediate'],
            'low' => $this->string['low']
        );
        break;
    }
  }
  
  private function load_stats($type) {
    $this->stats = array();

    if ($type == 'type') {
      $sql = 'SELECT COUNT(questions.q_id), q_type FROM questions, questions_modules WHERE questions.q_id = questions_modules.q_id AND idMod = ? AND deleted IS NULL GROUP BY q_type';
    } elseif ($type == 'status') {
      $sql = 'SELECT COUNT(questions.q_id), name FROM questions, questions_modules, question_statuses WHERE questions.status = question_statuses.id AND questions.q_id = questions_modules.q_id AND idMod = ? AND deleted IS NULL GROUP BY status';
    } elseif ($type == 'bloom') {
      $sql = 'SELECT COUNT(questions.q_id), bloom FROM questions, questions_modules WHERE questions.q_id = questions_modules.q_id AND idMod = ? AND deleted IS NULL GROUP BY bloom';
    } elseif ($type == 'keyword') {
      $sql = 'SELECT COUNT(questions.q_id), keywordID FROM questions, questions_modules, keywords_question, keywords_user WHERE keywords_question.keywordID = keywords_user.id AND questions.q_id = questions_modules.q_id AND idMod = ? AND questions.q_id = keywords_question.q_id AND deleted IS NULL GROUP BY keywordID';
    }
    
    $result = $this->db->prepare($sql);
    $result->bind_param('i', $this->idMod);
    $result->execute();
    $result->bind_result($number, $type);
    while ($result->fetch()) {
      $this->stats[$type] = $number;
    } 
    $result->close();
  }
    
}
?>