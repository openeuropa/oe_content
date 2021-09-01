@api
Feature: Page content creation
  In order to have pages on the site
  As an editor
  I need to be able to create and see pages

  @batch3
  Scenario: Creation of a Page content through the UI.
    Given I am logged in as a user with the "create oe_page content, access content, edit own oe_page content, view published skos concept entities" permission
    And I visit "the Page creation page"
    And I fill in "Page title" with "My page"
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"
    And I fill in "Redirect link" with "http://example.com"
    And I fill in "Navigation title" with "Navi title"
    And I fill in "Alternative title" with "Shorter title"
    And I fill in "Introduction" with "Summary text"
    And I fill in "Body text" with "Body text"
    And I fill in "URL" with "http://example.com"
    And I fill in "Link text" with "My link"
    And I fill in "Teaser" with "Teaser text"
    And I fill in "Subject" with "financing"
    And I fill in "Author" with "European Patent Office"

    # Authors field widget.
    And I press the "Add new author" button
    And I fill in "Corporate body (value 1)" with "Audit Board of the European Communities"
    And I press the "Add another item" button in the "Authors field" region
    And I fill in "Corporate body (value 2)" with "Arab Common Market"
    And I press the "Create author" button
    And I should see "Audit Board of the European Communities, Arab Common Market"

    When I press "Save"
    Then I should see "My page"
    And I should see "Body text"
    And I should see "Teaser text"
    And I should see "Summary text"
    And I should see "Shorter title"
    And I should not see the link "financing"
    And I should not see the link "European Patent Office"
    And I should see the link "My link"

  @javascript
  @batch1
  Scenario: Length limited fields are truncating characters exceeding the configured limit.
    Given I am logged in as a user with the "create oe_page content, access content, edit own oe_page content, view published skos concept entities" permission
    When I visit "the Page creation page"
    Then I should see the text "Content limited to 170 characters, remaining: 170" in the "title form element"
    And I should see the text "Content limited to 300 characters, remaining: 300" in the "teaser form element"
    And I should see the text "Content limited to 250 characters, remaining: 250" in the "summary form element"
    And I should see the text "Content limited to 170 characters, remaining: 170" in the "alternative title form element"
    When I fill in "Page title" with "My page"
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"
    And I fill in "Teaser" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin eu hendrerit lacus, vitae bibendum odio. Fusce orci purus, hendrerit a magna at nullam. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Lorem ipsum dolor sit amet Teaser. Text to remove"
    And I fill in "Introduction" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas felis leo, lobortis non eros in, consequat tempor est. Praesent sit amet sem eleifend, cursus arcu ac, eleifend nunc. Integer et orci sagittis, volutpat felis sit ametas Introduction. Text to remove"
    And I fill in "Alternative title" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin eu hendrerit lacus, vitae bibendum odio. Fusce orci purus, hendrerit a magna at nullamsa Alternative title. Text to remove"
    And I press "Save"
    # We assert that the extra characters are actually truncated from the end of the string.
    Then I should not see "Text to remove"
    And I should see "ametas Introduction."
    And I should see "nullamsa Alternative title."
    And I should see "amet Teaser."
