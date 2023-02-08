@api
Feature: News content creation
  In order to have news on the site
  As an editor
  I need to be able to create and see news items

  @javascript @cleanup:media @av_portal
  @batch1
  Scenario: Creation of a News content through the UI.
    Given I am logged in as a user with the "create oe_news content, access content, edit own oe_page content, view published skos concept entities, create av_portal_photo media, manage corporate content entities" permission
    And the following images:
      | name          | file           | alt                            |
      | Contact image | example_1.jpeg | Contact image alternative text |
    # Create a "Media AV portal photo".
    And I visit "the AV Portal photo creation page"
    And I fill in "Media AV Portal Photo" with "https://audiovisual.ec.europa.eu/en/photo/P-038924~2F00-15"
    And I press "Save"
    # Create a "News" content.
    And I visit "the News creation page"
    And I fill in "Page title" with "My News item"
    And the available options in the "News type" select should be:
      | Commissioners’ weekly activities |
      | Daily news                       |
      | Factsheet                        |
      | General publications             |
      | Minutes                          |
      | News announcement                |
      | News article                     |
      | News blog                        |
      | Newsletter                       |
      | Press release                    |
      | Provisional data                 |
      | Questions and answers            |
      | Schedule                         |
      | Speech                           |
      | Statement                        |
      | Supplementary information        |
    And I fill in "Introduction" with "Summary text"
    And I fill in "Teaser" with "Teaser text"
    And I fill in "Body text" with "Body text"
    And I fill in "Location" with "Budapest"
    And I fill in "Reference" with "Reference text"
    And I fill in "Publication date" with the date "21-02-2019"
    And I fill in "Last update date" with the date "29-07-2021"
    And I fill in "Subject" with "financing"
    And I fill in "Author" with "European Patent Office"
    And I fill in "Related department" with "ACP–EU Joint Assembly"
    # Reference the media photo to the news item.
    And I fill in "Use existing media" with "Euro with miniature figurines"
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"
    And I fill in "Redirect link" with "http://example.com"
    And I fill in "Navigation title" with "Navi title"
    And I fill in "Alternative title" with "Shorter title"
    And I fill in "URL" with "http://example.com" in the "Related links" region
    And I fill in "Link text" with "My link" in the "Related links" region
    And I fill in "URL" with "https://www.example.com" in the "News sources" region
    And I fill in "Link text" with "Source link text" in the "News sources" region
    # News contact field.
    And I press "Add new contact"
    And I wait for AJAX to finish
    Then I fill in "Name" with "Name of the contact" in the "News contact" region
    And I fill in "Organisation" with "News contact organisation" in the "News contact" region
    And I fill in "Body text" with "News contact body text" in the "News contact" region
    And I fill in "Website" with "http://www.example.com/news_contact" in the "News contact" region
    And I fill in "Email" with "test@example.com" in the "News contact" region
    And I fill in "Phone number" with "0488779033" in the "News contact" region
    And I fill in "Mobile number" with "0488779034" in the "News contact" region
    And I fill in "Fax number" with "0488779035" in the "News contact" region
    And I select "Hungary" from "Country" in the "News contact" region
    And I wait for AJAX to finish
    And I fill in "Street address" with "Back street 3" in the "News contact" region
    And I fill in "Postal code" with "9000" in the "News contact" region
    And I fill in "City" with "Budapest" in the "News contact" region
    And I fill in "Office" with "News contact office" in the "News contact" region
    And I fill in "URL" with "mailto:example@email.com" in the "Contact social media links" region
    And I fill in "Link text" with "News contact social link email" in the "Contact social media links" region
    And I fill in "Media item" with "Contact image" in the "News contact" region
    And I fill in "Caption" with "News contact caption" in the "News contact" region
    And I fill in "Press contacts" with "http://example.com/press_contacts" in the "News contact" region
    And I fill in "URL" with "https://www.example.com/link" in the "Contact link" region
    And I fill in "Link text" with "Contact link" in the "Contact link" region
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"
    When I press "Save"
    # News contact values.
    Then I should see the text "Name of the contact"
    And I should see the text "News contact body text"
    And I should see the text "News contact organisation"
    And I should see the link "http://www.example.com/news_contact"
    And I should see the text "test@example.com"
    And I should see the text "0488779033"
    And I should see the text "0488779034"
    And I should see the text "0488779035"
    And I should see the text "Back street 3"
    And I should see the text "Budapest"
    And I should see the text "9000"
    And I should see the text "Hungary"
    And I should see the link "News contact social link email"
    And I should see the text "News contact office"
    And I should see the link "Contact image"
    And I should see the text "News contact caption"
    And I should see the link "http://example.com/press_contacts"
    And I should see the link "Contact link"
    # Assert the rest of the values.
    And I should see "My News item"
    And I should see the link "Source link text"
    And I should see the link "My link"
    And I should see "Reference text"
    And I should see "Shorter title"
    And I should see "Teaser text"
    And I should see "Summary text"
    But I should not see "Navi title"
    And I should not see the link "Budapest"
    And I should not see "Thu, 02/21/2019"
    And I should not see "Thu, 07/29/2021"
    And I should not see the link "financing"
    And I should not see the link "European Patent Office"

  @javascript
  @batch2
  Scenario: Length limited fields are truncating characters exceeding the configured limit.
    Given I am logged in as a user with the "create oe_news content, access content, edit own oe_news content, view published skos concept entities, create av_portal_photo media" permission
    When I visit "the News creation page"
    Then I should see the text "Content limited to 170 characters, remaining: 170" in the "title form element"
    And I should see the text "Content limited to 300 characters, remaining: 300" in the "teaser form element"
    And I should see the text "Content limited to 250 characters, remaining: 250" in the "summary form element"
    And I should see the text "Content limited to 170 characters, remaining: 170" in the "alternative title form element"
    When I fill in "Page title" with "My news"
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"
    And I fill in "Teaser" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin eu hendrerit lacus, vitae bibendum odio. Fusce orci purus, hendrerit a magna at nullam. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Lorem ipsum dolor sit amet Teaser. Text to remove"
    And I fill in "Introduction" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas felis leo, lobortis non eros in, consequat tempor est. Praesent sit amet sem eleifend, cursus arcu ac, eleifend nunc. Integer et orci sagittis, volutpat felis sit ametas Introduction. Text to remove"
    And I fill in "Alternative title" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin eu hendrerit lacus, vitae bibendum odio. Fusce orci purus, hendrerit a magna at nullamsa Alternative title. Text to remove"
    And I fill in "Body text" with "Body text"
    And I fill in "Subject" with "financing"
    And I fill in "Author" with "European Patent Office"
    And I press "Save"
    # We assert that the extra characters are actually truncated from the end of the string.
    Then I should not see "Text to remove"
    And I should see "ametas Introduction."
    And I should see "nullamsa Alternative title."
    And I should see "amet Teaser."

  @javascript
  @batch3
  Scenario: By removing contact from the form only the reference is removed and the contact is not deleted.
    Given I am logged in as a user with the "create oe_news content, access content, edit any oe_news content, view published skos concept entities, manage corporate content entities" permission
    And the following General Contact entity:
      | Name | A general contact |
    And the following News Content entity:
      | Title     | Test news         |
      | News type | News article      |
      | Body text | Some text         |
      | Reference | Some reference    |
      | Contacts  | A general contact |
      | Teaser    | Some teaser       |
    When I am visiting the "Test news" content
    And I click "Edit"
    And I press "Remove"
    Then I should see "Are you sure you want to remove A general contact?"
    When I press "Remove"
    And I press "Save"
    Then I should see "News Test news has been updated."
    And the General Contact entity with title "A general contact" exists
