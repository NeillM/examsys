@core @javascript
Feature: Keywords Creation
  In order to use a keyword
  As a teacher
  I should be able to create new keyword

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

  Scenario: Teacher creates a module keyword
    Given I login as "teacher"
    And I follow "TEST1001"
    And I follow "Manage Keywords"
    And I create a new keyword "test"
    Then I should see "test"
