<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_entity\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\oe_content\Traits\EntityTypeUiTrait;

/**
 * Test corporate content entity UIs.
 */
class CorporateEntityUiTest extends BrowserTestBase {

  use EntityTypeUiTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'classy';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'oe_content_entity_contact',
    'oe_content_entity_organisation',
    'oe_content_entity_venue',
  ];

  /**
   * Tests corporate UIs, such as creation of a new bundle, actual content, etc.
   */
  public function testCorporateEntityUi(): void {
    foreach ($this->corporateEntityDataTestCases() as $info) {
      list($entity_type_id, $label) = $info;
      $bundle = str_replace(' ', '_', $label) . '_type_bundle';
      $user = $this->drupalCreateUser([
        'manage corporate content entity types',
        'access administration pages',
      ]);
      $this->drupalLogin($user);
      $this->createEntityTypeBundle($entity_type_id, $label, $bundle);

      $this->loginAdminUser($entity_type_id, $bundle);

      // Assert that we have no entities.
      $this->drupalGet("/admin/content/{$entity_type_id}");
      $this->assertSession()->pageTextContains("There are no {$label} entities yet.");

      // Create two revisions of the same entity.
      $this->drupalGet("/admin/content/{$entity_type_id}/add/{$bundle}");
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

      // Remove entity.
      $this->drupalGet("/admin/content/{$entity_type_id}/1/delete");
      $this->getSession()->getPage()->pressButton('Delete');

      $this->assertSession()->pageTextContains("The {$label} {$label} entity name 3 has been deleted.");
      $this->assertSession()->pageTextContains("There are no {$label} entities yet.");

      // Delete bundle.
      $this->removeEntityTypeBundle($entity_type_id, $label, $bundle);
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

  /**
   * Login as admin user.
   *
   * @param string $entity_type_id
   *   Entity type id.
   * @param string $bundle
   *   Entity type bundle.
   */
  protected function loginAdminUser(string $entity_type_id, string $bundle): void {
    $user = $this->drupalCreateUser([
      'manage corporate content entity types',
      'access ' . $entity_type_id . ' overview',
      'access ' . $entity_type_id . ' canonical page',
      'view published ' . $entity_type_id,
      'view unpublished ' . $entity_type_id,
      'create ' . $entity_type_id . ' ' . $bundle . ' corporate entity',
      'edit ' . $entity_type_id . ' ' . $bundle . ' corporate entity',
      'delete ' . $entity_type_id . ' ' . $bundle . ' corporate entity',
      'access administration pages',
    ]);
    $this->drupalLogin($user);
  }

}
