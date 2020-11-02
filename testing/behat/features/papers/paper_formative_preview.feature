@paper @javascript
Feature: Formative Preview
  In order to run formative exams
  As a teacher
  I need to be able to preview a formative exam

  Background:
    Given the following "modules" exist:
      | moduleid | fullname |
      | TEST1001 | Test module |
    And the following "users" exist:
      | username | roles |
      | teacher | Staff |
    And the following "module team members" exist:
      | moduleid | username |
      | TEST1001 | teacher |
    And the following "papers" exist:
      | type | papertitle | paperowner | modulename |
      | formative | a formative paper| teacher | Test module |
    And the following "questions" exist:
      | type | user | leadin | scenario | paper | screen | position | display_method | correct | marks_correct | marks_incorrect |
      | true_false | teacher | tf leadin | tf scenario | a formative paper | 1 | 1 | horizontal | true | 1 | 0 |
    And the following "questions" exist:
      | type | user | leadin | scenario | paper | screen | position | marks_correct | marks_incorrect | num_options | correct_options |
      | mrq | teacher | mrq leadin | mrq scenario | a formative paper | 2 | 1 | 1 | 0 | 3 | 2,3 |
    And the following "questions" exist:
      | type | user | leadin | scenario | paper | screen | position  | correct | marks_correct | marks_incorrect | num_options | correct_options | display_method |
      | mcq | teacher | mcq leadin | mcq scenario | a formative paper | 1 | 2 | 1 | 1 | 0 | 3 | 2 | vertical |
    And the following "questions" exist:
      | type | user | leadin | scenario | paper | screen | position  | correct | marks_correct | marks_incorrect | columns | rows | editor |
      | textbox | teacher | textbox 1 leadin | textbox 1 scenario | a formative paper | 2 | 2 | placeholder | 1 | 0 | 90 | 5 | WYSIWYG |
    And the following "questions" exist:
      | type | user | leadin | scenario | paper | screen | position | marks_correct | marks_incorrect | marks_partial | tolerance_full | tolerance_partial | variables | formula |
      | enhancedcalc | teacher | enhancedcalc $A x $B | enhancedcalc 1 scenario | a formative paper | 1 | 3 | 2 | 0 | 0.5 | 0 | 1 | {"$A":{"min":"12","max":"12","inc":"1","dec":"0"},"$B":{"min":"4","max":"4","inc":"1","dec":"0"}} | $A*$B |
    And the following "questions" exist:
      | type | user | leadin | scenario | paper | screen | position | marks_correct | marks_incorrect  | experts |
      | sct | teacher | sct leadin | sct scenario | a formative paper | 2 | 3 | 1 | 0 | 1,2,10,3,1 |
    And the following "questions" exist:
      | type | user | leadin | scenario | paper | screen | position | marks_correct | marks_incorrect | num_options | correct_options | display_method |
      | dichotomous | teacher | dichotomous leadin | dichotomous scenario | a formative paper | 3 | 1 | 1 | 0 | 3 | 1,2,3 | TF_Positive |
    And the following "questions" exist:
      | type | user | leadin | scenario | paper | screen | position | marks_correct | marks_incorrect | num_options | correct_order |
      | rank | teacher | rank leadin | rank scenario | a formative paper | 2 | 4 | 1 | 0 | 3 | 1,2,3 |
    And the following "questions" exist:
      | type | user | leadin | scenario | paper | screen | position  | correct | marks_correct | marks_incorrect | display_method | blanks |
      | blank | teacher | blank 1 leadin | blank 1 scenario | a formative paper | 3 | 3 | placeholder | 1 | 0 | dropdown | Red is a [blank]colour,country,animal[/blank]. France is a [blank]country,colour,animal[/blank] |
    And the following "questions" exist:
      | type | user | leadin | paper | screen | position | marks_correct | marks_incorrect | num_options | correct_options | num_stems |
      | extmatch | teacher | extmatch leadin | a formative paper | 3 | 2 | 1 | 0 | 3 | 3,1,2 | 3 |
    And the following "questions" exist:
      | type | user | leadin | paper | screen | position | marks_correct | marks_incorrect | num_options | correct_options | num_stems |
      | matrix | teacher | matrix leadin | a formative paper | 1 | 4 | 1 | 0 | 3 | 3,2,1 | 3 |

  @paper_preview_formative @jsevaluation
  Scenario: Preview an existing exam
    Given I login as "teacher"
    And I am on "Paper Details" page for "a formative paper"
    And I open the exam preview
    And I answer the questions:
      | position | type | answer |
      | 1 | true_false | t |
      | 2 | mcq | option 3 |
      | 3 | enhancedcalc | 47 |
      | 4 | matrix | {"stem 1":"option 1","stem 2":"option 2","stem 3":"option 3"} |
    And I navigate paper "Next"
    And I answer the questions:
      | position | type | answer |
      | 1 | mrq | ["option 1","option 3"] |
      | 2 | textbox | some nice blurb to answer the question |
      | 3 | sct | very likely |
      | 4 | rank | {"option 1":"3","option 2":"2","option 3":"1"} |
    And I navigate paper "Next"
    And I answer the questions:
      | position | type | answer |
      | 1 | dichotomous | ["t","f","t"] |
      | 2 | extmatch | {"stem 1":"C. option 3","stem 2":"B. option 2","stem 3":"A. option 1"} |
      | 3 | blank | ["colour","country"] |
    And I navigate paper "Finish"
    Then I should see your mark is "9.6 out of 22"
