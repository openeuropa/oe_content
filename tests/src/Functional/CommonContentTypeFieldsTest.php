<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\oe_content\Traits\NodeBodyFieldStorageTrait;
use Drupal\Tests\oe_content\Traits\RegionTrait;
use Drupal\Tests\sparql_entity_storage\Traits\SparqlConnectionTrait;
use OpenEuropa\TestingUtilities\Traits\CachedDatabaseInstallTrait;

/**
 * Tests the fields that are shared across all the content types.
 *
 * @group oe_content
 * @group batch3
 */
class CommonContentTypeFieldsTest extends BrowserTestBase {

  use CachedDatabaseInstallTrait;
  use RegionTrait;
  use SparqlConnectionTrait;
  use NodeBodyFieldStorageTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
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
   * The content types that share the common fields.
   */
  protected const CONTENT_TYPES = [
    'oe_call_proposals',
    'oe_call_tenders',
    'oe_consultation',
    'oe_event',
    'oe_news',
    'oe_organisation',
    'oe_page',
    'oe_person',
    'oe_policy',
    'oe_project',
    'oe_publication',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->cacheDbInstall = TRUE;
    parent::setUp();

    $this->setUpSparql();
    $this->ensureNodeBodyTextWithSummary();

    $permissions = ['access content'];
    foreach (self::CONTENT_TYPES as $content_type) {
      $permissions[] = 'create ' . $content_type . ' content';
    }
    $this->drupalLogin($this->drupalCreateUser($permissions));
  }

  /**
   * Tests the description of the fields shared by all the content types.
   */
  public function testCommonFieldDescriptions(): void {
    foreach (self::CONTENT_TYPES as $content_type) {
      $this->drupalGet('/node/add/' . $content_type);

      $this->assertRegionText('alternative title form element', 'Use this field to create an alternative title for use in the URL and in list views.');
      $this->assertRegionText('alternative title form element', 'If the page title is longer than 60 characters, you can add a shorter title here.');
      $this->assertRegionText('teaser form element', 'A short overview of the information on this page. The teaser will be displayed in list views and search engine results, not on the page itself.');
      $this->assertRegionText('teaser form element', 'Limited to 300 characters for SEO purposes.');
      $this->assertRegionText('summary form element', 'A short text that will be displayed below the page title. This should be a brief summary of the content on the page that tells the user what information they will find on this page.');
      $this->assertRegionText('redirect link form element', 'Add a link to this field to automatically redirect the user to a different page. Use this to prevent duplication of content.');
      $this->assertRegionText('content owner form element', 'This is not the writer of the content, but the subject matter expert responsible for keeping this content up to date.');

      // The Person content type has no title field, it is generated out of the
      // first and last name of the person.
      if ($content_type !== 'oe_person') {
        $this->assertRegionText('title form element', 'The ideal length is 50 to 60 characters including spaces.');
        $this->assertRegionText('title form element', 'If it must be longer, make sure you fill in a shorter version in the Alternative title field.');
      }

      // The Organisation content type has no subject field.
      if ($content_type !== 'oe_organisation') {
        $this->assertRegionText('subject form element', 'The topics mentioned on this page. These will be used by search engines and dynamic lists to determine their relevance to a user.');
      }
    }
  }

}
