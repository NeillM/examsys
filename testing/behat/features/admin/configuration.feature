@admin @configuration @javascript
Feature: Editing configuration
  As an administrator
  I need to be able to change configuration

  Background:
    Given the following "config" exist:
      | setting | value |
      | api_allow_superuser | 0 |
      | api_oauth_access_lifetime | 1209600 |
      | api_oauth_always_issue_new_refresh_token | 0 |
      | api_oauth_refresh_token_lifetime | 1209600 |
      | apilogfile |  |
      | cfg_api_enabled | 1 |
      | cfg_gradebook_enabled | 1 |
      | cfg_lti_allow_module_create | 1 |
      | cfg_lti_allow_module_self_reg | 0 |
      | cfg_lti_allow_staff_module_register | 0 |
      | lti_auth_timeout | 9072000 |
      | lti_integration | default |
      | paper_anomaly_detection | {"progress":0,"summative":0} |
      | paper_autosave_backoff_factor | 1.5 |
      | paper_autosave_frequency | 180 |
      | paper_autosave_retrylimit | 3 |
      | paper_autosave_settimeout | 10 |
      | paper_mathjax | 1 |
      | paper_max_duration | 779 |
      | paper_types | {"formative":1,"progress":1,"summative":1,"survey":1,"osce":1,"offline":1,"peer_review":1} |
      | cfg_summative_mgmt | 0 |
      | summative_hide_external | 0 |
      | summative_hour_warning | 10 |
      | summative_max_sittings | 6 |
      | summative_warn_external | 0 |
      | misc_company | University of |
      | misc_dictionary_file | 0 |
      | misc_full_question_history_display_limit | 200 |
      | misc_full_question_history_enable | 0 |
      | misc_logo_email | alt_logo.png |
      | misc_logo_main | logo.png |
      | misc_search_leadin_length | 160 |
      | cfg_calc_type | phpEval |
      | system_academic_year_start | 07/01 |
      | system_hostname_lookup | 0 |
      | system_install_type |  |
      | system_maintenance_mode | 0 |
      | system_maxmediasize | 2097152 |
      | system_mediatypes | {"gif":1,"jpg":1,"jpeg":1,"png":1,"doc":1,"docx":1,"ppt":1,"pptx":1,"xls":1,"xlsx":1,"pdf":1,"avi":1,"mpg":1,"mpeg":1,"mov":1,"mp3":1,"mid":1,"wav":1,"ram":1,"pdb":1,"ply":1,"obj":1,"mtl":1,"dds":1,"zip":1} |
      | system_password_expire | 30 |
      | system_recover_postdata | 0 |
      | system_user_accessibility | 1 |
      | rpt_percent_decimals | 2 |
      | stdset_hofstee_distinction | {"min_pass":"median","max_pass":100,"min_fail":0,"max_fail":100} |
      | stdset_hofstee_pass | {"min_pass":0,"max_pass":"median","min_fail":0,"max_fail":100} |
      | stdset_hofstee_whole_numbers | 1 |
      | cfg_ims_enabled | 0 |

  @edit_config
  Scenario: Editing config
    And I login as "admin"
    When I follow "Administrative Tools"
    And I click "Configuration" "admin_tool_link"
    And I set the field "cfg_calc_type" to "phpEvalaaa"
    And I click "Save" "link_or_button"
    Then the "cfg_calc_type" field should contain "phpEvalaaa"
    And I set the field "misc_company" to "misccompany"
    When I click "Save" "link_or_button"
    Then the "misc_company" field should contain "misccompany"
    When I check "api_allow_superuser"
    When I click "Save" "link_or_button"
    Then the "api_allow_superuser" checkbox should be checked
    