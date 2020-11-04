<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat;

use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ExpectationException;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\node\NodeInterface;
use Drupal\Tests\oe_content\Traits\UtilityTrait;
use PHPUnit\Framework\Assert;

/**
 * Defines step definitions that are generally useful in this project.
 */
class FeatureContext extends RawDrupalContext {

  use UtilityTrait;

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

  /**
   * Checks that the given select field has the options listed in the table.
   *
   * // phpcs:disable
   * @Then I should have the following options for the :select select:
   * | option 1 |
   * | option 2 |
   * |   ...    |
   * // phpcs:enable
   */
  public function assertSelectOptions(string $select, TableNode $options): void {
    // Retrieve the specified field.
    if (!$field = $this->getSession()->getPage()->findField($select)) {
      throw new ExpectationException("Field '$select' not found.", $this->getSession());
    }

    // Retrieve the options table from the test scenario and flatten it.
    $expected_options = $options->getRows();
    array_walk($expected_options, function (&$value) {
      $value = reset($value);
    });

    // Retrieve the actual options that are shown in the select field.
    $actual_options = $field->findAll('css', 'option');

    // Convert into a flat list of option text strings.
    array_walk($actual_options, function (&$value) {
      $value = $value->getText();
    });

    Assert::assertEquals($expected_options, $actual_options);
  }

  /**
   * Selects option in select field in a region.
   *
   * @When I select :option from :select in the :region region
   */
  public function selectOption(string $select, string $option, string $region): void {
    $session = $this->getSession();
    $regionObj = $session->getPage()->find('region', $region);
    if (!$regionObj) {
      throw new \Exception(sprintf('No region "%s" found on the page %s.', $region, $session->getCurrentUrl()));
    }
    $regionObj->selectFieldOption($select, $option);
  }

  /**
   * Selects option in select field with specified selector.
   *
   * @param string $element
   *   The element selector.
   * @param string $option
   *   The option to select.
   *
   * @When I select :option from :element form element
   */
  public function selectOptionFromFormElement(string $element, string $option): void {
    $this->getSession()->getPage()->find('css', $element)->selectOption($option);
  }

  /**
   * Assert non visibility of given element.
   *
   * @Then the :element is not visible
   */
  public function assertNonVisibility($element): void {
    $node = $this->getSession()->getPage()->find('css', $element);
    $this->assertNotVisuallyVisible($node);
  }

  /**
   * Assert visibility of given element.
   *
   * @Then the :element is visible
   */
  public function assertVisibility($element): void {
    $node = $this->getSession()->getPage()->find('css', $element);
    $this->assertVisuallyVisible($node);
  }

}
