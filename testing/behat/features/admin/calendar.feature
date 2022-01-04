@admin @calendar @javascript
Feature: Exam Calendar
  In order to review the exam schdule
  As a sys admin
  I should be able to view the calendar

  Background:
    Given the following "config" exist:
      | setting | value |
      | cfg_summative_mgmt | 0 |
    And the following "modules" exist:
      | moduleid | fullname |
      | TEST1001 | Test module |
    And the following "users" exist:
      | username | roles | sid | grade |
      | teacher | Staff | | University Lecturer |
    And the following "papers" exist:
      | type | papertitle | paperowner | modulename | session | startdate | enddate |
      | formative | a formative paper | teacher | Test module | today 12:00 | today 13:00 | |
      | summative | a summative paper this year | teacher | Test module | today 12:00 | today 13:00 | |
      | progress | a progress paper this year | teacher | Test module | today 12:00 | today 13:00 | |
      | osce | a osce paper this year | teacher | Test module | today 12:00 | today 13:00 | |
      | offline | a offline paper this year | teacher | Test module | today 12:00 | today 13:00 | |
      | survey | a survey paper this year | teacher | Test module | today 12:00 | today 13:00 | |
      | summative | a summative paper next year | teacher | Test module | next year | second wednesday of January next year 9:30 | second wednesday of January next year 14:00 |
      | osce | a osce paper next year | teacher | Test module | next year | second wednesday of January next year 9:30 | second wednesday of January next year 14:00 |

  @view_calendar
  Scenario: Navigate the calendar this year
    Given I login as "admin"
    And I am on "Calendar" page
    Then I should see "a summative paper this year"
    And I should see "a osce paper this year"
    And I should not see "a formative paper this year"
    And I should not see "a survey paper this year"
    And I should not see "a offline paper this year"
    And I should not see "a progress paper this year"
    And I should not see "a osce paper next year"
    And I should not see "a summative paper next year"

  @view_calendar_next_year
  Scenario: Navigate the calendar next year
    Given I login as "admin"
    And I am on "Calendar" page in "1" section for "next year"
    Then I should see "a summative paper next year"
    And I should see "a osce paper next year"
    And I should not see "a formative paper this year"
    And I should not see "a survey paper this year"
    And I should not see "a offline paper this year"
    And I should not see "a progress paper this year"
    And I should not see "a summative paper this year"
    And I should not see "a osce paper this year"
