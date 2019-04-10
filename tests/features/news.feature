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
    And I fill in "Title" with "My News item"
    And I fill in "Summary" with "Summary text"
    And I fill in "Teaser" with "Teaser text"
    And I fill in "Body" with "Body text"
    And I fill in "Location" with "Budapest"
    And I fill in "Publication date" with the date "2019-02-21"
    And I fill in "Subject" with "financing"
    And I fill in "Author" with "European Patent Office"
    # Reference the media photo to the news item.
    And I fill in "Use existing media" with "Euro with miniature figurines"
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"
    And I fill in "Legacy link" with "http://example.com"
    And I fill in "Navigation title" with "Navi title"
    And I fill in "Short title" with "Shorter title"
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

  @javascript
  Scenario: Length limited fields are truncating characters exceeding the configured limit.
    Given I am logged in as a user with the "create oe_news content, access content, edit own oe_page content, view published skos concept entities, create av_portal_photo media" permission
    When I visit "the News creation page"
    Then I should see the text "Content limited to 150 characters, remaining: 150" in the "news Teaser form element"
    And I should see the text "Content limited to 250 characters, remaining: 250" in the "news Summary form element"
    When I fill in "Title" with "My news"
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"
    And I fill in "Teaser" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin eu hendrerit lacus, vitae bibendum odio. Fusce orci purus, hendrerit a magna at nullam. Text to remove"
    And I fill in "Summary" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas felis leo, lobortis non eros in, consequat tempor est. Praesent sit amet sem eleifend, cursus arcu ac, eleifend nunc. Integer et orci sagittis, volutpat felis sit amet, tincidunt amet. Text to remove"
    And I fill in "Body" with "Body text"
    And I fill in "Subject" with "financing"
    And I fill in "Author" with "European Patent Office"
    And I press "Save"
    # We assert that the extra characters are actually truncated from the end of the string.
    Then I should not see "The text to remove."

  @purl-linkit @javascript
  Scenario: Make sure that persistent url work with linkit module.
    Given I am logged in as a user with the "create oe_news content, access content, edit own oe_page content, view published skos concept entities, create av_portal_photo media, use text format base_html, view the administration theme" permission
    And the following languages are available:
      | languages |
      | en        |
      | fr        |
    When I visit "the News creation page"
    And I fill in "Title" with "News1"
    And I enter "Teaser text" in the "Teaser" wysiwyg editor
    And I enter "Summary text" in the "Summary" wysiwyg editor
    And I enter "Body text" in the "Body" wysiwyg editor
    And I fill in "Subject" with "financing"
    And I fill in "Author" with "European Patent Office"
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"
    And I press "Save"
    Then I should see "News1"
    #Create second node
    When I visit "the News creation page"
    And I fill in "Title" with "News2"
    And I enter "Teaser text" in the "Teaser" wysiwyg editor
    And I enter "Summary text" in the "Summary" wysiwyg editor
    And I insert link to "News1" through wysiwyg editor in "Body" field
    And I fill in "Subject" with "financing"
    And I fill in "Author" with "European Patent Office"
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"
    And I press "Save"
    Then I should see "News2"
    # Check link to first node
    And I should see link to "News1" node
    # Check link to first node with alias
    When I update alias of "News1" node to "/alias1"
    Then I should see updated link to "News1" on "News2" page
    # Check link to first node with alias for another language.
    When I update alias of "News1" node to "/alias1_fr" for "French"
    Then I should see updated link to "News1" on "French" version of "News2" page

