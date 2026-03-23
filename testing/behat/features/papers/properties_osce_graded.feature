@paper @properties @javascript
Feature: Paper properties: OSCE (Grades finalised)
  In order to maintain the integrity of an OSCE
  As a teacher
  I should not be able to change grade related settings after the grades have been finalised

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
      | type | papertitle | paperowner | modulename | duration | timezone | startdate | enddate |
      | osce | OSCE station| teacher | Test module | 90 | Europe/London | monday last week 9:15am | monday last week 11:00am |
    # This causes the paper to be considered to have it's grades finalised.
    And the following "gradebooks" exist:
      | paper | user |
      | OSCE station | teacher |
    And the following "questions" exist:
      | type | user | leadin | scenario | paper | screen | position | scale type | scale |
      | likert | teacher | likert 1 | likert 1 | OSCE station | 1 | 1 | OSCE Station Scales | 0, 1 |
      | likert |teacher | likert 2 | likert 2 | OSCE station | 1 | 2 | OSCE Station Scales | 0, 1 |

  Scenario: I can change the paper general settings
    Given I login as "teacher"
    And I am on "Properties" page for "OSCE station"
    When I set the fields:
      | field | value |
      | Name  | My paper |
      | Folder | |
      | Examiner Marking Guidance | Guidance for the examiner |
    And I cannot change fields:
      | field |
      | External System |
      | External System ID |
      | Pass Mark |
      | Overall Classification |
    And I press "OK"
    Then I click "Edit Properties" "menu_item"
    And I should see the following fields:
      | field | value |
      | Name | My paper |
      | Folder | |
      | Pass Mark | 40 |
      | Overall Classification | N/A |
      | Examiner Marking Guidance | Guidance for the examiner |

  Scenario: I can change the paper security settings
    Given I login as "teacher"
    And I am on "Properties" page for "OSCE station"
    And I click "Security" "tab"
    When I set the fields:
      | field | value |
      | Modules > TEST1001: Test module | off |
      | Modules > TEST1002: Another test module | on |
    And I cannot change fields:
      | field |
      | Duration > Hours |
      | Duration > Minutes |
      | Time Zone |
      | Available from > Date |
      | Available from > Time |
      | Available until > Date |
      | Available until > Time |
    And I press "OK"
    Then I am on "Properties" page for "OSCE station"
    And I click "Security" "tab"
    And I should see the following fields:
      | field | value |
      | Duration > Hours | 1 |
      | Duration > Minutes | 30 |
      | Time Zone | (UTC) Dublin, Edinburgh, Lisbon, London |
      | Available from > Date | monday last week |
      | Available from > Time | 09:15 |
      | Available until > Date | monday last week |
      | Available until > Time | 11:00 |
      | Modules > TEST1001: Test module | off |
      | Modules > TEST1002: Another test module | on |

  Scenario: I can change the paper feedback settings
    Given I login as "teacher"
    And I am on "Properties" page for "OSCE station"
    And I click "Feedback" "tab"
    When I set the fields:
      | field | value |
      | Objectives-based Feedback (Students) > Feedback Report | On |
      | Question-based Feedback (Students) > Feedback Report | On |
      | Cohort Performance Report (Students) > Feedback Report | On |
    And I press "OK"
    Then I am on "Properties" page for "OSCE station"
    And I click "Feedback" "tab"
    And I should see the following fields:
      | field | value |
      | Objectives-based Feedback (Students) > Feedback Report | On |
      | Question-based Feedback (Students) > Feedback Report | On |
      | Cohort Performance Report (Students) > Feedback Report | On |

  Scenario: I can change the paper reviewer settings
    Given I login as "teacher"
    And I am on "Properties" page for "OSCE station"
    And I click "Reviewers" "tab"
    When I set the fields:
      | field | value |
      | Internal Review > Deadline | 2026-02-19 |
      | Internal Reviewers > Pasteur, Louis. Prof | on |
      | External Review > Deadline | 2026-03-19 |
      | External Examiners > Crumpler, Rebecca Lee. Dr | on |
    And I press "OK"
    Then I am on "Properties" page for "OSCE station"
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

  Scenario: Check tabs that should not be present
    Given I login as "teacher"
    When I am on "Properties" page for "OSCE station"
    Then I should not see "Safe Exam Browser" "tab"
    And I should not see "Exam Rubric" "tab"
    And I should not see "Prologue" "tab"
    And I should not see "Postscript" "tab"
    And I should not see "Reference Material" "tab"
