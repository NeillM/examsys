@paper @questions @copy @javascript
Feature: Copying questions
  In order to reuse papers
  As a teacher
  I should be able to copy questions between papers

  Scenario Outline: Copying questions into a paper
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
      | formative | test paper | teacher | Test module |
      | formative | a source paper | teacher | Test module |
      | formative | another paper | teacher | Test module |
    And the following "questions" exist:
      | type | user | leadin | scenario | paper | screen | position | display_method | correct | marks_correct | marks_incorrect |
      | true_false | teacher | tf leadin | tf scenario | a source paper | 1 | 1 | horizontal | true | 1 | 0 |
    And the following "questions" exist:
      | type | user | leadin | scenario | paper | screen | position | marks_correct | marks_incorrect | num_options | correct_options |
      | mrq | teacher | mrq leadin | mrq scenario | another paper | 2 | 1 | 1 | 0 | 3 | 2,3 |
    And I login as "teacher"
    And I am on "Paper Details" page for "test paper"
    And I should not see questions:
      | tf leadin |
      | mrq leadin |
    When I click "Copy questions from paper" "menu_item"
    And I set the fields:
      | field | value |
      | Source paper | <source> |
      | Copy type | <type> |
    And I press "OK"
    Then I should see questions:
      | <expected> |
    But I should not see questions:
      | <notexpected> |

    Examples:
      | source | type | expected | notexpected |
      | a source paper | Duplicate original papers questions | tf leadin | mrq leadin |
      | another paper | Link to original papers questions | mrq leadin | tf leadin |

  Scenario Outline: Copy paper (summative management off)
    Given the following "config" exist:
      | setting | value |
      | cfg_summative_mgmt | 0 |
    And the following "modules" exist:
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
      | formative | test paper | teacher | Test module |
    And the following "questions" exist:
      | type | user | leadin | scenario | paper | screen | position | display_method | correct | marks_correct | marks_incorrect |
      | true_false | teacher | tf leadin | tf scenario | test paper | 1 | 1 | horizontal | true | 1 | 0 |
    And the following "questions" exist:
      | type | user | leadin | scenario | paper | screen | position | marks_correct | marks_incorrect | num_options | correct_options |
      | mrq | teacher | mrq leadin | mrq scenario | test paper | 2 | 1 | 1 | 0 | 3 | 2,3 |
    And I login as "teacher"
    And I am on "Paper Details" page for "test paper"
    When I click "Copy Paper" "menu_item"
    And I set the fields:
      | field | value |
      | New Paper Name | My new paper |
      | Type | <paper> |
      | Copy standard settings | <standards> |
      | Copy Type | <type> |
    And I press "Copy Paper"
    Then I should see "My new paper" "paper_title"
    And I should see questions:
      | tf leadin |
      | mrq leadin |

    Examples:
      | paper | standards | type |
      | Formative Self-Assessment | 1 | Paper Only |
      | Progress Test | 0 | Paper and Questions |
      | Summative Exam | 1 | Paper and Questions |

  Scenario: Copy paper to summative (summative management on)
    Given the following "config" exist:
      | setting | value |
      | cfg_summative_mgmt | 1 |
    And the following "campuses" exist:
      | name | default |
      | Main campus | 1 |
      | Other campus | 0 |
    And the following "modules" exist:
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
      | formative | test paper | teacher | Test module |
    And the following "questions" exist:
      | type | user | leadin | scenario | paper | screen | position | display_method | correct | marks_correct | marks_incorrect |
      | true_false | teacher | tf leadin | tf scenario | test paper | 1 | 1 | horizontal | true | 1 | 0 |
    And the following "questions" exist:
      | type | user | leadin | scenario | paper | screen | position | marks_correct | marks_incorrect | num_options | correct_options |
      | mrq | teacher | mrq leadin | mrq scenario | test paper | 2 | 1 | 1 | 0 | 3 | 2,3 |
    And I login as "teacher"
    And I am on "Paper Details" page for "test paper"
    When I click "Copy Paper" "menu_item"
    And I set the fields:
      | field | value |
      | New Paper Name | My new paper |
      | Type | Formative Self-Assessment |
      | Copy standard settings | 1 |
      | Copy Type | Paper and Questions |
    And I should see "Copy Paper" "button"
    And I should not see "Next" "button"
    And I set the fields:
      | field | value |
      | Type | Summative Exam |
    And I should not see "Copy Paper" "button"
    And I should see "Next" "button"
    And I press "Next"
    And I set the fields:
      | field | value |
      | Campus | Other campus |
      | Barriers needed | 1 |
      | Hours | 1 |
      | Minutes | 30 |
      | Date Required | January |
      | Cohort Size | 41-50 |
      | Number of Sittings | 1 |
      | Notes | This is a note for the exams office |
    And I press "Copy Paper"
    Then I should see "My new paper" "paper_title"
    And I should see questions:
      | tf leadin |
      | mrq leadin |
