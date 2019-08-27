@api
Feature: Event content creation
  In order to have event on the site
  As an editor
  I need to be able to create and see event items

  @cleanup:media @javascript
  Scenario: Creation of a Event content through the UI.
    Given I am logged in as a user with the "create oe_event content, access content, edit own oe_event content, view published skos concept entities, create av_portal_photo media" permission
    # Create a "Media AV portal photo".
    And I visit "the AV Portal photo creation page"
    And I fill in "Media AV Portal Photo" with "https://audiovisual.ec.europa.eu/en/photo/P-038924~2F00-15"
    And I press "Save"
    # Create a "Event" content.
    And I visit "the Event creation page"
    And I fill in "Title" with "My Event item"
    And I fill in "Start date" with the date "21-02-2019"
    And I fill in "End date" with the date "21-02-2019"
    When I select "As planned" from "Status"
    Then I should have the following options for the "Status" select:
      | - Select a value - |
      | As planned         |
      | Cancelled          |
      | Rescheduled        |
      | Postponed          |
    When I select "Info days" from "Type"
    Then I should have the following options for the "Type" select:
      | Training and workshops            |
      | Info days                         |
      | Competitions and award ceremonies |
      | Conferences and summits           |
      | Public debates                    |
      | Partner meetings                  |
      | Political meetings                |
      | Exhibitions                       |
    And I fill in "Subject" with "financing"
    And I fill in "URL" with "http://ec.europa.eu"
    And I fill in "Link text" with "Website"
    And I fill in "Teaser" with "Teaser text"
    # Online field group.
    When I press "Online"
    And I select "Facebook" from "Online type"
    Then I should have the following options for the "Online type" select:
      | - None -   |
      | Facebook   |
      | Livestream |
    And I fill in "Online time start" with the date "21-02-2019"
    And I fill in "Online time start" with the time "26:59:00AM"
    And I fill in "Online time end" with the date "21-02-2019"
    And I fill in "Online time end" with the time "26:59:00"
    And I fill in "Online description" with "Online description text"
    # Organiser field group.
    When I press "Organiser"
    And I fill in "Organiser name" with "Organiser name"
    # Description field group.
    When I press "Description"
    And I fill in "Summary for description" with "Description summary text"
    And I fill in "Description title" with "Description title"
    And I fill in "Use existing media" with "Euro with miniature figurines"
    And I fill in "Featured media legend" with "Euro with miniature figurines"
    And I fill in "Full text" with "Full text paragraph"
    # Report field group.
    When I press "Report"
    And I fill in "Summary for report" with "Report summary text"
    And I fill in "Report title" with "Report title"
    And I fill in "Report text" with "Report text paragraph"
    # Registration field group.
    When I press "Registration"
    And I fill in "Registration URL" with "http://example.com"
    When I select "Open" from "Registration status"
    Then I should have the following options for the "Registration status" select:
      | - None - |
      | Open     |
      | Closed   |
    And I fill in "Registration start date" with the date "21-02-2019"
    And I fill in "Registration start date" with the time "26:59:00"
    And I fill in "Registration end date" with the date "21-02-2019"
    And I fill in "Registration end date" with the time "26:59:00"
    And I fill in "Entrance fee" with "Free of charge"
    And I fill in "Registration capacity" with "100 seats"

    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"
    And I fill in "Responsible department" with "Audit Board of the European Communities"
    When I press "Save"

  Scenario: As an editor when I create an Event node, the required fields are correctly marked when not filled in
