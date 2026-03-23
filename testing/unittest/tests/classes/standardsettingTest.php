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
 * Unit tests the StandardSetting classes methods.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 onwards The University of Nottingham
 * @package tests
 * @group core
 * @group paper
 * @covers StandardSetting
 */
class StandardSettingTest extends unittestdatabase
{
    /** @var array Data for a module used in the tests. */
    protected array $testmodule;

    /** @var array Data for a paper used in the tests. */
    protected array $paper;

    /** @var array Details of a school. */
    protected array $testschool;

    /** @var array Data for a staff user that is part of the tests. */
    protected array $staff;

    #[\Override]
    public function datageneration(): void
    {
        $modulegenerator = $this->get_datagenerator('modules');
        $papergenerator = $this->get_datagenerator('papers');
        $schoolgenerator = $this->get_datagenerator('school');
        $usergenerator = $this->get_datagenerator('users');

        $this->staff = $usergenerator->create_user(['roles' => ['Staff']]);

        $this->testschool = $schoolgenerator->create_school(['facultyID' => $this->faculty]);

        $this->testmodule = $modulegenerator->create_module([
            'schoolID' => $this->testschool['id'],
            'moduleid' => 'TEST1001',
            'fullname' => 'Test module',
        ]);

        $this->paper = $papergenerator->create_paper([
            'papertitle' => 'Test paper',
            'papertype' => assessment::TYPE_SUMMATIVE,
            'paperowner' => $this->staff['username'],
            'modulename' => $this->testmodule['fullname'],
        ]);
    }

    /**
     * Tests that we can gte a list of standard settings made for a paper.
     */
    public function testGetStdSettingWithName(): void
    {
        $papergenerator = $this->get_datagenerator('papers');
        /** @var \testing\datagenerator\standard_setting $datagenerator */
        $datagenerator = $this->get_datagenerator('standard_setting');

        // Add some data that should not be returned.
        $otherpaper = $papergenerator->create_paper([
            'papertitle' => 'Test paper 2',
            'papertype' => assessment::TYPE_SUMMATIVE,
            'paperowner' => $this->staff['username'],
            'modulename' => $this->testmodule['fullname'],
        ]);

        $datagenerator->createStandatdSetting($otherpaper['id'], $this->staff['id'], [
            'std_set' => '2026-02-18 13:01:59',
            'method' => 'Ebel',
            'group_review' => 'Some review details that should not be returned',
            'pass_score' => 22,
            'distinction_score' => 55.27,
        ]);

        // Add some data that should be returned.
        $settings1 = $datagenerator->createStandatdSetting($this->paper['id'], $this->staff['id'], [
            'std_set' => '2026-02-16 13:01:59',
            'method' => 'Ebel',
            'group_review' => 'The result of the group review',
            'pass_score' => 50.2,
            'distinction_score' => 88.67,
        ]);

        $settings2 = $datagenerator->createStandatdSetting($this->paper['id'], $this->staff['id'], [
            'std_set' => '2026-03-16 09:44:05',
            'method' => 'Modified Angoff',
            'group_review' => 'Some kind of review',
            'pass_score' => 39.1,
            'distinction_score' => 89.4,
        ]);

        $stdsetting = new StandardSetting($this->db);
        $settings = $stdsetting->getStdSettingWithName($this->paper['id']);

        $this->assertCount(2, $settings);

        // The results should list newer standard settings first.
        $this->assertEquals([
            'std_setID' => $settings2['id'],
            'title' => $this->staff['title'],
            'surname' => $this->staff['surname'],
            'initials' => $this->staff['initials'],
            'reviewer' => $this->staff['id'],
            'display_date' => '16/03/26 09:44',
            'group_review' => $settings2['group_review'],
        ], $settings[0]);

        $this->assertEquals([
            'std_setID' => $settings1['id'],
            'title' => $this->staff['title'],
            'surname' => $this->staff['surname'],
            'initials' => $this->staff['initials'],
            'reviewer' => $this->staff['id'],
            'display_date' => '16/02/26 13:01',
            'group_review' => $settings1['group_review'],
        ], $settings[1]);
    }
}
