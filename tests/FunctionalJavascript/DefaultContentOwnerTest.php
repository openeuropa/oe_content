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

  /**
   * No default content owner is set.
   *
   * Tests that no default content owner is set in the node
   * form if not set as site information.
   */
  public function testUnsetDefaultContentOwnerValues() {
    $this->container->get('config.factory')->getEditable('oe_corporate_site_info.settings')
      ->set('content_owners', [])
      ->save();

    $user = $this->drupalCreateUser([
      'access content',
      'create oe_page content',
    ]);

    $this->drupalLogin($user);

    $this->drupalGet('/node/add/oe_page');

    $this->assertSession()->fieldValueEquals('oe_content_content_owner[0][target_id]', '');
  }

  /**
   * Module "Corporate Site Information" not enabled.
   *
   * Tests that uninstalling the "Corporate Site Information" module
   * has no effect on the node form.
   */
  public function testCorporateSiteInfoNotEnabled() {
    $this->container->get('module_installer')->uninstall(['oe_corporate_site_info']);

    $user = $this->drupalCreateUser([
      'access content',
      'create oe_page content',
    ]);

    $this->drupalLogin($user);

    $this->drupalGet('/node/add/oe_page');

    $this->assertSession()->fieldValueEquals('oe_content_content_owner[0][target_id]', '');
  }

}
