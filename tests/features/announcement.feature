@api @run
Feature: Announcement feature
  In order to have announcements on the site
  As an editor
  I need to be able to create and see announcements

  Scenario: Create announcement
    Given I am logged in with a user that can create and view "Announcement" RDF entities
    And I visit "/rdf_entity/add/announcement"
    And I fill in "Title" with "My announcement"
    And I fill in "Introduction" with "My intro"
    And I fill in "Description" with "My description"
    And I fill in "Body" with "My body"
    And I fill in "First published on" with the date "2018-08-08"
    And I fill in "First published on" with the time "01:01:02"
    And I press "Save"
    Then I should see "My announcement"
    Then I should see "My intro"
    Then I should see "My description"
    Then I should see "My body"
    Then I should see "Wed, 08/08/2018 - 01:01"

