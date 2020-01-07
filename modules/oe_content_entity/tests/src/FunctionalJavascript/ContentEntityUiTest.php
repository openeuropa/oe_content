<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_entity\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Test corporate content entity UIs.
 */
class ContentEntityUiTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'oe_content',
    'oe_content_entity',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $user = $this->drupalCreateUser([
      'manage corporate content entities',
      'manage corporate content entity types',
      'access administration pages',
    ]);
    $this->drupalLogin($user);
  }

  /**
   * Tests corporate UIs, such as creation of a new bundle, actual content, etc.
   *
   * @param string $module
   *   The module to install.
   * @param string $entity_type_id
   *   The entity type to test.
   * @param string $label
   *   Human readable label used in UIs.
   *
   * @dataProvider corporateEntityDataProvider
   */
  public function testCorporateEntityUi(string $module, string $entity_type_id, string $label): void {
    $session = $this->getSession();
    $page = $session->getPage();
    $assert_session = $this->assertSession();

    $this->container->get('module_installer')->install([$module], TRUE);

    // Create a new bundle.
    $this->drupalGet("/admin/structure/{$entity_type_id}_type");
    $assert_session->pageTextContains("There are no {$label} type entities yet.");

    $this->drupalGet("/admin/structure/{$entity_type_id}_type/add");
    $assert_session->pageTextContains("Add {$label} type");

    // Set the value for the field, triggering the machine name update.
    $page->findField('Label')->setValue("{$label} type name");

    // Wait the set timeout for fetching the machine name.
    $this->assertJsCondition('jQuery("#edit-label-machine-name-suffix .machine-name-value").html() == "' . "{$label}_type_name" . '"');
    $page->fillField('Description', "{$label} type description");
    $page->pressButton('Save');

    // Assert that the bundle has been created and it's listed correctly.
    $assert_session->pageTextContains("Created the {$label} type name {$entity_type_id} entity type.");
    $assert_session->elementContains('css', 'div.region-content table tr td:nth-child(1)', "{$label} type name");
    $assert_session->elementContains('css', 'div.region-content table tr td:nth-child(2)', "{$label} type description");
    $assert_session->elementContains('css', 'div.region-content table tr td:nth-child(3)', "{$label}_type_name");

    // Assert that we have no entities.
    $this->drupalGet("/admin/content/{$entity_type_id}");
    $assert_session->pageTextContains("There are no {$label} entities yet.");

    // Create twi revisions of the same entity.
    $this->drupalGet("/admin/content/{$entity_type_id}/add/{$label}_type_name");

    $page->fillField('Name', "{$label} entity name 1");
    $page->fillField('Revision log message', "Revision log message 1.");
    $page->pressButton('Save');

    $this->drupalGet("/admin/content/{$entity_type_id}/1/edit");
    $page->fillField('Name', "{$label} entity name 2");
    $page->checkField('Create new revision');
    $page->fillField('Revision log message', "Revision log message 2.");
    $page->pressButton('Save');

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
    $page->fillField('Name', "{$label} entity name 3");
    $page->pressButton('Save');

    // Assert that changes have been saved but no revision has been created.
    $entity_storage->resetCache();
    $entity = $entity_storage->load(1);
    $this->assertEquals("{$label} entity name 3", $entity->getName());
    $this->assertNull($entity_storage->loadRevision(3));

    // Edit the entity without creating a new revision.
    $this->drupalGet("/admin/content/{$entity_type_id}/1/delete");
    $page->pressButton('Delete');

    $assert_session->pageTextContains("The {$label} {$label} entity name 3 has been deleted.");
    $assert_session->pageTextContains("There are no {$label} entities yet.");

    // Delete bundle.
    $this->drupalGet("/admin/structure/{$entity_type_id}_type");
    $this->clickLink('Edit');

    $assert_session->pageTextContains("Edit {$label} type name");
    $this->clickLink('Delete');

    $assert_session->pageTextContains("Are you sure you want to delete the {$label} type {$label} type name?");
    $page->pressButton('Delete');

    $assert_session->pageTextContains("The {$label} type {$label} type name has been deleted.");
    $assert_session->pageTextContains("There are no {$label} type entities yet.");
  }

  /**
   * Provide module, entity type and label to run content entity UIs tests.
   *
   * @return array
   *   List of corporate entity module, entity type and label triplets.
   */
  public function corporateEntityDataProvider(): array {
    return [
      ['oe_content_entity_contact', 'oe_contact', 'contact'],
      ['oe_content_entity_organisation', 'oe_organisation', 'organisation'],
      ['oe_content_entity_venue', 'oe_venue', 'venue'],
    ];
  }

}
