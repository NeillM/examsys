@paper @properties @javascript
Feature: Paper properties: Summative Exam (Grades finalised)
  In order to maintain the integrity of summative exams
  As a teacher
  I should not be able to edit settings that might affect grades after a paper has had had it's grades finalised

  Background:
    Given the "plugin_plain_texteditor" plugin is enabled
    And the following "config" exist:
      | setting | value |
      | cfg_summative_mgmt | 0 |
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
      | type | papertitle | paperowner | modulename | duration | startdate | enddate | timezone |
      | summative | A summative exam | teacher | Test module | 60 | monday last week 9:15am | monday last week 11:00am | Europe/London |
    # This causes the paper to be considered to have it's grades finalised.
    And the following "gradebooks" exist:
      | paper | user |
      | A summative exam | teacher |
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
      | type | user | leadin | scenario | paper | screen | position | display_method | correct | marks_correct | marks_incorrect | locked |
      | true_false | teacher | tf leadin | tf scenario | A summative exam | 1 | 1 | horizontal | true | 1 | 0 | monday last week 9:15am |
    And the following "questions" exist:
      | type | user | leadin | scenario | paper | screen | position | marks_correct | marks_incorrect | num_options | correct_options | locked |
      | mrq | teacher | mrq leadin | mrq scenario | A summative exam | 2 | 1 | 1 | 0 | 3 | 2,3 | monday last week 9:15am |

  Scenario: I can change the paper general settings
    Given I login as "teacher"
    And I am on "Properties" page for "A summative exam"
    When I set the fields:
      | field | value |
      | Folder | |
    And I cannot change fields:
      | field |
      | Name |
      | External System |
      | External System ID |
      | Client Lockdown |
      | Navigation |
      | Background |
      | Foreground |
      | Theme |
      | Labels/Notes |
      | display calculator |
      | demo sound clip |
      | Pass Mark |
      | Distinction |
      | Method > No Adjustment |
      | Method > Calculate Random Mark |
      | Method > Standard Setting |
    And I press "OK"
    Then I click "Edit Properties" "menu_item"
    And I should see the following fields:
      | field | value |
      | Name | A summative exam |
      | Folder | |
      | Client Lockdown | IE Fullscreen mode |
      | Navigation | Bidirectional |
      | display calculator | on |
      | demo sound clip | off |
      | Pass Mark | 40 |
      | Distinction | 70 |
      | Method | Calculate Random Mark |

  Scenario: I cannot change the paper security settings
    Given I login as "teacher"
    And I am on "Properties" page for "A summative exam"
    When I click "Security" "tab"
    Then I cannot change fields:
      | field |
      | Session |
      | Password |
      | Duration > Hours |
      | Duration > Minutes |
      | Time Zone |
      | Available from > Date |
      | Available from > Time |
      | Available until > Date |
      | Available until > Time |
      | Modules > TEST1001: Test module |
      | Modules > TEST1002: Another test module |
      | Labs > Main PC room |
      | Labs > Small Lab |
      | Labs > Large Lab |

  Scenario: I can change the paper Safe Exam Browser (SEB) settings when SEB is enabled
    Given the following "config" exist:
      | setting | value |
      | paper_seb_enabled | 1 |
    And I login as "teacher"
    And I am on "Properties" page for "A summative exam"
    And I click "Safe Exam Browser" "tab"
    When I set the fields:
      | field | value |
      | Enable | on |
      | Safe Exam Browser Keys | 81aad4ab9dfd447cc479e6a4a7c9a544e2cafc7f3adeb68b2a21efad68eca4dc |
    And I press "OK"
    Then I am on "Properties" page for "A summative exam"
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
    When I am on "Properties" page for "A summative exam"
    Then I should not see "Safe Exam Browser" "tab"

  Scenario: I can change the paper feedback settings
    Given I login as "teacher"
    And I am on "Properties" page for "A summative exam"
    And I click "Feedback" "tab"
    When I set the fields:
      | field | value |
      | Objectives-based Feedback (Students) > Feedback Report | On |
      | Question-based Feedback (Students) > Feedback Report | On |
      | Cohort Performance Report (Students) > Feedback Report | On |
      | Class Totals (External Examiners) > Feedback Report | On |
    And I press "OK"
    Then I am on "Properties" page for "A summative exam"
    And I click "Feedback" "tab"
    And I should see the following fields:
      | field | value |
      | Objectives-based Feedback (Students) > Feedback Report | On |
      | Question-based Feedback (Students) > Feedback Report | On |
      | Cohort Performance Report (Students) > Feedback Report | On |
      | Class Totals (External Examiners) > Feedback Report | On |

  Scenario: I can change the paper reviewer settings
    Given I login as "teacher"
    And I am on "Properties" page for "A summative exam"
    And I click "Reviewers" "tab"
    When I set the fields:
      | field | value |
      | Internal Review > Deadline | 2026-02-19 |
      | External Review > Deadline | 2026-03-19 |
    And I cannot change fields:
      | field |
      | Internal Reviewers > Pasteur, Louis. Prof |
      | Internal Reviewers > Nightingale, Florence. Dr |
      | Internal Reviewers > Jenner, Edward. Dr |
      | Internal Reviewers > Curie, Marie. Prof |
      | External Examiners > Crumpler, Rebecca Lee. Dr |
      | External Examiners > Vesalius, Andreas. Dr |
    And I press "OK"
    Then I am on "Properties" page for "A summative exam"
    And I click "Reviewers" "tab"
    And I should see the following fields:
      | field | value |
      | Internal Review > Deadline | 2026-02-19 |
      | Internal Reviewers > Pasteur, Louis. Prof | off |
      | Internal Reviewers > Nightingale, Florence. Dr | off |
      | Internal Reviewers > Jenner, Edward. Dr | off |
      | Internal Reviewers > Curie, Marie. Prof | off |
      | External Review > Deadline | 2026-03-19 |
      | External Examiners > Crumpler, Rebecca Lee. Dr | off |
      | External Examiners > Vesalius, Andreas. Dr | off |

  Scenario: I cannot change the paper rubric settings
    Given I login as "teacher"
    And I am on "Properties" page for "A summative exam"
    When I click "Exam Rubric" "tab"
    Then I cannot change fields:
      | field |
      | Exam rubric displayed to students before they start a summative exam. |

  Scenario: I cannot change the paper prologue settings
    Given I login as "teacher"
    And I am on "Properties" page for "A summative exam"
    When I click "Prologue" "tab"
    Then I cannot change fields:
      | field |
      | Text displayed at the top of screen 1 when paper is started. |

  Scenario: I cannot change the paper postscript settings
    Given I login as "teacher"
    And I am on "Properties" page for "A summative exam"
    When I click "Postscript" "tab"
    Then I cannot change fields:
      | field |
      | Text displayed after the student clicks 'Finish' at the end. |

  Scenario: Reference material tab works with no reference material
    Given I login as "teacher"
    And I am on "Properties" page for "A summative exam"
    When I click "Reference Material" "tab"
    Then I should see "There are no reference materials available for the module(s) assigned to this paper." "text"
