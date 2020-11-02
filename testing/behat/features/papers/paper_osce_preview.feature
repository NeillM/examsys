@paper @javascript
Feature: OSCE Preview
  In order to OSCEs
  As a teacher
  I need to be able to preview an OSCE

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
      | type | papertitle | paperowner | modulename | marking |
      | osce | an osce paper | teacher | Test module | Pass \| Fail |
    And the following "questions" exist:
      | type | user | leadin | scenario | paper | screen | position | display_method |
      | likert | teacher | likert 1 | likert 1 | an osce paper | 1 | 1 | Low\|High\|false |
      | likert |teacher | likert 2 | likert 2 | an osce paper | 1 | 2 | Low\|High\|false |

  @paper_preview_osce @jsevaluation
  Scenario: Preview an existing exam
    Given I login as "teacher"
    And I am on "Paper Details" page for "an osce paper"
    And I open the osce preview
    And I answer the questions:
      | position | type | answer |
      | 1 | likert | 1 |
      | 2 | likert | 2 |
    And I answer overall with "Fail"
    And I enter the feedback "some feedback"
    And I close the osce preview
    Then I should see "an osce paper" "paper_title"
