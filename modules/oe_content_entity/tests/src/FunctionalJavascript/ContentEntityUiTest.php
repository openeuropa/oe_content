<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_entity\FunctionalJavascript;

use Drupal\Tests\BrowserTestBase;

/**
 * Test corporate content entity UIs.
 */
class ContentEntityUiTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'composite_reference',
    'oe_content',
    'oe_content_entity',
    'oe_content_entity_contact',
    'oe_content_entity_organisation',
    'oe_content_entity_venue',
  ];

  /**
   * Tests corporate UIs, such as creation of a new bundle, actual content, etc.
   */
  public function testCorporateEntityUi(): void {
    $user = $this->drupalCreateUser([
      'manage corporate content entities',
      'manage corporate content entity types',
      'access administration pages',
    ]);

    $this->drupalLogin($user);

    foreach ($this->corporateEntityDataTestCases() as $info) {
      list($entity_type_id, $label) = $info;

      // Create a new bundle.
      $this->drupalGet("/admin/structure/{$entity_type_id}_type");
      $this->assertSession()->pageTextContains("{$label} type entities");

      $this->drupalGet("/admin/structure/{$entity_type_id}_type/add");
      $this->assertSession()->pageTextContains("Add {$label} type");

      // Set the label.
      $this->getSession()->getPage()->findField('Label')->setValue("{$label} type name");
      $this->getSession()->getPage()->findField('Machine-readable name')->setValue("{$label}_type_name");
      $this->getSession()->getPage()->fillField('Description', "{$label} type description");
      $this->getSession()->getPage()->pressButton('Save');

      // Assert that the bundle has been created and it's listed correctly.
      $this->assertSession()->pageTextContains("Created the {$label} type name {$entity_type_id} entity type.");
      $this->assertSession()->elementContains('css', 'div.region-content table', "{$label} type name");
      $this->assertSession()->elementContains('css', 'div.region-content table', "{$label} type description");
      $this->assertSession()->elementContains('css', 'div.region-content table', "{$label}_type_name");

      $user = $this->drupalCreateUser([
        'manage corporate content entity types',
        'access ' . $entity_type_id . ' overview',
        'access ' . $entity_type_id . ' canonical page',
        'view published ' . $entity_type_id,
        'view unpublished ' . $entity_type_id,
        'create ' . $entity_type_id . ' ' . $label . '_type_name corporate entity',
        'edit ' . $entity_type_id . ' ' . $label . '_type_name corporate entity',
        'delete ' . $entity_type_id . ' ' . $label . '_type_name corporate entity',
        'access administration pages',
      ]);
      $this->drupalLogin($user);

      // Assert that we have no entities.
      $this->drupalGet("/admin/content/{$entity_type_id}");
      $this->assertSession()->pageTextContains("There are no {$label} entities yet.");

      // Create two revisions of the same entity.
      $this->drupalGet("/admin/content/{$entity_type_id}/add/{$label}_type_name");
      $this->getSession()->getPage()->fillField('Name', "{$label} entity name 1");
      $this->getSession()->getPage()->fillField('Revision log message', "Revision log message 1.");
      $this->getSession()->getPage()->pressButton('Save');

      $this->drupalGet("/admin/content/{$entity_type_id}/1/edit");
      $this->getSession()->getPage()->fillField('Name', "{$label} entity name 2");
      $this->getSession()->getPage()->checkField('Create new revision');
      $this->getSession()->getPage()->fillField('Revision log message', "Revision log message 2.");
      $this->getSession()->getPage()->pressButton('Save');

      /** @var \Drupal\Core\Entity\ContentEntityStorageInterface $entity_storage */
      $entity_storage = \Drupal::entityTypeManager()->getStorage($entity_type_id);

      // Assert that the latest revision is the default one.
      $entity = $entity_storage->load(1);
      $this->assertEquals("{$label} entity name 2", $entity->getName());
      $this->assertEquals("Revision log message 2.", $entity->getRevisionLogMessage());

      // Assert that the first revision is correctly saved.
      $entity = $entity_storage->loadRevision(1);
      $this->assertEquals("{$label} entity name 1", $entity->getName());
      $this->assertEquals("Revision log message 1.", $entity->getRevisionLogMessage());

      // Assert that the second revision is correctly saved.
      $entity = $entity_storage->loadRevision(2);
      $this->assertEquals("{$label} entity name 2", $entity->getName());
      $this->assertEquals("Revision log message 2.", $entity->getRevisionLogMessage());

      // Edit the entity without creating a new revision.
      $this->drupalGet("/admin/content/{$entity_type_id}/1/edit");
      $this->getSession()->getPage()->fillField('Name', "{$label} entity name 3");
      $this->getSession()->getPage()->pressButton('Save');

      // Assert that changes have been saved but no revision has been created.
      $entity_storage->resetCache();
      $entity = $entity_storage->load(1);
      $this->assertEquals("{$label} entity name 3", $entity->getName());
      $this->assertNull($entity_storage->loadRevision(3));

      // Edit the entity without creating a new revision.
      $this->drupalGet("/admin/content/{$entity_type_id}/1/delete");
      $this->getSession()->getPage()->pressButton('Delete');

      $this->assertSession()->pageTextContains("The {$label} {$label} entity name 3 has been deleted.");
      $this->assertSession()->pageTextContains("There are no {$label} entities yet.");

      // Delete bundle.
      $this->drupalGet("/admin/structure/{$entity_type_id}_type/{$label}_type_name/edit");
      $this->assertSession()->pageTextContains("Edit {$label} type name");
      $this->clickLink('Delete');

      $this->assertSession()->pageTextContains("Are you sure you want to delete the {$label} type {$label} type name?");
      $this->getSession()->getPage()->pressButton('Delete');

      $this->assertSession()->pageTextContains("The {$label} type {$label} type name has been deleted.");
    }
  }

  /**
   * Provides a set of test cases to be used by self::testCorporateEntityUi().
   *
   * - entity type.
   * - label.
   *
   * We do not use a dataProvider because it slows down the speed greatly.
   *
   * @return array
   *   List of test cases.
   */
  public function corporateEntityDataTestCases(): array {
    return [
      ['oe_contact', 'contact'],
      ['oe_organisation', 'organisation'],
      ['oe_venue', 'venue'],
    ];
  }

}
