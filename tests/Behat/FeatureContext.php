<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat;

use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Mink\Element\NodeElement;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\node\NodeInterface;

/**
 * Defines step definitions that are generally useful in this project.
 */
class FeatureContext extends RawDrupalContext {

  /**
   * Fills a date or time field at a datetime widget.
   *
   * Example: When I fill in "Start date" with the date "29-08-2016".
   * Example: When I fill in "Start date" with the time "26:59:00".
   *
   * @param string $field_group
   *   The field component's label.
   * @param string $date_component
   *   The field to be filled.
   * @param string $value
   *   The value of the field.
   *
   * @throws \Exception
   *    Thrown when more than one elements match the given field in the given
   *    field group.
   *
   * @When I fill in :field_group with the :date_component :value
   */
  public function fillDateField($field_group, $date_component, $value) {
    $field_selectors = $this->findDateFields($field_group);
    if (count($field_selectors) > 1) {
      throw new \Exception("More than one elements were found.");
    }
    $field_selector = reset($field_selectors);
    $field_selector->fillField(ucfirst($date_component), $value);
  }

  /**
   * Finds a datetime field.
   *
   * @param string $field
   *   The field name.
   *
   * @return \Behat\Mink\Element\NodeElement[]
   *   The elements found.
   *
   * @throws \Exception
   *   Thrown when the field was not found.
   */
  public function findDateFields($field) {
    $field_selectors = $this->getSession()->getPage()->findAll('css', '.field--widget-datetime-default');
    $field_selectors = array_filter($field_selectors, function ($field_selector) use ($field) {
      return $field_selector->has('named', ['content', $field]);
    });
    if (empty($field_selectors)) {
      throw new \Exception("Date field {$field} was not found.");
    }
    return $field_selectors;
  }

  /**
   * Check that a link is pointing to a specific target.
   *
   * @Then I should see the link :link pointing to :url
   *
   * @throws \Exception
   *   If link cannot be found or target is incorrect.
   */
  public function assertLinkWithHref($link, $url) {
    $page = $this->getSession()->getPage();

    $result = $page->findLink($link);
    if (empty($result)) {
      throw new \Exception("No link '{$link}' on the page");
    }

    $href = $result->getAttribute('href');
    if ($url != $href) {
      throw new \Exception("The link '{$link}' points to '{$href}'");
    }

  }

  /**
   * Checks that the AV Portal photo is rendered.
   *
   * @param string $title
   *   The photo title.
   * @param string $src
   *   The final photo source.
   *
   * @Then I should see the AV Portal photo :title with source :src
   */
  public function assertAvPortalPhoto(string $title, string $src): void {
    $media = \Drupal::entityTypeManager()->getStorage('media')->loadByProperties(['name' => $title]);
    if (!$media) {
      throw new \Exception(sprintf('The media named "%s" does not exist', $title));
    }

    $this->assertSession()->elementAttributeContains('css', 'img.avportal-photo', 'src', $src);
  }

  /**
   * Enables config and modules for PURL processing functionalities.
   *
   * @param \Behat\Behat\Hook\Scope\BeforeScenarioScope $scope
   *   The scope.
   *
   * @beforeScenario @purl-linkit
   */
  public function enableTestModule(BeforeScenarioScope $scope): void {
    \Drupal::service('module_installer')->install([
      'ckeditor',
      'content_translation',
      'oe_content_persistent_test',
    ]);
  }

  /**
   * Remove config and disable modules for PURL processing functionalities.
   *
   * @param \Behat\Behat\Hook\Scope\AfterScenarioScope $scope
   *   The scope.
   *
   * @afterScenario @purl-linkit
   */
  public function disableTestModule(AfterScenarioScope $scope): void {
    \Drupal::service('module_installer')->uninstall([
      'ckeditor',
      'content_translation',
      'oe_content_persistent_test',
    ]);
    \Drupal::configFactory()->getEditable('filter.format.base_html')->delete();
  }

  /**
   * Set alias of the node.
   *
   * @param string $node_title
   *   Title of the node.
   * @param string $alias
   *   Alias of the node.
   * @param string|null $language_name
   *   Language name if applicable.
   *
   * @When I update alias of :node_title node to :alias
   * @When I update alias of :node_title node to :alias for :language_name
   */
  public function updateNodeAlias(string $node_title, string $alias, $language_name = NULL) {
    $node = $this->getNodeByTitle($node_title);

    if ($language_name) {
      $langcode = $this->getLangcodeByName($language_name);
      $node = $node->addTranslation($langcode, $node->toArray());
    }

    $node->get('path')->alias = $alias;
    $node->save();
    // @todo Investigate is this related to this issue https://www.drupal.org/node/2480077.
    // @see example \Drupal\Tests\path\Functional\PathAliasTest::testPathCache
    \Drupal::cache('data')->deleteAll();
  }

  /**
   * Check link to node.
   *
   * @param string $node_title
   *   Title of the node.
   * @param string|null $alias
   *   Alias of the node.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   *
   * @Then I should see a link pointing to the :node_title node
   * @Then I should see a persistent link for the node :node_title pointing to :alias
   */
  public function assertProcessedLink(string $node_title, $alias = NULL) {
    $node = $this->getNodeByTitle($node_title);
    if ($alias === NULL) {
      $alias = $node->toUrl()->toString();
    }
    $alias = '/' . $this->getDrupalParameter('drupal')['drupal_root'] . $alias;
    $node_url = \Drupal::config('oe_content_persistent.settings')->get('base_url') . $node->uuid();
    $this->assertLinkWithHref($node_url, $alias);
  }

  /**
   * Clicks on a fieldset form element.
   *
   * @param string $field
   *   The name of the fieldset.
   *
   * @Given I click the fieldset :field
   */
  public function assertClickFieldset(string $field): void {
    $this->getSession()->getPage()->find('named', ['link_or_button', $field])->click();
  }

  /**
   * Retrieves a node by its title.
   *
   * @param string $title
   *   The node title.
   *
   * @return \Drupal\node\NodeInterface
   *   The node entity.
   */
  protected function getNodeByTitle(string $title): NodeInterface {
    $storage = \Drupal::entityTypeManager()->getStorage('node');
    $nodes = $storage->loadByProperties([
      'title' => $title,
    ]);

    if (!$nodes) {
      throw new \Exception("Could not find node with title '$title'.");
    }

    if (count($nodes) > 1) {
      throw new \Exception("Multiple nodes with title '$title' found.");
    }

    return reset($nodes);
  }

  /**
   * Retrieves a langcode by language name.
   *
   * @param string $language_name
   *   Name of language.
   *
   * @return string
   *   Langcode of language.
   */
  protected function getLangcodeByName(string $language_name): string {
    $languages = \Drupal::service('language_manager')->getStandardLanguageList();

    foreach ($languages as $langcode => $language) {
      if ($language[0] === $language_name) {
        return $langcode;
      }
    }

    throw new \Exception("Language name '$language_name' is not valid.");
  }

  /**
   * Step to fill in multi value fields with columns.
   *
   * @Given I fill in :column with :value in the :row :field field element
   */
  public function fillInMultivalueField($column, $value, $row, $field) {
    $table = $this->getMultiColumnFieldTable($field);
    $row_map = [
      'first' => '1',
      'second' => '2',
      'third' => '3',
      'fourth' => '4',
      'fifth' => '5',
      'sixth' => '6',
    ];

    $row = $table->find('xpath', "//tbody//tr[position()={$row_map[$row]}]");
    if (!$row) {
      throw new \Exception(sprintf('The %s row for the field %field could not be found.', $row, $field));
    }

    $row->fillField($column, $value);
  }

  /**
   * Finds the table that holds a multiple columned field.
   *
   * @param string $field
   *   The field.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The table element.
   */
  protected function getMultiColumnFieldTable(string $field): ?NodeElement {
    $xpath = '//table[contains(concat(" ", normalize-space(@class), " "), " field-multiple-table ")]/descendant::h4[contains(text(), ' . $field . ')]';
    $heading = $this->getSession()->getPage()->find('xpath', $xpath);

    if (!$heading) {
      throw new \Exception(sprintf('Table for %s field not found', $field));
    }

    return $heading->getParent()->getParent()->getParent()->getParent();
  }

}
