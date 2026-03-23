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
 * Unit tests the PaperProperties classes methods.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 onwards The University of Nottingham
 * @package tests
 * @group core
 * @group paper
 * @covers PaperProperties
 */
class NewPaperPropertiesTest extends unittestdatabase
{
    /** @var array Details of a module. */
    protected array $testmodule;

    /** @var array Details of a school. */
    protected array $testschool;

    /** @var array Details of a staff user. */
    protected array $staff;

    #[\Override]
    public function datageneration(): void
    {
        $modulegenerator = $this->get_datagenerator('modules');
        $schoolgenerator = $this->get_datagenerator('school');
        $usergenerator = $this->get_datagenerator('users');

        $this->staff = $usergenerator->create_user(['roles' => ['Staff']]);

        $this->testschool = $schoolgenerator->create_school(['facultyID' => $this->faculty]);

        $this->testmodule = $modulegenerator->create_module([
            'schoolID' => $this->testschool['id'],
            'moduleid' => 'TEST1001',
            'fullname' => 'Test module',
        ]);
    }

    /**
     * Tests that we can get a list of potential external reviewers for the paper.
     */
    public function testGetExternalExaminerList(): void
    {
        $modulegenerator = $this->get_datagenerator('modules');
        $papergenerator = $this->get_datagenerator('papers');
        $usergenerator = $this->get_datagenerator('users');

        $modulegenerator->create_module_team(['moduleid' => $this->testmodule['moduleid'], 'username' => $this->staff['username']]);

        $reviewer1 = $usergenerator->create_user([
            'roles' => ['External Examiner'],
            'surname' => 'Cunningham',
            'initials' => 'J',
            'first_names' => 'John',
            'title' => 'Prof',
        ]);
        $reviewer2 = $usergenerator->create_user([
            'roles' => ['External Examiner'],
            'surname' => 'Slim',
            'initials' => 'W',
            'first_names' => 'William',
            'title' => 'Mr',
        ]);
        $reviewer3 = $usergenerator->create_user([
            'roles' => ['External Examiner'],
            'surname' => 'Cunningham',
            'initials' => 'A',
            'first_names' => 'Andrew',
            'title' => 'Dr',
        ]);

        $paper = $papergenerator->create_paper([
            'papertitle' => 'Test paper',
            'papertype' => assessment::TYPE_SUMMATIVE,
            'paperowner' => $this->staff['username'],
            'modulename' => $this->testmodule['fullname']
        ]);

        $otherpaper = $papergenerator->create_paper([
            'papertitle' => 'Test paper 2',
            'papertype' => assessment::TYPE_SUMMATIVE,
            'paperowner' => $this->staff['username'],
            'modulename' => $this->testmodule['fullname']
        ]);

        // Assign one user as an external reviewer.
        $papergenerator->addReviewer([
            'paper' => $paper['papertitle'],
            'reviewer' => $reviewer1['username'],
            'type' => 'external',
        ]);

        // Assign a different reviewer to another paper.
        $papergenerator->addReviewer([
            'paper' => $otherpaper['papertitle'],
            'reviewer' => $reviewer2['username'],
            'type' => 'external',
        ]);

        $properties = PaperProperties::get_paper_properties_by_id($paper['id'], $this->db, []);
        $reviewers = $properties->getExternalExaminerList();

        $this->assertCount(3, $reviewers);

        $this->assertEquals([
            'id' => $reviewer3['id'],
            'name' => "{$reviewer3['surname']}, {$reviewer3['first_names']}. {$reviewer3['title']}",
            'selected' => false,
        ], $reviewers[0]);
        $this->assertEquals([
            'id' => $reviewer1['id'],
            'name' => "{$reviewer1['surname']}, {$reviewer1['first_names']}. {$reviewer1['title']}",
            'selected' => true,
        ], $reviewers[1]);
        $this->assertEquals([
            'id' => $reviewer2['id'],
            'name' => "{$reviewer2['surname']}, {$reviewer2['first_names']}. {$reviewer2['title']}",
            'selected' => false,
        ], $reviewers[2]);
    }

    /**
     * Tests that we can get the list of potential internal reviewers.
     *
     * @param string $role The role of the user making the call
     * @param bool $in_school_team Flags that the user should be in the school admin team.
     * @param bool $in_mod_team Flags that the user should be in the module team.
     * @param bool $in_other_mod_team Flags that the user should be in the other module team.
     * @param array $expected An ordered list of expected names
     *
     * @dataProvider dataGetInternalReviewerList
     */
    public function testGetInternalReviewerList(
        string $role,
        bool $in_school_team,
        bool $in_mod_team,
        bool $in_other_mod_team,
        array $expected
    ): void {
        $modulegenerator = $this->get_datagenerator('modules');
        $papergenerator = $this->get_datagenerator('papers');
        $schoolgenerator = $this->get_datagenerator('school');
        $usergenerator = $this->get_datagenerator('users');

        $user = $usergenerator->create_user([
            'roles' => [$role],
            'surname' => 'Alexander',
            'initials' => 'H',
            'first_names' => 'Harold',
            'title' => 'Dr',
        ]);

        $reviewer1 = $usergenerator->create_user([
            'roles' => ['Staff'],
            'surname' => 'Cunningham',
            'initials' => 'J',
            'first_names' => 'John',
            'title' => 'Prof',
        ]);
        $reviewer2 = $usergenerator->create_user([
            'roles' => ['Staff'],
                'surname' => 'Slim',
                'initials' => 'W',
                'first_names' => 'William',
                'title' => 'Mr',
        ]);
        $reviewer3 = $usergenerator->create_user([
            'roles' => ['Staff'],
            'surname' => 'Cunningham',
            'initials' => 'A',
            'first_names' => 'Andrew',
            'title' => 'Dr',
        ]);

        $admin = $usergenerator->create_user([
            'roles' => ['Admin'],
            'surname' => 'Pound',
            'initials' => 'D',
            'first_names' => 'Dudley',
            'title' => 'Dr',
        ]);

        $schoolgenerator->addSchoolAdmin(['username' => $admin['username'], 'school' => $this->testschool['id']]);
        $othermodule = $modulegenerator->create_module([
            'schoolID' => $this->testschool['id'],
            'moduleid' => 'TEST2002',
            'fullname' => 'Another module',
        ]);

        $modulegenerator->create_module_team(['username' => $reviewer1['username'], 'moduleid' => $this->testmodule['moduleid']]);
        $modulegenerator->create_module_team(['username' => $reviewer2['username'], 'moduleid' => $othermodule['moduleid']]);
        $modulegenerator->create_module_team(['username' => $reviewer3['username'], 'moduleid' => $this->testmodule['moduleid']]);

        // Create a team for another module.
        $otheruser = $usergenerator->create_user([
            'roles' => ['Staff'],
            'surname' => 'Brooke',
            'initials' => 'A',
            'first_names' => 'Alan',
            'title' => 'Prof',
        ]);
        $otheradmin = $usergenerator->create_user([
            'roles' => ['Admin'],
            'surname' => "O'Conner",
            'initials' => 'R',
            'first_names' => 'Richard',
            'title' => 'Mr',
        ]);
        $otherschool = $schoolgenerator->create_school(['facultyID' => $this->faculty]);
        $schoolgenerator->addSchoolAdmin(['username' => $otheradmin['username'], 'school' => $otherschool['id']]);
        $otherschoolmodule = $modulegenerator->create_module([
            'schoolID' => $otherschool['id'],
            'moduleid' => 'TEST3003',
            'fullname' => 'Yet another module',
        ]);
        $modulegenerator->create_module_team(['username' => $reviewer1['username'], 'moduleid' => $otherschoolmodule['moduleid']]);
        $modulegenerator->create_module_team(['username' => $otheruser['username'], 'moduleid' => $otherschoolmodule['moduleid']]);
        $modulegenerator->create_module_team(['username' => $otheruser['username'], 'moduleid' => $otherschoolmodule['moduleid']]);

        // Put the user into the correct school and module teams.
        if ($in_school_team) {
            $schoolgenerator->addSchoolAdmin(['username' => $user['username'], 'school' => $this->testschool['id']]);
        }
        if ($in_mod_team) {
            $modulegenerator->create_module_team(['username' => $user['username'], 'moduleid' => $this->testmodule['moduleid']]);
        }
        if ($in_other_mod_team) {
            $modulegenerator->create_module_team(['username' => $user['username'], 'moduleid' => $otherschoolmodule['moduleid']]);
        }

        $paper = $papergenerator->create_paper([
            'papertitle' => 'Test paper',
            'papertype' => assessment::TYPE_PROGRESS,
            'paperowner' => $reviewer2['username'],
            'modulename' => $this->testmodule['fullname']
        ]);

        // Assign one user as an external reviewer.
        $papergenerator->addReviewer([
            'paper' => $paper['papertitle'],
            'reviewer' => $reviewer1['username'],
            'type' => 'internal',
        ]);

        $this->set_active_user($user['id']);

        $properties = PaperProperties::get_paper_properties_by_id($paper['id'], $this->db, []);
        $reviewers = $properties->getInternalReviewerList();

        if ($role === 'SysAdmin') {
            // Users from the base data are be included, so we cannot fully identify the users for a sysadmin.
            $this->assertLessThan(count($expected), count($reviewers));
            return;
        }

        $this->assertCount(count($expected), $reviewers);

        // Existing reviewers must be in the list.
        $found_reviewer = false;

        foreach ($expected as $key => $value) {
            $this->assertEquals($value, $reviewers[$key]['name']);

            if ($reviewers[$key]['id'] === $reviewer1['id']) {
                $found_reviewer = true;
                $this->assertTrue($reviewers[$key]['selected']);
            } else {
                $this->assertFalse($reviewers[$key]['selected']);
            }
        }

        $this->assertTrue($found_reviewer);
    }

    /**
     * Data for testing the external examiner list.
     */
    public function dataGetInternalReviewerList(): array
    {
        return [
            // A staff member should see everyone in the modules they are part of.
            ['Staff', false, true, false, [
                'Alexander, Harold. Dr',
                'Cunningham, Andrew. Dr',
                'Cunningham, John. Prof',
                'Pound, Dudley. Dr',
                'Slim, William. Mr',
            ]],
            ['Staff', false, true, true, [
                'Alexander, Harold. Dr',
                'Brooke, Alan. Prof',
                'Cunningham, Andrew. Dr',
                'Cunningham, John. Prof',
                "O'Conner, Richard. Mr",
                'Pound, Dudley. Dr',
                'Slim, William. Mr',
            ]],
            // A staff member should still see users that have been selected even if they are not part of their school.
            ['Staff', false, false, true, [
                'Alexander, Harold. Dr',
                'Brooke, Alan. Prof',
                'Cunningham, John. Prof',
                "O'Conner, Richard. Mr",
            ]],
            // An admin user should see everyone in the school they are part of.
            ['Admin', true, false, false, [
                'Alexander, Harold. Dr',
                'Cunningham, Andrew. Dr',
                'Cunningham, John. Prof',
                'Pound, Dudley. Dr',
                'Slim, William. Mr',
            ]],
            // SysAdmins should get everyone in the system.
            ['SysAdmin', false, false, false, [
                'Alexander, Harold. Dr',
                'Brooke, Alan. Prof',
                'Cunningham, Andrew. Dr',
                'Cunningham, John. Prof',
                "O'Conner, Richard. Mr",
                'Pound, Dudley. Dr',
                'Slim, William. Mr',
            ]],
        ];
    }

    /**
     * Test that there are no errors when we try to get the metadata types and there are none.
     */
    public function testGetUserMetadataTypesNoData(): void
    {
        $modulegenerator = $this->get_datagenerator('modules');
        $papergenerator = $this->get_datagenerator('papers');

        $modulegenerator->create_module_team(['moduleid' => $this->testmodule['moduleid'], 'username' => $this->staff['username']]);

        $paper = $papergenerator->create_paper([
            'papertitle' => 'Test paper',
            'papertype' => assessment::TYPE_FORMATIVE,
            'paperowner' => $this->staff['username'],
            'modulename' => $this->testmodule['fullname']
        ]);

        $properties = PaperProperties::get_paper_properties_by_id($paper['id'], $this->db, []);
        $this->assertEmpty($properties->getUserMetadataTypes());
    }

    /**
     * Test that there are no errors when we try to get the metadata types.
     */
    public function testGetUserMetadataTypes(): void
    {
        $modulegenerator = $this->get_datagenerator('modules');
        $papergenerator = $this->get_datagenerator('papers');
        $usergenerator = $this->get_datagenerator('users');
        $year = 2026;

        $type1 = 'My type';
        $type2 = 'Group';
        $type3 = 'Another group';

        $modulegenerator->create_module_team(['moduleid' => $this->testmodule['moduleid'], 'username' => $this->staff['username']]);

        $student1 = $usergenerator->create_user([
            'roles' => ['Student'],
            'surname' => 'Tudor',
            'initials' => 'E',
            'first_names' => 'Elizabeth',
            'title' => 'Miss',
            'sid' => '1234',
        ]);
        $usergenerator->create_metadata($student1['id'], $this->testmodule['id'], [
            'type' => $type1,
            'value' => 'Value 1',
            'calendar_year' => $year,
        ]);
        $usergenerator->create_metadata($student1['id'], $this->testmodule['id'], [
            'type' => $type2,
            'value' => '1',
            'calendar_year' => $year,
        ]);

        $student2 = $usergenerator->create_user([
            'roles' => ['Student'],
            'surname' => 'Tudor',
            'initials' => 'M',
            'first_names' => 'Mary',
            'title' => 'Mrs',
            'sid' => '2345',
        ]);
        $usergenerator->create_metadata($student2['id'], $this->testmodule['id'], [
            'type' => $type1,
            'value' => 'Value 1',
            'calendar_year' => $year,
        ]);
        $usergenerator->create_metadata($student2['id'], $this->testmodule['id'], [
            'type' => $type3,
            'value' => '',
            'calendar_year' => $year - 1,
        ]);

        $student3 = $usergenerator->create_user([
            'roles' => ['Student'],
            'surname' => 'Windsor',
            'initials' => 'E',
            'first_names' => 'Elizabeth',
            'title' => 'Mrs',
            'sid' => '3456',
        ]);
        $usergenerator->create_metadata($student3['id'], $this->testmodule['id'], [
            'type' => $type1,
            'value' => 'Value 2',
            'calendar_year' => $year,
        ]);
        $usergenerator->create_metadata($student3['id'], $this->testmodule['id'], [
            'type' => $type2,
            'value' => 2,
            'calendar_year' => $year,
        ]);

        $paper = $papergenerator->create_paper([
            'papertitle' => 'Test paper',
            'papertype' => assessment::TYPE_FORMATIVE,
            'paperowner' => $this->staff['username'],
            'modulename' => $this->testmodule['fullname'],
            'session' => $year,
        ]);

        $properties = PaperProperties::get_paper_properties_by_id($paper['id'], $this->db, []);
        $metadata = $properties->getUserMetadataTypes();

        $this->assertCount(3, $metadata);
        $this->assertArrayHasKey($type3, $metadata);
        $this->assertArrayHasKey($type1, $metadata);
        $this->assertArrayHasKey($type2, $metadata);
    }

    /**
     * Tests that we can correctly determine that a question of a specific type is on a paper.
     *
     * @param string $type A question type
     * @param bool $expected The expected result
     *
     * @dataProvider dataHasQuestionsOfType
     */
    public function testHasQuestionsOfType(string $type, bool $expected): void
    {
        $modulegenerator = $this->get_datagenerator('modules');
        $papergenerator = $this->get_datagenerator('papers');
        $questiongenerator = $this->get_datagenerator('questions', 'core');

        $modulegenerator->create_module_team(['moduleid' => $this->testmodule['moduleid'], 'username' => $this->staff['username']]);

        $paper = $papergenerator->create_paper([
            'papertitle' => 'Test paper',
            'papertype' => assessment::TYPE_FORMATIVE,
            'paperowner' => $this->staff['username'],
            'modulename' => $this->testmodule['fullname'],
        ]);

        $question = $questiongenerator->create_question([
            'user' => 'admin',
            'type' => 'enhancedcalc',
            'leadin' => 'A wonderfully insightful question, full of challenge',
        ]);
        $questiongenerator->add_question_to_paper([
            'paper' => $paper['id'],
            'question' => $question['id'],
            'screen' => 1,
            'displaypos' => 1,
        ]);

        $properties = PaperProperties::get_paper_properties_by_id($paper['id'], $this->db, []);

        $this->assertEquals($expected, $properties->hasQuestionsOfType($type));
    }

    /**
     * Data for testing if we can determine if a paper has a type of question on it.
     *
     * @return array[]
     */
    public function dataHasQuestionsOfType(): array
    {
        return [
            ['enhancedcalc', true],
            ['mcq', false],
        ];
    }
}
