<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Functional;

use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\oe_content\Traits\CompositeReferenceTestTrait;

/**
 * Functional tests for composite reference configuration forms.
 *
 * @package Drupal\Tests\oe_content\Functional
 */
class CompositeReferenceConfigurationTest extends BrowserTestBase {

  use CompositeReferenceTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'field_ui',
    'node',
    'field',
    'user',
    'oe_content',
    'entity_reference_revisions',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $permissions[] = 'administer node fields';
    $user = $this->drupalCreateUser($permissions);

    $this->drupalLogin($user);
  }

  /**
   * Asserts that field forms have the composite option if applicable.
   */
  public function testCompositeReferenceConfiguration(): void {
    $entity_type_manager = \Drupal::entityTypeManager();

    // Create a content type.
    $node_type = $entity_type_manager->getStorage('node_type')->create(['name' => 'Test content type', 'type' => 'test_ct']);
    $node_type->save();

    // Create a text field to be used as a dummy, non-composite field example.
    $entity_type_manager->getStorage('field_storage_config')->create([
      'entity_type' => 'node',
      'field_name' => 'text_field',
      'type' => 'text_long',
      'cardinality' => 1,
    ])->save();

    $text_field = $entity_type_manager->getStorage('field_config')->create([
      'entity_type' => 'node',
      'field_name' => 'text_field',
      'bundle' => $node_type->id(),
      'label' => 'Text field',
      'translatable' => TRUE,
    ]);
    $text_field->save();

    // Access the text field edit form and assert that the composite option is
    // not available.
    $url = Url::fromRoute("entity.field_config.node_field_edit_form", [
      'node_type' => $node_type->id(),
      'field_config' => $text_field->id(),
    ]);
    $this->drupalGet($url);
    $this->assertSession()->pageTextContains('Text field settings for Test content type');
    $this->assertSession()->pageTextNotContains('Composite field');

    $reference_field_definitions = [
      [
        'field_name' => 'entity_reference_field',
        'field_label' => 'Entity reference field',
        'revisions' => FALSE,
      ],
      [
        'field_name' => 'entity_reference_revisions_field',
        'field_label' => 'Entity reference revisions field',
        'revisions' => TRUE,
      ],
    ];

    foreach ($reference_field_definitions as $field_definition) {
      // Create an entity reference field.
      $entity_reference_field = $this->createEntityReferenceField('node', $node_type->id(), $field_definition['field_name'], $field_definition['field_label'], 'node', 'default', [
        'target_bundles' => [
          $node_type->id() => $node_type->id(),
        ],
      ], 1, $field_definition['revisions']);

      // Access the text entity reference edit form
      // and assert that the composite option is available.
      $url = Url::fromRoute("entity.field_config.node_field_edit_form", [
        'node_type' => $node_type->id(),
        'field_config' => $entity_reference_field->id(),
      ]);
      $this->drupalGet($url);
      $this->assertSession()->pageTextContains($field_definition['field_label'] . ' settings for Test content type');
      // The configuration is disabled by default.
      $this->assertSession()->checkboxNotChecked('Composite field');
      // Enable the composite reference and save it.
      $this->getSession()->getPage()->checkField('Composite field');
      $this->getSession()->getPage()->pressButton('Save settings');

      // Load the field configuration and assert the changes where saved.
      $entity_reference_field = $entity_type_manager->getStorage('field_config')->load($entity_reference_field->id());
      $this->assertEqual($entity_reference_field->getThirdPartySetting('oe_content', 'composite'), TRUE);
      // Reload the page and assert the changes are reflected.
      $this->drupalGet($url);
      $this->assertSession()->pageTextContains($field_definition['field_label'] . ' settings for Test content type');
      // The configuration is enabled.
      $this->assertSession()->checkboxChecked('Composite field');
    }

  }

}
