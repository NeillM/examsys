@staff @recyclebin @javascript
Feature: Recycle Bin
  In order to recover deleted item
  As a staff member
  I should be able to view my recycle bin

  Background:
    Given the following "modules" exist:
      | moduleid | fullname |
      | TEST1001 | Test module |
    And the following "users" exist:
      | username | roles | sid | grade |
      | teacher | Staff | | University Lecturer |
    And the following "papers" exist:
      | type | papertitle | paperowner | modulename | deleted |
      | formative | a formative paper | teacher | Test module | Second wednesday of August last year 9:30 |


  @recylebin_paper
  Scenario: Navigate my reyclebin for deleted papers
    Given I login as "teacher"
    When I follow "Recycle Bin"
    Then I should see "a formative paper"
    And I should see "09:30"
