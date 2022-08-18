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
 * Tests the QuestionsMetadata class.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2021 onwards The University of Nottingham
 * @package tests
 */
class QuestionsMetadataTest extends \testing\unittest\unittestdatabase
{
    /**
     * @var array Storage for question data in tests
     */
    private $question;

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
    }

    /**
     * Tests that question metadata is correctly set in the database
     * @group questions
     */
    public function testSet(): void
    {
        // Insert.
        QuestionsMetadata::set($this->question['id'], 'testtype', 'testvalue');
        $actual = $this->query(array('columns' => array('type', 'value', 'questionID'), 'table' => 'questions_metadata', 'orderby' => 'type'));
        $expected = array(
            0 => array(
                'type' => 'externalref',
                'value' =>  $this->question['externalref'],
                'questionID' =>  $this->question['id'],
            ),
            1 => array(
                'type' => 'testtype',
                'value' => 'testvalue',
                'questionID' =>  $this->question['id'],
            ),
        );
        $this->assertEquals($expected, $actual);

        // Update and test for long strings.
        QuestionsMetadata::set($this->question['id'], 'externalref', str_repeat('9876543210', 100));
        $actual = $this->query(array('columns' => array('type', 'value', 'questionID'), 'table' => 'questions_metadata', 'orderby' => 'type'));
        $expected = array(
            0 => array(
                'type' => 'externalref',
                'value' => str_repeat('9876543210', 100), // 1000 character string for long varchar
                'questionID' =>  $this->question['id'],
            ),
            1 => array(
                'type' => 'testtype',
                'value' => 'testvalue',
                'questionID' =>  $this->question['id'],
            ),
        );
        $this->assertEquals($expected, $actual);

        // Set the test value to empty, confirm deleted
        QuestionsMetadata::set($this->question['id'], 'externalref', $this->question['externalref']);
        QuestionsMetadata::set($this->question['id'], 'testtype', '');
        $actual = $this->query(array('columns' => array('type', 'value', 'questionID'), 'table' => 'questions_metadata', 'orderby' => 'type'));
        $expected = array(
            0 => array(
                'type' => 'externalref',
                'value' =>  $this->question['externalref'],
                'questionID' =>  $this->question['id'],
            ),
        );
        $this->assertEquals($expected, $actual);

        // Update and check that exception occurs attempting to set to >2500 characters
        $this->expectExceptionMessage('Maximum metadata size exceeded');
        QuestionsMetadata::set($this->question['id'], 'externalref', str_repeat('9876543210', 251));
    }

    /**
     * Tests that question metadata is correctly set in the database (array)
     * @group questions
     */
    public function testSetArray(): void
    {
        // Insert.
        QuestionsMetadata::setArray($this->question['id'], ['testtype' => 'testvalue', 'testtype2' => 'anothertestvalue']);
        $actual = $this->query(array('columns' => array('type', 'value', 'questionID'), 'table' => 'questions_metadata', 'orderby' => 'type'));
        $expected = array(
            0 => array(
                'type' => 'externalref',
                'value' =>  $this->question['externalref'],
                'questionID' =>  $this->question['id'],
            ),
            1 => array(
                'type' => 'testtype',
                'value' => 'testvalue',
                'questionID' =>  $this->question['id'],
            ),
            2 => array(
                'type' => 'testtype2',
                'value' => 'anothertestvalue',
                'questionID' =>  $this->question['id'],
            ),
        );
        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests that question metadata is correctly retrieved when multiple values present
     * @group questions
     */
    public function testGet(): void
    {
        // Add another metadata so there is not just one in there.
        QuestionsMetadata::set($this->question['id'], 'another testtype', 'another testvalue');
        // Confirm we get the right one.
        $actual = QuestionsMetadata::get($this->question['id'], 'externalref');
        $expected = 'testvalue';
        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests that array of question metadata is correctly retrieved when multiple values present
     * @group questions
     */
    public function testGetArray(): void
    {
        // Add another metadata so there is not just one in there.
        QuestionsMetadata::set($this->question['id'], 'testtype1', 'testvalue1');
        QuestionsMetadata::set($this->question['id'], 'testtype2', 'testvalue2');
        // Confirm we get both
        $actual = QuestionsMetadata::getArray($this->question['id'], ['testtype1', 'testtype2']);
        $expected = ['testtype1' => 'testvalue1', 'testtype2' => 'testvalue2'];
        ksort($actual);
        $this->assertEquals($expected, $actual);
        // Retrieve all
        $actual = QuestionsMetadata::getArray($this->question['id']);
        $expected = ['externalref' => 'testvalue', 'testtype1' => 'testvalue1', 'testtype2' => 'testvalue2'];
        ksort($actual);
        $this->assertEquals($expected, $actual);
    }
}
