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
    And I fill in "Start date" with the date "02/21/2019"
    And I fill in "Start date" with the time "02:21:00AM"
    And I fill in "End date" with the date "02/21/2019"
    And I fill in "End date" with the time "02:21:00PM"
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
    # Online field group.
    When I press "Online"
    And I select "Facebook" from "Online type"
    Then I should have the following options for the "Online type" select:
      | - None -   |
      | Facebook   |
      | Livestream |
    And I fill in "Online time start" with the date "02/22/2019"
    And I fill in "Online time start" with the time "02:22:00AM"
    And I fill in "Online time end" with the date "02/22/2019"
    And I fill in "Online time end" with the time "02:22:00PM"
    And I fill in "Online description" with "Online description text"
    And I fill in "URL" with "http://ec.europa.eu/2" in the "online link"
    And I fill in "Link text" with "Online link" in the "online link"
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
    And I should see "As planned"
    And I should see the link "Website"
    And I should see "Facebook"
    And I should see "Online description text"
    And I should see "Fri, 02/22/2019 - 02:22"
    And I should see "Fri, 02/22/2019 - 14:22"
    And I should see the link "Online link"
    And I should see "Organiser name"
    And I should see "Description summary text"
    And I should see "Description title"
    And I should see "Euro with miniature figurines"
    And I should see "Report summary text"
    And I should see "Report title"
    And I should see "Report text paragraph"
    And I should see the link "http://example.com"
    And I should see "Open"
    And I should see "Sat, 02/23/2019 - 02:23"
    And I should see "Sat, 02/23/2019 - 14:23"
    And I should see "Free of charge"
    And I should see "100 seats"

  @javascript @cleanup:media
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
    And I fill in "Subject" with "financing"
    And I press "Description"
    And I fill in "Summary for description" with "Description summary text"
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"
    And I fill in "Responsible department" with "Audit Board of the European Communities"
    When I press "Save"
    Then I should see the following error messages:
      | error messages                                                                  |
      | You have to fill in at least one of fields Internal organiser or Organiser name |
    # Make sure that errors related to the Organiser fields is fixed.
    When I press "Organiser"
    And I check "Organiser is internal"
    And I fill in "Internal organiser" with "Audit Board of the European Communities"
    And I press "Save"
    Then I should see the following success messages:
      | success messages                      |
      | Event My Event item has been created. |

    # Make sure that validation of the Online fields group work as expected.
    When I click "Edit"
    And I press "Online"
    And I select "Facebook" from "Online type"
    And I press "Save"
    Then I should see the following error messages:
      | error messages                       |
      | Online time start field is required. |
      | Online link field is required.       |
    # Make sure that errors related to the Organiser fields is fixed.
    When I press "Online"
    And I fill in "Online time start" with the date "02/22/2019"
    And I fill in "Online time start" with the time "02:22:00AM"
    And I fill in "Online time end" with the date "02/22/2019"
    And I fill in "Online time end" with the time "02:22:00PM"
    And I fill in "Online description" with "Online description text"
    And I fill in "URL" with "http://ec.europa.eu/2" in the "online link"
    And I fill in "Link text" with "Online link" in the "online link"
    And I press "Save"
    Then I should see the following success messages:
      | success messages                      |
      | Event My Event item has been updated. |

    # Make sure that validation of the Description fields group work as expected.
    When I click "Edit"
    And I press "Description"
    And I fill in "Full text" with "Full text paragraph"
    And I press "Save"
    Then I should see the following error messages:
      | error messages                           |
      | Featured media field is required.        |
      | Featured media legend field is required. |
    # Make sure that errors related to the Description fields is fixed.
    When I press "Description"
    And I fill in "Description title" with "Description title"
    And I fill in "Use existing media" with "Euro with miniature figurines"
    And I fill in "Featured media legend" with "Euro with miniature figurines"
    And I press "Save"
    Then I should see the following success messages:
      | success messages                      |
      | Event My Event item has been updated. |

    # Make sure that validation of the Registration fields group work as expected.
    When I click "Edit"
    And I press "Registration"
    And I fill in "Registration URL" with "http://example.com"
    And I press "Save"
    Then I should see the following error messages:
      | error messages                             |
      | Registration status field is required.     |
      | Registration start date field is required. |
      | Registration end date field is required.   |
    # Make sure that errors related to the Registration fields is fixed.
    When I press "Registration"
    And I select "Open" from "Registration status"
    And I fill in "Registration start date" with the date "02/23/2019"
    And I fill in "Registration start date" with the time "02:23:00AM"
    And I fill in "Registration end date" with the date "02/23/2019"
    And I fill in "Registration end date" with the time "02:23:00PM"
    And I press "Save"
    Then I should see the following success messages:
      | success messages                      |
      | Event My Event item has been updated. |

