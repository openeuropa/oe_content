@api
Feature: Department feature
  In order to have departments on the site
  As an editor
  I need to be able to create and see departments

  Scenario: Create department
    Given I am logged in with a user that can create and view "Department" RDF entities
    And I visit "the Add Department page"
    And I fill in "Name" with "Directorate-General for Informatics"
    And I fill in "Description" with "My description"
    And I fill in "Department type" with "Directorate-General"
    And I fill in "News from this department" with "https://ec.europa.eu/info/departments/informatics_en"
    And I fill in "Subject" with "information technology industry"
    And I press "Save"
    Then I should see "Directorate-General for Informatics"
    And I should see "My description"
    And I should see the link "Directorate-General"
    And I should see the link "https://ec.europa.eu/info/departments/informatics_en"
    And I should see the link "information technology industry"
    Then I delete the RDF entity with the name "Directorate-General for Informatics"

  @rdf-test
  Scenario: Department reference fields should link to Department pages
    Given I am logged in with a user that can create and view "Announcement" RDF entities
    And I create an "Department" RDF entity with the name "Directorate-General for Informatics"
    And I visit an announcement page that links to the department "Directorate-General for Informatics" taxonomy term
    And I click "Directorate-General for Informatics"
    Then I should be on the "Directorate-General for Informatics" RDF entity page