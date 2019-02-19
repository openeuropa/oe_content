@api
Feature: Page feature
  In order to have pages on the site
  As an editor
  I need to be able to create and see pages

  Scenario: Create Page content
    Given I am logged in as a user with the "create oe_page content, access content" permission
    And I visit "the page creation page"
    And I fill in "Title" with "My page"
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development (http://publications.europa.eu/resource/authority/corporate-body/EP_AGRI)"
    And I fill in "Legacy link" with "http://google.com"
    And I fill in "Navigation title" with "Navi title"
    And I fill in "Short title" with "Shorter title"
    And I fill in "Summary" with "Lorem Ipsum is simply dummy text of the printing and typesetting industry."
    And I fill in "Body" with "Lorem Ipsum is simply dummy text of the printing and typesetting industry."
    And I fill in "Teaser" with "Lorem Ipsum is simply dummy text of the printing and typesetting industry."
    And I fill in "Subject" with "financing (http://eurovoc.europa.eu/1000)"
    And I fill in "Author" with "European Patent Office (http://publications.europa.eu/resource/authority/corporate-body/EPOFF)"
    When I press "Save"
    Then I should see "My page"
