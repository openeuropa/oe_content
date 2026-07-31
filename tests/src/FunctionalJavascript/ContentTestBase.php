<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content\FunctionalJavascript;

use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\oe_content\Traits\CorporateEntityCreationTrait;
use Drupal\Tests\oe_content\Traits\DateFieldTrait;
use Drupal\Tests\oe_content\Traits\EntityLoadingTrait;
use Drupal\Tests\oe_content\Traits\EntityReferenceTrait;
use Drupal\Tests\oe_content\Traits\MediaCreationTrait;
use Drupal\Tests\oe_content\Traits\NodeBodyFieldStorageTrait;
use Drupal\Tests\oe_content\Traits\RegionTrait;
use Drupal\Tests\oe_content\Traits\UtilityTrait;
use Drupal\Tests\sparql_entity_storage\Traits\SparqlConnectionTrait;
use OpenEuropa\TestingUtilities\Traits\CachedDatabaseInstallTrait;

/**
 * Base class for the tests that create corporate content through the UI.
 */
abstract class ContentTestBase extends WebDriverTestBase {

  use CachedDatabaseInstallTrait;
  use CorporateEntityCreationTrait;
  use DateFieldTrait;
  use EntityLoadingTrait;
  use EntityReferenceTrait;
  use MediaCreationTrait;
  use RegionTrait;
  use SparqlConnectionTrait;
  use UtilityTrait;
  use NodeBodyFieldStorageTrait;

  /**
   * A teaser text longer than the limit configured on the teaser fields.
   */
  protected const LONG_TEASER = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin eu hendrerit lacus, vitae bibendum odio. Fusce orci purus, hendrerit a magna at nullam. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Lorem ipsum dolor sit amet Teaser. Text to remove';

  /**
   * An introduction longer than the limit configured on the summary fields.
   */
  protected const LONG_INTRODUCTION = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas felis leo, lobortis non eros in, consequat tempor est. Praesent sit amet sem eleifend, cursus arcu ac, eleifend nunc. Integer et orci sagittis, volutpat felis sit ametas Introduction. Text to remove';

  /**
   * A title longer than the limit configured on the short title fields.
   */
  protected const LONG_ALTERNATIVE_TITLE = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin eu hendrerit lacus, vitae bibendum odio. Fusce orci purus, hendrerit a magna at nullamsa Alternative title. Text to remove';

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   *
   * All the corporate content types are installed, regardless of the one under
   * test, so that the test classes share the same module list. This makes them
   * share the same cached database install instead of running the installer
   * once per class.
   *
   * @see \OpenEuropa\TestingUtilities\Traits\CachedDatabaseInstallTrait
   */
  protected static $modules = [
    'oe_content_call_proposals',
    'oe_content_call_tenders',
    'oe_content_consultation',
    'oe_content_event',
    'oe_content_news',
    'oe_content_organisation',
    'oe_content_page',
    'oe_content_person',
    'oe_content_policy',
    'oe_content_project',
    'oe_content_publication',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->cacheDbInstall = TRUE;
    parent::setUp();

    $this->setUpSparql();
    $this->setMediumDateFormatPattern();
    $this->ensureNodeBodyTextWithSummary();
  }

  /**
   * Pins the pattern of the core medium date format.
   *
   * The date fields of the project are displayed with the core medium date
   * format, whose pattern changed in Drupal 11.1. Pin it so that the tests
   * assert the same output across the supported core versions.
   *
   * @see https://www.drupal.org/node/3467774
   *
   * @todo Remove when core versions lower than 11.1 are not supported anymore.
   */
  protected function setMediumDateFormatPattern(): void {
    DateFormat::load('medium')->setPattern('D, j M Y - H:i')->save();
  }

  /**
   * Asserts that the length limited common fields have been truncated.
   */
  protected function assertCommonFieldsAreTruncated(): void {
    $assert_session = $this->assertSession();
    $assert_session->pageTextNotContains('Text to remove');
    $assert_session->pageTextContains('ametas Introduction.');
    $assert_session->pageTextContains('nullamsa Alternative title.');
    $assert_session->pageTextContains('amet Teaser.');
  }

  /**
   * Disables the browser validation of the required fields.
   */
  protected function disableBrowserRequiredFieldValidation(): void {
    $this->getSession()->executeScript("typeof jQuery === 'undefined' || jQuery(':input[required]').prop('required', false);");
  }

  /**
   * Asserts the options of a select field.
   *
   * @param string $select
   *   The field locator, usually its label.
   * @param array $expected
   *   The expected option labels, in the order they are expected to appear.
   * @param bool $ordered
   *   Whether the options are expected to appear in the given order. Pass FALSE
   *   for the fields whose options come from a source that does not guarantee
   *   an order, such as the SKOS concepts.
   */
  protected function assertSelectOptions(string $select, array $expected, bool $ordered = TRUE): void {
    $field = $this->getSession()->getPage()->findField($select);
    $this->assertNotNull($field, sprintf('The select field "%s" was not found.', $select));

    $options = array_map(function ($option) {
      return trim($option->getText());
    }, $field->findAll('css', 'option'));

    if (!$ordered) {
      sort($options);
      sort($expected);
    }

    $this->assertEquals($expected, $options);
  }

}
