@core @users @javascript
Feature: Editing user roles
  In order to manage access
  As a system administrator
  I need to be able to manage user roles

  Background:
    Given the following "courses" exist:
      | name | description | school |
      | test course | a test course | Training |
    And the following "users" exist:
      | username | roles | sid | grade |
      | student | Student | 987654321 | test course |

  Scenario: A system administrator can modify roles
    Given I login as "admin"
    And I am on "User profile" page in "Roles" section for "student"
    And "Student" is checked in "editroles" form
    But "SysAdmin" is unchecked in "editroles" form
    And "Admin" is unchecked in "editroles" form
    And "Staff" is unchecked in "editroles" form
    And "Internal Reviewer" is unchecked in "editroles" form
    And "Left University" is unchecked in "editroles" form
    And "Inactive Staff" is unchecked in "editroles" form
    And "External Examiner" is unchecked in "editroles" form
    And "Invigilator" is unchecked in "editroles" form
    And "Standards Setter" is unchecked in "editroles" form
    And "Graduate" is unchecked in "editroles" form
    And "Suspended" is unchecked in "editroles" form
    And "Locked" is unchecked in "editroles" form
    And "System CRON" is unchecked in "editroles" form
    When I check "Staff" in "editroles" form
    And I uncheck "Student" in "editroles" form
    And I check "Graduate" in "editroles" form
    And I submit "editroles" form
    Then "Staff" is checked in "editroles" form
    And "Graduate" is checked in "editroles" form
    But "Student" is unchecked in "editroles" form
    And "SysAdmin" is unchecked in "editroles" form
    And "Admin" is unchecked in "editroles" form
    And "Internal Reviewer" is unchecked in "editroles" form
    And "Left University" is unchecked in "editroles" form
    And "Inactive Staff" is unchecked in "editroles" form
    And "External Examiner" is unchecked in "editroles" form
    And "Invigilator" is unchecked in "editroles" form
    And "Standards Setter" is unchecked in "editroles" form
    And "Suspended" is unchecked in "editroles" form
    And "Locked" is unchecked in "editroles" form
    And "System CRON" is unchecked in "editroles" form

  Scenario: An administrator can update roles
    Given the following "users" exist:
      | username | roles |
      | sadmin | Staff,Admin |
    And I login as "sadmin"
    And I am on "User profile" page in "Roles" section for "student"
    When I check "Staff" in "editroles" form
    And I submit "editroles" form
    Then "Staff" is checked in "editroles" form
    And "Student" is checked in "editroles" form

  Scenario: Role combinations are enforced
    Given I login as "admin"
    And I am on "User profile" page in "Roles" section for "student"
    And "SysAdmin" is enabled in "editroles" form
    And "Admin" is enabled in "editroles" form
    And "Staff" is enabled in "editroles" form
    And "Internal Reviewer" is enabled in "editroles" form
    And "Left University" is enabled in "editroles" form
    And "Inactive Staff" is enabled in "editroles" form
    And "External Examiner" is enabled in "editroles" form
    And "Invigilator" is enabled in "editroles" form
    And "Standards Setter" is enabled in "editroles" form
    And "Student" is enabled in "editroles" form
    And "System CRON" is enabled in "editroles" form
    But "Graduate" is disabled in "editroles" form
    And "Suspended" is disabled in "editroles" form
    And "Locked" is disabled in "editroles" form
    When I check "Staff" in "editroles" form
    Then "SysAdmin" is enabled in "editroles" form
    And "Admin" is enabled in "editroles" form
    And "Staff" is enabled in "editroles" form
    And "Standards Setter" is enabled in "editroles" form
    And "Student" is enabled in "editroles" form
    And "System CRON" is enabled in "editroles" form
    But "Left University" is disabled in "editroles" form
    And "Internal Reviewer" is disabled in "editroles" form
    And "Inactive Staff" is disabled in "editroles" form
    And "External Examiner" is disabled in "editroles" form
    And "Invigilator" is disabled in "editroles" form
    And "Graduate" is disabled in "editroles" form
    And "Suspended" is disabled in "editroles" form
    And "Locked" is disabled in "editroles" form

  Scenario: An administrator cannot add the SysAdmin and SysCron roles
    Given the following "users" exist:
      | username | roles |
      | sadmin | Staff,Admin |
    And I login as "sadmin"
    When I am on "User profile" page in "Roles" section for "student"
    Then "SysAdmin" is disabled in "editroles" form
    And "System CRON" is disabled in "editroles" form

  Scenario: Cannot save when no roles are selected
    Given I login as "admin"
    And I am on "User profile" page in "Roles" section for "student"
    When I uncheck "Student" in "editroles" form
    And "Student" is enabled in "editroles" form
    And "Graduate" is enabled in "editroles" form
    And "Suspended" is enabled in "editroles" form
    And "Locked" is enabled in "editroles" form
    But I cannot submit "editroles" form

  Scenario: As Staff I should just see roles the user has
    Given the following "users" exist:
      | username | roles |
      | teacher | Staff |
    And I login as "teacher"
    When I am on "User profile" page in "Roles" section for "student"
    Then I should see "Student" "content"
    And I should not see "Staff" "content"
