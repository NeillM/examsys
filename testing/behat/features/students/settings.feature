@students @accessibility @javascript
Feature: Set user profile settings
  In order to set my settings
  As a student
  I should be able to access the user profile screen

  Background:
    Given the following "users" exist:
      | username | sid |
      | student1 | 42424242 |
    And the following "config" exist:
      | setting | value |
      | system_user_accessibility | 1 |

  Scenario: Student sets user profile settings
    Given I login as "student1"
    And I toggle the main menu
    And I click "User Profile" "main_menu_item"
    And I set the accessibility settings:
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
    And I click "Save" "button"
    Then the "textsize" field should contain "120"
    And the "font" field should contain "Verdana"
    And the "Select a Paper Background Colour" checkbox is checked
    And the "Select a Main Text Colour" checkbox is checked
    And the "Select a Question Marks Text Colour" checkbox is checked
    And the "Select a Question Heading Text Colour" checkbox is checked
    And the "Select a Question Labels Text Colour" checkbox is checked
    And the "Select a Unanswered Question Highlight Colour" checkbox is checked
    And the "Select a Dismiss Option Text Color" checkbox is checked
    And the "Select a Highlighted Question/Option Colour" checkbox is checked
    And the "Select a Exam Header/Footer Background Colour" checkbox is checked
    And the "Select a Exam Header/Footer Text Colour" checkbox is checked

  Scenario: Student resets user profile settings to default
    Given I login as "student1"
    And I toggle the main menu
    And I click "User Profile" "main_menu_item"
    And I set the accessibility settings:
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
    And I click "Save" "button"
    And I click "Reset to Default" "button"
    Then the "textsize" field should contain "100"
    And the "font" field should contain "Arial"
    And the "Reset Paper Background Colour to system default" checkbox is checked
    And the "Reset Main Text Colour to system default" checkbox is checked
    And the "Reset Question Marks Text Colour to system default" checkbox is checked
    And the "Reset Question Heading Text Colour to system default" checkbox is checked
    And the "Reset Question Labels Text Colour to system default" checkbox is checked
    And the "Reset Unanswered Question Highlight Colour to system default" checkbox is checked
    And the "Reset Dismiss Option Text Color to system default" checkbox is checked
    And the "Reset Highlighted Question/Option Colour to system default" checkbox is checked
    And the "Reset Exam Header/Footer Background Colour to system default" checkbox is checked
    And the "Reset Exam Header/Footer Text Colour to system default" checkbox is checked

  Scenario: Student resets user profile settings to previous save state
    Given I login as "student1"
    And I toggle the main menu
    And I click "User Profile" "main_menu_item"
    And I set the accessibility settings:
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
    And I click "Save" "button"
    And I click "Reset to Default" "button"
    And I set the accessibility settings:
      | fontsize | 110 |
      | typeface | Arial |
      | background | #EEECE1 |
      | foreground | #F79646 |
    And I click "Undo" "button"
    Then the "textsize" field should contain "120"
    And the "font" field should contain "Verdana"
    And the "Select a Paper Background Colour" checkbox is checked
    And the "Select a Main Text Colour" checkbox is checked
    And the "Select a Question Marks Text Colour" checkbox is checked
    And the "Select a Question Heading Text Colour" checkbox is checked
    And the "Select a Question Labels Text Colour" checkbox is checked
    And the "Select a Unanswered Question Highlight Colour" checkbox is checked
    And the "Select a Dismiss Option Text Color" checkbox is checked
    And the "Select a Highlighted Question/Option Colour" checkbox is checked
    And the "Select a Exam Header/Footer Background Colour" checkbox is checked
    And the "Select a Exam Header/Footer Text Colour" checkbox is checked

  Scenario: Student closed profile without saving
    Given I login as "student1"
    And I toggle the main menu
    And I click "User Profile" "main_menu_item"
    And I set the accessibility settings:
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
    And I click "Close" "button"
    And I toggle the main menu
    And I click "User Profile" "main_menu_item"
    Then the "textsize" field should contain "100"
    And the "font" field should contain "Arial"
    And the "Reset Paper Background Colour to system default" checkbox is checked
    And the "Reset Main Text Colour to system default" checkbox is checked
    And the "Reset Question Marks Text Colour to system default" checkbox is checked
    And the "Reset Question Heading Text Colour to system default" checkbox is checked
    And the "Reset Question Labels Text Colour to system default" checkbox is checked
    And the "Reset Unanswered Question Highlight Colour to system default" checkbox is checked
    And the "Reset Dismiss Option Text Color to system default" checkbox is checked
    And the "Reset Highlighted Question/Option Colour to system default" checkbox is checked
    And the "Reset Exam Header/Footer Background Colour to system default" checkbox is checked
    And the "Reset Exam Header/Footer Text Colour to system default" checkbox is checked
