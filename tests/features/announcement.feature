@api
Feature: Announcement feature
  In order to have announcements on the site
  As an editor
  I need to be able to create and see announcements

  Scenario: Create announcement
    Given I am logged in with a user that can create and view "Announcement" RDF entities
    And I visit "/rdf_entity/add/oe_announcement"
    And I fill in "Title" with "My announcement"
    And I fill in "Introduction" with "My intro"
    And I fill in "Description" with "My description"
    And I fill in "Location" with "My location"
    And I fill in "Announcement Type" with "Press release"
    And I fill in "Body" with "My body"
    And I fill in "First published on" with the date "2018-08-08"
    And I fill in "First published on" with the time "01:01:02"
    And I fill in "Responsible department" with "Directorate-General for Informatics"
    And I fill in "Subject" with "State monopoly"
    And I press "Save"
    Then I should see "My announcement"
    And I should see "My intro"
    And I should see "My description"
    And I should see "My location"
    And I should see the link "Press release"
    And I should see "My body"
    And I should see "Wed, 08/08/2018 - 01:01"
    And I should see the link "Directorate-General for Informatics"
    And I should see the link "State monopoly"
    And I delete the RDF entity with the name "My announcement"

