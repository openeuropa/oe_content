@api @organisation
Feature: Organisation content creation
  In order to have organisations on the site
  As an editor
  I need to be able to create and see organisation items

  @javascript @disable-browser-required-field-validation
  Scenario: Creation of a Organisation content through the UI.
    Given I am logged in as a user with the "create oe_organisation content, access content, edit own oe_organisation content, view published skos concept entities, manage corporate content entities" permission
    And the following images:
      | name    | file            | alt                |
      | Image 1 | placeholder.png | Alternative text 1 |
    And the following AV Portal photo:
      | url                                                         |
      | https://audiovisual.ec.europa.eu/en/photo/P-038924~2F00-15  |

    When I visit "the Organisation creation page"
    And I fill in "Page title" with "My Organisation"
    And I fill in "Introduction" with "Organisation introduction"
    And I fill in "Body text" with "Body text"
    And I fill in "Use existing media" with "Image 1"
    And I fill in "Acronym" with "Organisation Acronym"
    And I fill in "Teaser" with "Organisation teaser text"
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"
    And I should not see "Non-EU organisation type"
    When I select "Non-EU organisation" from "Organisation type"
    Then I should see "Non-EU organisation type"
    When I select "EU organisation" from "Organisation type"
    And I press "Save"
    Then I should see the error message "Please select an EU organisation."
    When I select "Non-EU organisation" from "Organisation type"
    And I press "Save"
    Then I should see the error message "Please select a non-EU organisation type."
    When I select "EU organisation" from "Organisation type"
    Then I fill in "EU organisation" with "Audit Board of the European Communities"

    # Organisation contact field group.
    When I press "Add new contact"
    And I wait for AJAX to finish
    Then I fill in "Name" with "Name of the organisation contact" in the "Organisation contact" region
    And I select "Hungary" from "Country" in the "Organisation contact" region
    And I wait for AJAX to finish
    And I fill in "Street address" with "Back street 3" in the "Organisation contact" region
    And I fill in "Postal code" with "9000" in the "Organisation contact" region
    And I fill in "City" with "Budapest" in the "Organisation contact" region
    And I fill in "Email" with "test@example.com" in the "Organisation contact" region
    And I fill in "Phone number" with "0488779033" in the "Organisation contact" region
    And I fill in "URL" with "mailto:example@email.com" in the "Contact social media links" region
    And I fill in "Link text" with "Email" in the "Contact social media links" region

    When I press "Save"
    Then I should see "My Organisation"
    And I should see "Organisation introduction"
    And I should see "Body text"
    And I should see "Image 1"
    And I should see "Organisation Acronym"
    And I should see "Organisation teaser text"

    # Organisation contact values.
    And I should see the text "Name of the organisation contact"
    And I should see the text "Back street 3"
    And I should see the text "Budapest"
    And I should see the text "9000"
    And I should see the text "Hungary"
    And I should see the text "test@example.com"
    And I should see the text "0488779033"
    And I should see the link "Email"

    # Assert organisation type for EU organisations.
    And I should see "Organisation type EU organisation"
    And I should see "EU organisation Audit Board of the European Communities"
    And I should see "EU organisation type European Union corporate body"

    # Assert organisation type for non-EU organisations.
    When I click "Edit"
    And I select "Non-EU organisation" from "Organisation type"
    And I select "non-governmental organisation" from "Non-EU organisation type"

    When I press "Save"
    Then I should see "Organisation type non-EU organisation"
    And I should see "Non-EU organisation type non-governmental organisation"

    # Assert logo with AV portal photo.
    When I click "Edit"
    And I fill in "Use existing media" with "Euro with miniature figurines"

    When I press "Save"
    Then I should see "Euro with miniature figurines"

  Scenario: By removing contact from the form only the reference is removed and the contact is not deleted.
    Given I am logged in as a user with the "create oe_organisation content, access content, edit any oe_organisation content, view published skos concept entities, manage corporate content entities" permission
    And the following General Contact entity:
      | Name | A general contact |
    And the following Organisation Content entity:
      | Title             | Organisation demo page         |
      | Introduction      | Organisation introduction text |
      | Acronym           | Organisation acronym           |
      | Body text         | Organisation body text         |
      | Organisation type | EU organisation                |
      | EU organisation   | Directorate-General for Budget |
      | Contact           | A general contact              |
    When I am visiting the "Organisation demo page" content
    And I click "Edit"
    And I press "Remove"
    Then I should see "Are you sure you want to remove A general contact?"
    When I press "Remove"
    And I press "Save"
    Then I should see "Organisation Organisation demo page has been updated."
    And the General Contact entity with title "A general contact" exists
