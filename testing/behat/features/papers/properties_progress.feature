@paper @properties @javascript
Feature: Paper properties: Progress test
  In order to run progress tests
  As a teacher
  I need to be able to change the settings of a progress test

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
      | progress | A progress test| teacher | Test module |
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
      | type | user | leadin | scenario | paper | screen | position | display_method | correct | marks_correct | marks_incorrect |
      | true_false | teacher | tf leadin | tf scenario | A progress test | 1 | 1 | horizontal | true | 1 | 0 |
    And the following "questions" exist:
      | type | user | leadin | scenario | paper | screen | position | marks_correct | marks_incorrect | num_options | correct_options |
      | mrq | teacher | mrq leadin | mrq scenario | A progress test | 2 | 1 | 1 | 0 | 3 | 2,3 |

  Scenario: I can change the paper general settings
    Given I login as "teacher"
    And I am on "Properties" page for "A progress test"
    When I set the fields:
      | field | value |
      | Name  | My paper |
      | Folder | My folder;Sub folder |
      | Client Lockdown | 3rd Party tool |
      | Navigation | Bidirectional |
      # We appear to be unable to test colour selection as the settings are not changed by behat for some reason.
      | display calculator | off |
      | demo sound clip | on |
      | Pass Mark | 35 |
      | Distinction | 75 |
      | Method | Calculate Random Mark |
    And I cannot change fields:
      | field |
      | External System |
      | External System ID |
    And I press "OK"
    Then I click "Edit Properties" "menu_item"
    And I should see the following fields:
      | field | value |
      | Name | My paper |
      | Folder | My folder;Sub folder |
      | Client Lockdown | 3rd Party tool |
      | Navigation | Bidirectional |
      | display calculator | off |
      | demo sound clip | on |
      | Pass Mark | 35 |
      | Distinction | 75 |
      | Method | Calculate Random Mark |

  Scenario: I can change the paper security settings
    Given I login as "teacher"
    And I am on "Properties" page for "A progress test"
    And I click "Security" "tab"
    When I set the fields:
      | field | value |
      | Password | |
      | Duration > Hours | 0 |
      | Duration > Minutes | 30 |
      | Time Zone | (UTC+01:00) Amsterdam, Berlin, Stockholm |
      | Available from > Date | monday next week |
      | Available from > Time | 09:00 |
      | Available until > Date | friday 2 weeks |
      | Available until > Time | 17:00 |
      | Modules > TEST1001: Test module | off |
      | Modules > TEST1002: Another test module | on |
      | Labs > Main PC room | on |
    And I press "OK"
    Then I am on "Properties" page for "A progress test"
    And I click "Security" "tab"
    And I should see the following fields:
      | field | value |
      | Password | |
      | Duration > Hours | 0 |
      | Duration > Minutes | 30 |
      | Time Zone | (UTC+01:00) Amsterdam, Berlin, Stockholm |
      | Available from > Date | monday next week |
      | Available from > Time | 09:00 |
      | Available until > Date | friday 2 weeks |
      | Available until > Time | 17:00 |
      | Modules > TEST1001: Test module | off |
      | Modules > TEST1002: Another test module | on |
      | Labs > Main PC room | on |
      | Labs > Small Lab | off |
      | Labs > Large Lab | off |

  Scenario: I can change the paper Safe Exam Browser (SEB) settings when SEB is enabled
    Given the following "config" exist:
      | setting | value |
      | paper_seb_enabled | 1 |
    And I login as "teacher"
    And I am on "Properties" page for "A progress test"
    And I click "Safe Exam Browser" "tab"
    When I set the fields:
      | field | value |
      | Enable | on |
      | Safe Exam Browser Keys | 81aad4ab9dfd447cc479e6a4a7c9a544e2cafc7f3adeb68b2a21efad68eca4dc |
    And I press "OK"
    Then I am on "Properties" page for "A progress test"
    And I click "Safe Exam Browser" "tab"
    And I should see the following fields:
      | field | value |
      | Enable | on |
      | Safe Exam Browser Keys | 81aad4ab9dfd447cc479e6a4a7c9a544e2cafc7f3adeb68b2a21efad68eca4dc |

  Scenario: I cannot see paper Safe Exam Browser (SEB) settings when SEB is disabled
    Given the following "config" exist:
      | setting | value |
      | paper_seb_enabled | 0 |
    And I login as "teacher"
    When I am on "Properties" page for "A progress test"
    Then I should not see "Safe Exam Browser" "tab"

  Scenario: I can change the paper feedback settings
    Given I login as "teacher"
    And I am on "Properties" page for "A progress test"
    And I click "Feedback" "tab"
    When I set the fields:
      | field | value |
      | Objectives-based Feedback (Students) > Feedback Report | On |
      | Question-based Feedback (Students) > Feedback Report | On |
      | Class Totals (External Examiners) > Feedback Report | On |
      # We are going to test that these are reordered correctly on reload.
      | Textual Feedback > Boundary 1 > Above | 90 |
      | Textual Feedback > Boundary 1 > Message | Great work |
      | Textual Feedback > Boundary 2 > Above | 20 |
      | Textual Feedback > Boundary 2 > Message | It looks like you need to make significant improvements |
      # This should be an entry in the last boundary box to test that it is also saved, and that gaps are handled properly.
      | Textual Feedback > Boundary 10 > Above | 40 |
      | Textual Feedback > Boundary 10 > Message | At this level you are just on track to pass |
    And I press "OK"
    Then I am on "Properties" page for "A progress test"
    And I click "Feedback" "tab"
    And I should see the following fields:
      | field | value |
      | Objectives-based Feedback (Students) > Feedback Report | On |
      | Question-based Feedback (Students) > Feedback Report | On |
      | Class Totals (External Examiners) > Feedback Report | On |
      # We are going to test that these are reordered correctly on reload.
      | Textual Feedback > Boundary 1 > Above | 20 |
      | Textual Feedback > Boundary 1 > Message | It looks like you need to make significant improvements |
      | Textual Feedback > Boundary 2 > Above | 40 |
      | Textual Feedback > Boundary 2 > Message | At this level you are just on track to pass |
      | Textual Feedback > Boundary 3 > Above | 90 |
      | Textual Feedback > Boundary 3 > Message | Great work |

  Scenario: I can change the paper reviewer settings
    Given I login as "teacher"
    And I am on "Properties" page for "A progress test"
    And I click "Reviewers" "tab"
    When I set the fields:
      | field | value |
      | Internal Review > Deadline | 2026-02-19 |
      | Internal Reviewers > Pasteur, Louis. Prof | on |
      | External Review > Deadline | 2026-03-19 |
      | External Examiners > Crumpler, Rebecca Lee. Dr | on |
    And I press "OK"
    Then I am on "Properties" page for "A progress test"
    And I click "Reviewers" "tab"
    And I should see the following fields:
      | field | value |
      | Internal Review > Deadline | 2026-02-19 |
      | Internal Reviewers > Pasteur, Louis. Prof | on |
      | Internal Reviewers > Nightingale, Florence. Dr | off |
      | Internal Reviewers > Jenner, Edward. Dr | off |
      | Internal Reviewers > Curie, Marie. Prof | off |
      | External Review > Deadline | 2026-03-19 |
      | External Examiners > Crumpler, Rebecca Lee. Dr | on |
      | External Examiners > Vesalius, Andreas. Dr | off |

  Scenario: I can change the paper rubric settings
    Given I login as "teacher"
    And I am on "Properties" page for "A progress test"
    And I click "Exam Rubric" "tab"
    When I set the fields:
      | field | value |
      | Exam rubric displayed to students before they start a summative exam. | This is a rubric |
    And I press "OK"
    Then I am on "Properties" page for "A progress test"
    And I click "Exam Rubric" "tab"
    And I should see the following fields:
      | field | value |
      | Exam rubric displayed to students before they start a summative exam. | This is a rubric |

  Scenario: I can change the paper prologue settings
    Given I login as "teacher"
    And I am on "Properties" page for "A progress test"
    And I click "Prologue" "tab"
    When I set the fields:
      | field | value |
      | Text displayed at the top of screen 1 when paper is started. | This is a prologue |
    And I press "OK"
    Then I am on "Properties" page for "A progress test"
    And I click "Prologue" "tab"
    And I should see the following fields:
      | field | value |
      | Text displayed at the top of screen 1 when paper is started. | This is a prologue |

  Scenario: I can change the paper postscript settings
    Given I login as "teacher"
    And I am on "Properties" page for "A progress test"
    And I click "Postscript" "tab"
    When I set the fields:
      | field | value |
      | Text displayed after the student clicks 'Finish' at the end. | This is a postscript |
    And I press "OK"
    Then I am on "Properties" page for "A progress test"
    And I click "Postscript" "tab"
    And I should see the following fields:
      | field | value |
      | Text displayed after the student clicks 'Finish' at the end. | This is a postscript |

  Scenario: Reference material tab works with no reference material
    Given I login as "teacher"
    And I am on "Properties" page for "A progress test"
    When I click "Reference Material" "tab"
    Then I should see "There are no reference materials available for the module(s) assigned to this paper." "text"
