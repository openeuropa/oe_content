@api @organisation
Feature: Organisation content creation
  In order to have organisations on the site
  As an editor
  I need to be able to create and see organisation items

  @javascript
  Scenario: Creation of a Organisation content through the UI.
    Given I am logged in as a user with the "create oe_organisation content, access content, edit own oe_organisation content, view published skos concept entities, manage corporate content entities" permission
    And the following images:
      | name    | file            | alt                |
      | Image 1 | placeholder.png | Alternative text 1 |

    When I visit "the Organisation creation page"
    And I fill in "Page title" with "My Organisation"
    And I fill in "Introduction" with "Organisation introduction"
    And I fill in "Body text" with "Body text"
    And I fill in "Use existing media" with "Image 1"
    And I fill in "Acronym" with "Organisation Acronym"
    And I fill in "Teaser" with "Organisation teaser text"
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"

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
