@api
Feature: Persistent URLs
  In order to use persistent urls on the site
  As an editor
  I need to be able to insert persistent urls in wysiwyg and see processed link

  @purl-linkit @javascript
  @batch2
  Scenario: Make sure that persistent url work with linkit module.
    Given I am logged in as a user with the "create oe_news content, access content, edit own oe_page content, view published skos concept entities, create av_portal_photo media, use text format base_html, view the administration theme, create url aliases" permission
    And the following languages are available:
      | languages |
      | en        |
      | fr        |
    When I visit "the News creation page"
    And I fill in "Page title" with "News 1"
    And I select "Factsheet" from "News type"
    And I enter "Teaser text" in the "Teaser" wysiwyg editor
    And I enter "Body text" in the "Body text" wysiwyg editor
    And I fill in "Subject" with "financing"
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"
    And I press "Save"
    Then I should see "News 1"
    # Create a second node.
    When I visit "the News creation page"
    And I fill in "Page title" with "News 2"
    And I enter "Teaser text" in the "Teaser" wysiwyg editor
    And I insert a link to "News 1" in the "Body text" field through the WYSIWYG editor
    And I fill in "Subject" with "financing"
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"
    And I click the fieldset "URL alias"
    And I fill in "URL alias" with "/news-2"
    And I select "Factsheet" from "News type"
    And I press "Save"
    Then I should see "News 2"
    # Check link to first node.
    And I should see a link pointing to the "News 1" node
    And I log out
    # Check link to first node with alias
    When I update alias of "News 1" node to "/alias1"
    And I go to "/news-2"
    Then I should see a persistent link for the node "News 1" pointing to "/en/alias1"
    # Check link to first node with alias for another language.
    When I update alias of "News 1" node to "/alias1_fr" for "French"
    And I update alias of "News 2" node to "/news2_fr" for "French"
    And I go to "/fr/news2_fr"
    Then I should see a persistent link for the node "News 1" pointing to "/fr/alias1_fr"
