@api
Feature: Person content creation
  In order to have "Person" on the site
  As an editor
  I need to be able to create and see oe_person items

  @javascript
  Scenario: Creation of a Person content through the UI.
    Given I am logged in as a user with the "create oe_person content, access content, edit own oe_person content, view published skos concept entities, manage corporate content entities" permission
    And the following images:
      | name          | file           | alt                |
      | Image 1       | example_1.jpeg | Alternative text 1 |
      | Image 2       | example_1.jpeg | Alternative text 2 |
      | Contact image | example_1.jpeg | Alternative text 4 |
    And the following document:
      | name          | file         |
      | My Document 1 | sample.pdf   |
      | My Document 2 | document.pdf |
    And the following "Organisation" Content entity:
      | Title             | Organisation demo page         |
      | Introduction      | Organisation introduction text |
      | Acronym           | Organisation acronym           |
      | Body text         | Organisation body text         |
      | Organisation type | EU organisation                |
      | EU organisation   | Directorate-General for Budget |
    # Create a "person" content, mandatory fields first.
    And I visit "the Person creation page"
    And I fill in "Title" with "My person item"
    And I fill in "Subject" with "financing"
    And I fill in "Teaser" with "Teaser text"
    And I select "EU institutions related person" from "What type of person are you adding?"
    And I fill in "First name" with "Firstname"
    And I fill in "Last name" with "Lastname"
    And I should have the following options for the "Gender" select:
      | - Select a value -  |
      | female              |
      | male                |
      | not stated          |
    And I select "not stated" from "Gender"
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"
    And I press "Save"
    Then I should see "My person item"
    And I should see the link "financing"
    And I should see "Teaser text"
    Then I should see "EU institutions related person"
    Then I should see "Firstname"
    Then I should see "Lastname"
    Then I should see "not stated"
    And I should see the link "Committee on Agriculture and Rural Development"
    # Optional fields.
    When I click "Edit"
    And I fill in "Introduction" with "Summary text"
    And I fill in "Navigation title" with "Navi title"
    And I fill in "Alternative title" with "Shorter title"
    And I fill in "Displayed name" with "Altered name"
    And I fill in "Use existing media" with "Image 1" in the "Portrait photo" region
    And I fill in "Use existing media" with "Image 2" in the "Person Media" region
    And I fill in "Departments" with "European Patent Office"
    And I fill in "Transparency introduction" with "transparency-introduction text"
    And I fill in "URL" with "http://transparency.example.com" in the "Transparency links" region
    And I fill in "Link text" with "Example link" in the "Transparency links" region
    And I fill in "Biography introduction" with "Bio-intro"
    And I fill in "Label" with "Label 1" in the "first" "Biography" field element
    And I fill in "Title" with "Title 1" in the "first" "Biography" field element
    And I fill in "Content" with "Body 1" in the "first" "Biography" field element
    And I press "Add another item" in the "Biography" region
    And I fill in "Label" with "Label 2" in the "second" "Biography" field element
    And I fill in "Title" with "Title 2" in the "second" "Biography" field element
    And I fill in "Content" with "Body 2" in the "second" "Biography" field element
    And I press "Add another item" in the "Biography" region
    And I fill in "Label" with "Label 3" in the "third" "Biography" field element
    And I fill in "Title" with "Title 3" in the "third" "Biography" field element
    And I fill in "Content" with "Body 3" in the "third" "Biography" field element
    And I fill in "Use existing media" with "My Document 1" in the "CV upload" region
    And I fill in "Declaration of interests introduction" with "declaration text"
    And I fill in "Use existing media" with "My Document 2" in the "Declaration of interests file" region
    And I fill in "URL" with "http://twitter.com" in the "Social media links" region
    And I fill in "Link text" with "Twitter" in the "Social media links" region
    And I select "Twitter" from "Link type"
    And I fill in "Redirect link" with "http://example.com"
    # Contact field.
    When I press "Add new contact"
    And I wait for AJAX to finish
    Then I fill in "Name" with "Name of the contact" in the "Person contact" region
    And I fill in "Organisation" with "Person contact organisation" in the "Person contact" region
    And I fill in "Body text" with "Person contact body text" in the "Person contact" region
    And I fill in "Website" with "http://www.example.com/person_contact" in the "Person contact" region
    And I fill in "Email" with "person_contact@example.com" in the "Person contact" region
    And I fill in "Phone number" with "0488779033" in the "Person contact" region
    And I fill in "Mobile number" with "0488779034" in the "Person contact" region
    And I fill in "Fax number" with "0488779035" in the "Person contact" region
    And I select "Hungary" from "Country" in the "Person contact" region
    And I wait for AJAX to finish
    And I fill in "Street address" with "Person contact street" in the "Person contact" region
    And I fill in "Postal code" with "9000" in the "Person contact" region
    And I fill in "City" with "Budapest" in the "Person contact" region
    And I fill in "Office" with "Person contact office" in the "Person contact" region
    And I fill in "URL" with "mailto:person_contact_social@example.com" in the "Contact social media links" region
    And I fill in "Link text" with "Person contact social link email" in the "Contact social media links" region
    And I fill in "Media item" with "Contact image" in the "Person contact" region
    And I fill in "Caption" with "Person contact caption" in the "Person contact" region
    And I fill in "Press contacts" with "http://example.com/press_contacts" in the "Person contact" region
    When I press "Save"
    Then I should see "My person item"
    And I should see "Navi title"
    And I should see "Shorter title"
    And I should see "Altered name"
    And I should see "Image 1"
    And I should see "Image 2"
    And I should see "European Patent Office"
    And I should see "transparency-introduction text"
    And I should see "Bio-intro"
    And I should see "Label 1"
    And I should see "Title 1"
    And I should see "Body 1"
    And I should see "Label 2"
    And I should see "Title 2"
    And I should see "Body 2"
    And I should see "Label 3"
    And I should see "Title 3"
    And I should see "Body 3"
    And I should see "My Document 1"
    And I should see "declaration text"
    And I should see "My Document 2"
    And I should see the link "Twitter"
    And I should see "http://example.com"
    And I should see the link "Example link"
    # Assert person contact values.
    And I should see the text "Name of the contact"
    And I should see the text "Person contact body text"
    And I should see the text "Person contact organisation"
    And I should see the link "http://www.example.com/person_contact"
    And I should see the text "person_contact@example.com"
    And I should see the text "0488779033"
    And I should see the text "0488779034"
    And I should see the text "0488779035"
    And I should see the text "Person contact street"
    And I should see the text "Budapest"
    And I should see the text "9000"
    And I should see the text "Hungary"
    And I should see the link "Person contact social link email"
    And I should see the text "Person contact office"
    And I should see the link "Contact image"
    And I should see the text "Person contact caption"
    And I should see the link "http://example.com/press_contacts"
    When I click "Edit"
    And I select "Person not part of the EU institutions" from "What type of person are you adding?"
    And I fill in "Organisation" with "Organisation demo page"
    When I press "Save"
    Then I should see "Organisation demo page"
    And I should not see the link "European Patent Office"

  @javascript
  Scenario: Length limited fields are truncating characters exceeding the configured limit.
    Given I am logged in as a user with the "create oe_person content, access content, edit own oe_person content, view published skos concept entities, manage corporate content entities" permission
    When I visit "the Person creation page"
    Then I should see the text "Content limited to 170 characters, remaining: 170" in the "title form element"
    And I should see the text "Content limited to 150 characters, remaining: 150" in the "teaser form element"
    And I should see the text "Content limited to 250 characters, remaining: 250" in the "summary form element"
    And I should see the text "Content limited to 170 characters, remaining: 170" in the "alternative title form element"
    And I fill in "Title" with "My person item"
    And I fill in "Subject" with "financing"
    And I fill in "Introduction" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas felis leo, lobortis non eros in, consequat tempor est. Praesent sit amet sem eleifend, cursus arcu ac, eleifend nunc. Integer et orci sagittis, volutpat felis sit amet, tincidunt amet. Text to remove"
    And I fill in "Teaser" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin eu hendrerit lacus, vitae bibendum odio. Fusce orci purus, hendrerit a magna at nullam. Text to remove"
    And I select "EU institutions related person" from "What type of person are you adding?"
    And I fill in "First name" with "Firstname"
    And I fill in "Last name" with "Lastname"
    And I select "male" from "Gender"
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"
    And I press "Save"
    # We assert that the extra characters are actually truncated from the end of the string.
    Then I should not see "The text to remove."
