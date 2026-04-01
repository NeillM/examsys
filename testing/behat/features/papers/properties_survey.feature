@paper @properties @javascript
Feature: Paper properties: OSCE
  In order to run an OSCE
  As a teacher
  I need to be able to change the settings of an OSCE

  Background:
    Given the "plugin_plain_texteditor" plugin is enabled
    And the following "modules" exist:
      | moduleid | fullname |
      | TEST1001 | Test module |
      | TEST1002 | Another test module |
    And the following "users" exist:
      | username | roles | first_names | surname | title |
      | teacher | Staff | Florence | Nightingale | Dr |
      | teacher2 | Staff | Edward | Jenner | Dr |
      | teacher3  | Staff | Marie | Curie | Prof |
      | internal  | Internal Reviewer | Louis | Pasteur | Prof |
      | external1 | External Examiner | Rebecca Lee | Crumpler | Dr |
      | external2 | External Examiner | Andreas | Vesalius | Dr |
    And the following "module team members" exist:
      | moduleid | username |
      | TEST1001 | teacher |
      | TEST1002 | teacher |
      | TEST1001 | teacher2 |
      | TEST1002 | teacher3 |
    And the following "folders" exist:
      | name | owner | parent |
      | My folder | teacher | |
      | Sub folder | teacher | My folder |
    And the following "papers" exist:
      | type | papertitle | paperowner | modulename |
      | survey | My survey | teacher | Test module |
    And the following "campuses" exist:
      | name |
      | Main Campus |
      | Second Campus |
    And the following "labs" exist:
      | name | campus |
      | Main PC room | Main Campus |
      | Small Lab | Main Campus |
      | Large Lab | Second Campus |
    And the following "exam pcs" exist:
      | address     | lab   |
      | 192.168.0.2 | Main PC room |
      | 192.168.0.3 | Small Lab |
      | 192.168.0.4 | Large Lab |
    And the following "questions" exist:
      | type | user | leadin | scenario | paper | screen | position | scale type | scale |
      | likert | teacher | likert 1 | likert 1 | My survey | 1 | 1 | OSCE Station Scales | 0, 1 |
      | likert |teacher | likert 2 | likert 2 | My survey | 1 | 2 | OSCE Station Scales | 0, 1 |

  Scenario: I can change the paper general settings
    Given I login as "teacher"
    And I am on "Properties" page for "My survey"
    When I set the fields:
      | field | value |
      | Name  | My paper |
      | Folder | |
      | Client Lockdown | IE Fullscreen mode |
      | Navigation | Unidirectional (linear) |
      # We appear to be unable to test colour selection as the settings are not changed by behat for some reason.
      | display calculator | off |
      | demo sound clip | off |
    And I cannot change fields:
      | field |
      | External System |
      | External System ID |
    And I press "OK"
    Then I click "Edit Properties" "menu_item"
    And I should see the following fields:
      | field | value |
      | Name | My paper |
      | Folder | |
      | Client Lockdown | IE Fullscreen mode |
      | Navigation | Unidirectional (linear) |
      # We appear to be unable to test colour selection as the settings are not changed by behat for some reason.
      | display calculator | off |
      | demo sound clip | off |

  Scenario: I can change the paper security settings
    Given I login as "teacher"
    And I am on "Properties" page for "My survey"
    And I click "Security" "tab"
    When I set the fields:
      | field | value |
      | Duration > Hours | 1 |
      | Duration > Minutes | 0 |
      | Time Zone | (UTC-05:00) Eastern Time (US and Canada) |
      | Available from > Date | monday next week |
      | Available from > Time | 09:00 |
      | Available until > Date | friday 2 weeks |
      | Available until > Time | 17:00 |
    And I press "OK"
    Then I am on "Properties" page for "My survey"
    And I click "Security" "tab"
    And I should see the following fields:
      | field | value |
      | Duration > Hours | 1 |
      | Duration > Minutes | 0 |
      | Time Zone | (UTC-05:00) Eastern Time (US and Canada) |
      | Available from > Date | monday next week |
      | Available from > Time | 09:00 |
      | Available until > Date | friday 2 weeks |
      | Available until > Time | 17:00 |
      | Modules > TEST1001: Test module | on |
      | Modules > TEST1002: Another test module | off |
      | Labs > Main PC room | off |
      | Labs > Small Lab | off |
      | Labs > Large Lab | off |

  Scenario: I can change the paper prologue settings
    Given I login as "teacher"
    And I am on "Properties" page for "My survey"
    And I click "Prologue" "tab"
    When I set the fields:
      | field | value |
      | Text displayed at the top of screen 1 when paper is started. | This is a prologue |
    And I press "OK"
    Then I am on "Properties" page for "My survey"
    And I click "Prologue" "tab"
    And I should see the following fields:
      | field | value |
      | Text displayed at the top of screen 1 when paper is started. | This is a prologue |

  Scenario: I can change the paper postscript settings
    Given I login as "teacher"
    And I am on "Properties" page for "My survey"
    And I click "Postscript" "tab"
    When I set the fields:
      | field | value |
      | Text displayed after the student clicks 'Finish' at the end. | This is a postscript |
    And I press "OK"
    Then I am on "Properties" page for "My survey"
    And I click "Postscript" "tab"
    And I should see the following fields:
      | field | value |
      | Text displayed after the student clicks 'Finish' at the end. | This is a postscript |

  Scenario: Check tabs that should not be present
    Given I login as "teacher"
    When I am on "Properties" page for "My survey"
    Then I should not see "Safe Exam Browser" "tab"
    And I should not see "Reviewers" "tab"
    And I should not see "Feedback" "tab"
    And I should not see "Exam Rubric" "tab"
