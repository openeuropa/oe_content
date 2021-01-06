<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\rdf_entity\Traits\RdfDatabaseConnectionTrait;

/**
 * Tests the default content owner field values.
 */
class DefaultContentOwnerTest extends WebDriverTestBase {

  use RdfDatabaseConnectionTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'oe_content',
    'oe_content_page',
    'oe_corporate_site_info',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->setUpSparql();

    $this->container->get('config.factory')->getEditable('oe_corporate_site_info.settings')
      ->set('content_owners', [
        'http://publications.europa.eu/resource/authority/corporate-body/AGRI',
        'http://publications.europa.eu/resource/authority/corporate-body/BUDG',
      ])
      ->save();
  }

  /**
   * Tests the default content owner values on new nodes.
   */
  public function testDefaultContentOwnerValues() {
    $user = $this->drupalCreateUser([
      'access content',
      'create oe_page content',
      'view published skos concept entities',
    ]);

    $this->drupalLogin($user);

    $this->drupalGet('/node/add/oe_page');

    $assert_session = $this->assertSession();
    $assert_session->fieldValueEquals('oe_content_content_owner[0][target_id]', 'Directorate-General for Agriculture and Rural Development (http://publications.europa.eu/resource/authority/corporate-body/AGRI)');
    $assert_session->fieldValueEquals('oe_content_content_owner[1][target_id]', 'Directorate-General for Budget (http://publications.europa.eu/resource/authority/corporate-body/BUDG)');
  }

}
