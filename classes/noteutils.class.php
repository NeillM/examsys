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
 * Utility class student notes functions.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require_once $cfg_web_root . '/classes/networkutils.class.php';

Class StudentNotes {
  static function get_note($paperID, $userID, $db) {
		$result = $db->prepare("SELECT note_id, note, DATE_FORMAT(note_date,'%d/%m/%Y %H:%i') AS note_date, au.title, au.initials, au.surname, su.title, su.initials, su.surname, student_id, su.username FROM (student_notes, users au, users su) LEFT JOIN sid ON su.id = sid.userID WHERE student_notes.note_authorID = au.id AND student_notes.userID = su.id AND paper_id = ? AND student_notes.userID = ?");
		$result->bind_param('ii', $paperID, $userID);
		$result->execute();
		$result->bind_result($note_id, $note, $note_date, $author_title, $author_initials, $author_surname, $student_title, $student_initials, $student_surname, $student_id, $student_username);
		$result->store_result();
		if ($result->num_rows == 0) {
		  return false;
		}		
		$result->fetch();
		$result->close();
		
		return array('note_id'=>$note_id, 'note'=>$note, 'date'=>$note_date, 'author_title'=>$author_title, 'author_initials'=>$author_initials, 'author_surname'=>$author_surname, 'student_title'=>$student_title, 'student_initials'=>$student_initials, 'student_surname'=>$student_surname, 'student_id'=>$student_id, 'student_username'=>$student_username);
  }
	
	static function add_note($student_userID, $note, $paperID, $authorID, $db) {
		$result = $db->prepare("INSERT INTO student_notes VALUES (NULL, ?, ?, NOW(), ?, ?)");
		$result->bind_param('isii', $student_userID, $note, $paperID, $authorID);
		$result->execute();  
		$result->close();
	}
	
	static function update_note($note, $note_id, $db) {
		$result = $db->prepare("UPDATE student_notes SET note = ? WHERE note_id = ?");
		$result->bind_param('si', $note, $note_id);
		$result->execute();  
		$result->close();
	}
}

Class PaperNotes {
  static function get_all_notes_by_paper($paperID, $db) {
    $notes = array();
    // Query any student notes for the current paper
    $result = $db->prepare("SELECT userID FROM student_notes WHERE paper_id = ?");
    $result->bind_param('i', $paperID);
    $result->execute();
    $result->bind_result($userID);
    while ($result->fetch()) {
      $notes[$userID] = 'y';
    }
    $result->close();
    
    return $notes;
  }
  
  static function get_note($paperID, $address, $db) {
    $result = $db->prepare("SELECT note_id, note FROM paper_notes WHERE paper_id = ? AND note_workstation = ?");
    $result->bind_param('is', $_GET['paperID'], $address);
    $result->execute();
    $result->bind_result($note_id, $note);
    $result->fetch();
    $result->close();
		
		return array('note_id'=>$note_id, 'note'=>$note);
	}

  static function add_note($note, $paperID, $authorID, $db) {
		$current_address = NetworkUtils::get_client_address();

		$result = $db->prepare("INSERT INTO paper_notes VALUES (NULL, ?, NOW(), ?, ?, ?)");
		$result->bind_param('siis', $note, $paperID, $authorID, $current_address);
		$result->execute();
		$result->close();
	}
	
	static function update_note($note, $note_id, $db) {
		$result = $db->prepare("UPDATE paper_notes SET note = ? WHERE note_id = ?");
    $result->bind_param('si', $note, $note_id);
    $result->execute();
    $result->close();
	}
}
?>