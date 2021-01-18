<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_entity\FunctionalJavascript;

/**
 * Test corporate content entity UIs.
 */
class DocumentReferenceCorporateEntityUiTest extends CorporateEntityUiTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'oe_content',
    'oe_content_entity',
    'oe_content_entity_document_reference',
  ];

  /**
   * Tests Document reference UIs.
   */
  public function testDocumentReferenceCorporateEntityUi(): void {
    $this->createCorporateEntityType('oe_document_reference', 'document reference', 'document_reference_type_name');
    $this->loginAdminUser('oe_document_reference', 'document_reference_type_name');

    // Assert that we have no entities.
    $this->drupalGet('admin/content/oe_document_reference');
    $this->assertSession()->pageTextContains('There are no document reference entities yet.');

    // Create two revisions of the same entity.
    $this->drupalGet('/admin/content/oe_document_reference/add/document_reference_type_name');
    $this->getSession()->getPage()->fillField('Revision log message', "Revision log message 1.");
    $this->getSession()->getPage()->pressButton('Save');

    $this->drupalGet('/admin/content/oe_document_reference/1/edit');
    $this->getSession()->getPage()->checkField('Create new revision');
    $this->getSession()->getPage()->fillField('Revision log message', "Revision log message 2.");
    $this->getSession()->getPage()->pressButton('Save');

    /** @var \Drupal\Core\Entity\ContentEntityStorageInterface $entity_storage */
    $entity_storage = \Drupal::entityTypeManager()->getStorage('oe_document_reference');

    // Assert that the latest revision is the default one.
    $entity = $entity_storage->load(1);
    $this->assertEquals('Revision log message 2.', $entity->getRevisionLogMessage());

    // Assert that the first revision is correctly saved.
    $entity = $entity_storage->loadRevision(1);
    $this->assertEquals('Revision log message 1.', $entity->getRevisionLogMessage());

    // Assert that the second revision is correctly saved.
    $entity = $entity_storage->loadRevision(2);
    $this->assertEquals('Revision log message 2.', $entity->getRevisionLogMessage());

    // Edit the entity without creating a new revision.
    $this->drupalGet("/admin/content/oe_document_reference/1/edit");
    $this->getSession()->getPage()->pressButton('Save');

    // Assert that changes have been saved but no revision has been created.
    $entity_storage->resetCache();
    $this->assertNull($entity_storage->loadRevision(3));

    // Remove entity.
    $this->drupalGet("/admin/content/oe_document_reference/1/delete");
    $this->getSession()->getPage()->pressButton('Delete');

    $this->assertSession()->pageTextContains('The document reference document reference type name has been deleted.');
    $this->assertSession()->pageTextContains('There are no document reference entities yet.');

    // Delete bundle.
    $this->removeCorporateEntityType('oe_document_reference', 'document reference', 'document_reference_type_name');
  }

}
