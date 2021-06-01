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
 * Tests for the PaperProperties::shouldLogLate() method.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2021 onwards The University of Nottingham
 * @package tests
 * @covers PaperProperties
 * @group paper
 */
class PaperPropertiesShouldLogLateTest extends unittestdatabase
{
    /** @var array Details of a lab in Rogo. */
    protected $lab;

    /** @var array Details of a module. */
    protected $testmodule;

    /** @var array Details of a module with timing enabled. */
    protected $timedmodule;

    /** @var array details of the user registered on the module. */
    protected $user;

    /**
     * Generate the base data used by all the tests.
     */
    public function datageneration(): void
    {
        $datagenerator = $this->get_datagenerator('labs');
        $datagenerator->create_campus(['isdefault' => true]);
        $this->lab = $datagenerator->create_lab([]);

        $datagenerator = $this->get_datagenerator('modules');
        $this->testmodule = $datagenerator->create_module(['moduleid' => 'ABCD1234', 'fullname' => 'Is late testing']);
        $timed_properties = [
            'moduleid' => 'ABCD4321',
            'fullname' => 'Is late timed testing',
            'timed_exams' => 1
        ];
        $this->timedmodule = $datagenerator->create_module($timed_properties);

        $datagenerator = $this->get_datagenerator('users');
        $this->user = $datagenerator->create_user(['sid' => '987654321']);
    }

    /**
     * Generates a paper and returns the PaperProperties object for it.
     *
     * @param int $type The type of paper to generate.
     * @param string $start The start time of the paper
     * @param string $end The end time of the paper
     * @param string $modulename The full name of the module.
     * @param string|null $labs The labs the paper is attached to
     * @param bool $remote Should a remote exam be generated.
     * @return PaperProperties
     */
    protected function generatePaperProperties(
        int $type,
        string $start,
        string $end,
        string $modulename,
        ?string $labs = null,
        bool $remote = false
    ): PaperProperties
    {
        $datagenerator = $this->get_datagenerator('papers');
        $papaer_details = [
            'papertitle' => 'Test paper',
            'papertype' => $type,
            'paperowner' => $this->admin['username'],
            'modulename' => $modulename,
            'duration' => 60,
            'startdate' => $start,
            'enddate' => $end,
            'labs' => $labs,
            'remote' => $remote,
        ];
        $paper = $datagenerator->create_paper($papaer_details);

        $paper_property = new PaperProperties(Config::get_instance()->db);
        $paper_property->set_property_id($paper['id']);
        $paper_property->load();

        return $paper_property;
    }

    /**
     * Generates and returns the log metadata for a user on the paper.
     *
     * @param int $paper_id
     * @param int $user_id
     * @param string $start
     * @return \LogMetadata
     */
    protected function generateMetaDataForPaper(int $paper_id, int $user_id, string $start): LogMetadata
    {
        $datagenerator = $this->get_datagenerator('log');
        $started = new DateTime($start);
        $params = [
            'userID' => $user_id,
            'paperID' => $paper_id,
            'started' => $started->format('Y-m-d H:i:s'),
        ];
        $datagenerator->create_metadata($params);
        $log = new LogMetadata($user_id, $paper_id, Config::get_instance()->db);
        $log->get_record();
        return $log;
    }

    /**
     * Tests that we will correctly put results into the late log if submitted now.
     *
     * @param string $start
     * @param string $end
     * @param bool $expected
     * @dataProvider dataProgress
     */
    public function testProgress(string $start, string $end, bool $expected)
    {
        $properties = $this->generatePaperProperties(assessment::TYPE_PROGRESS, $start, $end, $this->testmodule['fullname']);
        $metadata = $this->generateMetaDataForPaper($properties->get_property_id(), $this->user['id'], $start);
        $this->set_active_user($this->user['id']);
        $this->assertEquals($expected, $properties->shouldLogLate(null, $metadata));
    }

    /**
     * Data for testProgress.
     *
     * @return array
     */
    public function dataProgress(): array
    {
        return [
            'during time' => ['30 minutes ago', '30 minutes', false],
            'after time' => ['61 minutes ago', '1 minute ago', true],
        ];
    }

    /**
     * Test that formative exams never go to the late log.
     *
     * @param string $start
     * @param string $end
     * @dataProvider dataFormative
     */
    public function testFormative(string $start, string $end)
    {
        $properties = $this->generatePaperProperties(assessment::TYPE_FORMATIVE, $start, $end, $this->testmodule['fullname']);
        $metadata = $this->generateMetaDataForPaper($properties->get_property_id(), $this->user['id'], $start);
        $this->set_active_user($this->user['id']);
        $this->assertFalse($properties->shouldLogLate(null, $metadata));
    }

    /**
     * Data for testProgress.
     *
     * @return array
     */
    public function dataFormative(): array
    {
        return [
            'during time' => ['30 minutes ago', '30 minutes'],
            'after time' => ['61 minutes ago', '1 minute ago'],
        ];
    }

    /**
     * Tests that summative exams that are not times work correctly.
     *
     * @param string $start
     * @param string $end
     * @param bool $expected
     * @dataProvider dataSummativeUnTimed
     */
    public function testSummativeUnTimed(string $start, string $end, bool $expected)
    {
        // Create the paper and get the property.
        $properties = $this->generatePaperProperties(assessment::TYPE_SUMMATIVE, $start, $end, $this->testmodule['fullname'], $this->lab['id']);
        $metadata = $this->generateMetaDataForPaper($properties->get_property_id(), $this->user['id'], $start);

        // Test that the late log is used correctly.
        $this->set_active_user($this->user['id']);
        $this->assertEquals($expected, $properties->shouldLogLate($this->lab['id'], $metadata));
    }

    /**
     * Data for estSummativeUnTimed
     * @return array[]
     */
    public function dataSummativeUnTimed(): array
    {
        return [
            'during exam' => ['30 minutes ago', '30 minutes',false],
            'after exam' => ['61 minutes ago', '1 minute ago', true],
        ];
    }

    /**
     * Tests that when in a lab summative exams detect if they should put answers in the late log.
     *
     * @param string $paper_start
     * @param string $paper_end
     * @param string $lab_start
     * @param string $lab_end
     * @param bool $expected
     * @dataProvider dataSummativeTimed
     */
    public function testSummativeTimed(string $paper_start, string $paper_end, string $lab_start, string $lab_end, bool $expected)
    {
        // Create the paper and get the property.
        $properties = $this->generatePaperProperties(assessment::TYPE_SUMMATIVE, $paper_start, $paper_end, $this->timedmodule['fullname'], $this->lab['id']);
        $metadata = $this->generateMetaDataForPaper($properties->get_property_id(), $this->user['id'], $lab_start);

        // Create the lab end time.
        $datagenerator = $this->get_datagenerator('labs');
        $lab_time = [
            'labID' => $this->lab['id'],
            'invigilatorID' => $this->admin['id'],
            'paperID' => $properties->get_property_id(),
            'start_time' => $lab_start,
            'end_time' => $lab_end,
        ];
        $datagenerator->createLabTime($lab_time);

        // Test that the late log is used correctly.
        $this->set_active_user($this->user['id']);
        $this->assertEquals($expected, $properties->shouldLogLate($this->lab['id'], $metadata));
    }

    /**
     * Data for testSummativeTimed
     *
     * @return array[]
     */
    public function dataSummativeTimed(): array
    {
        return [
            'during exam' => ['30 minutes ago', '30 minutes', '30 minutes ago', '30 minutes', false],
            'during, lab ends after exam end' => ['61 minutes ago', '1 minute ago', '50 minutes ago', '10 minutes', false],
            'after exam' => ['61 minutes ago', '1 minute ago', '61 minutes ago', '1 minute ago', true],
            'after lab end, before exam end' => ['62 minutes ago', '30 minutes', '61 minutes ago', '1 minute ago', true],
        ];
    }

    /**
     * Tests that remote summative answers will be sent to the late log.
     *
     * @param string $paper_start
     * @param string $paper_end
     * @param string $user_start
     * @param bool $expected
     * @dataProvider dataSummativeRemote
     */
    public function testSummativeRemote(string $paper_start, string $paper_end, string $user_start, bool $expected)
    {
        // Create the paper and get the property.
        $properties = $this->generatePaperProperties(assessment::TYPE_SUMMATIVE, $paper_start, $paper_end, $this->testmodule['fullname'], null, true);
        $metadata = $this->generateMetaDataForPaper($properties->get_property_id(), $this->user['id'], $user_start);

        // Test that the late log is used correctly.
        $this->set_active_user($this->user['id']);
        $this->assertEquals($expected, $properties->shouldLogLate(null, $metadata));
    }

    /**
     * Data for testSummativeRemote.
     *
     * @return array
     */
    public function dataSummativeRemote(): array
    {
        return [
            'during exam' => ['4 hours ago', '4 hours', '30 minutes ago', false],
            'user out of time' => ['4 hours ago', '4 hours', '61 minutes ago', true],
            'exam over, user has time remaining' => ['8 hours ago', '1 minute ago', '30 minutes ago', true],
            'exam over, user out of time' => ['8 hours ago', '1 minute ago', '61 minutes ago', true],
        ];
    }
}