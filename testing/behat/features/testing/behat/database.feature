@backend
Feature: Behat Database resetting
  In order to have a known environment
  As behat
  I should be able to reset the database

  Scenario: Storing and resetting: Users table
    Given I store the database state
    And there are "3" records in the "users" table
    And the following "users" exist:
      | username | sid | roles |
      | student1 | 123456789 | Student |
      | staff1   |           | Staff   |
    And there are "5" records in the "users" table
    When I reset the database state
    Then there are "3" records in the "users" table
