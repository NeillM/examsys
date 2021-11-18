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
 * Tests the OptionsMetadata class.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @author Richard Aspden <richard@getjohn.co.uk>
 * @copyright Copyright (c) 2021 onwards The University of Nottingham
 * @package tests
 */
class OptionsMetadataTest extends \testing\unittest\unittestdatabase
{
    /**
     * @var array Storage for question data in tests
     */
    private $question;

    /**
     * @var array Storage for associated option data in tests
     */
    private $option;

    /**
     * Generate data for test.
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        $datagenerator = $this->get_datagenerator('questions', 'core');
        $this->question = $datagenerator->create_question(
            array(
                'type' => 'mcq',
                'user' => 'admin',
                'status' => 1,
                'theme' => 'test theme',
                'scenario' => 'test scenario',
                'leadin' => 'test leadin',
                'notes' => 'test notes',
                'q_media' => '1517406311.png',
                'q_media_width' => 480,
                'q_media_height' => 105,
                'q_media_alt' => 'question image',
                'q_option_order' => 'random',
                'display_method' => 'vertical',
                'score_method' => 'Mark per Option',
                'externalref' => 'testvalue',
            )
        );

        $this->option = $datagenerator->add_options_to_question([
            'question' => $this->question['id'],
            'option_text' => 'test option',
            'feedback_right' => 'right',
            'feedback_wrong' => 'wrong',
            'correct' => 1,
            'marks_correct' => 2,
            'marks_partial' => 1,
            'marks_incorrect' => 0
        ]);
    }

    /**
     * Tests that option metadata is correctly set in the database
     * @group questions
     */
    public function testSet(): void
    {
        // Insert.
        OptionsMetadata::set($this->option['id_num'], 'testtype', 'testvalue');
        OptionsMetadata::set($this->option['id_num'], 'longtest', str_repeat('testvalue1', 100));
        $actual = $this->query(array('columns' => array('type', 'value', 'optionID'), 'table' => 'options_metadata', 'orderby' => 'type'));
        $expected = [
            [
                'type' => 'longtest',
                'value' => str_repeat('testvalue1', 100),
                'optionID' => $this->option['id_num'],
            ],[
                'type' => 'testtype',
                'value' => 'testvalue',
                'optionID' => $this->option['id_num'],
            ],
        ];
        $this->assertEquals($expected, $actual);
        // Update.
        OptionsMetadata::set($this->option['id_num'], 'testtype', 'newvalue');
        $actual = $this->query(array('columns' => array('type', 'value', 'optionID'), 'table' => 'options_metadata', 'orderby' => 'type'));
        $expected = [
            [
                'type' => 'longtest',
                'value' => str_repeat('testvalue1', 100),
                'optionID' => $this->option['id_num'],
            ],[
                'type' => 'testtype',
                'value' => 'newvalue',
                'optionID' => $this->option['id_num'],
            ],
        ];
        $this->assertEquals($expected, $actual);
        // Update and check that exception occurs attempting to set to >2500 characters
        $this->expectExceptionMessage('Maximum metadata size exceeded');
        OptionsMetadata::set($this->option['id_num'], 'longtest', str_repeat('9876543210',251));
    }

    /**
     * Tests that option metadata is correctly set in the database (array)
     * @group questions
     */
    public function testSetArray(): void
    {
        // Insert.
        OptionsMetadata::setArray($this->option['id_num'], ['testtype' => 'testvalue', 'testtype2' => 'anothertestvalue']);
        $actual = $this->query(array('columns' => array('type', 'value', 'optionID'), 'table' => 'options_metadata', 'orderby' => 'type'));
        $expected = array(
            0 => array(
                'type' => 'testtype',
                'value' => 'testvalue',
                'optionID' => $this->option['id_num'],
            ),
            1 => array(
                'type' => 'testtype2',
                'value' => 'anothertestvalue',
                'optionID' => $this->option['id_num'],
            ),
        );
        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests that option metadata is correctly retrieved from the database.
     * @group questions
     */
    public function testGet(): void
    {
        // Insert a couple of metadata entries
        OptionsMetadata::set($this->option['id_num'], 'testtype', 'testvalue');
        OptionsMetadata::set($this->option['id_num'], 'another testtype', 'another testvalue');
        // Confirm we get the right one.
        $actual = OptionsMetadata::get($this->option['id_num'], 'testtype');
        $expected = 'testvalue';
        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests that option metadata is correctly retrieved when multiple values present
     * @group questions
     */
    public function testGetArray(): void
    {
        // Add another metadata so there is not just one in there.
        OptionsMetadata::set($this->option['id_num'], 'testtype1', 'testvalue1');
        OptionsMetadata::set($this->option['id_num'], 'testtype2', 'testvalue2');
        OptionsMetadata::set($this->option['id_num'], 'testtype3', 'testvalue3');
        // Confirm we get just the first two
        $actual = OptionsMetadata::getArray($this->option['id_num'], ['testtype1', 'testtype2']);
        $expected = ['testtype1' => 'testvalue1', 'testtype2' => 'testvalue2'];
        ksort($actual);
        $this->assertEquals($expected, $actual);
        // Check retrieving all
        $actual = OptionsMetadata::getArray($this->option['id_num']);
        $expected = ['testtype1' => 'testvalue1', 'testtype2' => 'testvalue2', 'testtype3' => 'testvalue3'];
        ksort($actual);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests that option metadata is correctly deleted from the database
     * @group questions
     */
    public function testDelete(): void
    {
        OptionsMetadata::set($this->option['id_num'], 'testtype', 'testvalue');
        OptionsMetadata::set($this->option['id_num'], 'deletetest', 'nothere');
        OptionsMetadata::delete($this->option['id_num'], 'deletetest');
        $actual = $this->query(array('columns' => array('type', 'value', 'optionID'), 'table' => 'options_metadata'));
        $expected = array(
            0 => array(
                'type' => 'testtype',
                'value' =>  'testvalue',
                'optionID' =>  $this->option['id_num'],
            ),
        );
        $this->assertEquals($expected, $actual);

        OptionsMetadata::setArray($this->option['id_num'], ['deletetest' => 'nothere', 'delete2' => 'alsogone']);
        OptionsMetadata::delete($this->option['id_num'], ['deletetest', 'delete2']);
        $actual = $this->query(array('columns' => array('type', 'value', 'optionID'), 'table' => 'options_metadata'));
        $expected = array(
            0 => array(
                'type' => 'testtype',
                'value' =>  'testvalue',
                'optionID' =>  $this->option['id_num'],
            ),
        );
        $this->assertEquals($expected, $actual);

        OptionsMetadata::setArray($this->option['id_num'], ['deletetest' => 'nothere', 'delete2' => 'alsogone']);
        OptionsMetadata::delete($this->option['id_num']);
        $actual = $this->query(array('columns' => array('type', 'value', 'optionID'), 'table' => 'options_metadata'));
        $expected = [];
        $this->assertEquals($expected, $actual);
    }
}
