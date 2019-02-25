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
    And I fill in "Teaser" with "Teaser text"
    And I fill in "Subject" with "financing"
    And I fill in "Author" with "European Patent Office"
    When I press "Save"
    Then I should see "My page"
    And I should see "Navi title"
    And I should see "Shorter title"
    And I should see "Summary text"
    And I should see "Body text"
    And I should see "Teaser text"
    And I should see the link "financing"
    And I should see the link "European Patent Office"
