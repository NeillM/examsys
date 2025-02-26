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

use testing\unittest\unittest;

/**
 * Test matrix question class
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 * @package tests
 */
class matrixtest extends unittest
{
    /**
     * Test question header setter
     * @group question
     */
    public function test_set_question_head()
    {
        $data = questiondata::get_datastore('matrix');
        $data->set_question_head();
        $this->assertTrue($data->displaydefault);
        $this->assertFalse($data->displaynotes);
        $this->assertTrue($data->displayleadin);
        $this->assertFalse($data->displaymedia);
        $data->notes = 'test';
        $data->set_question_head();
        $this->assertTrue($data->displaynotes);
    }

    /**
     * Test question question setter
     * @group question
     */
    public function test_set_question()
    {
        $data = questiondata::get_datastore('matrix');
        $data->scenario = 'Word|Excel|PowerPoint|Access5|Publisher|Data File||||';
        $useranswer = '3|4|2|5|1|6';
        $scenarios = ['Word', 'Excel', 'PowerPoint', 'Access5', 'Publisher', 'Data File', '', '', '', ''];
        $data->set_question(1, $useranswer, '');
        $this->assertEquals($scenarios, $data->scenarios);
        $this->assertEquals(['3', '4', '2', '5', '1', '6'], $data->usersanswers);
        $useranswer = null;
        $data->set_question(0, $useranswer, '');
        $this->assertEquals([], $data->usersanswers);
    }

    /**
     * Test question option setter
     * @group question
     */
    public function test_set_option_answer()
    {
        $data = questiondata::get_datastore('matrix');
        $data->matchoptions = [0 => ['option' => '.PUB']];
        $data->set_opt(1, ['optiontext' => '.PUB']);
        $data->set_opt(2, ['optiontext' => '.PPT']);
        $data->set_opt(3, ['optiontext' => '.DOC']);
        $data->set_opt(4, ['optiontext' => '.XLS']);
        $data->set_opt(5, ['optiontext' => '.MDB']);
        $data->set_opt(6, ['optiontext' => '.DAT']);
        $data->set_option_answer(2, '3|4|2|5|1|6', '', 1);
        $this->assertEquals([0 => ['option' => '.PUB'], 1 => ['option' => '.PPT']], $data->matchoptions);
    }

    /**
     * Test question additional option setter
     * @group question
     */
    public function test_process_options()
    {
        $data = questiondata::get_datastore('matrix');
        $data->matchoptions = [0 => ['option' => '.PUB'],
            1 => ['option' => '.PPT'],
            2 => ['option' => '.DOC'],
            3 => ['option' => '.XLS'],
            4 => ['option' => '.MDB'],
            5 => ['option' => '.DAT']];
        $data->usersanswers = ['3', '4', '2', '5', '1', '6'];
        $data->optionorder = '5,2,4,1,0,3';
        $data->scenarios = ['Word', 'Excel', 'PowerPoint', 'Access5', 'Publisher', 'Data File', '', '', '', ''];
        $useranswer = '3|4|2|5|1|6';
        $option['markscorrect'] = 1;
        $data->set_opt(1, $option);
        $data->set_opt(2, $option);
        $data->set_opt(3, $option);
        $data->set_opt(4, $option);
        $data->set_opt(5, $option);
        $data->set_opt(6, $option);
        $data->process_options(1, $useranswer, '', 1);
        $matchscenarios = [
            ['unanswered' => false, 'id' => 'A', 'value' => 'Word'],
            ['unanswered' => false, 'id' => 'B', 'value' => 'Excel'],
            ['unanswered' => false, 'id' => 'C', 'value' => 'PowerPoint'],
            ['unanswered' => false, 'id' => 'D', 'value' => 'Access5'],
            ['unanswered' => false, 'id' => 'E', 'value' => 'Publisher'],
            ['unanswered' => false, 'id' => 'F', 'value' => 'Data File'],
        ];
        $matchoptions = [
            ['option' => '.PUB', 'value' => 6, 'selected' => [1 => false,
                2 => false,
                3 => false,
                4 => false,
                5 => false,
                6 => true]],
            ['option' => '.PPT', 'value' => 3, 'selected' => [1 => true,
                2 => false,
                3 => false,
                4 => false,
                5 => false,
                6 => false]],
            ['option' => '.DOC', 'value' => 5, 'selected' => [1 => false,
                2 => false,
                3 => false,
                4 => true,
                5 => false,
                6 => false]],
            ['option' => '.XLS', 'value' => 2, 'selected' => [1 => false,
                2 => false,
                3 => true,
                4 => false,
                5 => false,
                6 => false]],
            ['option' => '.MDB', 'value' => 1, 'selected' => [1 => false,
                2 => false,
                3 => false,
                4 => false,
                5 => true,
                6 => false]],
            ['option' => '.DAT', 'value' => 4, 'selected' => [1 => false,
                2 => true,
                3 => false,
                4 => false,
                5 => false,
                6 => false]]
        ];
        $this->assertEquals($matchoptions, $data->matchoptions);
        $this->assertEquals($matchscenarios, $data->matchscenarios);
    }
}
