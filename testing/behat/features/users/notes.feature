@users @note @javascript
Feature: Add student note in the user profile
  In order to record issues that happended during an exam
  As a Admin/ Teacher
  I want to add a student note

  Background:
    Given the following "courses" exist:
      | name | description | school |
      | test course | a test course | Training |
    And the following "users" exist:
      | username | roles | sid | grade |
      | student1 | Student | 987654321 | test course |
    And the following "modules" exist:
      | moduleid | fullname |
      | m1 | m1 |
    And the following "module enrolment" exist:
      | sid | modulecode |
      | 987654321 | m1 |
    And the following "papers" exist:
      | type | papertitle | paperowner | modulename | startdate |
      | progress | paper1 | admin | m1 | 2020-02-27 12:00:00 |
      | progress | paper2 | admin | m1 | 2020-02-25 12:00:00 |
    And the following "paper note" exist:
      | paper | note | user | author |
      | paper2 | a note made by me | student1 | admin |

  Scenario: Add student note via user detail screen
    Given I login as "admin"
    And I am on "User profile" page in "Notes" section for "student1"
    And I add a note "test note" to the paper "paper1"
    Then I should see "test note"

  Scenario: Update student note via user detail screen
    Given I login as "admin"
    And I am on "User profile" page in "Notes" section for "student1"
    And I should see "a note made by me"
    And I add a note "another note" to the paper "paper2"
    Then I should see "another note"
