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
 * Tests for the question lineage functionality
 *
 * @author Richard Aspden <richard@getjohn.co.uk>
 * @version 1.0
 * @copyright Copyright (c) 2021 Get John Ltd
 * @package tests
 */
class QuestionLineageTest extends unittestdatabase
{
    /**
     * @var array Storage for question data in tests
     */
    private $questions;

    /**
     * @var array Lineage array
     */
    private $lineage;

    /**
     * @var array Array of parent IDs for primary question
     */
    private $primaryParents;

    /**
     * @var array Array of direct child IDs for primary question
     */
    private $primaryDirectChildren;

    /**
     * @var array Full expected changelog for lineage test
    */
    private $primaryExpectedHistory;

    /**
     * Generate data for test.
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        $datagenerator = $this->get_datagenerator('questions', 'core');
        $this->questions['grandparent'] = $datagenerator->create_question(
            [
                'user' => 'admin',
                'type' => 'enhancedcalc',
                'leadin' => 'grandparent test'
            ]
        );
        $datagenerator->track_question_change('Edit Question', $this->questions['grandparent']['id'], 'admin', 'grandparent test modified', 'grandparent test', 'leadin');
        $this->questions['unrelated'] = $datagenerator->create_question(
            [
                'user' => 'admin',
                'type' => 'enhancedcalc',
                'leadin' => 'unrelated test'
            ]
        );
        $datagenerator->track_question_change('Edit Question', $this->questions['unrelated']['id'], 'admin', 'unrelated test modified', 'unrelated test', 'leadin');
        $this->questions['unrelated_child'] = $datagenerator->create_question(
            [
                'user' => 'admin',
                'type' => 'enhancedcalc',
                'leadin' => 'unrelated child test'
            ],
            $this->questions['unrelated']['id']
        );
        $datagenerator->track_question_change('Edit Question', $this->questions['unrelated_child']['id'], 'admin', 'unrelated child modified', 'unrelated child test', 'leadin');
        $this->questions['parent'] = $datagenerator->create_question(
            [
                'user' => 'admin',
                'type' => 'enhancedcalc',
                'leadin' => 'parent test'
            ],
            $this->questions['grandparent']['id']
        );
        $datagenerator->track_question_change('Edit Question', $this->questions['parent']['id'], 'admin', 'parent test', 'parent test modified', 'leadin');
        $this->questions['parent_sibling'] = $datagenerator->create_question(
            [
                'user' => 'admin',
                'type' => 'enhancedcalc',
                'leadin' => 'parent sibling test'
            ],
            $this->questions['grandparent']['id']
        );
        $datagenerator->track_question_change('Edit Question', $this->questions['parent_sibling']['id'], 'admin', 'parent sibling modified', 'parent test modified', 'leadin');
        $this->questions['primary'] = $datagenerator->create_question(
            [
                'user' => 'admin',
                'type' => 'enhancedcalc',
                'leadin' => 'primary test'
            ],
            $this->questions['parent']['id']
        );
        $datagenerator->track_question_change('Edit Question', $this->questions['primary']['id'], 'admin', 'primary test', 'primary test modified', 'leadin');
        $datagenerator->track_question_change('Edit Question', $this->questions['primary']['id'], 'admin', 'primary test modified', 'primary test', 'leadin');
        $datagenerator->track_question_change('Edit Question', $this->questions['parent']['id'], 'admin', 'parent test modified', 'parent test', 'leadin');
        $this->questions['sibling'] = $datagenerator->create_question(
            [
                'user' => 'admin',
                'type' => 'enhancedcalc',
                'leadin' => 'sibling test'
            ],
            $this->questions['parent']['id']
        );
        $datagenerator->track_question_change('Edit Question', $this->questions['sibling']['id'], 'admin', 'sibling test modified', 'sibling test', 'leadin');
        $this->questions['child1'] = $datagenerator->create_question(
            [
                'user' => 'admin',
                'type' => 'enhancedcalc',
                'leadin' => 'child 1 test'
            ],
            $this->questions['primary']['id']
        );
        $datagenerator->track_question_change('Edit Question', $this->questions['child1']['id'], 'admin', 'child1 test modified', 'child1 test', 'leadin');
        $this->questions['child2'] = $datagenerator->create_question(
            [
                'user' => 'admin',
                'type' => 'enhancedcalc',
                'leadin' => 'child 2 test'
            ],
            $this->questions['primary']['id']
        );
        $datagenerator->track_question_change('Edit Question', $this->questions['child2']['id'], 'admin', 'child2 test modified', 'child2 test', 'leadin');
        $this->questions['grandchild1'] = $datagenerator->create_question(
            [
                'user' => 'admin',
                'type' => 'enhancedcalc',
                'leadin' => 'grandchild test'
            ],
            $this->questions['child1']['id']
        );
        $datagenerator->track_question_change('Edit Question', $this->questions['grandchild1']['id'], 'admin', 'grandchild1 test modified', 'grandchild1 test', 'leadin');

        $this->lineage = [
            $this->questions['grandparent']['id'] => null,
            $this->questions['parent']['id'] => $this->questions['grandparent']['id'],
            $this->questions['parent_sibling']['id'] => $this->questions['grandparent']['id'],
            $this->questions['primary']['id'] => $this->questions['parent']['id'],
            $this->questions['sibling']['id'] => $this->questions['parent']['id'],
            $this->questions['child1']['id'] => $this->questions['primary']['id'],
            $this->questions['child2']['id'] => $this->questions['primary']['id'],
            $this->questions['grandchild1']['id'] => $this->questions['child1']['id']
        ];

        $this->primaryParents = [
            $this->questions['parent']['id'],
            $this->questions['grandparent']['id']
        ];

        $this->primaryDirectChildren = [
            $this->questions['child1']['id'],
            $this->questions['child2']['id']
        ];

        /**
         * Construct expected history: we're expecting to see:
         * New grandparent question
         * Grandparent question modification
         * Copied question to parent
         * Modified parent
         * Copied question to primary
         * Modified primary
         * Modified primary (2)
         * Modified parent (2) - should not be shown
         * Copied question to child1
         * Copied question to child2
         *
         * No child edits should be visible, nor any edits outside of this tree.
         * Actual history pull will be the reverse of this for display ordering,
         * reflected below.
         **/
        $this->primaryExpectedHistory = [
            [
                'qID' => $this->questions['child2']['id'],
                'action' => 'Copied Question',
                'old' => (string)$this->questions['primary']['id'],
                'new' => (string)$this->questions['child2']['id'],
            ],[
                'qID' => $this->questions['child1']['id'],
                'action' => 'Copied Question',
                'old' => (string)$this->questions['primary']['id'],
                'new' => (string)$this->questions['child1']['id'],
            ],[
                'qID' => $this->questions['primary']['id'],
                'action' => 'Edit Question',
                'old' => 'primary test modified',
                'new' => 'primary test',
                'section' => 'leadin'
            ],[
                'qID' => $this->questions['primary']['id'],
                'action' => 'Edit Question',
                'old' => 'primary test',
                'new' => 'primary test modified',
                'section' => 'leadin'
            ],[
                'qID' => $this->questions['primary']['id'],
                'action' => 'Copied Question',
                'old' => (string)$this->questions['parent']['id'],
                'new' => (string)$this->questions['primary']['id'],
            ],[
                'qID' => $this->questions['parent']['id'],
                'action' => 'Edit Question',
                'old' => 'parent test',
                'new' => 'parent test modified',
                'section' => 'leadin'
            ],[
                'qID' => $this->questions['parent']['id'],
                'action' => 'Copied Question',
                'old' => (string)$this->questions['grandparent']['id'],
                'new' => (string)$this->questions['parent']['id'],
            ],[
                'qID' => $this->questions['grandparent']['id'],
                'action' => 'Edit Question',
                'old' => 'grandparent test modified',
                'new' => 'grandparent test',
                'section' => 'leadin'
            ],[
                'qID' => $this->questions['grandparent']['id'],
                'action' => 'New Question',
                'old' => 'grandparent test',
                'new' => '',
            ]
        ];
    }

    /**
     * Test that we can get the full lineage of the primary question.
     *
     * @group questionlineage
     */
    public function testLineage()
    {
        $this->assertEquals($this->lineage, QuestionUtils::getLineage($this->questions['primary']['id']));
    }

    /**
     * Test that when lineage is missing it will be built up.
     *
     * Some question lineage did not get added during the upgrade, we have made changes to
     * ensure that it will build up over time. This test shows how it should work.
     *
     * @group questionlineage
     */
    public function testLineageBrokenHistory()
    {
        // Delete the lineage of some questions.
        $db = $this->get_db_connection();
        $db->query('DELETE FROM questions_lineage WHERE rootID = ' . $this->questions['grandparent']['id']);

        // Initially just the direct ancestors will be found.
        $initialiniage = [
            $this->questions['grandparent']['id'] => null,
            $this->questions['parent']['id'] => $this->questions['grandparent']['id'],
            $this->questions['primary']['id'] => $this->questions['parent']['id'],
        ];
        $this->assertEquals($initialiniage, QuestionUtils::getLineage($this->questions['primary']['id']));

        // More records should get added when an ancestor is called.
        $descendantadded = [
            $this->questions['grandparent']['id'] => null,
            $this->questions['parent']['id'] => $this->questions['grandparent']['id'],
            $this->questions['primary']['id'] => $this->questions['parent']['id'],
            $this->questions['child1']['id'] => $this->questions['primary']['id'],
            $this->questions['grandchild1']['id'] => $this->questions['child1']['id'],
        ];
        $this->assertEquals($descendantadded, QuestionUtils::getLineage($this->questions['grandchild1']['id']));

        // Adding in a sibling should also add more records.
        $siblingadded = [
            $this->questions['grandparent']['id'] => null,
            $this->questions['parent']['id'] => $this->questions['grandparent']['id'],
            $this->questions['primary']['id'] => $this->questions['parent']['id'],
            $this->questions['sibling']['id'] => $this->questions['parent']['id'],
            $this->questions['child1']['id'] => $this->questions['primary']['id'],
            $this->questions['grandchild1']['id'] => $this->questions['child1']['id'],
        ];
        $this->assertEquals($siblingadded, QuestionUtils::getLineage($this->questions['sibling']['id']));

        // Now if we call all the leaves and then call the history for the primary we should get it all.
        QuestionUtils::getLineage($this->questions['child2']['id']);
        QuestionUtils::getLineage($this->questions['parent_sibling']['id']);
        $this->assertEquals($this->lineage, QuestionUtils::getLineage($this->questions['primary']['id']));
    }

    /**
     * Test that we can correctly get the lineage root in all cases
     *
     * @group questionlineage
     */
    public function testGetLineageRoot()
    {
        $this->assertEquals($this->questions['grandparent']['id'], QuestionUtils::getLineageRoot($this->questions['grandparent']['id']));
        $this->assertEquals($this->questions['grandparent']['id'], QuestionUtils::getLineageRoot($this->questions['child1']['id']));
        $this->assertEquals($this->questions['grandparent']['id'], QuestionUtils::getLineageRoot($this->questions['child2']['id']));
        $this->assertEquals($this->questions['grandparent']['id'], QuestionUtils::getLineageRoot($this->questions['grandchild1']['id']));
        $this->assertEquals($this->questions['unrelated']['id'], QuestionUtils::getLineageRoot($this->questions['unrelated_child']['id']));
    }

    /**
     * Tests that lineage will be generated if it is not present.
     *
     * @group questionlineage
     */
    public function testGetLinageRootBrokenHistory()
    {
        // Delete the lineage of some questions.
        $db = $this->get_db_connection();
        $db->query('DELETE FROM questions_lineage WHERE rootID = ' . $this->questions['grandparent']['id']);

        $this->assertEquals($this->questions['grandparent']['id'], QuestionUtils::getLineageRoot($this->questions['child2']['id']));
        $this->assertEquals($this->questions['grandparent']['id'], QuestionUtils::getLineageRoot($this->questions['child1']['id']));
        $this->assertEquals($this->questions['grandparent']['id'], QuestionUtils::getLineageRoot($this->questions['parent']['id']));
        $this->assertEquals($this->questions['grandparent']['id'], QuestionUtils::getLineageRoot($this->questions['primary']['id']));
    }

    /**
     * Test that we can correctly get the direct parents of a question
     *
     * @group questionlineage
     */
    public function testGetLineageParent()
    {
        $this->assertEquals(null, QuestionUtils::getLineageParent($this->questions['grandparent']['id']));
        $this->assertEquals($this->questions['grandparent']['id'], QuestionUtils::getLineageParent($this->questions['parent']['id']));
        $this->assertEquals($this->questions['parent']['id'], QuestionUtils::getLineageParent($this->questions['primary']['id']));
        $this->assertEquals($this->questions['primary']['id'], QuestionUtils::getLineageParent($this->questions['child1']['id']));
        $this->assertEquals($this->questions['primary']['id'], QuestionUtils::getLineageParent($this->questions['child2']['id']));
        $this->assertEquals($this->questions['child1']['id'], QuestionUtils::getLineageParent($this->questions['grandchild1']['id']));
        $this->assertEquals($this->questions['unrelated']['id'], QuestionUtils::getLineageParent($this->questions['unrelated_child']['id']));
    }

    /**
     * Test that we can correctly get the direct parents of a question
     *
     * @group questionlineage
     */
    public function testGetLineageParentBrokenHistory()
    {
        // Delete the lineage of some questions.
        $db = $this->get_db_connection();
        $db->query('DELETE FROM questions_lineage WHERE rootID = ' . $this->questions['grandparent']['id']);

        $this->assertEquals($this->questions['primary']['id'], QuestionUtils::getLineageParent($this->questions['child1']['id']));
        $this->assertEquals($this->questions['grandparent']['id'], QuestionUtils::getLineageParent($this->questions['parent']['id']));
        $this->assertEquals($this->questions['parent']['id'], QuestionUtils::getLineageParent($this->questions['primary']['id']));
        $this->assertEquals($this->questions['primary']['id'], QuestionUtils::getLineageParent($this->questions['child2']['id']));
    }

    /**
     * Test that we can correctly get the full parental lineage of a question
     *
     * @group questionlineage
     */
    public function testFilterParentLineage()
    {
        $string = ['history_exceeded_parent_limit' => 'Exceeded parent limit of %d when tracing lineage'];
        $parentLineage = QuestionUtils::filterParentLineage($this->questions['primary']['id'], $this->lineage, $string);
        $this->assertEquals($this->primaryParents, $parentLineage);
    }

    /**
     * Test that we can correctly get the direct children of a question
     *
     * @group questionlineage
     */
    public function testFilterDirectChildLineage()
    {
        $childLineage = QuestionUtils::filterChildLineage($this->questions['primary']['id'], $this->lineage);
        $this->assertEquals($childLineage, $this->primaryDirectChildren);
    }

    /**
     * Test to confirm question full history
     * Note: Needs other changes added to parent questions to be effective
     *
     * @group questionlineage
     */
    public function testFullHistory()
    {
        $string = ['history_exceeded_parent_limit' => 'Exceeded parent limit of %d when tracing lineage'];
        $fullHistory = \QuestionUtils::getFullHistory($this->questions['primary']['id'], 200, $string);
        $this->assertEquals(count($this->primaryExpectedHistory), count($fullHistory));
        for ($i = 0; $i < count($fullHistory); $i++) {
            $this->assertEquals($this->primaryExpectedHistory[$i]['qID'], $fullHistory[$i]['qID']);
            $this->assertEquals($this->primaryExpectedHistory[$i]['action'], $fullHistory[$i]['action']);
            $this->assertEquals($this->primaryExpectedHistory[$i]['old'], $fullHistory[$i]['old']);
            $this->assertEquals($this->primaryExpectedHistory[$i]['new'], $fullHistory[$i]['new']);
            if (isset($this->primaryExpectedHistory[$i]['section'])) {
                $this->assertEquals($this->primaryExpectedHistory[$i]['section'], $fullHistory[$i]['section']);
            }
        }
    }
}
