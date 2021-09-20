@users @accessibility @javascript
Feature: Add student accessibility settings in the user profile
  In order to set a students accessibility settings for an exam
  As a Admin/ Teacher
  I want to add accessibility settings

  Background:
    Given the following "courses" exist:
      | name | description | school |
      | test course | a test course | Training |
    Given the following "users" exist:
      | username | roles | sid | grade |
      | student1 | Student | 987654321 | test course |

  Scenario: Add accessibility settings via user detail screen
    Given I login as "admin"
    And I am on "User profile" page in "Accessibility" section for "student1"
    And I set the accessibility settings:
      | extratime | 5 |
      | fontsize | 120 |
      | typeface | Verdana |
      | background | #FFFFFF |
      | foreground | #000000 |
      | marks | #EEECE1 |
      | heading | #FFFF00 |
      | label | #F79646 |
      | unanswered | #C0504D |
      | dismiss | #938953 |
      | highlight | #95B3D7 |
      | globaltheme | #00B050 |
      | globalfont | #000000 |
      | medical | has these needs |
      | breaks | needs breaks |
      | breaktime | 10 |
    And I submit "accessibility" form
    Then the "extra_time" field should contain "5"
    And the "textsize" field should contain "120"
    And the "font" field should contain "Verdana"
    And the "Select a Background" checkbox is checked
    And the "Select a Foreground" checkbox is checked
    And the "Select a Marks Colour" checkbox is checked
    And the "Select a Heading/Theme Colour" checkbox is checked
    And the "Select a Note/Labels Colour" checkbox is checked
    And the "Select a Unanswered" checkbox is checked
    And the "Select a Dismiss Color" checkbox is checked
    And the "Select a Highlight Colour" checkbox is checked
    And the "Select a Global Theme Colour" checkbox is checked
    And the "Select a Global Theme Font Colour" checkbox is checked
    And the "medical" field should contain "has these needs"
    And the "breaks" field should contain "needs breaks"
    And the "break_time" field should contain "10"

  Scenario: Add acessiblity setting in user detail screen and view audit
    Given I login as "admin"
    And I am on "User profile" page in "Accessibility" section for "student1"
    And I set the accessibility settings:
      | typeface | Verdana |
    And I submit "accessibility" form
    And I am on "User profile" page in "Profile Audit" section for "student1"
    Then I should see "Verdana"
