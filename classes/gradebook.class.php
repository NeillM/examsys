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
* Gradebook package
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2015 onwards The University of Nottingham
*/

/**
 * Gradebook helper class.
 */
class gradebook {
    
    /**
     * The db connection
     */
    private $db;
    
    /**
     * Constructor
     * @param object $db
     * @return void 
     */
    function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Check if the paper has been graded.
     * @param integer $userid 
     * @param integer $paperid 
     * @return bool true if already graded
     */
    public function paper_graded($paperid) {
        $result = $this->db->prepare("SELECT count(paperid) FROM gradebook_paper WHERE paperid = ?");
        $result->bind_param('i', $paperid);
        $result->execute();
        $result->bind_result($count);
        $result->fetch();
        $result->close();
        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Store grade in gradebook.
     * @param integer $userid 
     * @param integer $paperid 
     * @param integer $grade - raw grade
     * @param double $adjusted - adjusted grade
     * @param integer $classification 
     * @return bool true if grade added to gradebook
     */
    public function store_grade($userid, $paperid, $grade, $adjusted, $classification) {
        
        $student = \UserUtils::has_user_role($userid, 'Student', $this->db);
        if ($student) {
            $sqluser = $this->db->prepare("INSERT INTO gradebook_user (paperid, userid, raw_grade, adjusted_grade, classification) VALUES (?, ?, ?, ?, ?)");
            $sqluser->bind_param('iiids', $paperid, $userid, $grade, $adjusted, $classification);
            $sqluser->execute();
            $sqluser->close();
            if ($this->db->errno != 0) {
                return false;
            }
            return true;
        } else {
            return false;
        }
       
    }
    
    /**
     * Create a gradebook for the paper
     * @param integer $paperid 
     * @return bool true if created 
     */
    public function create_gradebook($paperid) {
        if (!$this->paper_graded($paperid)) {
            $sqlpaper = $this->db->prepare("INSERT INTO gradebook_paper (paperid) VALUES (?)");
            $sqlpaper->bind_param('i', $paperid);
            $sqlpaper->execute();
            $sqlpaper->close();
            if ($this->db->errno != 0) {
                return false;
            }
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Get the gradebook for a paper
     * @param int $paperid 
     * @return array|bool gradebook for paper or false  
     */
    public function get_paper_gradebook($paperid) {
        if ($this->paper_graded($paperid)) {
            $sql = $this->db->prepare("SELECT gu.userid, u.username, gu.raw_grade, ROUND(gu.adjusted_grade, 2), gu.classification FROM
                gradebook_paper p, gradebook_user gu, users u WHERE p.paperid = gu.paperid AND u.id = gu.userid AND p.paperid = ?");
            $sql->bind_param('i', $paperid);
            $sql->execute();
            $sql->bind_result($userid, $username, $raw_grade, $adjusted_grade, $classification);
            $users = array();
            while ($sql->fetch()) {
                $users[$userid] = array('raw_grade' => $raw_grade, 'adjusted_grade' => $adjusted_grade,
                    'classification' => $classification, 'username' => $username);
            }
            $gradebook[$paperid] = $users;
            $sql->close();
            return $gradebook;
        } else {
            return false;
        }
    }
    
    /**
     * Get the gradebook for a modle
     * @param int $moduleid 
     * @return array|bool gradebook for module or false
     */
    public function get_module_gradebook($moduleid) {
        $sql = $this->db->prepare("SELECT
            p.paperid, gu.userid, u.username, gu.raw_grade, ROUND(gu.adjusted_grade, 2), gu.classification
            FROM
                gradebook_paper p, 
                gradebook_user gu, 
                users u,
                properties_modules m
            WHERE
                m.property_id = p.paperid AND
                p.paperid = gu.paperid AND 
                u.id = gu.userid AND
                m.idMod = ?");
        $sql->bind_param('i', $moduleid);
        $sql->execute();
        $sql->bind_result($paperid, $userid, $username, $raw_grade, $adjusted_grade, $classification);
        $papers = array();
        while ($sql->fetch()) {
            $users = array('raw_grade' => $raw_grade, 'adjusted_grade' => $adjusted_grade, 'classification' => $classification,
                'username' => $username);
            $papers[$paperid][$userid] = $users;
        }
        $sql->close();
        $gradebook[$moduleid] = $papers;
        if (count($gradebook[$moduleid]) > 0) {
            return $gradebook;
        } else {
            return false;
        }
    }
}
