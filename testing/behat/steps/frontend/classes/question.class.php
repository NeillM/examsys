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

namespace testing\behat\steps\frontend;

use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Tester\Exception\PendingException;

/**
 * Question creation and manipulation step definitions.
 *
 * @copyright Copyright (c) 2020 The University of Nottingham
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @package testing
 * @subpackage behat
 */
trait Question
{
    /**
     * Creates a new question when the user is on a module page.
     *
     * @Given I create a new :type question:
     * @param string $type The type of question
     * @param TableNode $data The parameters used to create the question
     */
    public function iCreateANewQuestion($type, TableNode $data): void
    {
        $this->i_click('New Question', 'link');
        $this->i_focus_popup('Rogō: New Question');
        switch ($type) {
            case 'enhancedcalc':
                $this->createEnhancedcalc($data);
                break;
            case 'dichotomous':
                $this->createDichotomous($data);
                break;
            case 'extmatch':
                $this->createExtmatch($data);
                break;
            case 'blank':
                $this->createBlank($data);
                break;
            case 'info':
                $this->createInfo($data);
                break;
            case 'matrix':
                $this->createMatrix($data);
                break;
            case 'likert':
                $this->createLikert($data);
                break;
            case 'mcq':
                $this->createMcq($data);
                break;
            case 'mrq':
                $this->createMrq($data);
                break;
            case 'rank':
                $this->createRank($data);
                break;
            case 'sct':
                $this->createSct($data);
                break;
            case 'textbox':
                $this->createTextbox($data);
                break;
            case 'true_false':
                $this->createTrueFalse($data);
                break;
            default:
                throw new PendingException('No handler for creating ' . $type . 'questions');
        }
        $this->i_click('Add to Bank', 'button');
    }

    /**
     * Creates a enhancedcalc question.
     *
     * @param TableNode $data
     */
    protected function createEnhancedcalc(TableNode $data): void
    {
        $fields = $data->getRowsHash();
        $this->questionBasics('enhancedcalc');
        $this->genericfields($fields);
        $this->fillField('option_min1', $fields['variable_min_1']);
        $this->fillField('option_max1', $fields['variable_max_1']);
        $this->fillField('option_decimals1', $fields['variable_decimal_1']);
        $this->fillField('option_increment1', $fields['variable_increment_1']);
        $this->fillField('option_formula1', $fields['formula_1']);
    }

    /**
     * Creates a dichotomous question.
     *
     * @param TableNode $data
     */
    protected function createDichotomous(TableNode $data): void
    {
        $fields = $data->getRowsHash();
        $this->questionBasics('dichotomous');
        $this->genericfields($fields);
        $this->fillField('option_text1', $fields['stem_1']);
        if ($fields['stem_true_1'] == 1) {
            $select = 'option_correct1_t';
        } else {
            $select = 'option_correct1_f';
        }
        $this->selectRadio($select);
        $this->fillField('option_text2', $fields['stem_2']);
        if ($fields['stem_true_2'] == 1) {
            $select = 'option_correct2_t';
        } else {
            $select = 'option_correct2_f';
        }
        $this->selectRadio($select);
    }

    /**
     * Creates a extmatch question.
     *
     * @param TableNode $data
     */
    protected function createExtmatch(TableNode $data): void
    {
        $fields = $data->getRowsHash();
        $this->questionBasics('extmatch');
        $this->fillField('theme', $fields['theme']);
        $this->fillField('notes', $fields['notes']);
        $this->fillTinyMCE('leadin', $fields['leadin']);
        $this->fillTinyMCE('question_stem1', $fields['stem_1']);
        $this->fillTinyMCE('question_stem2', $fields['stem_2']);
        $this->fillField('option_text1', $fields['option_1']);
        $this->fillField('option_text2', $fields['option_2']);
        $this->fillField('option_text3', $fields['option_3']);
        $this->fillDropDown('option_correct1', $fields['stem_select_1']);
        $this->fillDropDown('option_correct2', $fields['stem_select_2']);
    }

    /**
     * Creates a blank question.
     *
     * @param TableNode $data
     */
    protected function createBlank(TableNode $data): void
    {
        $fields = $data->getRowsHash();
        $this->questionBasics('blank');
        $this->fillField('theme', $fields['theme']);
        $this->fillField('notes', $fields['notes']);
        $this->fillTinyMCE('leadin', $fields['leadin']);
        $this->fillTinyMCE('option_text', $fields['question']);
    }

    /**
     * Creates a info block.
     *
     * @param TableNode $data
     */
    protected function createInfo(TableNode $data): void
    {
        $fields = $data->getRowsHash();
        $this->questionBasics('info');
        $this->fillField('theme', $fields['theme']);
        $this->fillTinyMCE('leadin', $fields['text']);
    }

    /**
     * Creates a matrix question.
     *
     * @param TableNode $data
     */
    protected function createMatrix(TableNode $data): void
    {
        $fields = $data->getRowsHash();
        $this->questionBasics('matrix');
        $this->fillField('theme', $fields['theme']);
        $this->fillField('notes', $fields['notes']);
        $this->fillTinyMCE('leadin', $fields['leadin']);
        $this->fillField('question_stem1', $fields['row_1']);
        $this->fillField('question_stem2', $fields['row_2']);
        $this->fillField('option_text1', $fields['column_1']);
        $this->fillField('option_text2', $fields['column_2']);
        $this->selectRadio('option_correct1_' . $fields['select_1']);
        $this->selectRadio('option_correct2_' . $fields['select_2']);
    }

    /**
     * Creates a likert question.
     *
     * @param TableNode $data
     */
    protected function createLikert(TableNode $data): void
    {
        $fields = $data->getRowsHash();
        $this->questionBasics('likert');
        $this->genericfields($fields);
        $this->fillDropDown('scale_type', $fields['scale']);
    }

    /**
     * Creates a mcq question.
     *
     * @param TableNode $data
     */
    protected function createMcq(TableNode $data): void
    {
        $fields = $data->getRowsHash();
        $this->questionBasics('mcq');
        $this->genericfields($fields);
        $this->fillTinyMCE('option_text1', $fields['option_1']);
        $this->fillTinyMCE('option_text2', $fields['option_2']);
        $this->fillTinyMCE('option_text3', $fields['option_3']);
        // Bottom Bar obscures page elements so need to scroll so we can click.
        $this->scrollToElement('#option_correct_fback1');
        $this->selectRadio('option_correct' . $fields['correct']);
    }

    /**
     * Creates a mrq question.
     *
     * @param TableNode $data
     */
    protected function createMrq(TableNode $data): void
    {
        $fields = $data->getRowsHash();
        $this->questionBasics('mrq');
        $this->genericfields($fields);
        $this->fillTinyMCE('option_text1', $fields['option_1']);
        $this->fillTinyMCE('option_text2', $fields['option_2']);
        $this->fillTinyMCE('option_text3', $fields['option_3']);
        // Bottom Bar obscures page elements so need to scroll so we can click.
        $this->scrollToElement('#option_correct_fback1');
        $this->selectCheckPoints('option_correct', $fields['correct']);
    }

    /**
     * Creates a rank question.
     *
     * @param TableNode $data
     */
    protected function createRank(TableNode $data): void
    {
        $fields = $data->getRowsHash();
        $this->questionBasics('rank');
        $this->genericfields($fields);
        $this->fillField('option_text1', $fields['option_1']);
        $this->fillField('option_text2', $fields['option_2']);
        $this->fillField('option_text3', $fields['option_3']);
        $this->fillDropDown('option_correct1', $fields['rank_1']);
        $this->fillDropDown('option_correct2', $fields['rank_2']);
        $this->fillDropDown('option_correct3', $fields['rank_3']);
    }

    /**
     * Creates a sct question.
     *
     * @param TableNode $data
     */
    protected function createSct(TableNode $data): void
    {
        $fields = $data->getRowsHash();
        $this->questionBasics('sct');
        $this->fillField('theme', $fields['theme']);
        $this->fillField('notes', $fields['notes']);
        $this->fillTinyMCE('scenario', $fields['clinical vignette']);
        $this->fillTinyMCE('hypothesis', $fields['hypothesis']);
        $this->fillDropDown('option_correct1', $fields['experts_1']);
        $this->fillDropDown('option_correct2', $fields['experts_2']);
        $this->fillDropDown('option_correct3', $fields['experts_3']);
        $this->fillDropDown('option_correct4', $fields['experts_4']);
        $this->fillDropDown('option_correct5', $fields['experts_5']);
    }

    /**
     * Creates a textbox question.
     *
     * @param TableNode $data
     */
    protected function createTextbox(TableNode $data): void
    {
        $fields = $data->getRowsHash();
        $this->questionBasics('textbox');
        $this->genericfields($fields);
    }

    /**
     * Creates a true_false question.
     *
     * @param TableNode $data
     */
    protected function createTrueFalse(TableNode $data): void
    {
        $fields = $data->getRowsHash();
        $this->questionBasics('true_false');
        $this->genericfields($fields);
        // Bottom Bar obscures page elements so need to scroll so we can click.
        $this->scrollToElement('#leadin_ifr');
        if ($fields['true'] == 1) {
            $select = 'option_correct1_t';
        } else {
            $select = 'option_correct1_f';
        }
        $this->selectRadio($select);
    }

    /**
     * Fills in the first page of the question creation dialogue.
     *
     * @param string $type The type of question to be created.
     */
    protected function questionBasics($type): void
    {
        $this->i_click($type, 'question_type');
        $this->only_main_window();
        $this->i_focus_main_window();
        $this->i_wait_for_page_to_load();
    }

    /**
     * Fill generic data.
     * @param array $fields field data
     */
    protected function genericfields(array $fields): void
    {
        $this->fillField('theme', $fields['theme']);
        $this->fillField('notes', $fields['notes']);
        $this->fillTinyMCE('scenario', $fields['scenario']);
        $this->fillTinyMCE('leadin', $fields['leadin']);
    }
}
