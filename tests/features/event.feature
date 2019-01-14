@api
Feature: Event feature
  In order to have events on the site
  As an editor
  I need to be able to create and see events

  Scenario: Create event
    Given I am logged in with a user that can create and view "Event" RDF entities
    And I visit "the Add Event page"
    And I fill in "Title" with "My event"
    And I fill in "Introduction" with "My intro"
    And I fill in "Description" with "My description"
    And I fill in "Subject" with "legal expenses"
    And I fill in "Available languages" with "fr"
    And I fill in "URL" with "http://example.com/my-event/more-info"
    And I fill in "Link text" with "My related link"
    And I fill in "Website" with "http://example.com/my-event"
    And I fill in "Start date" with the date "2018-08-08"
    And I fill in "Start date" with the time "12:00:00"
    And I fill in "End date" with the date "2018-08-08"
    And I fill in "End date" with the time "14:00:00"
    And I fill in "Who should attend" with "Legislative"
    And I fill in "Organiser" with "Committee on Legal Affairs"
    And I press "Save"
    Then I should see "My event"
    And I should see "My intro"
    And I should see "My description"
    And I should see the link "legal expenses"
    And I should see "fr"
    And I should see the link "My related link" pointing to "http://example.com/my-event/more-info"
    And I should see "http://example.com/my-event"
    And I should see "Wed, 08/08/2018 - 12:00"
    And I should see "Wed, 08/08/2018 - 14:00"
    And I should see the link "Legislative"
    And I should see the link "Committee on Legal Affairs"
    And I delete the RDF entity with the name "My event"

