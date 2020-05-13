@api @project
Feature: Project content creation
  In order to have projects on the site
  As an editor
  I need to be able to create and see project items

  @javascript @av_portal
  Scenario: Creation of a Project content through the UI.
    Given I am logged in as a user with the "create oe_project content, access content, edit own oe_project content, view published skos concept entities, manage corporate content entities" permission

    When I visit "the Project creation page"
    And I fill in "Title" with "My Project"
    And I fill in "Subject" with "EU financing"
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"
    And I fill in "Author" with "European Patent Office"
    And I fill in "Departments" with "Audit Board of the European Communities"
    And I fill in "Teaser" with "Project teaser"
    And I fill in "Summary" with "Summary text"
    And I fill in "Body text" with "Body text"

    # Project period.
    And I set "23-02-2019 02:15" as the "Start date" of "Project period"
    And I set "23-02-2019 14:15" as the "End date" of "Project period"

    # Add link fields.
    And I fill in "URL" with "http://ec.europa.eu" in the "Project Website" region
    And I fill in "Link text" with "Website" in the "Project Website" region
    And I fill in "URL" with "http://example.com" in the "Call for proposals" region
    And I fill in "Link text" with "Example proposal" in the "Call for proposals" region

    When I press "Save"

    Then I should see "My Project"
    And I should not see "EU financing"
    And I should not see "Committee on Agriculture and Rural Development"
    And I should see "European Patent Office"
    And I should see "Audit Board of the European Communities"
    And I should not see "Project teaser"
    And I should see "Summary text"
    And I should see "Body text"
    And I should see "2019-02-23"
    And I should see "Website"
    And I should see "Example proposal"
