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

use testing\unittest\unittestdatabase;

/**
 * Tests for the PaperProperties::can_user_edit_paper() method.
 *
 * @author Iyud Dissanayake <iyud.dissanayake@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2025 onwards The University of Nottingham
 * @package tests
 * @covers PaperProperties
 * @group paper
 */
class PaperPropertiesCanUserEditPaperTest extends unittestdatabase
{
    /** @var array Details of a module. */
    protected $testModule;

    /** @var array Details of a system module. */
    protected $systemModule;

    /** @var array Details of the paper owner. */
    protected $paperOwner;

    /** @var array Details of a standards setter user. */
    protected $standardsSetter;

    /** @var array Details of a staff user with module access. */
    protected $staffUser;

    /** @var array Details of a user with no special access. */
    protected $regularUser;

    /** @var array Details of the test paper. */
    protected $paper;

    /**
     * Generate the base data used by all the tests.
     */
    public function datageneration(): void
    {
        // Get the current session
        $yearutils = new \yearutils($this->db);
        $this->currentSession = $yearutils->get_current_session();
        
        // Create modules
        $moduleGen = $this->get_datagenerator('modules', 'core');
        
        // Create the test module and assign the system module
        $this->testModule = $moduleGen->create_module(['fullname' => 'Test Module', 'moduleid' => 'TEST123']);

        $systemModuleId = \module_utils::get_idMod('SYSTEM', $this->db);
        $this->systemModule = ['id' => $systemModuleId, 'moduleid' => 'SYSTEM'];
        
        // Create users with different roles
        $userGen = $this->get_datagenerator('users', 'core');
        $this->paperOwner = $userGen->create_user(['roles' => 'Staff', 'sid' => '12345', 'surname' => 'Owner']);
        $this->standardsSetter = $userGen->create_user(['roles' => 'Standards Setter', 'sid' => '23456', 'surname' => 'Setter']);
        $this->staffUser = $userGen->create_user(['roles' => 'Staff', 'sid' => '34567', 'surname' => 'Staff']);
        $this->regularUser = $userGen->create_user(['roles' => 'Student', 'sid' => '45678', 'surname' => 'Regular']);

        // Create module enrollments
        $moduleGen->create_enrolment(['moduleid' => $this->testModule['id'], 'userid' => $this->staffUser['id']]);

        // Create a paper
        $paperGen = $this->get_datagenerator('papers', 'core');
        $paperParams = [
            'papertitle' => 'Test Paper',
            'papertype' => \assessment::TYPE_FORMATIVE,
            'paperowner' => $this->paperOwner['username'],
            'modulename' => [$this->testModule['fullname']],
            'calendaryear' => $this->currentSession,
        ];
        $this->paper = $paperGen->create_paper($paperParams);
    }

    /**
     * Test that a SysAdmin can edit any paper.
     *
     * @group paper
     */
    public function testSysAdminCanEditPaper()
    {
        // Get a SysAdmin user
        $admin = $this->get_base_admin();
        $this->set_active_user($admin['id']);
        
        // Get the paper properties
        $property = PaperProperties::get_paper_properties_by_id($this->paper['id'], $this->db, '', false);

        // Test that the SysAdmin can edit the paper
        $this->assertTrue($property->can_user_edit_paper($this->userobject));
    }
}