@review @external @javascript
Feature: External Reviewer
  In order to review a paper
  As an external reviewer
  I should be able to review papers

  Background:
    And the following "modules" exist:
      | moduleid | fullname |
      | TEST1001 | Test module |
    And the following "users" exist:
      | username | roles | sid | grade |
      | external | External Examiner | | External Examiner |
      | teacher | Staff | | University Lecturer |
    And the following "papers" exist:
      | type | papertitle | paperowner | modulename | external_review_deadline | startdate | enddate |
      | formative | a formative paper in the future | teacher | Test module | tomorrow | | |
      | formative | a formative paper in the past | teacher | Test module | tomorrow | first day of last month 9:00 | first day of last month 10:00 |
    And the following "questions" exist:
      | type | user | leadin | scenario | paper | screen | position | display_method | correct | marks_correct | marks_incorrect |
      | true_false | teacher | tf leadin | tf scenario | a formative paper in the future | 1 | 1 | horizontal | true | 1 | 0 |
    And the following "questions" exist:
      | type | user | leadin | scenario | paper | screen | position  | correct | marks_correct | marks_incorrect | num_options | correct_options | display_method |
      | mcq | teacher | mcq leadin | mcq scenario | a formative paper in the past | 1 | 1 | 1 | 1 | 0 | 3 | 2 | vertical |
    And the following "reviewers" exist:
      | reviewer | paper | type |
      | external | a formative paper in the future | external |
      | external | a formative paper in the past | external |

  @external_review_list_pre_papers
  Scenario: Navigate to the reviewers homepage
    Given I login as "external"
    Then I should see "a formative paper in the future" "link"
    And I should not see "a formative paper in the past" "link"
