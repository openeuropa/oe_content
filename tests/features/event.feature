@api @event
Feature: Event content creation
  In order to have events on the site
  As an editor
  I need to be able to create and see event items

  @javascript
  @batch1
  Scenario: Length limited fields are truncating characters exceeding the configured limit.
    Given I am logged in as a user with the "create oe_event content, access content, edit own oe_event content, view published skos concept entities, create oe_event_programme oe_default corporate entity" permission
    And the following AV Portal photo:
      | url                                                         |
      | https://audiovisual.ec.europa.eu/en/photo/P-038924~2F00-15  |
    When I visit "the Event creation page"
    Then I should see the text "Content limited to 170 characters, remaining: 170" in the "title form element"
    And I should see the text "Content limited to 250 characters, remaining: 250" in the "summary form element"
    And I should see the text "Content limited to 150 characters, remaining: 150" in the "featured media legend form element"
    And I should see the text "Content limited to 170 characters, remaining: 170" in the "alternative title form element"
    And I should see the text "Content limited to 300 characters, remaining: 300" in the "teaser form element"
    When I fill in "Page title" with "My Event"
    And I select "Info days" from "Type"
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"
    And I fill in "Description summary" with "Description summary text"
    And I fill in "Use existing media" with "Euro with miniature figurines" in the "Description" region
    And I fill in "Featured media legend" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin eu hendrerit lacus, vitae bibendum odio. Fusce orci purus, hendr Featured media legend. Text to remove"
    And I fill in "Full text" with "Full text paragraph"
    And I fill in "Teaser" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin eu hendrerit lacus, vitae bibendum odio. Fusce orci purus, hendrerit a magna at nullam. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Lorem ipsum dolor sit amet Teaser. Text to remove"
    And I fill in "Introduction" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas felis leo, lobortis non eros in, consequat tempor est. Praesent sit amet sem eleifend, cursus arcu ac, eleifend nunc. Integer et orci sagittis, volutpat felis sit ametas Introduction. Text to remove"
    And I fill in "Alternative title" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin eu hendrerit lacus, vitae bibendum odio. Fusce orci purus, hendrerit a magna at nullamsa Alternative title. Text to remove"
    And I fill in "Subject" with "financing"
    And I fill in "Responsible department" with "European Patent Office"
    And I fill in "Languages" with "English"
    And I press "Add new Programme"
    And I wait for AJAX to finish
    Then I should see the text "Content limited to 150 characters, remaining: 150" in the "Programme name"
    When I press "Cancel"
    And I press "Save"
    # We assert that the extra characters are actually truncated from the end of the string.
    Then I should not see "Text to remove"
    And I should see the text "hendr Featured media legend."
    And I should see the text "ametas Introduction."
    And I should see the text "nullamsa Alternative title."
    And I should see the text "amet Teaser."

  @javascript
  @batch2
  Scenario: Fields on the event content creation forms should be grouped logically.
    Given I am logged in as a user with the "create oe_event content, access content, edit own oe_event content, view published skos concept entities, manage corporate content entities" permission
    When I visit "the Event creation page"

    # The text assertions are actually checking for fields.
    # Proper steps will be introduced in OPENEUROPA-2160.
    Then I should see the text "Type"
    And I should see the text "Page title"
    And I should see the text "Description summary"
    And I should see the text "Subject"
    And I should see the text "Start date"
    And I should see the text "End date"
    And I should see the text "Online only"
    And I should see the text "Status"
    And I should see the text "Status description"
    And I should see the text "Languages"
    And I should see the text "Who should attend"
    And I should see the text "Event website"
    And I should see the text "Link type"

    # The registration group is collapsed by default.
    And I should see the text "Registration"
    And I should not see the text "Registration URL"
    And I should not see the text "Registration date"
    And I should not see the text "Entrance fee"
    And I should not see the text "Registration capacity"
    When I press "Registration"
    Then I should see the text "Registration URL"
    And I should see the text "Registration date"
    And I should see the text "Entrance fee"
    And I should see the text "Registration capacity"

     # The venue group is open by default.
    And I should see the text "Venue"
    When I press "Add new venue"
    And I wait for AJAX to finish
    And I should see the text "Name"
    And I should see the text "Capacity"
    And I should see the text "Room"
    And I should see the text "Country"

    # The online group is collapsed by default.
    And I should see the text "Online"
    And I should not see the text "Online type"
    And I should not see the text "Online time"
    And I should not see the text "Online description"
    And I should not see the text "Online link"
    When I press "Online"
    Then I should see the text "Online type"
    And I should see the text "Online time"
    And I should see the text "Online description"
    And I should see the text "Online link"

    # The organiser group is opened by default.
    And I should see the text "Organiser"
    And I should see the text "Organiser is internal"
    And the "Internal organiser field" is visible
    And I should not see the text "Organiser name"
    When I uncheck "Organiser is internal"
    Then I should see the text "Organiser name"
    And the "Internal organiser field" is not visible

    And I should see the text "Media" in the "Event media" region

    # The full description group is opened by default.
    And I should see the text "Full description"
    And I should see the text "Featured media"
    And I should see the text "Featured media legend"
    And I should see the text "Full text"

    # The full report group is collapsed by default.
    And I should see the text "Event report"
    And I should not see the text "Report text"
    And I should not see the text "Summary for report"
    And I should not see the text "Main link to further media items"
    And I should not see the text "Other links to further media items"
    When I press "Event report"
    Then I should see the text "Report text"
    And I should see the text "Summary for report"
    And I should see the text "Main link to further media items" in the "Event report" region
    And I should see the text "Other links to further media items" in the "Event report" region

    # Make sure that the Event contact field group contains expected fields.
    And I should see the text "Event contact"
    When I press "Add new contact"
    And I wait for AJAX to finish
    Then I should see "Name" in the "Event contact" region
    Then I should see "Organisation" in the "Event contact" region
    Then I should see "Body text" in the "Event contact" region
    Then I should see "Website" in the "Event contact" region
    Then I should see "Email" in the "Event contact" region
    Then I should see "Phone number" in the "Event contact" region
    Then I should see "Mobile number" in the "Event contact" region
    Then I should see "Fax number" in the "Event contact" region
    Then I should see "Country" in the "Event contact" region
    Then I should see "Office" in the "Event contact" region
    Then I should see "Social media links" in the "Event contact" region
    Then I should see "Image" in the "Event contact" region
    Then I should see "Press contacts" in the "Event contact" region

    # The alternative titles and teaser group is open by default.
    And I should see the text "Alternative titles and teaser"
    And I should see the text "Alternative title"
    And I should see the text "Navigation title"
    And I should see the text "Teaser"

    # The event programme group is open by default.
    And I should see the text "Programme"
    When I press "Add new Programme"
    And I wait for AJAX to finish
    And I should see the text "Name" in the "Event programme" region
    And I should see the text "Description" in the "Event programme" region
    And I should see the text "Start/end date" in the "Event programme" region

    # Metadata fields are visible
    And I should see the text "Content owner"
    And I should see the text "Responsible department"
    And I should see the text "Language"

  @javascript
  @batch3
  Scenario: Make sure that the selectboxes contains correct options.
    Given I am logged in as a user with the "create oe_event content, access content, edit own oe_event content, view published skos concept entities" permission
    When I visit "the Event creation page"
    Then I should have the following options for the "Status" select:
      | As planned  |
      | Cancelled   |
      | Rescheduled |
      | Postponed   |
    When I press "Online"
    Then I should have the following options for the "Online type" select:
      | - None -   |
      | Facebook   |
      | Livestream |
    And I should have the following options for the "Link type" select:
      | Email     |
      | Facebook  |
      | Flickr    |
      | Google+   |
      | Instagram |
      | Linkedin  |
      | Pinterest |
      | RSS       |
      | Storify   |
      | Twitter   |
      | Yammer    |
      | YouTube   |
      | Telegram  |
      | Mastodon  |

  @javascript @av_portal
  @batch1
  Scenario: Creation of a Event content through the UI.
    Given I am logged in as a user with the "create oe_event content, access content, edit own oe_event content, view published skos concept entities, manage corporate content entities" permission
    And the following images:
      | name          | file           | alt                            |
      | Contact image | example_1.jpeg | Contact image alternative text |
      | Media image   | example_2.jpeg | Media alternative text         |
    # Create a "Media AV portal photo".
    And the following AV Portal photos:
      | url                                                        |
      | https://audiovisual.ec.europa.eu/en/photo/P-038924~2F00-15 |
      | https://audiovisual.ec.europa.eu/en/photo/P-039321~2F00-04 |
    # Create a "Event" content.
    When I visit "the Event creation page"
    Then I select "Info days" from "Type"
    And I fill in "Page title" with "My Event item"

    # Registration field group.
    When I press "Registration"
    Then I fill in "Registration URL" with "http://example.com"
    And I fill in "Start date" of "Registration date" with the date "23-02-2019 02:30" in the timezone "Europe/Brussels"
    And I fill in "End date" of "Registration date" with the date "23-02-2019 14:30" in the timezone "Europe/Brussels"
    And I fill in "Entrance fee" with "Free of charge"
    And I fill in "Registration capacity" with "100 seats"

    And I fill in "Description summary" with "Description summary text"
    And I fill in "Subject" with "EU financing"
    And I fill in "Start date" of "Event date" with the date "21-02-2019 02:15" in the timezone "Europe/Brussels"
    And I fill in "End date" of "Event date" with the date "21-02-2019 14:15" in the timezone "Europe/Brussels"
    And I check the box "Online only"
    # Venue reference by Inline entity form - Complex.
    When I press "Add new venue"
    And I wait for AJAX to finish
    Then I fill in "Name" with "Name of the venue" in the "Event venue" region
    And I fill in "Capacity" with "Capacity of the venue" in the "Event venue" region
    And I fill in "Room" with "Room of the venue" in the "Event venue" region
    And I select "Belgium" from "Country" in the "Event venue" region
    And I wait for AJAX to finish
    And I fill in "Street address" with "Rue belliard 28" in the "Event venue" region
    And I fill in "Postal code" with "1000" in the "Event venue" region
    And I fill in "City" with "Brussels" in the "Event venue" region
    And I press "Create venue"
    # Online field group.
    When I press "Online"
    Then I select "Facebook" from "Online type"
    And I fill in "Start date" of "Online time" with the date "22-02-2019 02:30" in the timezone "Europe/Brussels"
    And I fill in "End date" of "Online time" with the date "22-02-2019 14:30" in the timezone "Europe/Brussels"
    And I fill in "Online description" with "Online description text"
    And I fill in "URL" with "http://ec.europa.eu/2" in the "Online link" region
    And I fill in "Link text" with "Online link" in the "Online link" region

    And I select "As planned" from "Status"
    And I fill in "Status description" with "Status description message"
    And I fill in "Languages" with "Hungarian"
    And I fill in "Who should attend" with "Types of audiences that this event targets"

    # Organiser field group.
    When I uncheck "Organiser is internal"
    Then I fill in "Organiser name" with "Organiser name"

    # Event website field group.
    And I fill in "URL" with "http://ec.europa.eu" in the "Website" region
    And I fill in "Link text" with "Website" in the "Website" region

    # Add a social media link
    And I fill in "URL" with "http://twitter.com" in the "Social media links" region
    And I fill in "Link text" with "Twitter" in the "Social media links" region
    And I select "Twitter" from "Link type"

    # Add a media item.
    And I fill in "Use existing media" with "Media image" in the "Event media" region

    # Description field group.
    And I fill in "Use existing media" with "Euro with miniature figurines" in the "Description" region
    And I fill in "Featured media legend" with "Euro with miniature figurines"
    And I fill in "Full text" with "Full text paragraph"

    # Report field group.
    When I press "Event report"
    And I fill in "Report text" with "Report text paragraph"
    And I fill in "Summary for report" with "Report summary text"
    And I fill in "URL" with "<front>" in the "Event report" region
    And I fill in "Link text" with "More media items" in the "Event report" region
    And I fill in "Other links to further media items" with "More links to media items" in the "Event report" region

    # Event contact field group.
    When I press "Add new contact"
    And I wait for AJAX to finish
    Then I fill in "Name" with "Name of the event contact" in the "Event contact" region
    And I fill in "Organisation" with "Event contact organisation" in the "Event contact" region
    And I fill in "Body text" with "Event contact body text" in the "Event contact" region
    And I fill in "Website" with "http://www.example.com/event_contact" in the "Event contact" region
    And I fill in "Email" with "test@example.com" in the "Event contact" region
    And I fill in "Phone number" with "0488779033" in the "Event contact" region
    And I fill in "Mobile number" with "0488779034" in the "Event contact" region
    And I fill in "Fax number" with "0488779035" in the "Event contact" region
    And I select "Hungary" from "Country" in the "Event contact" region
    And I wait for AJAX to finish
    And I fill in "Street address" with "Back street 3" in the "Event contact" region
    And I fill in "Postal code" with "9000" in the "Event contact" region
    And I fill in "City" with "Budapest" in the "Event contact" region
    And I fill in "Office" with "Event contact office" in the "Event contact" region
    And I fill in "URL" with "mailto:example@email.com" in the "Contact social media links" region
    And I fill in "Link text" with "Event contact social link email" in the "Contact social media links" region
    And I fill in "Media item" with "Contact image" in the "Event contact" region
    And I fill in "Caption" with "Event contact caption" in the "Event contact" region
    And I fill in "Press contacts" with "http://example.com/press_contacts" in the "Event contact" region
    And I fill in "URL" with "https://www.example.com/link" in the "Contact link" region
    And I fill in "Link text" with "Contact link" in the "Contact link" region

    # The event programme field group.
    When I press "Add new Programme"
    And I wait for AJAX to finish
    And I fill in "Name" with "Event programme" in the "Event programme" region
    And I fill in "Description" with "Event programme description" in the "Event programme" region
    And I fill in "Start date" of "Start/end date" with the date "21-10-2021 02:15" in the timezone "Europe/Brussels"
    And I fill in "End date" of "Start/end date" with the date "21-10-2021 14:15" in the timezone "Europe/Brussels"

    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"
    And I fill in "Responsible department" with "Audit Board of the European Communities"
    And I fill in "Teaser" with "Event teaser"
    When I press "Save"

    Then I should see "My Event item"
    And I should see "Full text paragraph"
    And I should see "Thu, 02/21/2019 - 02:15"
    And I should see "Thu, 02/21/2019 - 14:15"
    And I should see "Info days"
    And I should see "Hungarian"
    And I should see "Types of audiences that this event targets"
    And I should see "As planned"
    And I should see "Status description message"
    And I should see the link "Website"
    And I should see the link "Twitter"
    And I should see "Facebook"
    And I should see "Media image"
    And I should see "Online description text"
    And I should see "Fri, 02/22/2019 - 02:30"
    And I should see "Fri, 02/22/2019 - 14:30"
    And I should see the link "Online link"
    And I should see "Organiser name"
    And I should see "Description summary text"
    And I should see "Euro with miniature figurines"
    And I should see "Report summary text"
    And I should see "Report text paragraph"
    And I should see the link "More media items"
    And I should see "More links to media items"
    And I should see the link "http://example.com"
    And I should see "Open"
    And I should see "Sat, 02/23/2019 - 02:30"
    And I should see "Sat, 02/23/2019 - 14:30"
    And I should see "Free of charge"
    And I should see "100 seats"
    # Venue entity values.
    And I should see the text "Name of the venue"
    And I should see the text "Capacity of the venue"
    And I should see the text "Room of the venue"
    And I should see the text "Rue belliard 28"
    And I should see the text "1000 Brussels"
    And I should see the text "Belgium"
    # Event contact values.
    And I should see the text "Name of the event contact"
    And I should see the text "Event contact body text"
    And I should see the text "Event contact organisation"
    And I should see the link "http://www.example.com/event_contact"
    And I should see the text "test@example.com"
    And I should see the text "0488779033"
    And I should see the text "0488779034"
    And I should see the text "0488779035"
    And I should see the text "Back street 3"
    And I should see the text "Budapest"
    And I should see the text "9000"
    And I should see the text "Hungary"
    And I should see the link "Event contact social link email"
    And I should see the text "Event contact office"
    And I should see the link "Contact image"
    And I should see the text "Event contact caption"
    And I should see the link "http://example.com/press_contacts"
    And I should see the link "Contact link"
    # Event programme values.
    And I should see the text "Event programme"
    And I should see the text "Event programme description"
    And I should see "Thu, 10/21/2021 - 02:15"
    And I should see "Thu, 10/21/2021 - 14:15"

  @javascript @av_portal
  @batch2
  Scenario: As an editor when I create an Event node, the required fields are correctly marked when not filled in.
    Given I am logged in as a user with the "create oe_event content, access content, edit own oe_event content, view published skos concept entities" permission
    # Create a "Media AV portal photo".
    And the following AV Portal photos:
      | url                                                        |
      | https://audiovisual.ec.europa.eu/en/photo/P-038924~2F00-15 |

    # Create a "Event" content.
    When I visit "the Event creation page"
    And I fill in "Page title" with "My Event item"
    And I select "As planned" from "Status"
    And I fill in "Languages" with "Hungarian"
    And I select "Info days" from "Type"
    And I fill in "Subject" with "EU financing"
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"
    And I fill in "Responsible department" with "Audit Board of the European Communities"
    And I fill in "Teaser" with "Event teaser"

    # Make sure that one value is saved, even if both are filled in.
    When I check "Organiser is internal"
    And I fill in "Internal organiser" with "Audit Board of the European Communities"
    And I uncheck "Organiser is internal"
    And I fill in "Organiser name" with "Organiser external"
    And I press "Save"
    Then I should see "Organiser name Organiser external"
    But I should not see "Internal organiser Audit Board of the European Communities"

    When I click "Edit"
    And I uncheck "Organiser is internal"
    And I fill in "Organiser name" with "Organiser external"
    And I check "Organiser is internal"
    And I fill in "Internal organiser" with "Audit Board of the European Communities"
    And I press "Save"
    Then I should not see "Organiser name Organiser external"
    But I should see "Internal organiser Audit Board of the European Communities"

    # Make sure that validation of the Online fields group works as expected.
    When I click "Edit"
    And I press "Online"
    And I select "Facebook" from "Online type"
    And I press "Save"
    Then I should see the following error messages:
      | error messages                 |
      | Online time field is required. |
      | Online link field is required. |
    # Make sure that errors related to the Online fields are fixed.
    And I fill in "Start date" of "Online time" with the date "22-02-2019 02:30" in the timezone "Europe/Brussels"
    And I fill in "End date" of "Online time" with the date "22-02-2019 14:30" in the timezone "Europe/Brussels"
    And I fill in "Online description" with "Online description text"
    And I fill in "URL" with "http://ec.europa.eu/2" in the "Online link" region
    And I fill in "Link text" with "Online link" in the "Online link" region
    And I press "Save"
    Then I should see the following success messages:
      | success messages                      |
      | Event My Event item has been updated. |

    # Make sure that validation of the Registration fields group works as expected.
    When I click "Edit"
    And I press "Registration"
    And I fill in "Start date" of "Registration date" with the date "23-02-2019 02:15" in the timezone "Europe/Brussels"
    And I fill in "End date" of "Registration date" with the date "23-02-2019 14:15" in the timezone "Europe/Brussels"
    And I fill in "Registration capacity" with "100"
    And I press "Save"
    Then I should see the following error messages:
      | error messages                      |
      | Registration URL field is required. |
    # Make sure that errors related to the Registration fields are fixed.
    And I fill in "Registration URL" with "http://example.com"
    And I press "Save"
    Then I should see the following success messages:
      | success messages                      |
      | Event My Event item has been updated. |

    # Make sure that validation of the Social media links works as expected.
    When I click "Edit"
    And I fill in "URL" with "htt://twitter.com" in the "Social media links" region
    And I fill in "Link text" with "Twitter" in the "Social media links" region
    And I select "Twitter" from "Link type"
    And I press "Save"
    Then I should see the following error messages:
      | error messages                      |
      | The path 'htt://twitter.com' is invalid. |
    # Make sure that errors related to the Social media links fields are fixed.
    And I fill in "URL" with "http://twitter.com" in the "Social media links" region
    And I press "Save"
    Then I should see the following success messages:
      | success messages                      |
      | Event My Event item has been updated. |

  @javascript
  @batch3
  Scenario: By removing venue and contact from the form only the reference is removed and the entities are not deleted.
    Given I am logged in as a user with the "create oe_event content, access content, edit any oe_event content, view published skos concept entities, manage corporate content entities" permission
    And the following Default Venue entity:
      | Name | A venue |
    And the following General Contact entity:
      | Name | A general contact |
    And the following Event Content entity:
      | Title               | Event demo page          |
      | Type                | Exhibitions              |
      | Introduction        | Event introduction text  |
      | Languages           | Valencian                |
      | Start date          | 2019-02-21 02:21:00      |
      | End date            | 2019-02-21 14:21:00      |
      | Status              | as_planned               |
      | Teaser              | Event teaser             |
      | Venue               | A venue                  |
      | Contact             | A general contact        |
    When I am visiting the "Event demo page" content
    And I click "Edit"
    And I press "Remove" in the "Event venue" region
    Then I should see "Are you sure you want to remove A venue?"
    When I press "Remove" in the "Event venue" region
    And I press "Remove" in the "Event contact" region
    Then I should see "Are you sure you want to remove A general contact?"
    When I press "Remove" in the "Event contact" region
    And I press "Save"
    Then I should see "Event Event demo page has been updated."
    And the Default Venue entity with title "A venue" exists
    And the General Contact entity with title "A general contact" exists
