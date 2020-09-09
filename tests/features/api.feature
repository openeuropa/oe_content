@api @event
  Feature: context API testing feature.
    Test entity aware hooks in context classes.

    @javascript
    Scenario: Test EventContentContext where BeforeParseEntityFields alterations are done.
      Given I am logged in as a user with the "edit any oe_event content, access content, view published skos concept entities, manage corporate content entities" permission
      And the following image:
        | name              | file            |
        | Image placeholder | example_1.jpeg  |
      And the following Event Content entity:
        | Title                   | My event                                                      |
        | Type                    | Exhibitions                                                   |
        | Introduction            | Short intro                                                   |
        | Description summary     | Event description summary                                     |
        | Description             | Event description                                             |
        | Featured media          | Image placeholder                                             |
        | Featured media legend   | Event media legend                                            |
        | Summary for report      | Event summary for report                                      |
        | Report text             | Report text                                                   |
        | Registration start date | 2020-03-01 12:30:00                                           |
        | Registration end date   | 2020-03-10 18:30:00                                           |
        | Start date              | 2020-06-15 12:30:00                                           |
        | End date                | 2020-06-20 18:30:00                                           |
        | Registration URL        | http://example.com                                            |
        | Registration capacity   | Event capacity                                                |
        | Entrance fee            | 1234                                                          |
        | Online type             | facebook                                                      |
        | Online time start       | 2019-02-21 09:15:00                                           |
        | Online time end         | 2019-02-21 14:00:00                                           |
        | Online description      | Event online description                                      |
        | Online link             | uri: http://ec.europa.eu/info - title: Info site              |
        | Languages               | Valencian                                                     |
        | Organiser is internal   | Yes                                                           |
        | Internal organiser      | Directorate-General for Informatics                           |
        | Status                  | as_planned                                                    |
        | Event website           | uri: https://ec.europa.eu/inea/ - title: INEA                 |
        | Social media links      | uri: http://example.com - title: Twitter - link_type: twitter |

      When I visit node "My event" edit page
      Then I should see the text "My event"
      And I should see the text "Exhibitions"
      And I should see the text "Short intro"
      And I should see the text "Event description summary"
      And I should see the text "Event description"
      And the "Featured media legend" field should contain "Event media legend"
      And I press "Event report"
      And I should see the text "Event summary for report"
      And I should see the text "Report text"
      And I press "Registration"
      And I should see the text "http://example.com"
      And the "Registration capacity" field should contain "Event capacity"
      And the "Entrance fee" field should contain "1234"
      And I should see the text "Facebook"
      And datetime "1 Mar 2020 12 30" is selected for "Start date" of "Registration date"
      And datetime "10 Mar 2020 18 30" is selected for "End date" of "Registration date"
      And I press "Online"
      And the "Online description" field should contain "Event online description"
      And datetime "21 Feb 2019 9 15" is selected for "Start date" of "Online time"
      And datetime "21 Feb 2019 14 00" is selected for "End date" of "Online time"
      And the "oe_event_online_link[0][title]" field should contain "Info site"
      And the "oe_event_online_link[0][uri]" field should contain "http://ec.europa.eu/info"
      And the "oe_event_languages[0][target_id]" field should contain "Valencian (http://publications.europa.eu/resource/authority/language/0D0)"
      And the "Internal organiser" field should contain "Directorate-General for Informatics (http://publications.europa.eu/resource/authority/corporate-body/DIGIT)"
      And I should see the text "As planned"
      And I should see the text "INEA"
      And I should see the text "http://example.com"
      And I should see the text "Twitter"
      And the "oe_event_featured_media[0][target_id]" field contains "Image placeholder"
      And datetime "15 Jun 2020 12 30" is selected for "Start date" of "Event date"
      And datetime "20 Jun 2020 18 30" is selected for "End date" of "Event date"
      And the "oe_subject[0][target_id]" field should contain "financing (http://data.europa.eu/uxp/1000)"
      And the "oe_author[0][target_id]" field should contain "Directorate-General for Communication (http://publications.europa.eu/resource/authority/corporate-body/COMMU)"
      And the "oe_content_content_owner[0][target_id]" field should contain "Directorate-General for Communication (http://publications.europa.eu/resource/authority/corporate-body/COMMU)"

      # Add related entities, such as venues and contacts and reload the page.
      When the following Default Venue entity:
        | Name     | DIGIT                                                                                      |
        | Address  | country_code: BE - locality: Brussels - address_line1: Rue Belliard 28 - postal_code: 1000 |
        | Capacity | 12 people                                                                                  |
        | Room     | B-28 03/A150                                                                               |
      And the following Press Contact entity:
        | Name               | A press contact                                                                          |
        | Address            | country_code: HU - locality: Szeged - address_line1: Press contact 1 - postal_code: 6700 |
        | Email              | press@example.com                                                                        |
        | Phone number       | +32477777778                                                                             |
        | Social media links | uri: http://facebook.com - title: Facebook - link_type: facebook                         |
      And the following General Contact entity:
        | Name               | A general contact                                                                            |
        | Address            | country_code: HU - locality: Budapest - address_line1: General contact 1 - postal_code: 1011 |
        | Email              | general@example.com                                                                          |
        | Phone number       | +32477792933                                                                                 |
        | Social media links | uri: http://instagram.com - title: Instagram - link_type: instagram                          |
      And the Event Content "My event" is updated as follows:
        | Venue                 | DIGIT                               |
        | Contact               | A press contact, A general contact  |
        | Organiser is internal | No                                  |
        | Organiser name        | Name of the organiser               |
      And I reload the page
      And the "Organiser name" field should contain "Name of the organiser"

      # Assert contacts.
      And I press "Edit" in the "A general contact" row
      And I wait for AJAX to finish
      And the "Phone number" field should contain "+32477792933"
#      And "HU" should be selected for "Country" select in the "Event contact" region
#      And the "Country" field should contain "HU"
#      And the "oe_event_contact[form][inline_entity_form][entities][0][form][oe_address][0][address][locality]" field should contain "Budapest"
#      And I fill in "Office" with "Office" in the "Event contact" region
#      And I take a screenshot
#      And the "City" field should contain "Budapest"


  #, General contact 1, 1011, Hungary""
#      And I should see the text "general@example.com"
#      And I should see the link "Instagram"

#    Scenario: Test ProjectContentContext and BeforeParseEntityFields alterations are done.
#      Given I am logged in as a user with the "edit any oe_project content, access content, view published skos concept entities, manage corporate content entities" permission
      # @TODO: finish this.
