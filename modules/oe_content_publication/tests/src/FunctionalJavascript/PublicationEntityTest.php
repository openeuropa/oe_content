<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_publication\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\sparql_entity_storage\Traits\SparqlConnectionTrait;

/**
 * Functional tests for the Publication content type.
 */
class PublicationEntityTest extends WebDriverTestBase {

  use SparqlConnectionTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'oe_content_publication',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->setUpSparql();
  }

  /**
   * Tests the Publication content type form.
   */
  public function testPublicationForm() {
    $admin = $this->createUser([], NULL, TRUE);
    $this->drupalLogin($admin);
    $this->drupalGet('/node/add/oe_publication');

    // Assert collection related field visibilities when loading the form.
    $this->assertTrue($this->getSession()->getPage()->findField('oe_documents[0][target_id]')->isVisible());
    $this->assertTrue($this->getSession()->getPage()->findField('oe_documents[0][target_id]')->hasAttribute('required'));
    $this->assertFalse($this->getSession()->getPage()->findField('oe_publication_publications[0][target_id]')->isVisible());

    // Mark the publication as collection and assert the field visibilities.
    $this->getSession()->getPage()->selectFieldOption('Yes', '1');
    $this->assertFalse($this->getSession()->getPage()->findField('oe_documents[0][target_id]')->isVisible());
    $this->assertTrue($this->getSession()->getPage()->findField('oe_publication_publications[0][target_id]')->isVisible());
    $this->assertTrue($this->getSession()->getPage()->findField('oe_publication_publications[0][target_id]')->hasAttribute('required'));
  }

}
