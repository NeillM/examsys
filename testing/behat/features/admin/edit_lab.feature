@admin @labs @javascript
Feature: Editing computer labs
  In order to summative security
  As an administrator
  I need to be able to maintain the list of PCs in them

  Background:
    Given the following "config" exist:
      | setting                | value |
      | system_hostname_lookup | 0     |
    And the following "campuses" exist:
      | name        |
      | Main campus |
    And the following "labs" exist:
      | name  | campus      | building  | room |
      | Lab 1 | Main campus | Whitehall | 45   |
      | Lab 2 | Main campus | Whitehall | 13   |
    And the following "exam pcs" exist:
      | address     | lab   |
      | 192.168.0.2 | Lab 1 |
      | 192.168.0.4 | Lab 2 |

  Scenario: Editing a lab
    Given the following "campuses" exist:
      | name          |
      | Second campus |
    And I login as "admin"
    And I am on "Lab" page for "Lab 2"
    And I click "Edit" "link_or_button"
    When I set the fields:
      | field        | value                          |
      | IP Addresses | 192.168.0.5                    |
      | Name         | Lab 2 (renamed)                |
      | Low          | 1                              |
      | Building     | Westminster                    |
      | Room         | Room 101                       |
      | Timetabling  | Only available on Thursdays    |
      | IT Support   | Organised by Sauron            |
      | Plagarism    | Not available at this location |
    And I click "Save" "link_or_button"
    Then I am on "Lab" page for "Lab 2 (renamed)"

  Scenario: Duplicate IP address are not saved
    Given I login as "admin"
    And I am on "Lab" page for "Lab 2"
    And I click "Edit" "link_or_button"
    And I set the field "IP Addresses" to "192.168.0.2"
    When I click "Save" "link_or_button"
    Then I should see "The following IP addresses are already in use, they need to be removed from other labs before the can be added here:" "text"
    And I should see "No changes have been saved" "text"
    And I click "192.168.0.2" "link"
    And I should see "Lab 1" "text"
    But I should not see "Lab 2" "text"

  Scenario: Invalid IP address are not saved
    Given I login as "admin"
    And I am on "Lab" page for "Lab 2"
    And I click "Edit" "link_or_button"
    And I set the field "IP Addresses" to "notanipaddress"
    When I click "Save" "link_or_button"
    Then I should see "The following IP addresses are invalid:" "text"
    And I should see "No changes have been saved" "text"
