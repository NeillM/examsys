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
 * Tests for the Retention class
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2021 onwards The University of Nottingham
 * @package tests
 */
class RetentionTest extends unittestdatabase
{
    /** @var array Storage for audit data in tests. */
    private array $audit;

    /** @var array Storage for audit data in tests. */
    private array $audit2;

    /** @var array Storage for anomaly data in tests. */
    private array $anomaly;

    /** @var array Storage for anomaly data in tests. */
    private array $anomaly2;

    /**
     * Generate data for test.
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        $datagenerator = $this->get_datagenerator('audit', 'core');
        $time = new \DateTime('91 days ago');
        $this->audit = $datagenerator->create(
            array(
                'userID' => $this->student['id'],
                'action' => Audit::ADDROLE,
                'details' => 'Student',
                'time' => $time->format('Y-m-d H:i:s')
            )
        );
        $time = new \DateTime('89 days ago');
        $this->audit2 = $datagenerator->create(
            array(
                'userID' => $this->student['id'],
                'action' => Audit::REMOVEROLE,
                'details' => 'Student',
                'time' => $time->format('Y-m-d H:i:s')
            )
        );
        $datagenerator = $this->get_datagenerator('papers', 'core');
        $paper = $datagenerator->create_paper(
            array(
                'papertitle' => 'test summative',
                'bidirectional' => '1',
                'fullscreen' => '1',
                'paperowner' => 'admin',
                'papertype' => '2',
                'modulename' => 'Training Module',
                'remote' => 1
            )
        );
        $datagenerator = $this->get_datagenerator('anomaly', 'core');
        $time = new \DateTime('366 days ago');
        $this->anomaly = $datagenerator->createAnomaly(
            array(
                'userid' => $this->student['id'],
                'paperid' => $paper['id'],
                'screen' => 2,
                'type' => \Anomaly::CLOCK,
                'previous' => 'Tue Aug 19 1975 23:15:30 GMT+0200 (CEST)',
                'current' => 'Tue Aug 19 1975 23:10:30 GMT+0200 (CEST)',
                'time' => $time->getTimestamp(),
            )
        );
        $time = new \DateTime('364 days ago');
        $this->anomaly2 = $datagenerator->createAnomaly(
            array(
                'userid' => $this->student['id'],
                'paperid' => $paper['id'],
                'screen' => 3,
                'type' => \Anomaly::CLOCK,
                'previous' => 'Tue Aug 20 1975 23:15:30 GMT+0200 (CEST)',
                'current' => 'Tue Aug 20 1975 23:10:30 GMT+0200 (CEST)',
                'time' => $time->getTimestamp(),
            )
        );
    }

    /**
     * Test Data deletion based on retention policy.
     * - table with timestamp time column
     * @group retention
     */
    public function testDeleteDataByRetentionPolicy(): void
    {
        Retention::deleteDataByRetentionPolicy('audit_log');
        $queryTable = $this->query(
            array(
                'table' => 'audit_log',
                'columns' => array('userID', 'action', 'details', 'sourceID', 'source')
            )
        );
        $expectedTable = array(
            0 => array (
                'userID' => $this->audit2['userID'],
                'action' => $this->audit2['action'],
                'details' => $this->audit2['details'],
                'sourceID' => $this->audit2['sourceID'],
                'source' => $this->audit2['source'],
            ),
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Test Data deletion based on retention policy.
     * - table with bigint time column
     * @group retention
     */
    public function testDeleteDataByRetentionPolicyBigInt(): void
    {
        Retention::deleteDataByRetentionPolicy('anomaly');
        $queryTable = $this->query(array('columns' => array('id', 'type', 'time', 'details', 'userID',
            'paperID', 'screen'), 'table' => 'anomaly'));
        $expectedTable = array(
            0 => array(
                'id' => $this->anomaly2['id'],
                'type' => $this->anomaly2['type'],
                'time' => $this->anomaly2['timestamp'],
                'details' => json_encode($this->anomaly2['details']),
                'userID' => $this->anomaly2['userid'],
                'paperID' => $this->anomaly2['paperid'],
                'screen' => $this->anomaly2['screen'],
            ),
        );
        $this->assertEquals($expectedTable, $queryTable);
    }
}
