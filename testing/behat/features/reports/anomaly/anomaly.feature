@anomaly @javascript
Feature: View paper anomalies
  In order to investigate issues that happended during an exam
  As a Admin/ Teacher
  I want to view the anomaly report

  Background:
    Given the following "config" exist:
      | setting | value |
      | paper_anomaly_detection | {"progress":1,"summative":0} |
    And the following "users" exist:
      | username | roles | sid |
      | student1 | Student | 987654321 |
      | student2 | Student | 123487453 |
    And the following "modules" exist:
      | moduleid | fullname |
      | m1 | m1 |
    And the following "module enrolment" exist:
      | sid | modulecode |
      | 987654321 | m1 |
    And the following "papers" exist:
      | type | papertitle | paperowner | modulename | startdate |
      | progress | paper1 | admin | m1 | 2020-02-27 12:00:00 |
      | summative | paper2 | admin | m1 | 2020-02-27 12:00:00 |
    And the following "anomaly" exist:
      | user | paper | type | previous | current | screen | time |
      | student1 | paper1 | clock | Tue Aug 19 1975 20:15:30 GMT+0200 (CEST) | Tue Aug 19 1975 20:10:30 GMT+0200 (CEST) | 1 | today |
      | student2 | paper2 | clock | Tue Aug 19 1975 20:15:30 GMT+0200 (CEST) | Tue Aug 19 1975 20:10:30 GMT+0200 (CEST) | 1 | -2 days |

  Scenario: View paper anomaly report
    Given I login as "admin"
    And I run report "Anomaly" for "paper1" with filters:
      | moduleid | m1 |
      | startdate | yesterday |
      | enddate | tomorrow |
    Then I should see "987654321" clock anomaly
    And I should not see "123487453" clock anomaly

  Scenario: Deny access to view paper anomaly report is disabled in config for paper type
    Given I login as "admin"
    And I run report "Anomaly" for "paper2" with filters:
      | moduleid | m1 |
      | startdate | yesterday |
      | enddate | tomorrow |
    Then I should see "Access Denied"
