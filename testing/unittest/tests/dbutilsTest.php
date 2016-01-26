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

use testing\unittest\unittestdatabase;

/**
 * Test dbutils class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class dbutilstest extends unittestdatabase {

    /**
     * Get init data set from yml
     * @return dataset
     */
    public function getDataSet() {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . "dbutilsTest" . DIRECTORY_SEPARATOR . "campus.yml");
    }
    
    /**
     * Get expected data set from yml
     * @param string $name filename of fixtures
     * @return dataset
     */
    public function getExpectedDataSet($name) {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . "dbutilsTest" . DIRECTORY_SEPARATOR . $name . ".yml");
    }
    
    /**
     * Test generic db insert function
     * @group dbutils
     */
    public function testexecdbinsert() {
        $table = 'campus';
        $params = array('name' => array('s', 'Test Campus'), 'isdefault' => array('i', 0));
        $campus = DBUtils::exec_db_insert($table, $params, $this->db);
        $queryTable = $this->getConnection()->createQueryTable('campus', 'SELECT * FROM campus');
        $expectedTable = $this->getExpectedDataSet('insertcampus')->getTable("campus");
        $this->assertTablesEqual($expectedTable, $queryTable);
    }
    
    /**
     * Test generic db upadte function
     * @group dbutils
     */
    public function testexecdbupdate() {
        $table = 'campus';
        $tableid = 'id';
        $id = 1;
        $params = array('isdefault' => array('i', 0));
        $campus = DBUtils::exec_db_update($table, $tableid, $params, $id, $this->db);
        $queryTable = $this->getConnection()->createQueryTable('campus', 'SELECT * FROM campus');
        $expectedTable = $this->getExpectedDataSet('updatecampus')->getTable("campus");
        $this->assertTablesEqual($expectedTable, $queryTable);
    }
}
