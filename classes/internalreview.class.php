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
* Internal Review package
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2016 onwards The University of Nottingham
*/

/**
 * Internal review helper class.
 */
 class internalreview {
     
    /*
     * Db connection
     * @var $db
     */
    private $db;
    
    /**
     * Constuctor
     * @param mysqli $db
     */
    function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Get a list of internal reviews for the current user.
     * @param int $userID  - Question ID of the random question to be loaded.
     * @return array Paper details the current user should review.
     */
    public function get_review_papers($userID) {
        $papers = array();
        $result = $this->db->prepare("SELECT paper_title, property_id, fullscreen, DATE_FORMAT(internal_review_deadline,'%d/%m/%Y') AS internal_review_deadline, crypt_name, paper_type FROM (properties, properties_reviewers) WHERE properties.property_id = properties_reviewers.paperID AND deleted IS NULL AND internal_review_deadline >= CURDATE() AND reviewerID = ? AND type = 'internal' ORDER BY paper_title");
        $result->bind_param('i', $userID);
        $result->execute();
        $result->bind_result($paper_title, $property_id, $fullscreen, $internal_review_deadline, $crypt_name, $paper_type);
        $result->store_result();    
        while ($result->fetch()) {
            $reviewed = '';
            $result2 = $this->db->prepare("SELECT DATE_FORMAT(MAX(started),'%d/%m/%Y %T') AS started FROM review_metadata WHERE reviewerID = ? AND paperID = ?");
            $result2->bind_param('ii', $userID, $property_id);
            $result2->execute();
            $result2->bind_result($reviewed);
            $result2->fetch();
            $result2->close();
        
            $papers[] = array('paper_title'=>$paper_title, 'crypt_name'=>$crypt_name, 'fullscreen'=>$fullscreen, 'reviewed'=>$reviewed, 'internal_review_deadline'=>$internal_review_deadline, 'type' => $paper_type);
        }
        $result->close();
        return $papers;
    }
 }
