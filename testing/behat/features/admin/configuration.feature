@admin @configuration @javascript
Feature: Editing configuration
  As an administrator
  I need to be able to change configuration

  @edit_config_api
  Scenario: Editing config API area then save
    Given the following "config" exist:
      | setting | value |
      | api_allow_superuser | 0 |
      | api_oauth_access_lifetime | 1209600 |
      | api_oauth_always_issue_new_refresh_token | 0 |
      | api_oauth_refresh_token_lifetime | 1209600 |
      | apilogfile |  |
      | cfg_api_enabled | 1 |
    And I login as "admin"
    And I am on "Configuration" page
    And I should see "API" "configarea"
    When I check "api_allow_superuser"
    And I set the field "api_oauth_access_lifetime" to "1209555"
    And I uncheck "cfg_api_enabled"
    And I click "Save" "link_or_button"
    Then the "api_oauth_access_lifetime" field should contain "1209555"
    And the "api_allow_superuser" checkbox should be checked
    And the "cfg_api_enabled" checkbox should be unchecked

  @edit_config_gradebook
  Scenario: Editing config gradebook area then save
    Given I login as "admin"
    And I am on "Configuration" page
    Then I should see "Gradebook" "configarea"
    Given the following "config" exist:
      | cfg_gradebook_enabled | 1 |
    When I uncheck "cfg_gradebook_enabled"
    And I click "Save" "link_or_button"
    And the "cfg_gradebook_enabled" checkbox should be unchecked

  @edit_config_lti
  Scenario: Editing config LTI integration area then save
    Given I login as "admin"
    And I am on "Configuration" page
    Then I should see "LTI Integration" "configarea"
    Given the following "config" exist:
      | setting | value |
      | cfg_lti_allow_module_create | 1 |
      | cfg_lti_allow_module_self_reg | 0 |
      | cfg_lti_allow_staff_module_register | 0 |
      | lti_auth_timeout | 9072000 |
      | lti_integration | default |
    When I uncheck "cfg_lti_allow_module_create"
    And I check "cfg_lti_allow_staff_module_register"
    And I click "Save" "link_or_button"
    And the "cfg_lti_allow_staff_module_register" checkbox should be checked
    And the "cfg_lti_allow_module_create" checkbox should be unchecked

  @edit_config_assessments
  Scenario: Editing config assessments integration area then save
    Given I login as "admin"
    And I am on "Configuration" page
    Then I should see "All Assessments" "configarea"
    Given the following "config" exist:
      | setting | value |
      | paper_anomaly_detection | {"progress":0,"summative":0} |
      | paper_autosave_backoff_factor | 1.5 |
      | paper_autosave_frequency | 180 |
      | paper_autosave_retrylimit | 3 |
      | paper_autosave_settimeout | 10 |
      | paper_mathjax | 1 |
      | paper_max_duration | 779 |
      | paper_types | {"formative":1,"progress":1,"summative":1,"survey":1,"osce":1,"offline":1,"peer_review":1} |
    And I uncheck "paper_mathjax"
    And I click "Save" "link_or_button"
    And the "paper_mathjax" checkbox should be unchecked
    And I set the field "paper_autosave_frequency" to "888"
    And I click "Save" "link_or_button"
    Then the "paper_autosave_frequency" field should contain "888"
    And I check "paper_anomaly_detection_progress"
    And I check "paper_anomaly_detection_summative"
    And I click "Save" "link_or_button"
    And the "paper_anomaly_detection_progress" checkbox should be checked
    And the "paper_anomaly_detection_summative" checkbox should be checked

  @edit_config_summative_assessments
  Scenario: Editing config summative assessments integration area then save
    Given I login as "admin"
    And I am on "Configuration" page
    Then I should see "Summative Assessments" "configarea"
    Given the following "config" exist:
      | setting | value |
      | cfg_summative_mgmt | 0 |
      | summative_hide_external | 0 |
      | summative_hour_warning | 10 |
      | summative_max_sittings | 6 |
      | summative_warn_external | 0 |
    And I check "cfg_summative_mgmt"
    And I click "Save" "link_or_button"
    And the "cfg_summative_mgmt" checkbox should be checked
    And I set the field "summative_hour_warning" to "2"
    And I click "Save" "link_or_button"
    Then the "summative_hour_warning" field should contain "2"

  @edit_config_miscellaneous
  Scenario: Editing config miscellaneous area then save
    Given I login as "admin"
    And I am on "Configuration" page
    Then I should see "Miscellaneous" "configarea"
    Given the following "config" exist:
      | setting | value |
      | misc_company | University of |
      | misc_dictionary_file | 0 |
      | misc_full_question_history_display_limit | 200 |
      | misc_full_question_history_enable | 0 |
      | misc_logo_email | alt_logo.png |
      | misc_logo_main | logo.png |
      | misc_search_leadin_length | 160 |
    And I set the field "misc_company" to "UoN"
    And I click "Save" "link_or_button"
    Then the "misc_company" field should contain "UoN"
    And I set the field "misc_full_question_history_display_limit" to "123"
    And I click "Save" "link_or_button"
    Then the "misc_full_question_history_display_limit" field should contain "123"

  @edit_config_calculation_questions
  Scenario: Editing config calculation questions area then save
    Given I login as "admin"
    And I am on "Configuration" page
    Then I should see "Calculation Questions" "configarea"
    Given the following "config" exist:
      | setting | value |
      | cfg_calc_type | phpEval |
    And I set the field "cfg_calc_type" to "phpEvaluate"
    And I click "Save" "link_or_button"
    Then the "cfg_calc_type" field should contain "phpEvaluate"

  @edit_config_system_settings
  Scenario: Editing config system settings area then save
    Given the following "config" exist:
      | setting | value |
      | system_academic_year_start | 07/01 |
      | system_hostname_lookup | 0 |
      | system_install_type |  |
      | system_maintenance_mode | 0 |
      | system_maxmediasize | 2097152 |
      | system_mediatypes | {"gif":1,"jpg":1,"jpeg":1,"png":1,"doc":1,"docx":1,"ppt":1,"pptx":1,"xls":1,"xlsx":1,"pdf":1,"avi":1,"mpg":1,"mpeg":1,"mov":1,"mp3":1,"mid":1,"wav":1,"ram":1,"pdb":1,"ply":1,"obj":1,"mtl":1,"dds":1,"zip":1} |
      | system_password_expire | 30 |
      | system_recover_postdata | 0 |
      | system_user_accessibility | 1 |
    When I login as "admin"
    And I am on "Configuration" page
    Then I should see "System settings" "configarea"
    And I set the field "system_academic_year_start" to "07/01"
    And I uncheck "system_mediatypes_png"
    And I uncheck "system_mediatypes_doc"
    And I check "system_user_accessibility"
    When I click "Save" "link_or_button"
    Then the "system_academic_year_start" field should contain "07/01"
    And the "system_user_accessibility" checkbox should be checked
    And the "system_mediatypes_gif" checkbox should be checked
    And the "system_mediatypes_jpg" checkbox should be checked
    And the "system_mediatypes_jpeg" checkbox should be checked
    And the "system_mediatypes_png" checkbox should be unchecked
    And the "system_mediatypes_doc" checkbox should be unchecked

  @edit_config_reports
  Scenario: Editing config system settings area then save
    Given the following "config" exist:
      | setting | value |
      | rpt_percent_decimals | 2 |
    When I login as "admin"
    And I am on "Configuration" page
    Then I should see "Reports" "configarea"
    And I set the field "rpt_percent_decimals" to "4"
    And I click "Save" "link_or_button"
    Then the "rpt_percent_decimals" field should contain "4"

  @edit_config_standard
  Scenario: Editing config standard setting settings area then save
    Given the following "config" exist:
      | setting | value |
      | stdset_hofstee_distinction | {"min_pass":"median","max_pass":100,"min_fail":0,"max_fail":100} |
      | stdset_hofstee_pass | {"min_pass":0,"max_pass":"median","min_fail":0,"max_fail":100} |
      | stdset_hofstee_whole_numbers | 1 |
      | cfg_ims_enabled | 0 |
    When I login as "admin"
    And I am on "Configuration" page
    Then I should see "Standard setting" "configarea"
    And I set the field "stdset_hofstee_distinction_max_pass" to "200"
    And I set the field "stdset_hofstee_pass_min_fail" to "4"
    And I click "Save" "link_or_button"
    Then the "stdset_hofstee_distinction_max_pass" field should contain "200"
    Then the "stdset_hofstee_pass_min_fail" field should contain "4"
