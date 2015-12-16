@backend
Feature: Behat Database resetting
  In order to have a known environment
  As behat
  I should be able to reset the database

  Scenario: Storing and resetting: Users table
    Given I store the database state
    And there are "102" records in the "users" table
    And the following "users" exist:
      | username |
      | student1 |
      | staff 1 |
    And there are "104" records in the "users" table
    When I reset the database state
    Then there are "102" records in the "users" table
