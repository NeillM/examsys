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
 * Unit tests the folder_utils classes methods.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 onwards The University of Nottingham
 * @package tests
 * @group core
 * @group folder_utils
 * @covers folder_utils
 */
class Folder_utilsTest extends unittestdatabase
{
    /** @var array A top level folder */
    protected array $folder1;

    /** @var array A sub-folder */
    protected array $folder1a;

    /** @var array A top level folder */
    protected array $folder2;

    /** @var array A module */
    protected array $testmodule;

    /** @var array Details of a teacher who owns folder 1 (and it's sub folders) */
    protected array $user1;

    /** @var array Details of a teacher who owns folder 2 */
    protected array $user2;

    #[\Override]
    public function datageneration(): void
    {
        $foldergenerator = $this->get_datagenerator('folder');
        $modulegenerator = $this->get_datagenerator('modules');
        $usergenerator = $this->get_datagenerator('users');

        $this->testmodule = $modulegenerator->create_module([
            'moduleid' => 'TEST1001',
            'fullname' => 'Test module',
        ]);

        $this->user1 = $usergenerator->create_user([
            'username' => 'teacher1',
            'roles' => 'Staff',
        ]);
        $this->user2 = $usergenerator->create_user([
            'username' => 'teacher2',
            'roles' => 'Staff',
        ]);

        $modulegenerator->create_module_team([
            'moduleid' => $this->testmodule['moduleid'],
            'username' => $this->user2['username']
        ]);

        $this->folder1 = $foldergenerator->create_folder([
            'name' => 'My little folder',
            'ownerID' => $this->user1['id'],
        ]);

        $this->folder1a = $foldergenerator->create_folder([
            'name' => 'My sub-folder',
            'ownerID' => $this->user1['id'],
            'parent' => $this->folder1['id'],
        ]);
        $foldergenerator->addTeamToFolder([
            'folder' => $this->folder1a['id'],
            'module' => $this->testmodule['id'],
        ]);

        $this->folder2 = $foldergenerator->create_folder([
            'name' => 'Special folder',
            'ownerID' => $this->user2['id'],
        ]);
    }

    /**
     * Tests that we can successfully get details of folders a user is associated with.
     */
    public function testGetUsersFolderNames(): void
    {
        // Test when the user on no modules.
        $this->assertEquals(
            [
                $this->folder2['id'] => $this->folder2['name'],
            ],
            folder_utils::getUsersFolderNames($this->user2['id'], []),
        );

        // Test when the user is on a module that has folders attached to it.
        $this->assertEquals(
            [
                $this->folder1a['id'] => $this->folder1a['name'],
                $this->folder2['id'] => $this->folder2['name'],
            ],
            folder_utils::getUsersFolderNames($this->user2['id'], [$this->testmodule['id']]),
        );

        // Test that we get a specified module even if the user does nto have access to it.
        $this->assertEquals(
            [
                $this->folder1['id'] => $this->folder1['name'],
                $this->folder2['id'] => $this->folder2['name'],
            ],
            folder_utils::getUsersFolderNames($this->user2['id'], [], $this->folder1['id']),
        );
    }
}
