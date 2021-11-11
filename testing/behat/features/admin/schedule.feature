@admin @schedule @javascript
Feature: Summative Schedule
  In order to schedule summative exams
  As a sys admin
  I should be able to view the exam schedule

  Background:
    Given the following "modules" exist:
      | moduleid | fullname |
      | TEST1001 | Test module |
    And the following "users" exist:
      | username | roles | sid | grade |
      | teacher | Staff | | University Lecturer |
    And the following "schedule" exist:
      | papertitle | paperowner | modulename |
      | a summative paper this year| teacher | Test module |

  @summative_schedule
  Scenario: Navigate the summative scheduling page
    Given I login as "admin"
    When I follow "Administrative Tools"
    And I click "Summative Exam Scheduling" "admin_tool_link"
    Then I should see "a summative paper this year"
