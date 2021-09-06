# This file contains UTF-8 characters, and should not be edited with a non-UTF8 aware editor.

@questions @javascript
Feature: Hotspot question editing
  In order to run exams
  As a teacher
  I need to be able to edit hotspot questions and have non-breaking spaces removed

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

  @question_edit_hotspot_label
  Scenario: Check that non-breaking spaces are removed from hotspot labels
    Given I login as "teacher"
    And I follow "TEST1001"
    And The upload source path is "questions/html5"
    And I select a "hotspot" question type
    And I create a new "hotspot" question:
      | theme | hotspot theme |
      | notes | hotspot notes |
      | scenario | hotspot scenario |
      | file | plants.jpg |
      # Contains non-breaking spaces, chr 160
      | hotspot_1 | Nonbreaking space test,rectangle,0,200,100,100 |
      | hotspot_2 | Figure space test,ellipse,100,100,100,100 |
      | hotspot_3 | Narrow non-breaking space⁠with invisible space,polygon,100,50,100,0,0,100,-100,-100 |
    And I click "Add to Bank" "button"
    And I follow "All Questions"
    When I double click "hotspot theme" "question_bank_item"
    # regular spaces, not non-breaking ones
    Then I should see "Nonbreaking space test" in the "div#hotspot_label_0" element
    And I should see "Figure space test" in the "div#hotspot_label_1" element
    And I should see "Narrow non-breaking space with invisible space" in the "div#hotspot_label_2" element
