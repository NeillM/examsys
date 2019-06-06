@core 
Feature: Searching Papers,People and their related Academic Sessions
   In order to check searching on Papers,People
   As a Admin/ Teacher
   I want to do search

   @javascript
   Scenario: Test search Papers and People
      Given the following "users" exist:
         | username |roles |
         | myteacher1 | Staff |
         | myteacher2 | Staff |
         | myteacher3 | Staff |
      And the following "modules" exist:
         | moduleid | fullname |
         | m1 | m1 |
         | m2 | m2 |
         | m3 | m3 |
      And the following "academic year" exist:
         | calendar_year | academic_year |
         | 2016 | 2016/17 |
         | 2017 | 2017/18 |
         | 2018 | 2018/19 |
      And the following "papers" exist:
         | papertitle | papertype | paperowner | modulename |
         | paper1 | 2 | myteacher3 | m1 |
         | paper2 | 2 | myteacher3 | m2 |
         | paper3 | 2 | myteacher3 | m3 |
      And the following "module team members" exist:
         | moduleid | username |
         | m1 | myteacher1 |
         | m1 | myteacher2 |
         | m1 | myteacher3 |
         | m2 | myteacher1 |
         | m2 | myteacher2 |
         | m2 | myteacher3 |
         | m3 | myteacher1 |
         | m3 | myteacher2 |
         | m3 | myteacher3 |
      And I login as "admin"
      When I go to the homepage
      And I follow "Search"
      Then I should see submenu with following items:
         | menu_items |
         | Questions |
         | Papers |
         | People |
      When I click "Papers" "sub_search_menu_item"
      And I should be on "/paper/search.php"
      And I fill in the following:
         | searchterm | paper2 |
      And I check "summative"
      And I check "peerreview"
      And I check "offline"
      And I check "osce"
      And I check "survey"
      And I check "progress"
      And I check "formative"
      And I press "Search"  
      Then I should see "paper2" "link"
      When I log out
      And I login as "myteacher3"
      And I go to the homepage
      When I follow "Search"
      Then I should see submenu with following items:
         | menu_items |
         | Questions |
         | Papers |
         | People |
      When I click "Papers" "sub_search_menu_item"   
      And I should be on "/paper/search.php"
      And I fill in the following:
         | searchterm | paper2 |
      And I check "summative"
      And I check "peerreview"
      And I check "offline"
      And I check "osce"
      And I check "survey"
      And I check "progress"
      And I check "formative"
      And I press "Search"  
      Then I should see "paper2" "link"
      When I follow "paper2"
      Then I should see "Paper Tasks" menu section with following items
      | items |
      | Test & Preview |
      | Add Questions to Paper |
      | Edit Properties |
      | Reports |
      | Copy Paper |
      | Delete Paper |
      | Retire Paper |
      | Print Hardcopy version |
      | Import/Export |
      And I should see "Question Tasks" menu section with following items
      | items |
      | Edit Question |
      | Information |
      | Copy onto Paper X |
      | Link to paper |
      | Remove from Paper |
      | Preview Question |