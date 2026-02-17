@core @javascript @sidebarkey
Feature: Sidebar keyboard navigation
   In order to navigate the sidebar menu using keyboard
   As a keyboard user
   I should be able to navigate menus using arrow keys

   Background:
      Given I login as "admin"
      Then I should see menu with following items:
         | menu_items |
         | Administrative Tools |
         | Create folder |
         | My Personal Keywords |
         | Search |

   Scenario: Focus moves to first item when submenu opens
      When I focus on "Search" "menu_item"
      And I press "ArrowRight" key
      And item "1" in the popup menu should have focus
      And I should see submenu with following items:
         | menu_items |
         | Questions |
         | Papers |
         | People |

   Scenario: ArrowDown navigates down in sidebar menu
      When I focus on "Create folder" "menu_item"
      And I press "ArrowDown" key
      Then "My Personal Keywords" "menu_item" should have focus

   Scenario: ArrowUp navigates up in sidebar menu
      When I focus on "My Personal Keywords" "menu_item"
      And I press "ArrowUp" key
      Then "Create folder" "menu_item" should have focus

   Scenario: ArrowRight opens submenu and focuses first item
      When I focus on "Search" "menu_item"
      And I press "ArrowRight" key
      And item "1" in the popup menu should have focus
      And I should see submenu with following items:
         | menu_items |
         | Questions |
         | Papers |
         | People |

   Scenario: ArrowLeft closes submenu
      When I focus on "Search" "menu_item"
      And I press "ArrowRight" key
      And I should see submenu with following items:
         | menu_items |
         | Questions |
         | Papers |
         | People |
      When I press "ArrowLeft" key
      Then no popup menus should be visible
      And "Search" "menu_item" should have focus

   Scenario: ArrowDown navigates within popup menu
      When I focus on "Search" "menu_item"
      And I press "ArrowRight" key
      Then item "1" in the popup menu should have focus
      And I should see submenu with following items:
         | menu_items |
         | Questions |
         | Papers |
         | People |
      And I press "ArrowDown" key
      Then item "2" in the popup menu should have focus

   Scenario: ArrowUp navigates within popup menu
      When I focus on "Search" "menu_item"
      And I press "ArrowRight" key
      And I press "ArrowDown" key
      Then item "2" in the popup menu should have focus
      When I press "ArrowUp" key
      Then item "1" in the popup menu should have focus

   Scenario: ArrowDown navigates past parent when submenu is open
      When I focus on "Search" "menu_item"
      And I press "ArrowRight" key
      And I should see submenu with following items:
         | menu_items |
         | Questions |
         | Papers |
         | People |
      And item "1" in the popup menu should have focus
      When I focus on "Search" "menu_item"
      Then "Search" "menu_item" should have focus
      And I press "ArrowDown" key
      Then the popup menu should be visible
      And "Search" "menu_item" should have focus

   Scenario: ArrowUp navigates above parent when submenu is open
      When I focus on "Search" "menu_item"
      And I press "ArrowRight" key
      And I should see submenu with following items:
         | menu_items |
         | Questions |
         | Papers |
         | People |
      And item "1" in the popup menu should have focus
      When I focus on "Search" "menu_item"
      Then "Search" "menu_item" should have focus
      And I press "ArrowUp" key
      Then the popup menu should be visible
      And "My Personal Keywords" "menu_item" should have focus

   Scenario: Escape closes submenu
      When I focus on "Search" "menu_item"
      And I press "ArrowRight" key
      And I should see submenu with following items:
         | menu_items |
         | Questions |
         | Papers |
         | People |
      When I press "Escape" key
      Then no popup menus should be visible
      And "Search" "menu_item" should have focus

   Scenario: ArrowRight on popup item without submenu keeps focus
      When I focus on "Search" "menu_item"
      And I press "ArrowRight" key
      And I press "ArrowDown" key
      Then item "2" in the popup menu should have focus
      When I press "ArrowRight" key
      Then item "2" in the popup menu should have focus

   @staff
   Scenario: Keyboard navigation works for staff role
      Given the following "users" exist:
         | username | roles |
         | sadmin | Staff |
      When I log out
      And I login as "sadmin"
      Then I should see menu with following items:
         | menu_items |
         | Create folder |
         | My Personal Keywords |
         | Search |
      When I focus on "Search" "menu_item"
      And I press "ArrowRight" key
      And item "1" in the popup menu should have focus
      And I should see submenu with following items:
         | menu_items |
         | Questions |
         | Papers |
         | People |
      When I press "ArrowDown" key
      Then item "2" in the popup menu should have focus
      When I press "ArrowUp" key
      Then item "1" in the popup menu should have focus
      When I press "ArrowLeft" key
      Then no popup menus should be visible
      And "Search" "menu_item" should have focus