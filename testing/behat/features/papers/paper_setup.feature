@paper @questions @javascript
Feature: Paper setup
  In order to run exams
  As a teacher
  I need to be able to add/create questions from the paper details page

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
      | papertitle | papertype | paperowner | modulename |
      | a formative paper | 0 | teacher | Test module |
    And the following "questions" exist:
      | user | type | leadin | scenario | options |
      | teacher | true_false | tf 1 leadin | tf 1 scenario | {"correct":"t","marks_correct":"1","marks_incorrect":"0","marks_partial":"0"} |
      | teacher | true_false | tf 2 leadin | tf 2 scenario | {"correct":"f","marks_correct":"1","marks_incorrect":"0","marks_partial":"0"} |
      | teacher | true_false | tf 3 leadin | tf 3 scenario | {"correct":"t","marks_correct":"1","marks_incorrect":"0","marks_partial":"0"} |

  @paper_question_create_blank
  Scenario: Create a blank question on the paper
    Given I login as "teacher"
    And I am on "Paper Details" page for "a formative paper"
    And I follow "Create new Question"
    And I click "Fill-in-the-Blank" "sub_search_menu_item"
    And I create a new "blank" question:
      | theme | blank theme |
      | notes | blank notes |
      | leadin | blank leadin |
      | question | fill in [blank]the,a,their[/blank] blank |
    When I click "Add to Bank & Paper" "button"
    Then I should see questions:
      | blank leadin |

  @paper_question_add_true_false @iframe @jsevaluation
  Scenario: Add a true_false question from the question bank to the paper
    Given I login as "teacher"
    And I am on "Paper Details" page for "a formative paper"
    And I follow "Add Questions to Paper"
    And I add the following questions via "Rogō: Questions Bank":
      | tf 1 leadin |
      | tf 2 leadin |
    And I wait for questions to load
    Then I should see questions:
      | tf 1 leadin |
      | tf 2 leadin |

