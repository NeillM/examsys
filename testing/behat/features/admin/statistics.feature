@admin @statistics @javascript
Feature: System Statistics
  In order to view system statistics
  As a sys admin
  I should be able to lookup statistics

  Background:
    Given the following "academic year" exist:
      | calendar_year | academic_year |
      | 2012 | 2012/13 |
      | 2015 | 2015/16 |
    And the following "modules" exist:
      | moduleid | fullname |
      | TEST1001 | Test module |
    And the following "users" exist:
      | username | roles | sid | grade |
      | teacher | Staff | | University Lecturer |
    And the following "papers" exist:
      | type | papertitle | paperowner | modulename | startdate | enddate |
      | summative | a summative paper this year| teacher | Test module | 2015-12-31 09:00:00 | 2016-12-31 10:00:00 |
      | formative | a formative paper continuing this year | teacher | Test module | 2014-07-01 09:00:00 | 2017-10-01 10:00:00 |
      | formative | a formative paper ending within a year | teacher | Test module | 2014-07-01 09:00:00 | 2016-08-30 10:00:00 |
      | formative | a formative paper starting within a year| teacher | Test module | 2016-08-08 09:00:00 | 2017-01-01 10:00:00 |
      | progress | a progress paper previous year | teacher | Test module | 2012-10-01 09:00:00 | 2013-07-01 10:00:00 |

  @statistics_papers_by_school
  Scenario: Navigate the Papers by school page
    Given I login as "admin"
    When I follow "Administrative Tools"
    And I click "Statistics" "admin_tool_link"
    And I click "Papers by School" "admin_tool_link"
    Then I should see "UNKNOWN School" school statistics:
      | formative | 0 |
      | progress | 0 |
      | summative | 0 |
      | survey | 0 |
      | osce | 0 |
      | offline | 0 |
      | peer | 0 |
    And I click "2015/16" "tab"
    Then I should see "UNKNOWN School" school statistics:
      | formative | 3 |
      | progress | 0 |
      | summative | 1 |
      | survey | 0 |
      | osce | 0 |
      | offline | 0 |
      | peer | 0 |
    And I click "2012/13" "tab"
    Then I should see "UNKNOWN School" school statistics:
      | formative | 0 |
      | progress | 1 |
      | summative | 0 |
      | survey | 0 |
      | osce | 0 |
      | offline | 0 |
      | peer | 0 |
