@api @event
  Feature: context API testing feature.
    Test entity aware hooks in context classes.

    Scenario: Test EventContentContext and BeforeParseEntityFields alterations are done.
      Given I am logged in as a user with the "edit any oe_event content, access content, view published skos concept entities, manage corporate content entities" permission
      And the following image:
        | name              | file            |
        | Image placeholder | example_1.jpeg  |
      When the following Event Content entity:
        | Title                   | My event                  |
        | Type                    | Exhibitions               |
        | Introduction            | Short intro               |
        | Description summary     | Event description summary |
        | Description             | Event description         |
        | Featured media legend   | Event media legend        |
        | Summary for report      | Event summary for report  |
        | Report text             | Report text               |
        | Registration start date | 2020-03-01 12:30:00       |
        | Registration end date   | 2020-03-10 18:30:00       |
        | Start date              | 2020-06-15 12:30:00       |
        | End date                | 2020-06-20 18:30:00       |
        | Registration URL        | http://example.com        |
        | Registration capacity   | Event capacity            |
        | Entrance fee            | 1234                      |
        | Online type             | facebook                  |
        | Online time start       | 2019-02-21 09:15:00                                           |
        | Online time end         | 2019-02-21 14:00:00                                           |
        | Online description      | Event online description                                      |
        | Online link             | uri: http://ec.europa.eu/info - title: Info                   |
        | Languages               | Valencian                                                     |
        | Organiser is internal   | Yes                                                           |
        | Internal organiser      | Directorate-General for Informatics                           |
        | Status                  | as_planned                                                    |
        | Event website           | uri: https://ec.europa.eu/inea/ - title: INEA                 |
        | Social media links      | uri: http://example.com - title: Twitter - link_type: twitter |
        | Featured media          | Image placeholder                                             |

      When I visit node "My event" edit page
      Then I should see the text "My event"
      And I should see the text "Exhibitions"
      And I should see the text "Short intro"
      And I should see the text "Event description summary"
      And I should see the text "Event description"
#      And I should see the text "Event media legend"
      And I should see the text "Event summary for report"
      And I should see the text "Report text"
      And I should see the text "http://example.com"
#      And I should see the text "Event capacity"
      And I should see the text "1234"
      And I should see the text "Facebook"
      And I should see the text "Event online description"
      And I should see the text "Info"
#      And I should see the text "http://ec.europa.eu/info"
#      And I should see the text "Valencian"
#      And I should see the text "Directorate-General for Informatics"
      And I should see the text "As planned"
      And I should see the text "INEA"
      And I should see the text "http://example.com"
      And I should see the text "Twitter"
#      And I should see the text "Image placeholder"
      And "15 Jun 2020 12 30" is selected for "Start date" of "Event date"
#    'Organiser name' => 'oe_event_organiser_name',
      # @TODO: finish checking daterange fields, check organiser name, see why the commented out ones fail.
      # @TODO: check venue & organiser.

    Scenario: Test ProjectContentContext and BeforeParseEntityFields alterations are done.
      Given I am logged in as a user with the "edit any oe_project content, access content, view published skos concept entities, manage corporate content entities" permission
      # @TODO: finish this.
