<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_entity\FunctionalJavascript;

use Drupal\Tests\BrowserTestBase;

/**
 * Base class to test corporate content entity UIs.
 */
abstract class CorporateEntityUiTestBase extends BrowserTestBase {

  /**
   * Creates corporate entity type.
   *
   * @param string $entity_type_id
   *   Entity type id.
   * @param string $label
   *   Entity type label.
   * @param string $bundle
   *   Entity type bundle.
   */
  protected function createCorporateEntityType(string $entity_type_id, string $label, string $bundle): void {
    $user = $this->drupalCreateUser([
      'manage corporate content entities',
      'manage corporate content entity types',
      'access administration pages',
    ]);
    $this->drupalLogin($user);

    // Create a new bundle.
    $this->drupalGet("/admin/structure/{$entity_type_id}_type");
    $this->assertSession()->pageTextContains("{$label} type entities");

    $this->drupalGet("/admin/structure/{$entity_type_id}_type/add");
    $this->assertSession()->pageTextContains("Add {$label} type");

    // Set the label.
    $this->getSession()->getPage()->findField('Label')->setValue("{$label} type name");
    $this->getSession()->getPage()->findField('Machine-readable name')->setValue($bundle);
    $this->getSession()->getPage()->fillField('Description', "{$label} type description");
    // Assert that fields description is correct.
    $this->assertSession()->pageTextContains('Label for the ' . $entity_type_id . ' entity type (bundle).');
    $this->assertSession()->pageTextContains('This text will be displayed on the "Add ' . $entity_type_id . '" page.');
    $this->getSession()->getPage()->pressButton('Save');

    // Assert that the bundle has been created and it's listed correctly.
    $this->assertSession()->pageTextContains("Created the {$label} type name {$entity_type_id} entity type.");
    $this->assertSession()->elementContains('css', 'div.region-content table', "{$label} type name");
    $this->assertSession()->elementContains('css', 'div.region-content table', "{$label} type description");
    $this->assertSession()->elementContains('css', 'div.region-content table', $bundle);
  }

  /**
   * Removes corporate entity type.
   *
   * @param string $entity_type_id
   *   Entity type id.
   * @param string $label
   *   Entity type label.
   * @param string $bundle
   *   Entity type bundle.
   */
  protected function removeCorporateEntityType(string $entity_type_id, string $label, string $bundle): void {
    // Delete bundle.
    $this->drupalGet("/admin/structure/{$entity_type_id}_type/$bundle/edit");
    $this->assertSession()->pageTextContains("Edit {$label} type name");
    $this->clickLink('Delete');

    $this->assertSession()->pageTextContains("Are you sure you want to delete the {$label} type {$label} type name?");
    $this->getSession()->getPage()->pressButton('Delete');

    $this->assertSession()->pageTextContains("The {$label} type {$label} type name has been deleted.");
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
