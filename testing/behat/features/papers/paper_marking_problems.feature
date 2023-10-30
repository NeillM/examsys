@paper @marking @javascript
Feature: Marking papers with problem questions
  In order to get results
  As a teacher
  I should be able to get results even if the questions are setup in a way that is broken

  Scenario: Marking calculation questions containing a divide by zero
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
      | formative | a formative paper| teacher | Test module |
    And the following "questions" exist:
      | type | user | leadin | scenario | paper | screen | position | marks_correct | marks_incorrect | marks_partial | tolerance_full | tolerance_partial | fulltoltyp | variables | formula |
      | enhancedcalc | teacher | enhancedcalc $A / $B (No tolerance) | enhancedcalc 1 scenario | a formative paper | 1 | 2 | 2 | 0 | 0.5 | 0 | 0 | # | {"$A":{"min":"12","max":"12","inc":"1","dec":"0"},"$B":{"min":"0","max":"0","inc":"1","dec":"0"}} | $A/$B |
      | enhancedcalc | teacher | enhancedcalc $A / $B (absolute tolerance) | enhancedcalc 2 scenario | a formative paper | 1 | 2 | 2 | 0 | 0.5 | 1 | 2 | # | {"$A":{"min":"12","max":"12","inc":"1","dec":"0"},"$B":{"min":"0","max":"0","inc":"1","dec":"0"}} | $A/$B |
      | enhancedcalc | teacher | enhancedcalc $A / $B (percentage tolerance) | enhancedcalc 3 scenario | a formative paper | 1 | 3 | 2 | 0 | 0.5 | 1 | 2 | % | {"$A":{"min":"12","max":"12","inc":"1","dec":"0"},"$B":{"min":"0","max":"0","inc":"1","dec":"0"}} | $A/$B |
    And I login as "teacher"
    And I am on "Paper Details" page for "a formative paper"
    And I open the exam preview
    And I answer the questions:
      | position | type | answer |
      | 1 | enhancedcalc | 1 |
      | 2 | enhancedcalc | 1 |
      | 3 | enhancedcalc | 1 |
    And I navigate paper "Finish"
    Then I should see your mark is "0 out of 6"
