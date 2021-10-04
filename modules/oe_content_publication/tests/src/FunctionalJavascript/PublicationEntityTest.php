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

    // Create a publication and test the field constraints.
    $collection = \Drupal::entityTypeManager()->getStorage('node')->create([
      'type' => 'oe_publication',
      'title' => 'Test Publication node',
      'oe_teaser' => 'Test teaser text.',
      'oe_publication_type' => 'http://publications.europa.eu/resource/authority/resource-type/ABSTRACT_JUR',
      'oe_publication_collection' => 0,
      'oe_publication_date' => [
        'value' => '2020-04-15',
      ],
      'oe_subject' => 'http://data.europa.eu/uxp/1000',
      'oe_author' => 'http://publications.europa.eu/resource/authority/corporate-body/AASM',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'uid' => 0,
      'status' => 1,
    ]);
    $collection->save();

    $this->drupalGet($collection->toUrl('edit-form'));

    // Disable the browser required field validation and assert files field.
    $this->getSession()->executeScript("typeof jQuery === 'undefined' || jQuery(':input[required]').prop('required', false);");
    $this->getSession()->getPage()->pressButton('Save');
    $this->assertSession()->pageTextContainsOnce('Files field is required');
    $this->getSession()->getPage()->selectFieldOption('Yes', '1');

    // Disable the browser required field validation and assert publications
    // field.
    $this->getSession()->executeScript("typeof jQuery === 'undefined' || jQuery(':input[required]').prop('required', false);");
    $this->getSession()->getPage()->pressButton('Save');
    $this->assertSession()->pageTextContainsOnce('Publications field is required');
  }

}
