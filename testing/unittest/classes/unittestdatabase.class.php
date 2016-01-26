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

namespace testing\unittest;
use Config as RogoConfig;
    
/**
 * Unit test database class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
abstract class unittestdatabase extends \PHPUnit_Extensions_Database_TestCase {
    /**
     * @var pdo object $pdo Only instantiate pdo once for test clean-up/fixture load.
     */
    static private $pdo = null;

    /**
     * @var pdo connection $conn Only instantiate PHPUnit_Extensions_Database_DB_IDatabaseConnection once per test.
     */
    private $conn = null;

    /**
     * @var object $default_config config object used during test.
     */
    public $config;
    
    /**
     * @var object $default_config config object used to reset test.
     */
    public $default_config;
    
    /**
     * @var mysqli $db database object.
     */
    public $db;
    
    /**
     * Set-up config and db connections.
     */
    final public function setUp() {
        $this->config = RogoConfig::get_instance();
        $this->default_config = clone($this->config);
        $this->config->use_phpunit_site();
        // Open db connection.
        $this->db = new \mysqli($this->config->get('cfg_db_host'), $this->config->get('cfg_db_sysadmin_user'), $this->config->get('cfg_db_sysadmin_passwd'),
            $this->config->get('cfg_db_database'), $this->config->get('cfg_db_port'));
        parent::setUp();
    }
    
    /**
     * Tear down config object and close db connections.
     * @return  
     */
    final public function tearDown() {
        // Reset the config object.
        RogoConfig::set_mock_instance(clone($this->default_config));
        // Close db connection.
        $this->db->close();
        parent::tearDown();
    }

    /**
     * Get PDO connection for dbunit
     * @return PDOobject
     */
    final public function getConnection() {
        if ($this->conn === null) {
            if (self::$pdo == null) {
                self::$pdo = new \PDO("mysql:dbname=" . $this->config->get('cfg_db_database') . ";" . "host=" . $this->config->get('cfg_db_host'), $this->config->get('cfg_phpunit_db_user'), $this->config->get('cfg_phpunit_db_password'));
            }
            $this->conn = $this->createDefaultDBConnection(self::$pdo, $this->config->get('cfg_db_database'));
        }
        return $this->conn;
    }
}
