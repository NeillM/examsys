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

namespace testing\behat\steps\frontend;

/**
 * Steps for use on the invigilation pages on ExamSys
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2023 onwards The University of Nottingham
 * @package tests
 */
trait Invigilation
{
    /**
     * Adds a note to a student on the invigilation screen.
     *
     * @When I add a :note note to :student student on :paper paper
     *
     * @param string $note
     * @param string $student
     * @param string $paper
     */
    public function iAddANoteToStudentOnPaper(string $note, string $student, string $paper)
    {
        $this->iNavigateToInvigilatorNote($student, $paper);
        $this->fillField('note', $note);
        $this->iSaveInvigilatorNote();
    }

    /**
     * Adds a note to a student on the invigilation screen.
     *
     * @Then the :student student on :paper paper invigilator note is :note
     *
     * @param string $student
     * @param string $paper
     * @param string $note
     */
    public function theStudentOnPaperHasNote(string $student, string $paper, string $note)
    {
        $this->iNavigateToInvigilatorNote($student, $paper);
        $this->assertFieldContains('note', $note);
        $this->iCloseInvigilatorNote();
    }

    /**
     * Navigate to a student note on the invigilation page.
     *
     * @When I navigate to invigilator note for :student student on :paper paper
     *
     * @param string $student
     * @param string $paper
     */
    public function iNavigateToInvigilatorNote(string $student, string $paper)
    {
        $this->find('link_or_button', $paper)->click();
        $this->find('invigilation_student', $student)->click();
        // We have to use a css selector here because the menu is not really built in an accessible
        // way, it really should be a link, so that we can click on it directly using natural language.
        $this->find('css', 'li.menu-note')->click();
        $this->i_focus_popup('Note');
    }

    /**
     * Saves an invigilator note.
     *
     * You must have an invigilator note open and focused for this to work.
     *
     * @When I save the invigilator note
     */
    public function iSaveInvigilatorNote()
    {
        $this->find('button', 'Save')->click();
        $this->only_main_window();
        $this->i_focus_main_window();
    }

    /**
     * Closes an invigilator note without saving.
     *
     * You must have an invigilator note open and focused for this to work.
     *
     * @When I close the invigilator note
     */
    public function iCloseInvigilatorNote()
    {
        $this->find('button', 'Cancel')->click();
        $this->only_main_window();
        $this->i_focus_main_window();
    }
}
