@papers @javascript
Feature: Paper changes
  In order to understand why a paper is setup the way it is
  As a teacher
  I need to be able to view the history of changes made to it
  
  Background:
    Given the following "modules" exist:
      | moduleid | fullname |
      | TEST1001 | Test module |
    And the following "users" exist:
      | username | roles | title | surname |
      | teacher | Staff | Dr | Who |
    And the following "module team members" exist:
      | moduleid | username |
      | TEST1001 | teacher |
    And the following "papers" exist:
      | type | papertitle | paperowner | modulename |
      | formative | a formative paper | teacher | Test module |
    And the following "paper change logs" exist:
      | paper | type | old | new | date | user |
      | a formative paper | navigation | 0 | 1 | 2025-03-15 11:00:00 | teacher |
      | a formative paper | marking | 0 | 1 | 2025-03-15 12:00:00 | teacher |

  Scenario: I can open and close the paper changes page
    Given I login as "teacher"
    And I am on "Paper Details" page for "a formative paper"
    When I click "Paper Changes" "menu_item"
    And I click "Close" "link_or_button"
    Then I should see page with title "a formative paper"

  Scenario: I can see the list of changes
    Given I login as "teacher"
    And I am on "Paper Details" page for "a formative paper"
    When I click "Paper Changes" "menu_item"
    Then I should see table with:
      | Part | Old | New | Date | User |
      | Marking | No Adjustment | Calculate Random Mark | 15/03/25 12:00 | Dr Who |
      | Navigation | Unidirectional (linear) | Bidirectional | 15/03/25 11:00 | Dr Who |
