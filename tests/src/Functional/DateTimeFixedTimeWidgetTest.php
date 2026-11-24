<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\oe_content\Traits\EntityLoadingTrait;
use Drupal\Tests\oe_content\Traits\EntityReferenceTrait;
use Drupal\Tests\oe_content\Traits\NodeBodyFieldStorageTrait;
use Drupal\Tests\sparql_entity_storage\Traits\SparqlConnectionTrait;
use Drupal\node\NodeInterface;
use OpenEuropa\TestingUtilities\Traits\CachedDatabaseInstallTrait;

/**
 * Tests the "Date only (fixed time)" field widget.
 *
 * @group oe_content
 * @group batch3
 */
class DateTimeFixedTimeWidgetTest extends BrowserTestBase {

  use CachedDatabaseInstallTrait;
  use EntityLoadingTrait;
  use EntityReferenceTrait;
  use NodeBodyFieldStorageTrait;
  use SparqlConnectionTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   *
   * The modules match CommonContentTypeFieldsTest so the cached database
   * install is shared.
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
    $this->ensureNodeBodyTextWithSummary();

    \Drupal::service('oe_content.datetime_field_storage_converter')->convertToDateTime('node', 'oe_publication_date');
    $this->setWidgetTime('10:00:00');

    // Use a timezone far from UTC to make sure the date is not shifted.
    $this->config('system.date')
      ->set('timezone.default', 'Australia/Sydney')
      ->set('timezone.user.configurable', FALSE)
      ->save();

    $this->drupalLogin($this->drupalCreateUser([
      'access content',
      'edit any oe_news content',
    ]));
  }

  /**
   * Tests the widget saves the selected date with the configured time.
   */
  public function testDateWithFixedTimeWidget(): void {
    $node = $this->drupalCreateNode([
      'type' => 'oe_news',
      'title' => 'Test news',
      'oe_news_types' => $this->getReferenceTargetIds('node', 'oe_news', 'oe_news_types', 'News article'),
      'oe_teaser' => 'Some teaser',
      'oe_subject' => 'http://data.europa.eu/uxp/1000',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/AGRI',
      'oe_publication_date' => '2026-03-15T10:00:00',
    ]);

    // The edit form shows only the stored date.
    $this->drupalGet($node->toUrl('edit-form'));
    $assert_session = $this->assertSession();
    $assert_session->fieldValueEquals('oe_publication_date[0][value][date]', '2026-03-15');
    $assert_session->fieldNotExists('oe_publication_date[0][value][time]');

    // The selected date is saved with the configured time.
    $this->submitForm([
      'oe_publication_date[0][value][date]' => '2026-03-20',
    ], 'Save');
    $this->assertEquals('2026-03-20T10:00:00', $this->reloadNode($node)->get('oe_publication_date')->value);

    // Change the configured time and save again: the new time is used.
    $this->setWidgetTime('12:00:00');
    $this->drupalGet($node->toUrl('edit-form'));
    $assert_session->fieldValueEquals('oe_publication_date[0][value][date]', '2026-03-20');
    $this->submitForm([], 'Save');
    $this->assertEquals('2026-03-20T12:00:00', $this->reloadNode($node)->get('oe_publication_date')->value);
  }

  /**
   * Sets the time saved by the widget on the news form display.
   *
   * @param string $time
   *   The time, in the H:i:s format.
   */
  protected function setWidgetTime(string $time): void {
    \Drupal::service('entity_display.repository')->getFormDisplay('node', 'oe_news')
      ->setComponent('oe_publication_date', [
        'type' => 'oe_content_datetime_fixed_time',
        'settings' => ['time' => $time],
      ])
      ->save();
  }

  /**
   * Reloads a node, bypassing the cache.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node.
   *
   * @return \Drupal\node\NodeInterface
   *   The reloaded node.
   */
  protected function reloadNode(NodeInterface $node): NodeInterface {
    $storage = \Drupal::entityTypeManager()->getStorage('node');
    $storage->resetCache([$node->id()]);

    return $storage->load($node->id());
  }

}
