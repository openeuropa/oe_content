@api @run
Feature: Vocabularies feature
  In order to categorise my content
  As an editor
  I need to vocabularies and terms

  Scenario: Vocabularies and terms exist
    Given I am logged in as a user with the "administer taxonomy" permission
    And I visit "/admin/structure/taxonomy"
    Then I should see "Corporate Bodies"
    And I should see "Organisation Types"
    And I should see "Resource Types"
    And I should see "Target Audiences"
    And I should see "Thesaurus"
    And all vocabularies have terms in them

