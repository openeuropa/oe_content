@api
Feature: Page content creation
  In order to have pages on the site
  As an editor
  I need to be able to create and see pages

  Scenario: Creation of a Page content through the UI.
    Given I am logged in as a user with the "create oe_page content, access content, edit own oe_page content, view published skos concept entities" permission
    And I visit "the Page creation page"
    And I fill in "Title" with "My page"
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"
    And I fill in "Legacy link" with "http://example.com"
    And I fill in "Navigation title" with "Navi title"
    And I fill in "Short title" with "Shorter title"
    And I fill in "Summary" with "Summary text"
    And I fill in "Body" with "Body text"
    And I fill in "URL" with "http://example.com"
    And I fill in "Link text" with "My link"
    And I fill in "Teaser" with "Teaser text"
    And I fill in "Subject" with "financing"
    And I fill in "Author" with "European Patent Office"
    When I press "Save"
    Then I should see "My page"
    And I should see "Body text"
    And I should not see "Teaser text"
    And I should not see the link "financing"
    And I should not see the link "European Patent Office"
    And I should see the link "My link"

  @javascript
  Scenario: Length limited fields are truncating characters exceeding the configured limit.
    Given I am logged in as a user with the "create oe_page content, access content, edit own oe_page content, view published skos concept entities" permission
    When I visit "the Page creation page"
    Then I should see the text "Content limited to 255 characters, remaining: 255" in the "title form element"
    And I should see the text "Content limited to 150 characters, remaining: 150" in the "teaser form element"
    And I should see the text "Content limited to 250 characters, remaining: 250" in the "summary form element"
    When I fill in "Title" with "My page"
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"
    And I fill in "Teaser" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin eu hendrerit lacus, vitae bibendum odio. Fusce orci purus, hendrerit a magna at nullam. Text to remove"
    And I fill in "Summary" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas felis leo, lobortis non eros in, consequat tempor est. Praesent sit amet sem eleifend, cursus arcu ac, eleifend nunc. Integer et orci sagittis, volutpat felis sit amet, tincidunt amet. Text to remove"
    And I press "Save"
    # We assert that the extra characters are actually truncated from the end of the string.
    Then I should not see "The text to remove."
