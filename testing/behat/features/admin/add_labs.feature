@admin @labs @javascript
Feature: Adding computer labs
  In order to summative security
  As an administrator
  I need to be able to create lists of PCs that are in computer labs

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
    And the following "exam pcs" exist:
      | address     | lab   |
      | 192.168.0.2 | Lab 1 |

  Scenario: Add a lab
    Given I login as "admin"
    And I am on "Lab" page
    And I should not see "A brand new lab" "text"
    And I follow "Create new lab"
    When I set the fields:
      | field        | value                          |
      | IP Addresses | 192.168.0.5                    |
      | Name         | A brand new lab                |
      | Low          | 1                              |
      | Building     | Westminster                    |
      | Room         | Room 101                       |
      | Timetabling  | Only available on Thursdays    |
      | IT Support   | Organised by Sauron            |
      | Plagarism    | Not available at this location |
    And I click "Save" "link_or_button"
    Then I am on "Lab" page
    And I should see "A brand new lab" "text"

  Scenario: Duplicate IP addresses are not saved
    Given I login as "admin"
    And I am on "Lab" page
    And I should not see "A brand new lab" "text"
    And I follow "Create new lab"
    When I set the fields:
      | field        | value                          |
      | IP Addresses | 192.168.0.2                    |
      | Name         | A brand new lab                |
      | Low          | 1                              |
      | Building     | Westminster                    |
      | Room         | Room 101                       |
      | Timetabling  | Only available on Thursdays    |
      | IT Support   | Organised by Sauron            |
      | Plagarism    | Not available at this location |
    And I click "Save" "link_or_button"
    Then I should see "The following IP addresses are already in use, they need to be removed from other labs before the can be added here:" "text"
    And I should see "The lab has not been created" "text"
    And I click "192.168.0.2" "link"
    And I should see "Lab 1" "text"
    But I should not see "A brand new lab" "text"

  Scenario: Invalid IP address are not saved
    Given I login as "admin"
    And I am on "Lab" page
    And I should not see "A brand new lab" "text"
    And I follow "Create new lab"
    When I set the fields:
      | field        | value                          |
      | IP Addresses | notanipaddress                 |
      | Name         | A brand new lab                |
      | Low          | 1                              |
      | Building     | Westminster                    |
      | Room         | Room 101                       |
      | Timetabling  | Only available on Thursdays    |
      | IT Support   | Organised by Sauron            |
      | Plagarism    | Not available at this location |
    And I click "Save" "link_or_button"
    Then I should see "The following IP addresses are invalid:" "text"
    And I should see "The lab has not been created" "text"
