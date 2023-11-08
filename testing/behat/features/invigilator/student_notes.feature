@invigilator @note @summative @javascript
Feature: Invigilator notes about students
  In order to record things that might cause an extenuating circumstance
  As an invigilator
  I need to be able to record notes against students

  Background:
    Given the following "config" exist:
      | setting | value |
      | system_hostname_lookup | 1 |
    And the following "modules" exist:
      | moduleid | fullname |
      | TEST1001 | Test module |
    And the following "users" exist:
      | username | roles | sid |
      | student1 | Student | 123456 |
      | student2 | Student | 234567 |
      | student3 | Student | 345678 |
      | invigilator | Invigilator | |
    And the following "module enrolment" exist:
      | modulecode | sid |
      | TEST1001 | 123456 |
      | TEST1001 | 234567 |
      | TEST1001 | 345678 |
    And the following "campuses" exist:
      | name        |
      | Main campus |
    And the following "labs" exist:
      | name  | campus      | building  | room |
      | Lab 1 | Main campus | Whitehall | 45   |
    And the following "exam pcs" exist:
      | address | lab   |
      | behat   | Lab 1 |
    And the following "papers" exist:
      | type | papertitle | paperowner | modulename | startdate | labs | duration |
      | summative | A summative paper | admin | Test module | 30 minutes ago | Lab 1 | 120 |
    And the following "paper note" exist:
      | paper | note | user | author |
      | A summative paper | An older note | student2 | invigilator |

  Scenario: Add a note to a student
    Given I login as "invigilator"
    And I am on "Invigilation" page
    When I add a "Look mum, its a note!" note to "student1" student on "A summative paper" paper
    Then the "student1" student on "A summative paper" paper invigilator note is "Look mum, its a note!"
    And the "student2" student on "A summative paper" paper invigilator note is "An older note"
    And the "student3" student on "A summative paper" paper invigilator note is ""

  Scenario: Update a student note
    Given the following "paper note" exist:
      | paper | note | user | author |
      | A summative paper | a previous note | student1 | invigilator |
    And I login as "invigilator"
    And I am on "Invigilation" page
    When I navigate to invigilator note for "student1" student on "A summative paper" paper
    And the "note" field should contain "a previous note"
    And I fill in "note" with "I have completely changed this note!"
    And I save the invigilator note
    Then the "student1" student on "A summative paper" paper invigilator note is "I have completely changed this note!"
    And the "student2" student on "A summative paper" paper invigilator note is "An older note"
    And the "student3" student on "A summative paper" paper invigilator note is ""
