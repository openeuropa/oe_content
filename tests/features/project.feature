@api
Feature: Project content creation
  In order to have projects on the site
  As an editor
  I need to be able to create and see project items

  @javascript
  Scenario: Creation of a Project content through the UI.
    Given I am logged in as a user with the "create oe_project content, access content, edit own oe_project content, view published skos concept entities, manage corporate content entities" permission
    And the following images:
      | name    | file           | alt                |
      | Image 1 | example_1.jpeg | Alternative text 1 |
      | Image 2 | example_1.jpeg | Alternative text 2 |
    # Create stakeholders, to be referenced later on.
    And the following Stakeholder Organisation entity:
      | Name              | Coordinators stakeholder                                                                                                                                      |
      | Acronym           | CS                                                                                                                                                            |
      | Address           | country_code: FR - locality: Cruscades - organization: Ma société 1 - address_line1: 1 rue de la Paix - address_line2: Etage 1 - postal_code: 11111 - code: 1 |
      | Contact page URL  | https://ec.europa.eu/contact1                                                                                                                                 |
      | Logo              | Image 1                                                                                                                                                       |
      | Website           | https://ec.europa.eu/1                                                                                                                                        |
      | Published         | Yes                                                                                                                                                           |
    And the following Stakeholder Organisation entity:
      | Name              | Participants stakeholder                                                                                                                                  |
      | Acronym           | PS                                                                                                                                                        |
      | Address           | country_code: LU - locality: Luxembourg city - organization: Ma société 2 - address_line1: 2 Avenue du Blues - address_line2: Etage 2 - postal_code: 2222 |
      | Contact page URL  | https://ec.europa.eu/contact2                                                                                                                             |
      | Logo              | Image 2                                                                                                                                                   |
      | Website           | https://ec.europa.eu/2                                                                                                                                    |
      | Published         | Yes                                                                                                                                                       |

    # Create a document, to be referenced later on.
    And the following document:
      | name          | file       |
      | My Document 1 | sample.pdf |

    When I visit "the Project creation page"
    # Fill in mandatory fields.
    And I fill in "Title" with "My Project"
    And I fill in "Subject" with "EU financing"
    And I fill in "Author" with "European Patent Office"
    And I fill in "Body text" with "Body text"
    And I fill in "Coordinators" with "Coordinators stakeholder"
    And I fill in "Participants" with "Participants stakeholder"
    And I fill in "Teaser" with "Project teaser text" in the "Alternative titles and teaser" region
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"
    # Fill in optional fields.
    And I fill in "Summary" with "Summary text"
    And I fill in "Reference" with "Reference text"
    And I set "23-02-2019" as the "Start date" of "Project period"
    And I set "24-02-2019" as the "End date" of "Project period"
    And I fill in "Overall budget" with "1000" in the "Budget" region
    And I fill in "EU contribution" with "200" in the "Budget" region
    And I fill in "Funding programme" with "Anti Fraud Information System (AFIS) (http://publications.europa.eu/resource/authority/eu-programme/AFIS2020)"
    And I fill in "URL" with "http://project.website" in the "Project Website" region
    And I fill in "Link text" with "Website" in the "Project Website" region
    And I fill in "URL" with "http://example.com" in the "Call for proposals" region
    And I fill in "Link text" with "Example proposal" in the "Call for proposals" region
    And I fill in "Results" with "Result 1 text" in the "Result" region
    And I fill in "Result files" with "My Document 1" in the "Result" region
    And I fill in "Alternative title" with "My alternative title text" in the "Alternative titles and teaser" region
    And I fill in "Navigation title" with "My navigation title text" in the "Alternative titles and teaser" region
    And I fill in "Departments" with "Audit Board of the European Communities"

    When I press "Save"
    Then I should see "My Project"
    And I should not see "EU financing"
    And I should see "European Patent Office"
    And I should see "Body text"
    # Assert project coordinators field values.
    And I should see "Coordinators stakeholder" in the "Project coordinators" region
    And I should see "CS" in the "Project coordinators" region
    And I should see "France" in the "Project coordinators" region
    And I should see "Ma société 1" in the "Project coordinators" region
    And I should see "1 rue de la Paix" in the "Project coordinators" region
    And I should see "Etage 1" in the "Project coordinators" region
    And I should see "11111" in the "Project coordinators" region
    And I should see "Cruscades" in the "Project coordinators" region
    And I should see "https://ec.europa.eu/contact1" in the "Project coordinators" region
    And I should see "Logo" in the "Project coordinators" region
    And I should see "https://ec.europa.eu/1" in the "Project coordinators" region
    # Assert project participants field values.
    And I should see "Participants stakeholder" in the "Project participants" region
    And I should see "PS" in the "Project participants" region
    And I should see "Luxembourg" in the "Project participants" region
    And I should see "Ma société 2" in the "Project participants" region
    And I should see "2 Avenue du Blues" in the "Project participants" region
    And I should see "Etage 2" in the "Project participants" region
    And I should see "2222" in the "Project participants" region
    And I should see "Luxembourg city" in the "Project participants" region
    And I should see "https://ec.europa.eu/contact2" in the "Project participants" region
    And I should see "Logo" in the "Project participants" region
    And I should see "https://ec.europa.eu/2" in the "Project participants" region
    # Assert project field values.
    And I should see "Summary text"
    And I should see "Reference text"
    And I should see "2019-02-23"
    And I should see "2019-02-24"
    And I should see "1000"
    And I should see "200"
    And I should see "Anti Fraud Information System (AFIS)"
    And I should see "Website"
    And I should see "Example proposal"
    And I should see "Result 1 text"
    And I should see "sample.pdf"
    And I should see "Audit Board of the European Communities"
