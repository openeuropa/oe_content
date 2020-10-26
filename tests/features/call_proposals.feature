@api @aabbcc
Feature: Call for proposals content creation and editing.
  In order to have Calls for proposals on the site
  As an editor
  I need to be able to create, edit and see the call for proposals items

  @javascript
  Scenario: Creation of a Call for proposals content through the UI.
    Given I am logged in as a user with the "create oe_call_proposals content, access content, edit own oe_call_proposals content, view published skos concept entities, manage corporate content entities" permission

    And the following document:
      | name          | file       |
      | My Document 1 | sample.pdf |

    And the following images:
      | name          | file           | alt                            |
      | Contact image | example_1.jpeg | Contact image alternative text |

    When I visit "the Call for proposals creation page"
    Then I should have the following options for the "Deadline model" select:
      | - Select a value - |
      | Single-stage       |
      | Two-stage          |
      | Multiple cut-off   |
      | Permanent          |
    And I fill in "Title" with "My Call for proposals 1"
    And I set "Publication date" to the date "24-10-2020"
    And I select "Permanent" from "Deadline model"
    And I set "Deadline date" to the date "31-12-2020 23:45" using format "d-m-Y H:i"
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development (http://publications.europa.eu/resource/authority/corporate-body/EP_AGRI)"

    And I press "Save"

    Then I should see "Call for proposals My Call for proposals 1 has been created."
    And I should see "My call for proposals 1"
    And I should see "10/24/2020"
    And I should see "Permanent"
    And I should see "12/31/2020 - 23:45"

    When I click "Edit"
    And I fill in "Body text" with "My Call for proposals 1 body"
    And I fill in "Reference" with "My Call for proposals 1 reference"
    And I fill in "URL" with "http://example.com/1" in the "Publication in the official journal" region
    And I fill in "Link text" with "Official Journal publication 1" in the "Publication in the official journal" region
    And I set "Opening date" to the date "25-10-2020"
    And I fill in "URL" with "http://example.com/2" in the "Grants awarded link" region
    And I fill in "Link text" with "Grants awarded link 1" in the "Grants awarded link" region
    And I fill in "Funding programme" with "Anti Fraud Information System (AFIS) (http://publications.europa.eu/resource/authority/eu-programme/AFIS2020)"
    And I fill in "Responsible department" with "Audit Board of the European Communities (http://publications.europa.eu/resource/authority/corporate-body/ABEC)"
    And I fill in "Use existing media" with "My Document 1" in the "Documents" region
    # And I wait 10 seconds

    # Call for proposals contact field group.
    When I press "Add new contact" in the "Call for proposals contact" region
    And I wait for AJAX to finish
    And I fill in "Name" with "Name of the call for proposals contact" in the "Call for proposals contact" region
    And I fill in "Organisation" with "Call for proposals contact organisation" in the "Call for proposals contact" region
    And I fill in "Body text" with "Call for proposals contact body text" in the "Call for proposals contact" region
    And I fill in "Website" with "http://www.example.com/call_for_proposals_contact" in the "Call for proposals contact" region
    And I fill in "Email" with "test@example.com" in the "Call for proposals contact" region
    And I fill in "Phone number" with "0488779033" in the "Call for proposals contact" region
    And I fill in "Mobile number" with "0488779034" in the "Call for proposals contact" region
    And I fill in "Fax number" with "0488779035" in the "Call for proposals contact" region
    And I select "Hungary" from "Country" in the "Call for proposals contact" region
    And I wait for AJAX to finish
    And I fill in "Street address" with "Back street 3" in the "Call for proposals contact" region
    And I fill in "Postal code" with "9000" in the "Call for proposals contact" region
    And I fill in "City" with "Budapest" in the "Call for proposals contact" region
    And I fill in "Office" with "Call for proposals contact office" in the "Call for proposals contact" region
    And I fill in "URL" with "mailto:example@email.com" in the "Contact social media links" region
    And I fill in "Link text" with "Call for proposals contact social link email" in the "Contact social media links" region
    And I fill in "Media item" with "Contact image" in the "Call for proposals contact" region
    And I fill in "Caption" with "Call for proposals contact caption" in the "Call for proposals contact" region
    And I fill in "Press contacts" with "http://example.com/press_contacts" in the "Call for proposals contact" region

    And I fill in "Alternative title" with "Alternative title 1"
    And I fill in "Navigation title" with "Navi title 1"
    And I fill in "Redirect link" with "http://example.com"

    And I press "Save"
    And I should see "My Call for proposals 1 body"
    And I should see "My Call for proposals 1 reference"
    And I should see the link "Official Journal publication 1"
    And I should see "10/25/2020"
    And I should see the link "Grants awarded link 1"
    And I should see "Anti Fraud Information System (AFIS)"
    And I should see "Audit Board of the European Communities"
    And I should see "My Document 1"

    And I should see the text "Name of the call for proposals contact"
    And I should see the text "Call for proposals contact body text"
    And I should see the text "Call for proposals contact organisation"
    And I should see the link "http://www.example.com/call_for_proposals_contact"
    And I should see the text "test@example.com"
    And I should see the text "0488779033"
    And I should see the text "0488779034"
    And I should see the text "0488779035"
    And I should see the text "Back street 3"
    And I should see the text "Budapest"
    And I should see the text "9000"
    And I should see the text "Hungary"
    And I should see the link "Call for proposals contact social link email"
    And I should see the text "Call for proposals contact office"
    And I should see the link "Contact image"
    And I should see the text "Call for proposals contact caption"
    And I should see the link "http://example.com/press_contacts"

    And I should not see "Alternative title 1"
    And I should not see "Navi title 1"
