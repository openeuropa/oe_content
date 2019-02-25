@api
Feature: News content creation
  In order to have news on the site
  As an editor
  I need to be able to create and see news items

  @cleanup:media
  Scenario: Creation of a News content through the UI.
    Given I am logged in as a user with the "create oe_news content, access content, edit own oe_page content, view published skos concept entities, create av_portal_photo media" permission
    # Create a "Media AV portal photo".
    And I visit "the AV Portal photo creation page"
    And I fill in "Media AV Portal Photo" with "https://ec.europa.eu/avservices/photo/photoDetails.cfm?sitelang=en&ref=038924#14"
    And I press "Save"
    # Create a "News" content.
    And I visit "the News creation page"
    And I fill in "Title" with "My News item"
    And I fill in "Summary" with "Summary text"
    And I fill in "Teaser" with "Teaser text"
    And I fill in "Body" with "Body text"
    And I fill in "Location" with "Budapest"
    And I fill in "Publication date" with the date "2019-02-21"
    And I fill in "Subject" with "financing"
    And I fill in "Author" with "European Patent Office"
    # Reference the media photo to the news item.
    And I fill in "Use existing media" with "Euro with miniature figurines"
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"
    And I fill in "Legacy link" with "http://example.com"
    And I fill in "Navigation title" with "Navi title"
    And I fill in "Short title" with "Shorter title"
    When I press "Save"
    Then I should see "My News item"
    And I should see "Navi title"
    And I should see "Shorter title"
    And I should see "Summary text"
    And I should see "Body text"
    And I should see the link "Budapest"
    And I should see "Thu, 02/21/2019"
    And I should see "Teaser text"
    And I should see the link "financing"
    And I should see the link "European Patent Office"
    And I should see the AV Portal photo "Euro with miniature figurines" with source "//ec.europa.eu/avservices/avs/files/video6/repository/prod/photo/store/store2/4/P038924-352937.jpg"
