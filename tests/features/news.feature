@api
Feature: News content creation
  In order to have news on the site
  As an editor
  I need to be able to create and see news items

  @cleanup:media
  Scenario: Creation of a News content through the UI.
    Given I am logged in as a user with the "create oe_news content, access content, edit own oe_page content, view published skos concept entities, create av_portal_photo media" permission
    # Create a "Media AV portal photo".
    And I visit "the AV Portal photo creation page"
    And I fill in "Media AV Portal Photo" with "https://audiovisual.ec.europa.eu/en/photo/P-038924~2F00-15"
    And I press "Save"
    # Create a "News" content.
    And I visit "the News creation page"
    And I fill in "Page title" with "My News item"
    And I fill in "Introduction" with "Summary text"
    And I fill in "Teaser" with "Teaser text"
    And I fill in "Body text" with "Body text"
    And I fill in "Location" with "Budapest"
    And I fill in "Publication date" with the date "2019-02-21"
    And I fill in "Subject" with "financing"
    And I fill in "Author" with "European Patent Office"
    # Reference the media photo to the news item.
    And I fill in "Use existing media" with "Euro with miniature figurines"
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"
    And I fill in "Redirect link" with "http://example.com"
    And I fill in "Navigation title" with "Navi title"
    And I fill in "Alternative title" with "Shorter title"
    And I fill in "URL" with "http://example.com"
    And I fill in "Link text" with "My link"
    When I press "Save"
    Then I should see "My News item"
    And I should not see "Navi title"
    And I should not see "Shorter title"
    And I should not see "Summary text"
    And I should not see the link "Budapest"
    And I should not see "Thu, 02/21/2019"
    And I should not see "Teaser text"
    And I should not see the link "financing"
    And I should not see the link "European Patent Office"
    And I should see the link "My link"

  @javascript
  Scenario: Length limited fields are truncating characters exceeding the configured limit.
    Given I am logged in as a user with the "create oe_news content, access content, edit own oe_page content, view published skos concept entities, create av_portal_photo media" permission
    When I visit "the News creation page"
    Then I should see the text "Content limited to 170 characters, remaining: 170" in the "title form element"
    And I should see the text "Content limited to 150 characters, remaining: 150" in the "teaser form element"
    And I should see the text "Content limited to 250 characters, remaining: 250" in the "summary form element"
    When I fill in "Page title" with "My news"
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"
    And I fill in "Teaser" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin eu hendrerit lacus, vitae bibendum odio. Fusce orci purus, hendrerit a magna at nullam. Text to remove"
    And I fill in "Introduction" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas felis leo, lobortis non eros in, consequat tempor est. Praesent sit amet sem eleifend, cursus arcu ac, eleifend nunc. Integer et orci sagittis, volutpat felis sit amet, tincidunt amet. Text to remove"
    And I fill in "Body text" with "Body text"
    And I fill in "Subject" with "financing"
    And I fill in "Author" with "European Patent Office"
    And I press "Save"
    # We assert that the extra characters are actually truncated from the end of the string.
    Then I should not see "The text to remove."

  Scenario: Fields have the correct description.
    Given I am logged in as a user with the "create oe_news content, access content, edit own oe_page content, view published skos concept entities, create av_portal_photo media" permission
    When I visit "the News creation page"
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
