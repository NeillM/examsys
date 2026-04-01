@paper @properties @javascript
Feature: Paper properties: Peer review
  In order to run a Peer review
  As a teacher
  I need to be able to change the settings of a Peer review

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
    And the following "users" exist:
      | username | roles | sid |
      | student1  | Student | 123456789 |
      | student2  | Student | 234567891 |
      | student3  | Student | 345678912 |
      | student4  | Student | 456789123 |
    And the following "user metadata" exist:
      | username | modulecode | type | value |
      | student1 | TEST1001 | Group | Group 1 |
      | student2 | TEST1001 | Group | Group 1 |
      | student3 | TEST1001 | Group | Group 2 |
      | student4 | TEST1001 | Group | Group 2 |
      | student1 | TEST1001 | Campus | Main |
      | student2 | TEST1001 | Campus | Alternative |
      | student3 | TEST1001 | Campus | Alternative |
      | student4 | TEST1001 | Campus | Main |
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
      | peer_review | Peer review paper| teacher | Test module |
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
      | type | user | leadin | scenario | paper | screen | position | correct | marks_correct | marks_incorrect | columns | rows | editor |
      | textbox | teacher | textbox 1 leadin | textbox 1 scenario | Peer review paper | 1 | 1 | placeholder | 1 | 0 | 90 | 5 | WYSIWYG |

  Scenario: I can change the paper general settings
    Given I login as "teacher"
    And I am on "Properties" page for "Peer review paper"
    When I set the fields:
      | field | value |
      | Name  | My paper |
      | Folder | My folder;Sub folder |
      | Client Lockdown | 3rd Party tool |
      | Navigation | Bidirectional |
      # We appear to be unable to test colour selection as the settings are not changed by behat for some reason.
      | Photos > if available | off |
      | Group Details | Campus |
      | Number from | 0 |
      | Review | Single review |
    And I cannot change fields:
      | field |
      | External System |
      | External System ID |
    And I press "OK"
    Then I click "Edit Properties" "menu_item"
    And I should see the following fields:
      | field | value |
      | Name  | My paper |
      | Folder | My folder;Sub folder |
      | Client Lockdown | 3rd Party tool |
      | Navigation | Bidirectional |
      # We appear to be unable to test colour selection as the settings are not changed by behat for some reason.
      | Photos > if available | off |
      | Group Details | Campus |
      | Number from | 0 |
      | Review | Single review |

  Scenario: I can change the paper security settings
    Given I login as "teacher"
    And I am on "Properties" page for "Peer review paper"
    And I click "Security" "tab"
    When I set the fields:
      | field | value |
      | Password | |
      | Duration > Hours | 0 |
      | Duration > Minutes | 30 |
      | Time Zone | (UTC+01:00) Amsterdam, Berlin, Stockholm |
      | Available from > Date | monday last week |
      | Available from > Time | 09:00 |
      | Available until > Date | friday 2 weeks |
      | Available until > Time | 17:00 |
      | Modules > TEST1001: Test module | off |
      | Modules > TEST1002: Another test module | on |
      | Labs > Main PC room | on |
    And I press "OK"
    Then I am on "Properties" page for "Peer review paper"
    And I click "Security" "tab"
    And I should see the following fields:
      | field | value |
      | Password | |
      | Duration > Hours | 0 |
      | Duration > Minutes | 30 |
      | Time Zone | (UTC+01:00) Amsterdam, Berlin, Stockholm |
      | Available from > Date | monday last week |
      | Available from > Time | 09:00 |
      | Available until > Date | friday 2 weeks |
      | Available until > Time | 17:00 |
      | Modules > TEST1001: Test module | off |
      | Modules > TEST1002: Another test module | on |
      | Labs > Main PC room | on |

  Scenario: I can change the paper prologue settings
    Given I login as "teacher"
    And I am on "Properties" page for "Peer review paper"
    And I click "Prologue" "tab"
    When I set the fields:
      | field | value |
      | Text displayed at the top of screen 1 when paper is started. | This is a prologue |
    And I press "OK"
    Then I am on "Properties" page for "Peer review paper"
    And I click "Prologue" "tab"
    And I should see the following fields:
      | field | value |
      | Text displayed at the top of screen 1 when paper is started. | This is a prologue |

  Scenario: I can change the paper postscript settings
    Given I login as "teacher"
    And I am on "Properties" page for "Peer review paper"
    And I click "Postscript" "tab"
    When I set the fields:
      | field | value |
      | Text displayed after the student clicks 'Finish' at the end. | This is a postscript |
    And I press "OK"
    Then I am on "Properties" page for "Peer review paper"
    And I click "Postscript" "tab"
    And I should see the following fields:
      | field | value |
      | Text displayed after the student clicks 'Finish' at the end. | This is a postscript |

  Scenario: Check tabs that should not be present
    Given I login as "teacher"
    When I am on "Properties" page for "Peer review paper"
    Then I should not see "Safe Exam Browser" "tab"
    And I should not see "Exam Rubric" "tab"
    And I should not see "Reviewer" "tab"
    And I should not see "Feedback" "tab"
    And I should not see "Reference Material" "tab"
