<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Functional;

use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;

/**
 * Functional tests for composite reference configuration forms.
 *
 * @package Drupal\Tests\oe_content\Functional
 */
class CompositeReferenceConfigurationTest extends BrowserTestBase {

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

    // Create an entity reference field.
    $entity_type_manager->getStorage('field_storage_config')->create([
      'entity_type' => 'node',
      'field_name' => 'entity_reference_field',
      'type' => 'entity_reference',
      'cardinality' => 1,
    ])->save();
    /** @var \Drupal\field\FieldConfigInterface $entity_reference_field */
    $entity_reference_field = $entity_type_manager->getStorage('field_config')->create([
      'entity_type' => 'node',
      'field_name' => 'entity_reference_field',
      'bundle' => $node_type->id(),
      'label' => 'Entity reference field',
      'translatable' => FALSE,
      'settings' => [
        'handler' => 'default:node',
        'handler_settings' => [
          'target_bundles' => [
            'test_ct' => 'test_ct',
          ],
        ],
      ],
    ]);
    $entity_reference_field->save();

    // Create a text field.
    // Create an entity reference field.
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

    // Access the text field edit form and assert that the composite
    // option is not available.
    $url = Url::fromRoute("entity.field_config.node_field_edit_form", ['node_type' => $node_type->id(), 'field_config' => $text_field->id()]);
    $this->drupalGet($url);
    $this->assertSession()->pageTextContains('Text field settings for Test content type');
    $this->assertSession()->pageTextNotContains('Composite field');

    // Access the text entity reference edit form and assert that the composite
    // option is available.
    $url = Url::fromRoute("entity.field_config.node_field_edit_form", ['node_type' => $node_type->id(), 'field_config' => $entity_reference_field->id()]);
    $this->drupalGet($url);
    $this->assertSession()->pageTextContains('Entity reference field settings for Test content type');
    $this->assertSession()->pageTextContains('Composite field');
    // The configuration is disabled by default.
    $this->assertSession()->checkboxChecked('Disabled');
    $this->assertSession()->checkboxNotChecked('Enabled');
    // Enable the composite reference and save it.
    $this->getSession()->getPage()->selectFieldOption('composite', '1');
    $this->getSession()->getPage()->pressButton('Save settings');

    // Reload the page and assert the changes where saved.
    $this->drupalGet($url);
    $this->assertSession()->pageTextContains('Entity reference field settings for Test content type');
    $this->assertSession()->pageTextContains('Composite field');
    // The configuration is enabled.
    $this->assertSession()->checkboxNotChecked('Disabled');
    $this->assertSession()->checkboxChecked('Enabled');
  }

}
