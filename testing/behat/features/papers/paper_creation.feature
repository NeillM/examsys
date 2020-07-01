@papers @javascript
Feature: Paper creation
  In order to run exams
  As a teacher
  I need to be able to create papers

  Background:
    Given the following "academic year" exist:
      | year |
      | next year |
    And the following "modules" exist:
      | moduleid | fullname |
      | TEST1001 | Test module |
    And the following "users" exist:
      | username | roles |
      | teacher | Staff |
    And the following "module team members" exist:
      | moduleid | username |
      | TEST1001 | teacher |

  @paper_formative
  Scenario: Create a formative paper
    Given I login as "teacher"
    And I follow "TEST1001"
    When I create a new "formative" paper:
      | name | Paper 1 |
      | start | first day of april this year 9:00 |
      | end | first day of march next year noon |
    Then I should see "Paper 1" "paper_title"

  @paper_progress
  Scenario: Create a Progress test
    Given I login as "teacher"
    And I follow "TEST1001"
    When I create a new "progress" paper:
      | name | Paper 2 |
      | start | first monday of May this year 9:30 |
      | end | last friday of May this year 17:00 |
    Then I should see "Paper 2" "paper_title"

  @paper_survey
  Scenario: Create a Survey
    Given I login as "teacher"
    And I follow "TEST1001"
    When I create a new "survey" paper:
      | name | Survey of users |
      | start | first day of next month 9:30 |
      | end | last day of +6 month 17:00 |
      | timezone | America/New_York |
    Then I should see "Survey of users" "paper_title"

  @paper_osce
  Scenario: Create a OSCE paper
    Given I login as "teacher"
    And I follow "TEST1001"
    When I create a new "osce" paper:
      | name | Clinical skills |
      | session | next year |
      | start | second wednesday of August next year 9:30 |
      | end | second wednesday of August next year 14:00 |
      | timezone | Asia/Kuala_Lumpur |
    Then I should see "Clinical skills" "paper_title"

  @paper_offline
  Scenario: Create a Offline paper
    Given I login as "teacher"
    And I follow "TEST1001"
    When I create a new "offline" paper:
      | name | Reflections on the works of Kipling |
      | session | last year |
      | start | second tuesday of January this year 9:30 |
      | end | second tuesday of January this year 11:00 |
    Then I should see "Reflections on the works of Kipling" "paper_title"

  @paper_peer
  Scenario: Create a Peer review paper
    Given I login as "teacher"
    And I follow "TEST1001"
    When I create a new "peer review" paper:
      | name | Code review |
      | start | first monday of next month 9:30 |
      | end | last friday of next month 17:00 |
    Then I should see "Code review" "paper_title"

  @paper_summative
  Scenario: Create a Summative paper (management off)
    Given the following "config" exist:
      | setting | value |
      | cfg_summative_mgmt | 0 |
    And I login as "teacher"
    And I follow "TEST1001"
    When I create a new "summative" paper:
      | name | Logic |
      | session | next year |
      | start | first monday of next month 9:30 |
      | end | last friday of next month 17:00 |
    Then I should see "Logic" "paper_title"

  @paper_summative
  Scenario: Create a Summative paper (management on)
    Given the following "config" exist:
      | setting | value |
      | cfg_summative_mgmt | 1 |
    And the following "campuses" exist:
      | name | default |
      | Main campus | 1 |
      | Other campus | 0 |
    And I login as "teacher"
    And I follow "TEST1001"
    When I create a new "summative" paper:
      | name | Logic |
      | session | now |
      | barriers | 0 |
      | duration | 1:30 |
      | period | March |
      | size | 31-40 |
      | sittings | 2 |
      | campus | Other campus |
      | notes | This exam is really important to me! |
    Then I should see "Logic" "paper_title"
