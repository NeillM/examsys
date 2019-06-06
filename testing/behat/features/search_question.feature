@core
Feature: Searching Papers,People and their related Academic Sessions
  In order to check searching on Papers,People
  As a Admin/ Teacher
  I want to do search

  @javascript
  Scenario: Test search Papers and People
    Given the following "users" exist:
      | username | roles |
      | teacher1 | Staff |
    And the following "modules" exist:
      | moduleid | fullname |
      | m1       | m1       |
      | m2       | m2       |
      | m3       | m3       |
    And the following "questions" exist:
      | type    | leadin           | user     |
      | textbox | textbox_question | teacher1 |
    And the following "module team members" exist:
      | moduleid | username |
      | m1         | teacher1 |
      | m2         | teacher1 |
      | m3         | teacher1 |
    And I login as "teacher1"
    When I follow "Search"
    Then I should see submenu with following items:
      | menu_items  |
      | Questions |
      | Papers    |
      | People    |
    When I click "Question" "sub_search_menu_item"
    And I should be on "/question/search.php"
    And I fill in the following:
      | searchterm | textbox_question |
    And I press "Search"
    Then I should see "textbox_question" in the "td.u" element