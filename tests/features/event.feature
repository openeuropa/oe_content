@api
Feature: Event content creation
  In order to have events on the site
  As an editor
  I need to be able to create and see event items

  @javascript
  Scenario: Fields on the event content creation forms should be grouped logically.
    Given I am logged in as a user with the "create oe_event content, access content, edit own oe_event content, view published skos concept entities" permission
    When I visit "the Event creation page"
    Then I should see "Online"
    And I should not see "Online type"
    And I should not see "Online time start"
    And I should not see "Online time end"
    And I should not see "Online description"
    And I should not see "Online link"

    And I should see "Organiser"
    And I should not see "Organiser name"
    And I should not see "Internal organiser"

    And I should see "Description"
    And I should not see "Description summary"
    And I should not see "Featured media"
    And I should not see "Featured media legend"
    And I should not see "Full text"

    And I should see "Report"
    And I should not see "Summary for report"
    And I should not see "Report text"

    And I should see "Registration"
    And I should not see "Registration URL"
    And I should not see "Registration status"
    And I should not see "Registration start date"
    And I should not see "Registration end date"
    And I should not see "Entrance fee"
    And I should not see "Registration capacity"

    # Make sure that the Online field group contains expected fields.
    When I press "Online"
    Then I should see "Online type"
    And I should see "Online time start"
    And I should see "Online time end"
    And I should see "Online description"
    And I should see "Online link"
    # Collapse Online field group
    And I press "Online"

    # Make sure that the Organiser field group contains expected fields.
    When I press "Organiser"
    Then I should see "Organiser name"
    And I should see "Internal organiser"
    When I check "Organiser is internal"
    Then I should see "Internal organiser"
    And I should not see "Organiser name"
    # Collapse Organiser field group
    And I press "Organiser"

    # Make sure that the Description field group contains expected fields.
    When I press "Description"
    Then I should see "Description summary"
    And I should see "Featured media"
    And I should see "Featured media legend"
    And I should see "Full text"
    # Collapse Description field group
    And I press "Description"

    # Make sure that the Report field group contains expected fields.
    When I press "Report"
    Then I should see "Summary for report"
    And I should see "Report text"
    # Collapse Report field group
    And I press "Report"

    # Make sure that the Registration field group contains expected fields.
    When I press "Registration"
    Then I should see "Registration URL"
    And I should see "Registration status"
    And I should see "Registration start date"
    And I should see "Registration end date"
    And I should see "Entrance fee"
    And I should see "Registration capacity"

  @javascript
  Scenario: Make sure that the selectboxes contains correct options.
    Given I am logged in as a user with the "create oe_event content, access content, edit own oe_event content, view published skos concept entities" permission
    When I visit "the Event creation page"
    Then I should have the following options for the "Status" select:
      | - Select a value - |
      | As planned         |
      | Cancelled          |
      | Rescheduled        |
      | Postponed          |
    And I should have the following options for the "Type" select:
      | Training and workshops            |
      | Info days                         |
      | Competitions and award ceremonies |
      | Conferences and summits           |
      | Public debates                    |
      | Partner meetings                  |
      | Political meetings                |
      | Exhibitions                       |
    When I press "Online"
    Then I should have the following options for the "Online type" select:
      | - None -   |
      | Facebook   |
      | Livestream |
    When I press "Registration"
    Then I should have the following options for the "Registration status" select:
      | - None - |
      | Open     |
      | Closed   |

  @cleanup:media @javascript @av_portal
  Scenario: Creation of a Event content through the UI.
    Given I am logged in as a user with the "create oe_event content, access content, edit own oe_event content, view published skos concept entities, create av_portal_photo media" permission
    # Create a "Media AV portal photo".
    And I visit "the AV Portal photo creation page"
    And I fill in "Media AV Portal Photo" with "https://audiovisual.ec.europa.eu/en/photo/P-038924~2F00-15"
    And I press "Save"
    # Create a "Event" content.
    When I visit "the Event creation page"
    Then I fill in "Title" with "My Event item"
    And I fill in "Start date" with the date "02/21/2019"
    And I fill in "Start date" with the time "02:21:00AM"
    And I fill in "End date" with the date "02/21/2019"
    And I fill in "End date" with the time "02:21:00PM"
    And I select "As planned" from "Status"
    And I select "Info days" from "Type"
    And I fill in "Subject" with "EU financing"
    And I fill in "URL" with "http://ec.europa.eu"
    And I fill in "Link text" with "Website"
    # Online field group.
    When I press "Online"
    Then I select "Facebook" from "Online type"
    And I fill in "Online time start" with the date "02/22/2019"
    And I fill in "Online time start" with the time "02:22:00AM"
    And I fill in "Online time end" with the date "02/22/2019"
    And I fill in "Online time end" with the time "02:22:00PM"
    And I fill in "Online description" with "Online description text"
    And I fill in "URL" with "http://ec.europa.eu/2" in the "Online link" region
    And I fill in "Link text" with "Online link" in the "Online link" region
    # Organiser field group.
    When I press "Organiser"
    Then I fill in "Organiser name" with "Organiser name"
    # Description field group.
    When I press "Description"
    And I fill in "Description summary" with "Description summary text"
    And I fill in "Use existing media" with "Euro with miniature figurines"
    And I fill in "Featured media legend" with "Euro with miniature figurines"
    And I fill in "Full text" with "Full text paragraph"
    # Report field group.
    When I press "Report"
    And I fill in "Summary for report" with "Report summary text"
    And I fill in "Report text" with "Report text paragraph"
    # Registration field group.
    When I press "Registration"
    Then I fill in "Registration URL" with "http://example.com"
    And I select "Open" from "Registration status"
    And I fill in "Registration start date" with the date "02/23/2019"
    And I fill in "Registration start date" with the time "02:23:00AM"
    And I fill in "Registration end date" with the date "02/23/2019"
    And I fill in "Registration end date" with the time "02:23:00PM"
    And I fill in "Entrance fee" with "Free of charge"
    And I fill in "Registration capacity" with "100 seats"

    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"
    And I fill in "Responsible department" with "Audit Board of the European Communities"
    When I press "Save"
    Then I should see "My Event item"
    And I should see "Full text paragraph"
    And I should see "Thu, 02/21/2019 - 02:21"
    And I should see "Thu, 02/21/2019 - 14:21"
    And I should see "Info days"
    And I should see "As planned"
    And I should see the link "Website"
    And I should see "Facebook"
    And I should see "Online description text"
    And I should see "Fri, 02/22/2019 - 02:22"
    And I should see "Fri, 02/22/2019 - 14:22"
    And I should see the link "Online link"
    And I should see "Organiser name"
    And I should see "Description summary text"
    And I should see "Euro with miniature figurines"
    And I should see "Report summary text"
    And I should see "Report text paragraph"
    And I should see the link "http://example.com"
    And I should see "Open"
    And I should see "Sat, 02/23/2019 - 02:23"
    And I should see "Sat, 02/23/2019 - 14:23"
    And I should see "Free of charge"
    And I should see "100 seats"

  @javascript @cleanup:media @av_portal
  Scenario: As an editor when I create an Event node, the required fields are correctly marked when not filled in.
    Given I am logged in as a user with the "create oe_event content, access content, edit own oe_event content, view published skos concept entities, create av_portal_photo media" permission
    # Create a "Media AV portal photo".
    And I visit "the AV Portal photo creation page"
    And I fill in "Media AV Portal Photo" with "https://audiovisual.ec.europa.eu/en/photo/P-038924~2F00-15"
    And I press "Save"

    # Create a "Event" content.
    When I visit "the Event creation page"
    And I fill in "Title" with "My Event item"
    And I select "As planned" from "Status"
    And I select "Info days" from "Type"
    And I fill in "Subject" with "EU financing"
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"
    And I fill in "Responsible department" with "Audit Board of the European Communities"
    And I press "Save"
    Then I should see the following error messages:
      | error messages                                                                                 |
      | You have to fill in at least one of the following fields: Internal organiser or Organiser name |
    # Make sure that errors related to the Organiser fields are fixed.
    When I check "Organiser is internal"
    And I fill in "Internal organiser" with "Audit Board of the European Communities"
    And I press "Save"
    Then I should see the following success messages:
      | success messages                      |
      | Event My Event item has been created. |

    # Make sure that validation of the Online fields group works as expected.
    When I click "Edit"
    And I press "Online"
    And I select "Facebook" from "Online type"
    And I press "Save"
    Then I should see the following error messages:
      | error messages                       |
      | Online time start field is required. |
      | Online link field is required.       |
    # Make sure that errors related to the Online fields are fixed.
    When I fill in "Online time start" with the date "02/22/2019"
    And I fill in "Online time start" with the time "02:22:00AM"
    And I fill in "Online time end" with the date "02/22/2019"
    And I fill in "Online time end" with the time "02:22:00PM"
    And I fill in "Online description" with "Online description text"
    And I fill in "URL" with "http://ec.europa.eu/2" in the "Online link" region
    And I fill in "Link text" with "Online link" in the "Online link" region
    And I press "Save"
    Then I should see the following success messages:
      | success messages                      |
      | Event My Event item has been updated. |

    # Make sure that validation of the Description fields group works as expected.
    When I click "Edit"
    And I press "Description"
    And I fill in "Full text" with "Full text paragraph"
    And I press "Save"
    Then I should see the following error messages:
      | error messages                           |
      | Featured media field is required.        |
      | Featured media legend field is required. |
      | Description summary field is required.   |
    # Make sure that errors related to the Description fields are fixed.
    When I fill in "Description summary" with "Description summary text"
    And I fill in "Use existing media" with "Euro with miniature figurines"
    And I fill in "Featured media legend" with "Euro with miniature figurines"
    And I press "Save"
    Then I should see the following success messages:
      | success messages                      |
      | Event My Event item has been updated. |

    # Make sure that validation of the Registration fields group works as expected.
    When I click "Edit"
    And I press "Registration"
    And I fill in "Registration URL" with "http://example.com"
    And I press "Save"
    Then I should see the following error messages:
      | error messages                             |
      | Registration status field is required.     |
      | Registration start date field is required. |
      | Registration end date field is required.   |
    # Make sure that errors related to the Registration fields are fixed.
    When I select "Open" from "Registration status"
    And I fill in "Registration start date" with the date "02/23/2019"
    And I fill in "Registration start date" with the time "02:23:00AM"
    And I fill in "Registration end date" with the date "02/23/2019"
    And I fill in "Registration end date" with the time "02:23:00PM"
    And I press "Save"
    Then I should see the following success messages:
      | success messages                      |
      | Event My Event item has been updated. |
