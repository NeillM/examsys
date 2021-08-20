@admin @javascript
Feature: Audit Administration
  In order to view access audit logs
  As a sys admin
  I should be able to manaage the logs

  Background:
    Given the following "modules" exist:
      | moduleid | fullname |
      | TEST1001 | Test module |
    And the following "courses" exist:
       | name | description | school |
       | test course | a test course | Training |
    And the following "users" exist:
      | username | roles | sid | grade |
      | teacher | Staff | | University Lecturer |
      | student1 | Student | 42424242 | test course |
    And the following "access audit" exist:
      | username | action | sourceuser | source | details |
      | student1 | Add-Enrolment | teacher | /api/modulemanagement/enrol | TEST1001 |
      | student1 | Remove-Enrolment | teacher | /plugins/ims/cron.php | TEST1001 |
      | student1 | Remove-Enrolment | teacher | /plugins/SMS/plugin_cs_sms/sync_all_cron.php | TEST1001 |
      | teacher | Add-Team-Member | teacher | /module/do_edit_team.php | TEST1001 |

  @admin_audit_manage_object_role
  Scenario: Navigate to the object
    Given I login as "admin"
    When I follow "Administrative Tools"
    And I click "Access Audit" "admin_tool_link"
    And I click "Staff" "audit_object"
    Then I should be on "/users/details.php"

  @admin_audit_manage_object_module
  Scenario: Navigate to the object
    Given I login as "admin"
    When I follow "Administrative Tools"
    And I click "Access Audit" "admin_tool_link"
    And I click "TEST1001" "audit_object"
    Then I should be on "/module/index.php"

  @admin_audit_manage_user
  Scenario: Navigate to the affected user
    Given I login as "admin"
    When I follow "Administrative Tools"
    And I click "Access Audit" "admin_tool_link"
    And I click "student1" "audit_user"
    Then I should be on "/users/details.php"

  @admin_audit_manage_source_api
  Scenario: Navigate to page the source of the change
    Given I login as "admin"
    When I follow "Administrative Tools"
    And I click "Access Audit" "admin_tool_link"
    And I click "Enrolment API" "audit_source"
    Then I should be on "/admin/oauth/list_oauth.php"

  @admin_audit_manage_source_ims
  Scenario: Navigate to page the source of the change
    Given I login as "admin"
    When I follow "Administrative Tools"
    And I click "Access Audit" "admin_tool_link"
    And I click "IMS" "audit_source"
    Then I should be on "/plugins/ims/ims_settings.php"

  @admin_audit_manage_source_sms
  Scenario: Navigate to page the source of the change
    Given I login as "admin"
    When I follow "Administrative Tools"
    And I click "Access Audit" "admin_tool_link"
    And I click "SMS" "audit_source"
    Then I should be on "/admin/sms_import_summary.php"

  @admin_audit_manage_source_module
  Scenario: Navigate to page the source of the change
    Given I login as "admin"
    When I follow "Administrative Tools"
    And I click "Access Audit" "admin_tool_link"
    And I click "Module" "audit_source"
    And I should be on "/module/index.php"
    Then I should see page with title "Module: TEST1001"
