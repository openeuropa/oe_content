@api
Feature: Common fields across the content types
  In order to add content
  As an editor
  I should have common fields configured the same for each content type.

  Scenario Outline: Fields have the correct description.
    Given I am logged in as a user with the "create oe_event content, create oe_news content, create oe_page content, create oe_policy content, create oe_project content, create oe_publication content, access content" permission
    When I visit "the <content_type> creation page"
    Then I should see the text "The ideal length is 50 to 60 characters including spaces." in the "title form element"
    And I should see the text "If it must be longer, make sure you fill in a shorter version in the Alternative title field." in the "title form element"
    And I should see the text "Use this field to create an alternative title for use in the URL and in list views." in the "alternative title form element"
    And I should see the text "If the page title is longer than 60 characters, you can add a shorter title here." in the "alternative title form element"
    And I should see the text "A short overview of the information on this page. The teaser will be displayed in list views and search engine results, not on the page itself." in the "teaser form element"
    And I should see the text "Limited to 150 characters for SEO purposes." in the "teaser form element"
    And I should see the text "A short text that will be displayed in the blue header, below the page title." in the "summary form element"
    And I should see the text "This should be a brief summary of the content on the page that tells the user what information they will find on this page." in the "summary form element"
    And I should see the text "The topics mentioned on this page. These will be used by search engines and dynamic lists to determine their relevance to a user." in the "subject form element"
    And I should see the text "Add a link to this field to automatically redirect the user to a different page. Use this to prevent duplication of content." in the "redirect link form element"
    And I should see the text "This is not the writer of the content, but the subject matter expert responsible for keeping this content up to date." in the "content owner form element"

    Examples:
      | content_type |
      | Event        |
      | Page         |
      | News         |
      | Policy       |
      | Publication  |
