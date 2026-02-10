@component @javascript
Feature: Component library
  In order to use components
  As a developer
  I should be to preview components available in ExamSys

  Scenario: Navigate to a component preview
    Given I login as "admin"
    When I follow "Administrative Tools"
    And I click "Component Library" "admin_tool_link"
    And I follow "breadcrumb"
    And I follow "Breadcrumb"
    Then I should see "Component Library (breadcrumb\Breadcrumb)" "text"
