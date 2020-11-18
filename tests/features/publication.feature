@api
Feature: Publication content creation
  In order to have publications on the site
  As an editor
  I need to be able to create and see publication items

  @javascript
  Scenario: Creation of a Publication content through the UI.
    Given I am logged in as a user with the "create oe_publication content, access content, edit own oe_publication content, manage corporate content entities, view published skos concept entities" permission
    And the following documents:
    | name          | file       |
    | My Document 1 | sample.pdf |
    And the following "Organisation" Content entity:
      | Title             | Organisation demo page         |
      | Organisation type | EU organisation                |
      | EU organisation   | Directorate-General for Budget |
    And I visit "the Publication creation page"
    And I fill in "Page title" with "My Publication item"
    And I fill in "Introduction" with "Summary text"
    And I fill in "Teaser" with "Teaser text"
    And I fill in "Subject" with "financing"
    And I should see "Publication date"
    And I fill in "Use existing media" with "My Document 1"
    And I select "Organisation" from "publication contact type" form element
    And I press "Add new contact"
    And I fill in "Name" with "Organisation contact"
    And I fill in "Organisation" with "Organisation demo page"
    And I press "Create contact"
    And I fill in "Type" with "Acknowledgement receipt"
    And I fill in "Responsible department" with "European Patent Office"
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"
    And I fill in "Redirect link" with "http://example.com"
    And I fill in "Navigation title" with "Navi title"
    And I fill in "Alternative title" with "Shorter title"
    When I press "Save"
    Then I should see "My Publication item"
    And I should see "sample.pdf"
    And I should see "Contact"
    And I should see "Organisation contact"
    And I should not see "Acknowledgement receipt"
    And I should not see "Summary text"
    And I should not see "Navi title"
    And I should not see "Shorter title"
    And I should not see "Teaser text"
    And I should not see the link "financing"
    And I should not see the link "European Patent Office"

  @javascript
  Scenario: Length limited fields are truncating characters exceeding the configured limit.
    Given I am logged in as a user with the "create oe_publication content, access content, edit own oe_publication content, view published skos concept entities" permission
    When I visit "the Publication creation page"
    Then I should see the text "Content limited to 170 characters, remaining: 170" in the "title form element"
    And I should see the text "Content limited to 250 characters, remaining: 250" in the "summary form element"
    And I should see the text "Content limited to 170 characters, remaining: 170" in the "alternative title form element"
    When I fill in "Page title" with "My Publication"
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"
    And I fill in "Teaser" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin eu hendrerit lacus, vitae bibendum odio. Fusce orci purus, hendrerit a magna at nullam. Text to remove"
    And I fill in "Introduction" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas felis leo, lobortis non eros in, consequat tempor est. Praesent sit amet sem eleifend, cursus arcu ac, eleifend nunc. Integer et orci sagittis, volutpat felis sit amet, tincidunt amet. Text to remove"
    And I fill in "Subject" with "financing"
    And I fill in "Responsible department" with "European Patent Office"
    And I press "Save"
    # We assert that the extra characters are actually truncated from the end of the string.
    Then I should not see "The text to remove."

