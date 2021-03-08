@questions @javascript
Feature: Question edit metadata
  In order to run exams
  As a teacher
  I need to be able to edit a questions metadata from the question bank

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
    And the following "questions" exist:
      | type | user | leadin | scenario | correct | marks_correct | marks_incorrect | columns | rows | editor | modules | locked |
      | textbox | teacher | textbox 1 leadin | textbox 1 scenario | placeholder | 1 | 0 | 90 | 5 | WYSIWYG | ["TEST1001"] | 2018-01-01 01:00:00 |
    
  @question_edit_metadata_textbox
  Scenario: Add a keyword to an existing textbox question
    Given I login as "teacher"
    And I follow "TEST1001"
    And I follow "All Questions"
    And I double click "textbox 1 leadin" "question_bank_item"
    And I add external reference "atestref"
    And I click "Save" "button"
    Then I should see "Question Bank" "paper_title"
