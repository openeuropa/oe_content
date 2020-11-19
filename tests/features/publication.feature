@api
Feature: Publication content creation
  In order to have publications on the site
  As an editor
  I need to be able to create and see publication items

  @javascript
  Scenario: Creation of a Publication content through the UI.
    Given I am logged in as a user with the "create oe_publication content, access content, edit own oe_publication content, manage corporate content entities, view published skos concept entities" permission
    And the following documents:
    | name          | file       |
    | My Document 1 | sample.pdf |
    And the following images:
    | name         | file           | alt          |
    | Sample image | example_1.jpeg | example text |

    When I visit "the Publication creation page"
    And I fill in "Page title" with "My Publication item"
    And I fill in "Introduction" with "Summary text"
    And I fill in "Teaser" with "Teaser text"
    And I fill in "Subject" with "financing"
    And I set "Publication date" to the date "21-02-2019"
    And I fill in "Use existing media" with "My Document 1" in the "Documents" region
    And I fill in "Resource type" with "Acknowledgement receipt"
    And I fill in "Use existing media" with "Sample image" in the "Publication thumbnail" region
    And I fill in "Responsible department" with "European Patent Office"
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"
    And I fill in "Redirect link" with "http://example.com"
    And I fill in "Navigation title" with "Navi title"
    And I fill in "Alternative title" with "Shorter title"
    And I set "Last update date" to the date "04-11-2019"
    And I fill in "Body" with "Body text"
    And I fill in "Identifier code" with "123456789"
    And I fill in "Related department" with "European Labour Authority"
    And I fill in "Country" with "Hungary"

    # Publication contact field group.
    And I press "Add new contact"
    And I wait for AJAX to finish
    And I fill in "Name" with "Name of the publication contact" in the "Publication contact" region
    And I fill in "Organisation" with "Publication contact organisation" in the "Publication contact" region
    And I fill in "Body text" with "Publication contact body text" in the "Publication contact" region
    And I fill in "Website" with "http://www.example.com/publication_contact" in the "Publication contact" region
    And I fill in "Email" with "test@example.com" in the "Publication contact" region
    And I fill in "Phone number" with "0488779033" in the "Publication contact" region
    And I fill in "Mobile number" with "0488779034" in the "Publication contact" region
    And I fill in "Fax number" with "0488779035" in the "Publication contact" region
    And I select "Hungary" from "Country" in the "Publication contact" region
    And I wait for AJAX to finish
    And I fill in "Street address" with "Back street 3" in the "Publication contact" region
    And I fill in "Postal code" with "9000" in the "Publication contact" region
    And I fill in "City" with "Budapest" in the "Publication contact" region
    And I fill in "Office" with "Publication contact office" in the "Publication contact" region
    And I fill in "URL" with "mailto:example@email.com" in the "Contact social media links" region
    And I fill in "Link text" with "Publication contact social link email" in the "Contact social media links" region
    And I fill in "Media item" with "Sample image" in the "Publication contact" region
    And I fill in "Caption" with "Publication contact caption" in the "Publication contact" region
    And I fill in "Press contacts" with "http://example.com/press_contacts" in the "Publication contact" region

    And I press "Save"
    Then I should see "My Publication item"
    And I should see "sample.pdf"
    And I should see "Contact"
    And I should see "Body text"
    And I should see "123456789"
    And I should see "European Labour Authority"
    And I should see "Hungary"

    # Publication contact data display.
    And I should see the text "Name of the publication contact"
    And I should see the text "Publication contact body text"
    And I should see the text "Publication contact organisation"
    And I should see the link "http://www.example.com/publication_contact"
    And I should see the text "test@example.com"
    And I should see the text "0488779033"
    And I should see the text "0488779034"
    And I should see the text "0488779035"
    And I should see the text "Back street 3"
    And I should see the text "Budapest"
    And I should see the text "9000"
    And I should see the text "Hungary"
    And I should see the link "Publication contact social link email"
    And I should see the text "Publication contact office"
    And I should see the link "Sample image"
    And I should see the text "Publication contact caption"
    And I should see the link "http://example.com/press_contacts"

    And I should not see "Acknowledgement receipt"
    And I should not see "Summary text"
    And I should not see "Navi title"
    And I should not see "Shorter title"
    And I should not see "Teaser text"
    And I should not see the link "financing"
    And I should not see the link "European Patent Office"

  @javascript
  Scenario: Length limited fields are truncating characters exceeding the configured limit.
    Given I am logged in as a user with the "create oe_publication content, access content, edit own oe_publication content, view published skos concept entities, manage corporate content entities" permission
    And the following documents:
    | name          | file       |
    | My Document 1 | sample.pdf |

    When I visit "the Publication creation page"
    And I should see the text "Content limited to 170 characters, remaining: 170" in the "title form element"
    And I should see the text "Content limited to 250 characters, remaining: 250" in the "summary form element"
    And I should see the text "Content limited to 170 characters, remaining: 170" in the "alternative title form element"
    And I fill in "Page title" with "My Publication"
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"
    And I fill in "Teaser" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin eu hendrerit lacus, vitae bibendum odio. Fusce orci purus, hendrerit a magna at nullam. Text to remove"
    And I fill in "Introduction" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas felis leo, lobortis non eros in, consequat tempor est. Praesent sit amet sem eleifend, cursus arcu ac, eleifend nunc. Integer et orci sagittis, volutpat felis sit amet, tincidunt amet. Text to remove"
    And I fill in "Subject" with "financing"
    And I fill in "Responsible department" with "European Patent Office"
    And I fill in "Resource type" with "Acknowledgement receipt"
    And I fill in "Use existing media" with "My Document 1" in the "Documents" region
    And I press "Save"
    # We assert that the extra characters are actually truncated from the end of the string.
    Then I should not see "The text to remove."

