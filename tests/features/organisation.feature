@api @organisation
Feature: Organisation content creation
  In order to have organisations on the site
  As an editor
  I need to be able to create and see organisation items

  @javascript @disable-browser-required-field-validation @av_portal
  @batch1
  Scenario: Creation of a Organisation content through the UI.
    Given I am logged in as a user with the "create oe_organisation content, access content, edit own oe_organisation content, view published skos concept entities, manage corporate content entities" permission
    And the following images:
      | name          | file            | alt                            |
      | Image 1       | placeholder.png | Alternative text 1             |
      | Contact image | example_1.jpeg  | Contact image alternative text |

    And the following AV Portal photo:
      | url                                                         |
      | https://audiovisual.ec.europa.eu/en/photo/P-038924~2F00-15  |
    And the following Person Content entity:
      | First name | Jane   |
      | Last name  | Doe    |
      | Gender     | female |
    And the following document:
      | name          | file       |
      | My Document 1 | sample.pdf |

    When I visit "the Organisation creation page"
    And I fill in "Page title" with "My Organisation"
    And I fill in "Introduction" with "Organisation introduction"
    And I fill in "Body text" with "Body text"
    And I fill in "Use existing media" with "Image 1"
    And I fill in "Acronym" with "Organisation Acronym"
    And I fill in "Teaser" with "Organisation teaser text"
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"
    And I fill in "Subject tags" with "financing"
    And I should not see "Non-EU organisation type"
    And I select "Non-EU organisation" from "Organisation type"
    Then I should see "Non-EU organisation type"
    When I select "EU organisation" from "Organisation type"
    And I press "Save"
    Then I should see the error message "Please select an EU organisation."
    When I select "Non-EU organisation" from "Organisation type"
    And I press "Save"
    Then I should see the error message "Please select a non-EU organisation type."
    When I select "EU organisation" from "Organisation type"
    And I fill in "EU organisation" with "Audit Board of the European Communities"

    # Organisation contact field group.
    And I press "Add new contact"
    And I wait for AJAX to finish
    And I fill in "Name" with "Name of the organisation contact 1" in the "Organisation contact" region
    And I fill in "Organisation" with "Contact organisation" in the "Organisation contact" region
    And I fill in "Body text" with "Contact body text" in the "Organisation contact" region
    And I fill in "Website" with "http://www.example.com/website" in the "Organisation contact" region
    And I select "Hungary" from "Country" in the "Organisation contact" region
    And I wait for AJAX to finish
    And I fill in "Street address" with "Back street 3" in the "Organisation contact" region
    And I fill in "Postal code" with "9000" in the "Organisation contact" region
    And I fill in "City" with "Budapest" in the "Organisation contact" region
    And I fill in "Office" with "Contact office" in the "Organisation contact" region
    And I fill in "Email" with "test@example.com" in the "Organisation contact" region
    And I fill in "Phone number" with "0488779033" in the "Organisation contact" region
    And I fill in "Mobile number" with "0488779034" in the "Organisation contact" region
    And I fill in "Fax number" with "0488779035" in the "Organisation contact" region
    And I fill in "URL" with "mailto:example@email.com" in the "Contact social media links" region
    And I fill in "Link text" with "Email" in the "Contact social media links" region
    And I select "Email" from "Link type" in the "Contact social media links" region
    And I fill in "Media item" with "Contact image" in the "Organisation contact" region
    And I fill in "Caption" with "Contact caption" in the "Organisation contact" region
    And I fill in "Press contacts" with "http://example.com/press_contacts" in the "Organisation contact" region
    And I fill in "URL" with "https://www.example.com/link" in the "Contact link" region
    And I fill in "Link text" with "Contact link" in the "Contact link" region
    And I press "Create contact"
    And I wait for AJAX to finish
    # Add another contact.
    And I press "Add new contact"
    And I wait for AJAX to finish
    And I fill in "Name" with "Name of the organisation contact 2" in the "Organisation contact" region
    And I fill in "Term" with "Overview Term text"
    And I fill in "Description" with "Overview Description text"
    And I fill in "Use existing media" with "My Document 1" in the "Organisation chart" region
    And I fill in "URL" with "http://example.com"
    And I fill in "Link text" with "Staff search"
    And I select "Email" from "Link type"
    And I fill in "Persons" with "Jane Doe"
    And I press "Save"
    Then I should see "Organisation My organisation has been created."
    And I should see "My Organisation"
    And I should see "Organisation introduction"
    And I should see "Body text"
    And I should see "Image 1"
    And I should see "Organisation Acronym"
    And I should see "Organisation teaser text"
    And I should see "financing"
    # Organisation contacts values.
    And I should see the text "Name of the organisation contact 1"
    And I should see the text "Back street 3"
    And I should see the text "Budapest"
    And I should see the text "9000"
    And I should see the text "Hungary"
    And I should see the text "Contact office"
    And I should see the text "test@example.com"
    And I should see the text "0488779033"
    And I should see the text "0488779034"
    And I should see the text "0488779035"
    And I should see the link "Email"
    And I should see the link "Contact image"
    And I should see the text "Contact caption"
    And I should see the link "http://example.com/press_contacts"
    And I should see the link "Contact link"
    And I should see the text "Name of the organisation contact 2"

    # Assert organisation type for EU organisations.
    And I should see "Organisation type EU organisation"
    And I should see "EU organisation Audit Board of the European Communities"
    And I should see "EU organisation type European Union corporate body"

    # Assert overview field values.
    And I should see the text "Overview Term text"
    And I should see the text "Overview Description text"

    # Assert organisation chart value.
    And I should see "sample.pdf"

    # Assert referenced person.
    And I should see the text "Jane Doe"

    # Assert the staff search link value.
    And I should see the link "Staff search"

    # Assert organisation type for non-EU organisations.
    When I click "Edit"
    And I select "Non-EU organisation" from "Organisation type"
    And I select "non-governmental organisation" from "Non-EU organisation type"

    And I press "Save"
    Then I should see "Organisation type non-EU organisation"
    And I should see "Non-EU organisation type non-governmental organisation"

    # Assert logo with AV portal photo.
    When I click "Edit"
    And I fill in "Use existing media" with "Euro with miniature figurines"

    And I press "Save"
    Then I should see "Organisation My organisation has been updated."
    And I should see "Euro with miniature figurines"

  @javascript
  @batch2
  Scenario: By removing contact from the form only the reference is removed and the contact is not deleted.
    Given I am logged in as a user with the "create oe_organisation content, access content, edit any oe_organisation content, view published skos concept entities, manage corporate content entities" permission
    And the following General Contact entity:
      | Name | A general contact |
    And the following Organisation Content entity:
      | Title             | Organisation demo page         |
      | Introduction      | Organisation introduction text |
      | Subject tags      | financing                      |
      | Acronym           | Organisation acronym           |
      | Body text         | Organisation body text         |
      | Organisation type | EU organisation                |
      | EU organisation   | Directorate-General for Budget |
      | Contacts          | A general contact              |
    When I am visiting the "Organisation demo page" content
    And I click "Edit"
    And I press "Remove"
    Then I should see "Are you sure you want to remove A general contact?"
    When I press "Remove"
    And I wait for AJAX to finish
    And I press "Save"
    Then I should see "Organisation Organisation demo page has been updated."
    And the General Contact entity with title "A general contact" exists
