@api
Feature: Project content creation
  In order to have projects on the site
  As an editor
  I need to be able to create and see project items

  @javascript
  Scenario: Creation of a Project content through the UI.
    Given I am logged in as a user with the "create oe_project content, access content, edit own oe_project content, view published skos concept entities, manage corporate content entities" permission
    # Create Stakeholders.
    And the following stakeholders:
      | name                      |
      | Coordinators stakeholder  |
      | Participants stakeholder  |
    # Create a "Document".
    And the following documents:
      | name          | file       |
      | My Document 1 | sample.pdf |

    When I visit "the Project creation page"
    # Mandatory fields.
    And I fill in "Title" with "My Project"
    And I fill in "Subject" with "EU financing"
    And I fill in "Author" with "European Patent Office"
    And I fill in "Body text" with "Body text"
    And I fill in "Coordinators" with "Coordinators stakeholder"
    And I fill in "Participants" with "Participants stakeholder"
    And I fill in "Teaser" with "Project teaser text" in the "Alternative titles and teaser" region
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"

    # Optional fields.
    And I fill in "Summary" with "Summary text"
    And I fill in "Reference" with "Reference text"
    And I set "23-02-2019" as the "Start date" of "Project period"
    And I set "23-02-2019" as the "End date" of "Project period"
    And I fill in "Overall budget" with "1000" in the "Budget" region
    And I fill in "EU contribution" with "200" in the "Budget" region
    And I fill in "Funding programme" with "Anti Fraud Information System (AFIS) (http://publications.europa.eu/resource/authority/eu-programme/AFIS2020)"
    And I fill in "URL" with "http://ec.europa.eu" in the "Project Website" region
    And I fill in "Link text" with "Website" in the "Project Website" region
    And I fill in "URL" with "http://example.com" in the "Call for proposals" region
    And I fill in "Link text" with "Example proposal" in the "Call for proposals" region
    And I fill in "Results" with "Result 1 text" in the "Result" region
    And I fill in "Result files" with "My Document 1" in the "Result" region
    And I fill in "Alternative title" with "My alternative title text" in the "Alternative titles and teaser" region
    And I fill in "Navigation title" with "My navigation title text" in the "Alternative titles and teaser" region
    And I fill in "Departments" with "Audit Board of the European Communities"

    # Add link fields.
    When I press "Save"
    Then I should see "My Project"
    And I should not see "EU financing"
    And I should not see "Committee on Agriculture and Rural Development"
    And I should see "European Patent Office"
    And I should see "Coordinators stakeholder"
    And I should see "Participants stakeholder"
    And I should see "Audit Board of the European Communities"
    And I should see "Anti Fraud Information System (AFIS)"
    And I should not see "Project teaser"
    And I should see "Summary text"
    And I should see "Body text"
    And I should see "2019-02-23"
    And I should see "Website"
    And I should see "Example proposal"
