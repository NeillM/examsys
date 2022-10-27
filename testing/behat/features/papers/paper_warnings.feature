@paper @questions
Feature: Paper warnings
  In order to avoid issues during exams
  As a teacher
  I should be notified of potential problems

  Scenario: Warning when there are too many image questions on a page
    Given the following "modules" exist:
      | moduleid | fullname |
      | TEST1001 | Test module |
    And the following "users" exist:
      | username | roles |
      | teacher | Staff |
    And the following "module team members" exist:
      | moduleid | username |
      | TEST1001 | teacher |
    And the following "papers" exist:
      | type | papertitle | paperowner | modulename |
      | formative | a formative paper | teacher | Test module |
    And the following "questions" exist:
      | user | type | leadin | scenario | correct | marks_correct | marks_incorrect | media | paper | screen | position | options |
      | teacher | hotspot | hs 1 leadin | hs 1 scenario | 1 | 1 | 0 | questions/html5/plants.jpg | a formative paper | 1 | 1 | [{"option_text": "A"}, {"option_text": "B"}] |
      | teacher | hotspot | hs 2 leadin | hs 2 scenario | 2 | 1 | 0 | questions/html5/plants.jpg | a formative paper | 1 | 2 | [{"option_text": "A"}, {"option_text": "B"}] |
    And the following "questions" exist:
      | user | type | leadin | scenario | correct | marks_correct | marks_incorrect | paper | screen | position | options |
      | teacher | mcq | mcq 1 leadin | mcq 1 scenario | 1 | 1 | 0 | a formative paper | 1 | 3 | [{"media": "questions/media/circle.jpg"}] |
    When I login as "teacher"
    And I am on "Paper Details" page for "a formative paper"
    Then I should see "This screen contains many questions with images. This may harm performance during an exam. Consider breaking it up into multiple screens" "content"

  Scenario: No warning when there are not too many image questions on a page
    Given the following "modules" exist:
      | moduleid | fullname |
      | TEST1001 | Test module |
    And the following "users" exist:
      | username | roles |
      | teacher | Staff |
    And the following "module team members" exist:
      | moduleid | username |
      | TEST1001 | teacher |
    And the following "papers" exist:
      | type | papertitle | paperowner | modulename |
      | formative | a formative paper | teacher | Test module |
    And the following "questions" exist:
      | user | type | leadin | scenario | correct | marks_correct | marks_incorrect | media | paper | screen | position | options |
      | teacher | hotspot | hs 1 leadin | hs 1 scenario | 1 | 1 | 0 | questions/html5/plants.jpg | a formative paper | 1 | 1 | [{"option_text": "A"}, {"option_text": "B"}] |
      | teacher | hotspot | hs 3 leadin | hs 3 scenario | 1 | 1 | 0 | questions/html5/plants.jpg | a formative paper | 2 | 4 | [{"option_text": "A"}, {"option_text": "B"}] |
    And the following "questions" exist:
      | user | type | leadin | scenario | correct | marks_correct | marks_incorrect | paper | screen | position | options |
      | teacher | mcq | mcq 1 leadin | mcq 1 scenario | 1 | 1 | 0 | a formative paper | 1 | 3 | [{"media": "questions/media/circle.jpg"}] |
    And the following "questions" exist:
      | user | type | leadin | scenario | correct | marks_correct | marks_incorrect | paper | screen | position |
      | teacher | true_false | tf 1 leadin | tf 1 scenario | false | 1 | 0 | a formative paper | 1 | 2 |
    When I login as "teacher"
    And I am on "Paper Details" page for "a formative paper"
    Then I should not see "This screen contains many questions with images. This may harm performance during an exam. Consider breaking it up into multiple screens" "content"
