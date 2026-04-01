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
 * @group labs
 * @covers LabFactory
 */
class LabFactoryTest extends unittestdatabase
{
    /** @var array Data for a campus */
    protected array $campus1;

    /** @var array Data for a campus */
    protected array $campus2;

    /** @var array Data for a lab */
    protected array $lab1;

    /** @var array Data for a lab */
    protected array $lab2;

    /** @var array Data for a lab */
    protected array $lab3;

    #[\Override]
    public function datageneration(): void
    {
        $labgenerator = $this->get_datagenerator('labs');

        $this->campus1 = $labgenerator->create_campus(['name' => 'Test Campus', 'isdefault' => 1]);
        $this->campus2 = $labgenerator->create_campus(['name' => 'Secondary Campus', 'isdefault' => 0]);

        $this->lab1 = $labgenerator->create_lab(['name' => 'Main computer room', 'campus' => $this->campus1['name']]);
        $this->lab2 = $labgenerator->create_lab(['name' => 'Another room', 'campus' => $this->campus1['name']]);
        $this->lab3 = $labgenerator->create_lab(['name' => 'Green room', 'campus' => $this->campus2['name']]);

        // Add 4 PCs to lab 1.
        $labgenerator->create_exam_pc(['lab' => $this->lab1['name']]);
        $labgenerator->create_exam_pc(['lab' => $this->lab1['name']]);
        $labgenerator->create_exam_pc(['lab' => $this->lab1['name']]);
        $labgenerator->create_exam_pc(['lab' => $this->lab1['name']]);

        // Add 2 PCs to lab 2.
        $labgenerator->create_exam_pc(['lab' => $this->lab2['name']]);
        $labgenerator->create_exam_pc(['lab' => $this->lab2['name']]);

        // Add 3 PCs to lab 3.
        $labgenerator->create_exam_pc(['lab' => $this->lab3['name']]);
        $labgenerator->create_exam_pc(['lab' => $this->lab3['name']]);
        $labgenerator->create_exam_pc(['lab' => $this->lab3['name']]);
    }

    /**
     * Tests that we can get a list of the labs in ExamSys
     */
    public function testGetAllLabs(): void
    {
        $labfactory = new LabFactory($this->db);
        $labs = $labfactory->getAllLabs();

        $this->assertCount(3, $labs);

        $this->assertInstanceOf(Lab::class, $labs[0]);
        $this->assertEquals($this->lab3['id'], $labs[0]->get_id());
        $this->assertEquals($this->lab3['name'], $labs[0]->get_name());
        $this->assertEquals($this->campus2['name'], $labs[0]->get_campus());
        $this->assertEquals(3, $labs[0]->getNumberOfPC());

        $this->assertInstanceOf(Lab::class, $labs[1]);
        $this->assertEquals($this->lab2['id'], $labs[1]->get_id());
        $this->assertEquals($this->lab2['name'], $labs[1]->get_name());
        $this->assertEquals($this->campus1['name'], $labs[1]->get_campus());
        $this->assertEquals(2, $labs[1]->getNumberOfPC());

        $this->assertInstanceOf(Lab::class, $labs[2]);
        $this->assertEquals($this->lab1['id'], $labs[2]->get_id());
        $this->assertEquals($this->lab1['name'], $labs[2]->get_name());
        $this->assertEquals($this->campus1['name'], $labs[2]->get_campus());
        $this->assertEquals(4, $labs[2]->getNumberOfPC());
    }
}
