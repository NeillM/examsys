@core
Feature: Login Data Recovery
  In order to avoid lost POST data due to session expiry
  As a user
  I should be able to continue without losing data

  @javascript
  Scenario: Recover data from admin session when configuration key enabled
    Given the following "config" exist:
      | setting | value |
      | system_recover_postdata | 1 |
    And I login as "admin"
    And I follow "Create folder"
    And I fill in "folder_name" with "recoverytest"
    And I destroy the session
    And I click "Create" "button"
    And I should see "The page you are trying to access requires authentication."
    And I relogin as "admin"
    Then I should see "recoverytest" "folder"

  @javascript
  Scenario: Do not recover data from admin session when configuration key disabled
    Given the following "config" exist:
      | setting | value |
      | system_recover_postdata | 0 |
    And I login as "admin"
    And I follow "Create folder"
    And I fill in "folder_name" with "norecoverytest"
    And I destroy the session
    And I click "Create" "button"
    And I should see "The page you are trying to access requires authentication."
    And I relogin as "admin"
    Then I should not see "norecoverytest" "folder"
