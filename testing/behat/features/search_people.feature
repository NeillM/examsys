@core 
Feature: Searching People
   In order to check searching on People
   As a Admin/ Teacher
   I want to do search

   @javascript
   Scenario: Test search People
      Given the following "users" exist:
         | username |roles |
         | teacher1 | Staff |
         | teacher2 | Staff |
         | teacher3 | Staff |
         | student1 | Student |
      When I login as "admin"
      Then I should see menu with following items:
         | menu_items |
         | Administrative Tools |
         | Create folder |
         | My Personal Keywords |
         | Search |
      When I follow "Search"
      Then I should see submenu with following items:
         | menu_items |
         | Questions |
         | Papers |
         | People |
      When I click "People" "sub_search_menu_item"
      And I should be on "/users/search.php"
      And I fill in the following:
         | search_username | teacher1 |
      And I click "icon3" "id"
      And I check "staff"
      And I press "Search"
      Then I should see "teacher1" in the "table#maindata" element
      When I log out
      And I login as "teacher1"
      And I go to the homepage
      And I follow "Search"
      Then I should see submenu with following items:
         | menu_items |
         | Questions |
         | Papers |
         | People |
      When I click "People" "sub_search_menu_item"
      And I should be on "/users/search.php"
      And I fill in the following:
         | search_username | teacher2 |
      And I click "icon3" "id"
      Then I should see "Staff" in the "div#menu3" element
      When I check "staff"
      And I press "Search"
      Then I should see "teacher2" in the "table#maindata" element
      When I go to the homepage
      And I follow "Search"
      Then I should see submenu with following items:
         | menu_items |
         | Questions |
         | Papers |
         | People |
      When I click "People" "sub_search_menu_item"
      And I should be on "/users/search.php"
      And I fill in the following:
         | search_username | student1 |
      And I click "icon3" "id"
      Then I should see "Students" in the "div#menu3" element
      When I check "students"
      And I press "Search"
      Then I should see "student1" in the "table#maindata" element