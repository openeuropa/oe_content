@api
Feature: Policy content creation
  In order to have policies on the site
  As an editor
  I need to be able to create and see policy items

  @javascript
  Scenario: Creation of a Policy content through the UI.
    Given I am logged in as a user with the "create oe_policy content, access content, edit own oe_policy content, view published skos concept entities" permission
    And the following languages are available:
      | languages |
      | en        |
      | fr        |
    # Create a "Policy" content.
    And I visit "the Policy creation page"
    And I fill in "Title" with "My Policy item"
    And I fill in "Summary" with "Summary text"
    And I fill in "Teaser" with "Teaser text"
    And I fill in "Body" with "Body text"
    And I fill in "Subject" with "financing"
    And I fill in "Responsible department" with "European Patent Office"
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"
    And I fill in "Legacy link" with "http://example.com"
    And I fill in "Navigation title" with "Navi title"
    And I fill in "Short title" with "Shorter title"
    And I fill in "Label" with "Label 1" in the "first" "Timeline" field element
    And I fill in "Title" with "Title 1" in the "first" "Timeline" field element
    And I fill in "Body" with "Body 1" in the "first" "Timeline" field element
    And I press "Add another item"
    And I fill in "Label" with "Label 2" in the "second" "Timeline" field element
    And I fill in "Title" with "Title 2" in the "second" "Timeline" field element
    And I fill in "Body" with "Body 2" in the "second" "Timeline" field element
    And I press "Add another item"
    And I fill in "Label" with "Label 3" in the "third" "Timeline" field element
    And I fill in "Title" with "Title 3" in the "third" "Timeline" field element
    And I fill in "Body" with "Body 3" in the "third" "Timeline" field element
    When I press "Save"
    Then I should see "My Policy item"
    And I should see "Body text"
    And I should see "Label 1"
    And I should see "Title 1"
    And I should see "Body 1"
    And I should see "Label 2"
    And I should see "Title 2"
    And I should see "Body 2"
    And I should see "Label 3"
    And I should see "Title 3"
    And I should see "Body 3"
    And I should see the button "Show full timeline"
    And I should not see "Navi title"
    And I should not see "Shorter title"
    And I should not see "Summary text"
    And I should not see "Teaser text"
    And I should not see the link "financing"
    And I should not see the link "European Patent Office"
    When I click "français"
    Then I should see "Voir l’historique complet"

  @javascript
  Scenario: Length limited fields are truncating characters exceeding the configured limit.
    Given I am logged in as a user with the "create oe_policy content, access content, edit own oe_policy content, view published skos concept entities" permission
    When I visit "the Policy creation page"
    Then I should see the text "Content limited to 170 characters, remaining: 170" in the "title form element"
    And I should see the text "Content limited to 150 characters, remaining: 150" in the "teaser form element"
    And I should see the text "Content limited to 250 characters, remaining: 250" in the "summary form element"
    When I fill in "Title" with "My Policy"
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"
    And I fill in "Teaser" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin eu hendrerit lacus, vitae bibendum odio. Fusce orci purus, hendrerit a magna at nullam. Text to remove"
    And I fill in "Summary" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas felis leo, lobortis non eros in, consequat tempor est. Praesent sit amet sem eleifend, cursus arcu ac, eleifend nunc. Integer et orci sagittis, volutpat felis sit amet, tincidunt amet. Text to remove"
    And I fill in "Body" with "Body text"
    And I fill in "Subject" with "financing"
    And I fill in "Responsible department" with "European Patent Office"
    And I press "Save"
    # We assert that the extra characters are actually truncated from the end of the string.
    Then I should not see "The text to remove."
