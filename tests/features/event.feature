@api
Feature: Event feature
  In order to have events on the site
  As an editor
  I need to be able to create and see events

  @rdf-provenance
  Scenario: Create event
    Given I am logged in with a user that can create and view "Event" RDF entities
    And I visit "/rdf_entity/add/oe_event"
    And I fill in "Title" with "My event"
    And I fill in "Introduction" with "My intro"
    And I fill in "Description" with "My description"
    And I fill in "Available languages" with "fr"
    And I fill in "Related links" with "http://example.com/my-event/more-info"
    And I fill in "Website" with "http://example.com/my-event"
    And I fill in "Start date" with the date "2018-08-08"
    And I fill in "Start date" with the time "12:00:00"
    And I fill in "End date" with the date "2018-08-08"
    And I fill in "End date" with the time "14:00:00"
    And I press "Save"
    Then I should see "My event"
    Then I should see "My intro"
    Then I should see "My description"
    Then I should see "fr"
    Then I should see "http://example.com/my-event/more-info"
    Then I should see "http://example.com/my-event"
    Then I should see "Wed, 08/08/2018 - 12:00"
    Then I should see "Wed, 08/08/2018 - 14:00"
    Then I delete the RDF entity with the name "My event"

