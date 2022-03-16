<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\field\Traits\EntityReferenceTestTrait;
use Drupal\Tests\sparql_entity_storage\Traits\SparqlConnectionTrait;

/**
 * Tests the default content owner field values.
 */
class DefaultContentOwnerTest extends WebDriverTestBase {

  use SparqlConnectionTrait;
  use EntityReferenceTestTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'oe_content',
    'oe_content_page',
    'oe_corporate_site_info',
    'oe_content_corporate_fields',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->setUpSparql();
  }

  /**
   * Tests the content owner values on new nodes.
   */
  public function testContentOwnerValues() {
    // Set the default content owner values.
    $this->container->get('config.factory')->getEditable('oe_corporate_site_info.settings')
      ->set('content_owners', [
        'http://publications.europa.eu/resource/authority/corporate-body/AGRI',
        'http://publications.europa.eu/resource/authority/corporate-body/BUDG',
      ])
      ->save();

    $user = $this->drupalCreateUser([
      'access content',
      'create oe_page content',
      'view published skos concept entities',
    ]);

    $this->drupalLogin($user);

    $this->drupalGet('/node/add/oe_page');

    $assert_session = $this->assertSession();

    // Assert that default owner values are set when creating a new node.
    $assert_session->fieldValueEquals('oe_content_content_owner[0][target_id]', 'Directorate-General for Agriculture and Rural Development (http://publications.europa.eu/resource/authority/corporate-body/AGRI)');
    $assert_session->fieldValueEquals('oe_content_content_owner[1][target_id]', 'Directorate-General for Budget (http://publications.europa.eu/resource/authority/corporate-body/BUDG)');

    // Unset the default content owner values.
    $this->container->get('config.factory')->getEditable('oe_corporate_site_info.settings')
      ->set('content_owners', [])
      ->save();

    $this->drupalGet('/node/add/oe_page');

    // Assert that no default content owners are set when creating a new node.
    $assert_session->fieldValueEquals('oe_content_content_owner[0][target_id]', '');

    // Uninstall the "Corporate Site Information" module.
    $this->container->get('module_installer')->uninstall(['oe_corporate_site_info']);

    $this->drupalGet('/node/add/oe_page');

    // Assert that the Content Owner form fields are displayed.
    $assert_session->fieldValueEquals('oe_content_content_owner[0][target_id]', '');
  }

}
