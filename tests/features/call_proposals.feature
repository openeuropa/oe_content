@api
Feature: Call for proposals content creation and editing.
  In order to have Calls for proposals on the site
  As an editor
  I need to be able to create, edit and see the call for proposals items

  @javascript
  @batch3
  Scenario: Creation of a Call for proposals content through the UI.
    Given I am logged in as a user with the "create oe_call_proposals content, access content, edit own oe_call_proposals content, view published skos concept entities, manage corporate content entities" permission
    And the following document:
      | name          | file       |
      | My Document 1 | sample.pdf |
    And the following images:
      | name          | file           | alt                            |
      | Contact image | example_1.jpeg | Contact image alternative text |

    When I visit "the Call for proposals creation page"
    Then I should see "Single-stage" in the "Deadline model"
    And I should see "Two-stage" in the "Deadline model"
    And I should see "Multiple cut-off" in the "Deadline model"
    And I should see "Permanent" in the "Deadline model"

    # Fill in the mandatory fields.
    And I fill in "Page title" with "My Call for proposals 1"
    And I fill in "Publication date" with the date "24-10-2020"
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development (http://publications.europa.eu/resource/authority/corporate-body/EP_AGRI)"
    And I fill in "Teaser" with "Teaser text"
    And I fill in "Subject" with "financing"

    # Fill in an invalid deadline date and switch to Permanent.
    And I select the radio button "Single-stage"
    And I fill in "Deadline date" with the date "31-12-2020"
    # Test also that no Deadline Date field is visible when the Permanent model is selected.
    And I select the radio button "Permanent"
    And I should not see "Deadline date"

    When I press "Save"
    Then I should see "Call for proposals My Call for proposals 1 has been created."

    When I click "Edit"

    And I select the radio button "Two-stage"
    And I fill in "Deadline date" with the date "31-12-2020"
    And I fill in "Deadline date" with the time "23:45:00"

    And I press "Save"

    Then I should see "Call for proposals My Call for proposals 1 has been updated."
    And I should see "My call for proposals 1"
    And I should see "10/24/2020"
    And I should see "Two-stage"
    And I should see "12/31/2020 - 23:45"
    And I should see "Teaser text"
    And I should see the link "financing"

    When I click "Edit"
    And I fill in "Body text" with "My Call for proposals 1 body"
    And I fill in "Introduction" with "My Introduction text"
    And I fill in "Reference" with "My Call for proposals 1 reference"
    And I fill in "URL" with "http://example.com/1" in the "Publication in the official journal" region
    And I fill in "Link text" with "Official Journal publication 1" in the "Publication in the official journal" region
    And I fill in "Opening date" with the date "25-10-2020"
    And I fill in "Awarded grants" with "http://example.com/2"
    And I fill in "Funding programme" with "Anti Fraud Information System (AFIS) (http://publications.europa.eu/resource/authority/eu-programme/AFIS2020)"
    And I fill in "Responsible department" with "Audit Board of the European Communities (http://publications.europa.eu/resource/authority/corporate-body/ABEC)"
    And I fill in "Use existing media" with "My Document 1" in the "Documents" region

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
    And I fill in "URL" with "https://www.example.com/link" in the "Contact link" region
    And I fill in "Link text" with "Contact link" in the "Contact link" region
    And I fill in "Alternative title" with "Alternative title 1"
    And I fill in "Navigation title" with "Navi title 1"
    And I fill in "Redirect link" with "http://example.com"

    When I press "Save"

    Then I should see "My Call for proposals 1 body"
    And I should see "My Call for proposals 1 reference"
    And I should see the link "Official Journal publication 1"
    And I should see "10/25/2020"
    And I should see the link "http://example.com/2"
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
    And I should see the link "Contact link"
    And I should see the text "Alternative title 1"
    And I should not see "Navi title 1"

  @javascript
  @batch3
  Scenario: Test the maximum string length and the valid date requirements of the Call for proposals content type.
    Given I am logged in as a user with the "create oe_call_proposals content, access content, edit own oe_call_proposals content, view published skos concept entities, manage corporate content entities" permission
    When I visit "the Call for proposals creation page"
    Then I should see the text "Content limited to 170 characters, remaining: 170" in the "title form element"
    And I should see the text "Content limited to 250 characters, remaining: 250" in the "summary form element"
    And I should see the text "Content limited to 170 characters, remaining: 170" in the "alternative title form element"
    And I should see the text "Content limited to 128 characters, remaining: 128" in the "Publication in the official journal"
    And I should see the text "Content limited to 150 characters, remaining: 150" in the "Reference code form element"
    And I should see the text "Content limited to 300 characters, remaining: 300" in the "teaser form element"
    And I fill in "Page title" with "My Call for proposals 1"
    And I fill in "Publication date" with the date "24-10-2020"
    And I select the radio button "Two-stage"
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development (http://publications.europa.eu/resource/authority/corporate-body/EP_AGRI)"
    And I fill in "URL" with "http://example.com/1" in the "Publication in the official journal" region
    And I fill in "Link text" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas felis leo, lobortis non eros in, consequat tempora Link text. Text to remove" in the "Publication in the official journal" region
    And I fill in "Reference" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin eu hendrerit lacus, vitae bibendum odio. Fusce orci purus, hedrerit a magna Reference. Text to remove"
    And I fill in "Teaser" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin eu hendrerit lacus, vitae bibendum odio. Fusce orci purus, hendrerit a magna at nullam. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Lorem ipsum dolor sit amet Teaser. Text to remove"
    And I fill in "Introduction" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas felis leo, lobortis non eros in, consequat tempor est. Praesent sit amet sem eleifend, cursus arcu ac, eleifend nunc. Integer et orci sagittis, volutpat felis sit ametas Introduction. Text to remove"
    And I fill in "Alternative title" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin eu hendrerit lacus, vitae bibendum odio. Fusce orci purus, hendrerit a magna at nullamsa Alternative title. Text to remove"
    And I fill in "Subject" with "financing"

    When I press "Save"

    Then I should see "The selected \"Two-stage\" model requires a valid date!"
    And I fill in "Deadline date" with the date "31-12-2020"
    And I fill in "Deadline date" with the time "23:45:00"

    When I press "Save"

    Then I should see "Call for proposals My Call for proposals 1 has been created."
    And I should not see "Text to remove"
    And I should see "consequat tempora Link text"
    And I should see "hedrerit a magna Reference"
    And I should see "ametas Introduction."
    And I should see "nullamsa Alternative title."
    And I should see "amet Teaser."

  @javascript
  @batch3
  Scenario: Test multiple Deadline Date values for the "Two-stage" model.
    Given I am logged in as a user with the "create oe_call_proposals content, access content, edit own oe_call_proposals content, view published skos concept entities, manage corporate content entities" permission

    When I visit "the Call for proposals creation page"
    And I fill in "Page title" with "My Call for proposals 1"
    And I fill in "Publication date" with the date "24-10-2020"
    And I select the radio button "Two-stage"
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development (http://publications.europa.eu/resource/authority/corporate-body/EP_AGRI)"
    And I fill in "Deadline date" with the date "31-12-2020"
    And I fill in "Deadline date" with the time "23:45:00"
    And I press "Add another item" in the "Deadline date" region
    And I wait for AJAX to finish
    And I fill in "Deadline date" with the date "15-01-2021" at position 2
    And I fill in "Deadline date" with the time "12:00:00" at position 2
    And I fill in "URL" with "http://example.com/1" in the "Publication in the official journal" region
    And I fill in "Link text" with "Official Journal publication 1" in the "Publication in the official journal" region
    And I fill in "Teaser" with "Teaser text"
    And I fill in "Subject" with "financing"

    And I press "Save"

    Then I should see "Call for proposals My Call for proposals 1 has been created."
    And I should see "Thu, 12/31/2020 - 23:45"
    And I should see "Fri, 01/15/2021 - 12:00"

  @javascript
  @batch3
  Scenario: By removing contact from the form only the reference is removed and the contact is not deleted.
    Given I am logged in as a user with the "create oe_call_proposals content, access content, edit any oe_call_proposals content, view published skos concept entities, manage corporate content entities" permission
    And the following General Contact entity:
      | Name | A general contact |
    And the following "Call for proposals" Content entity:
      | Title                               | Proposals demo page                               |
      | Introduction                        | Call for proposals introduction text              |
      | Body text                           | Call for proposals body text                      |
      | Opening date                        | 2019-02-22                                        |
      | Deadline model                      | Single-stage                                      |
      | Deadline date                       | 2019-03-21 18:30:00                               |
      | Awarded grants                      | http://example.com                                |
      | Funding programme                   | Connecting Europe Facility (CEF 2021)             |
      | Publication in the official journal | uri: http://example.com - title: Publication link |
      | Contact                             | A general contact                                 |
      | Publication date                    | 2019-02-21                                        |
      | Reference                           | CALL/100/10                                       |
      | Responsible department              | Directorate-General for Budget                    |
      | Subject                             | export financing                                  |
      | Teaser                              | Teaser                                            |
    When I am visiting the "Proposals demo page" content
    And I click "Edit"
    And I select the radio button "Single-stage"
    And I press "Remove"
    Then I should see "Are you sure you want to remove A general contact?"
    When I press "Remove"
    And I wait for AJAX to finish
    And I press "Save"
    Then I should see "Call for proposals Proposals demo page has been updated."
    And the General Contact entity with title "A general contact" exists
