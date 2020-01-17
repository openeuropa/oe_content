@api
Feature: Publication content creation
  In order to have publications on the site
  As an editor
  I need to be able to create and see publication items

  @cleanup:media @cleanup:file
  Scenario: Creation of a Publication content through the UI.
    Given I am logged in as a user with the "create oe_publication content, access content, edit own oe_publication content, view published skos concept entities, create document media" permission
    # Create a "Document".
    When I go to "the document creation page"
    Then I should see the heading "Add Document"
    When I fill in "Name" with "My Document 1"
    And I attach the file "sample.pdf" to "File"
    And I press "Save"
    # Create a "Publication" content.
    And I visit "the Publication creation page"
    And I fill in "Page title" with "My Publication item"
    And I fill in "Introduction" with "Summary text"
    And I fill in "Teaser" with "Teaser text"
    And I fill in "Subject" with "financing"
    And I fill in "Publication date" with the date "2019-02-21"
    And I fill in "Use existing media" with "My Document 1"
    And I fill in "Type" with "Acknowledgement receipt"
    And I fill in "Responsible department" with "European Patent Office"
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"
    And I fill in "Redirect link" with "http://example.com"
    And I fill in "Navigation title" with "Navi title"
    And I fill in "Alternative title" with "Shorter title"
    When I press "Save"
    Then I should see "My Publication item"
    And I should see "sample.pdf"
    And I should not see "Acknowledgement receipt"
    And I should not see "Summary text"
    And I should not see "Navi title"
    And I should not see "Shorter title"
    And I should not see "Teaser text"
    And I should not see the link "financing"
    And I should not see the link "European Patent Office"

  @javascript
  Scenario: Length limited fields are truncating characters exceeding the configured limit.
    Given I am logged in as a user with the "create oe_publication content, access content, edit own oe_publication content, view published skos concept entities" permission
    When I visit "the Publication creation page"
    Then I should see the text "Content limited to 170 characters, remaining: 170" in the "title form element"
    And I should see the text "Content limited to 250 characters, remaining: 250" in the "summary form element"
    When I fill in "Page title" with "My Publication"
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"
    And I fill in "Teaser" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin eu hendrerit lacus, vitae bibendum odio. Fusce orci purus, hendrerit a magna at nullam. Text to remove"
    And I fill in "Introduction" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas felis leo, lobortis non eros in, consequat tempor est. Praesent sit amet sem eleifend, cursus arcu ac, eleifend nunc. Integer et orci sagittis, volutpat felis sit amet, tincidunt amet. Text to remove"
    And I fill in "Subject" with "financing"
    And I fill in "Responsible department" with "European Patent Office"
    And I press "Save"
    # We assert that the extra characters are actually truncated from the end of the string.
    Then I should not see "The text to remove."

  Scenario: Fields have the correct description.
    Given I am logged in as a user with the "create oe_publication content, access content, edit own oe_publication content, view published skos concept entities" permission
    When I visit "the Publication creation page"
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
