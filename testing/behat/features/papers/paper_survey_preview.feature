@paper @javascript @wip
Feature: Survey Preview
  In order to ensure that my survey papers work well
  As a teacher
  I need to be able to preview them before sending them to users

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
      | survey | a survey | teacher | Test module |
    And the following "questions" exist:
      | type | user | leadin | scenario | paper | screen | position | scale type | scale | not applicable |
      | likert | teacher | 3 point leadin | 3 point scenario | a survey | 1 | 1 | 3 Point Scales | Disagree, Neutral, Agree | false |
      | likert | teacher | 4 point leadin | 4 point scenario | a survey | 2 | 1 | 4 Point Scales | Never to Always | false |
      | likert | teacher | 4 point leadin | 4 point scenario | a survey | 2 | 2 | 4 Point Scales | Never to Always | true |
      | likert | teacher | 5 point leadin | 5 point scenario | a survey | 3 | 1 | 5 Point Scales | Low to High | false |
      | likert | teacher | Custom leadin | Custom scenario | a survey | 4 | 1 | Custom | Stinks! $$ Rocks! | false |

  @paper_preview_survey @jsevaluation
  Scenario: Preview an existing exam
    Given I login as "teacher"
    And I am on "Paper Details" page for "a survey"
    And I open the survey preview
    And I answer the questions:
      | position | type | answer |
      | 1 | likert | Agree |
    And I navigate paper "Next"
    And I answer the questions:
      | position | type | answer |
      | 1 | likert | Hardly |
      | 2 | likert | N/A |
    And I navigate paper "Next"
    And I answer the questions:
      | position | type | answer |
      | 1 | likert | Tending High |
    And I navigate paper "Next"
    And I answer the questions:
      | position | type | answer |
      | 1 | likert | Stinks! |
    And I navigate paper "Finish"
    Then I should see "Thank you"