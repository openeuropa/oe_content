<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content\FunctionalJavascript;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\field_ui\Traits\FieldUiTestTrait;
use Drupal\Tests\oe_content\Traits\CollapsibleFieldTrait;
use Drupal\Tests\oe_content\Traits\TableDragTrait;
use Drupal\Tests\sparql_entity_storage\Traits\SparqlConnectionTrait;

/**
 * Test collapsible field widget.
 */
class CollapsibleWidgetTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  use FieldUiTestTrait;
  use CollapsibleFieldTrait;
  use TableDragTrait;
  use SparqlConnectionTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'node',
    'filter',
    'field',
    'field_ui',
    'oe_content',
    'oe_collapsible_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->setUpSparql();

    $this->createContentType([
      'type' => 'collapsible_test',
      'name' => 'Collapsible test',
    ]);

    $field_storage = FieldStorageConfig::loadByName('node', 'oe_collapsible_test');
    if (!$field_storage) {
      $field_storage = FieldStorageConfig::create([
        'field_name' => 'oe_collapsible_test',
        'entity_type' => 'node',
        'type' => 'collapsible_test_field',
        'cardinality' => -1,
      ]);
      $field_storage->save();
    }
    $field = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => 'collapsible_test',
    ]);
    $field->save();

    $form_display = \Drupal::service('entity_display.repository')->getFormDisplay('node', 'collapsible_test');
    $form_display = $form_display->setComponent('oe_collapsible_test', ['type' => 'collapsible_test_widget']);
    $form_display->save();

    $view_display = \Drupal::service('entity_display.repository')->getViewDisplay('node', 'collapsible_test');
    $view_display->setComponent('oe_collapsible_test', ['type' => 'collapsible_test_formatter']);
    $view_display->save();
  }

  /**
   * Tests the workflow of the widget.
   */
  public function testCollapsibleWidget() {
    $user = $this->drupalCreateUser([
      'access content',
      'create collapsible_test content',
      'edit any collapsible_test content',
    ]);

    $this->drupalLogin($user);

    // Go to the node creation page and locate the field.
    $this->drupalGet('node/add/collapsible_test');
    $page = $this->getSession()->getPage();
    $field = $page->find('css', '.field--name-oe-collapsible-test');
    $this->assertNotEmpty($field);

    // Add three items.
    $field->pressButton('Add another item');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $field->pressButton('Add another item');
    $this->assertSession()->assertWaitOnAjaxRequest();

    $this->assertCollapsibleTable($field, [
      ['mode' => 'open', 'Title' => '', 'Content' => ''],
      ['mode' => 'open', 'Title' => '', 'Content' => ''],
      ['mode' => 'open', 'Title' => '', 'Content' => ''],
    ]);

    // Test expanding and collapsing items.
    $this->fillCollapsibleRow($field, 1, ['Title' => 'T1', 'Content' => 'B1']);
    $this->fillCollapsibleRow($field, 2, ['Title' => 'T2', 'Content' => 'B2']);
    $this->performCollapsibleAction($field, 1, 'Collapse');
    $this->performCollapsibleAction($field, 2, 'Collapse');
    $this->assertCollapsibleTable($field, [
      ['mode' => 'closed', 'Title' => 'T1', 'Content' => 'B1'],
      ['mode' => 'closed', 'Title' => 'T2', 'Content' => 'B2'],
      ['mode' => 'open', 'Title' => '', 'Content' => ''],
    ]);
    $this->fillCollapsibleRow($field, 3, ['Title' => 'T3', 'Content' => 'B3']);
    $this->performCollapsibleAction($field, 2, 'Edit');
    $this->assertCollapsibleTable($field, [
      ['mode' => 'closed', 'Title' => 'T1', 'Content' => 'B1'],
      ['mode' => 'open', 'Title' => 'T2', 'Content' => 'B2'],
      ['mode' => 'open', 'Title' => 'T3', 'Content' => 'B3'],
    ]);
    $field->pressButton('Collapse all');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertCollapsibleTable($field, [
      ['mode' => 'closed', 'Title' => 'T1', 'Content' => 'B1'],
      ['mode' => 'closed', 'Title' => 'T2', 'Content' => 'B2'],
      ['mode' => 'closed', 'Title' => 'T3', 'Content' => 'B3'],
    ]);
    $this->performCollapsibleAction($field, 1, 'Edit');
    $this->performCollapsibleHeaderSubaction($field, 'Edit all');
    $this->assertCollapsibleTable($field, [
      ['mode' => 'open', 'Title' => 'T1', 'Content' => 'B1'],
      ['mode' => 'open', 'Title' => 'T2', 'Content' => 'B2'],
      ['mode' => 'open', 'Title' => 'T3', 'Content' => 'B3'],
    ]);

    // Test removing items.
    $this->performCollapsibleSubaction($field, 2, 'Remove');
    $this->assertCollapsibleTable($field, [
      ['mode' => 'open', 'Title' => 'T1', 'Content' => 'B1'],
      ['mode' => 'open', 'Title' => 'T3', 'Content' => 'B3'],
    ]);

    // Test duplicating items.
    $this->performCollapsibleAction($field, 2, 'Collapse');
    $this->performCollapsibleSubaction($field, 1, 'Duplicate');
    $this->assertCollapsibleTable($field, [
      ['mode' => 'open', 'Title' => 'T1', 'Content' => 'B1'],
      ['mode' => 'open', 'Title' => 'T1', 'Content' => 'B1'],
      ['mode' => 'closed', 'Title' => 'T3', 'Content' => 'B3'],
    ]);

    $this->fillCollapsibleRow($field, 2, ['Title' => 'T2-2', 'Content' => 'B2-2']);
    $this->performCollapsibleSubaction($field, 3, 'Duplicate');
    $this->assertCollapsibleTable($field, [
      ['mode' => 'open', 'Title' => 'T1', 'Content' => 'B1'],
      ['mode' => 'open', 'Title' => 'T2-2', 'Content' => 'B2-2'],
      ['mode' => 'closed', 'Title' => 'T3', 'Content' => 'B3'],
      ['mode' => 'open', 'Title' => 'T3', 'Content' => 'B3'],
    ]);

    // Test adding items above.
    $this->fillCollapsibleRow($field, 4, ['Title' => 'T4', 'Content' => 'B4']);
    $field->pressButton('Collapse all');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->performCollapsibleSubaction($field, 3, 'Add above');
    $this->performCollapsibleSubaction($field, 1, 'Add above');
    $this->assertCollapsibleTable($field, [
      ['mode' => 'open', 'Title' => '', 'Content' => ''],
      ['mode' => 'closed', 'Title' => 'T1', 'Content' => 'B1'],
      ['mode' => 'closed', 'Title' => 'T2-2', 'Content' => 'B2-2'],
      ['mode' => 'open', 'Title' => '', 'Content' => ''],
      ['mode' => 'closed', 'Title' => 'T3', 'Content' => 'B3'],
      ['mode' => 'closed', 'Title' => 'T4', 'Content' => 'B4'],
    ]);

    // Test removing items.
    $this->performCollapsibleSubaction($field, 2, 'Remove');
    $this->performCollapsibleSubaction($field, 3, 'Remove');
    $this->performCollapsibleSubaction($field, 4, 'Remove');
    $this->assertCollapsibleTable($field, [
      ['mode' => 'open', 'Title' => '', 'Content' => ''],
      ['mode' => 'closed', 'Title' => 'T2-2', 'Content' => 'B2-2'],
      ['mode' => 'closed', 'Title' => 'T3', 'Content' => 'B3'],
    ]);

    // Test saving the node with different element modes.
    $this->fillCollapsibleRow($field, 1, ['Title' => 'T0', 'Content' => 'B0']);
    $this->performCollapsibleAction($field, 1, 'Collapse');
    $this->performCollapsibleAction($field, 2, 'Edit');
    $this->assertCollapsibleTable($field, [
      ['mode' => 'closed', 'Title' => 'T0', 'Content' => 'B0'],
      ['mode' => 'open', 'Title' => 'T2-2', 'Content' => 'B2-2'],
      ['mode' => 'closed', 'Title' => 'T3', 'Content' => 'B3'],
    ]);
    $page->fillField('Title', 'Test node');
    $page->fillField('oe_content_content_owner[0][target_id]', 'Audit Board of the European Communities (http://publications.europa.eu/resource/authority/corporate-body/ABEC)');
    $page->pressButton('Save');
    $node = $this->drupalGetNodeByTitle('Test node');
    $this->drupalGet($node->toUrl('edit-form'));
    $field = $page->find('css', '.field--name-oe-collapsible-test');
    $this->assertCollapsibleTable($field, [
      ['mode' => 'closed', 'Title' => 'T0', 'Content' => 'B0'],
      ['mode' => 'closed', 'Title' => 'T2-2', 'Content' => 'B2-2'],
      ['mode' => 'closed', 'Title' => 'T3', 'Content' => 'B3'],
    ]);

    // Test reordering items.
    $this->sortTableDragRow('.field--name-oe-collapsible-test table', 1, 2);
    $this->assertCollapsibleTable($field, [
      ['mode' => 'closed', 'Title' => 'T2-2', 'Content' => 'B2-2'],
      ['mode' => 'closed', 'Title' => 'T0', 'Content' => 'B0'],
      ['mode' => 'closed', 'Title' => 'T3', 'Content' => 'B3'],
    ]);

    // Duplicate the first element and move it after the second element.
    $this->performCollapsibleSubaction($field, 1, 'Duplicate');
    $this->performCollapsibleAction($field, 2, 'Collapse');
    $this->sortTableDragRow('.field--name-oe-collapsible-test table', 2, 3);
    $this->performCollapsibleAction($field, 3, 'Edit');
    $this->assertCollapsibleTable($field, [
      ['mode' => 'closed', 'Title' => 'T2-2', 'Content' => 'B2-2'],
      ['mode' => 'closed', 'Title' => 'T0', 'Content' => 'B0'],
      ['mode' => 'open', 'Title' => 'T2-2', 'Content' => 'B2-2'],
      ['mode' => 'closed', 'Title' => 'T3', 'Content' => 'B3'],
    ]);

    // Close all, then edit all and check if the order is preserved.
    $field->pressButton('Collapse all');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $field->pressButton('Edit all');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertCollapsibleTable($field, [
      ['mode' => 'open', 'Title' => 'T2-2', 'Content' => 'B2-2'],
      ['mode' => 'open', 'Title' => 'T0', 'Content' => 'B0'],
      ['mode' => 'open', 'Title' => 'T2-2', 'Content' => 'B2-2'],
      ['mode' => 'open', 'Title' => 'T3', 'Content' => 'B3'],
    ]);

    // Add an empty element and remove it immediately.
    // Then collapse all and check if the order is preserved.
    // This tests an edge case for proper handling of widget state.
    $this->performCollapsibleSubaction($field, 4, 'Add above');
    $this->performCollapsibleSubaction($field, 4, 'Remove');
    $field->pressButton('Collapse all');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertCollapsibleTable($field, [
      ['mode' => 'closed', 'Title' => 'T2-2', 'Content' => 'B2-2'],
      ['mode' => 'closed', 'Title' => 'T0', 'Content' => 'B0'],
      ['mode' => 'closed', 'Title' => 'T2-2', 'Content' => 'B2-2'],
      ['mode' => 'closed', 'Title' => 'T3', 'Content' => 'B3'],
    ]);

    // Change order, save and check results.
    $this->sortTableDragRow('.field--name-oe-collapsible-test table', 2, 3);
    $this->assertCollapsibleTable($field, [
      ['mode' => 'closed', 'Title' => 'T2-2', 'Content' => 'B2-2'],
      ['mode' => 'closed', 'Title' => 'T2-2', 'Content' => 'B2-2'],
      ['mode' => 'closed', 'Title' => 'T0', 'Content' => 'B0'],
      ['mode' => 'closed', 'Title' => 'T3', 'Content' => 'B3'],
    ]);
    $page->pressButton('Save');
    $this->assertSession()->pageTextContains('Test node has been updated.');
    $this->drupalGet($node->toUrl('edit-form'));
    $this->assertCollapsibleTable($field, [
      ['mode' => 'closed', 'Title' => 'T2-2', 'Content' => 'B2-2'],
      ['mode' => 'closed', 'Title' => 'T2-2', 'Content' => 'B2-2'],
      ['mode' => 'closed', 'Title' => 'T0', 'Content' => 'B0'],
      ['mode' => 'closed', 'Title' => 'T3', 'Content' => 'B3'],
    ]);
  }

  /**
   * Tests the configuration of the widget.
   */
  public function testCollapsibleWidgetSettings() {
    $user = $this->drupalCreateUser([
      'access content',
      'administer content types',
      'administer node form display',
      'create collapsible_test content',
      'edit any collapsible_test content',
    ]);
    $this->drupalLogin($user);

    // Add a node with two collapsible items.
    $node = $this->drupalCreateNode([
      'type' => 'collapsible_test',
      'oe_collapsible_test' => [
        ['title' => 'T1', 'body' => 'B1'],
        ['title' => 'T2', 'body' => 'B2'],
      ],
    ]);

    // Check if the collapsible items are closed by default.
    $page = $this->getSession()->getPage();
    $this->drupalGet($node->toUrl('edit-form'));
    $field = $page->find('css', '.field--name-oe-collapsible-test');
    $this->assertCollapsibleTable($field, [
      ['mode' => 'closed', 'Title' => 'T1', 'Content' => 'B1'],
      ['mode' => 'closed', 'Title' => 'T2', 'Content' => 'B2'],
    ]);

    // Go to the form display page and change the settings.
    $this->drupalGet('admin/structure/types/manage/collapsible_test/form-display');
    $edit_formatter_button = $this->assertSession()->waitForElementVisible('css', '[data-drupal-selector="edit-fields-oe-collapsible-test-settings-edit"]');
    $edit_formatter_button->press();
    $this->assertSession()->waitForText('Widget settings');
    $mode_field = $this->assertSession()->waitForField('fields[oe_collapsible_test][settings_edit_form][settings][edit_mode]');
    $mode_field->setValue('open');
    $page->uncheckField('fields[oe_collapsible_test][settings_edit_form][settings][additional_options][duplicate]');
    $page->uncheckField('fields[oe_collapsible_test][settings_edit_form][settings][additional_options][collapse_all]');
    $page->uncheckField('fields[oe_collapsible_test][settings_edit_form][settings][additional_options][edit_all]');
    $page->uncheckField('fields[oe_collapsible_test][settings_edit_form][settings][additional_options][add_new_item_before]');
    $update_button = $page->find('css', '[data-drupal-selector="edit-fields-oe-collapsible-test-settings-edit-form-actions-save-settings"]');
    $update_button->press();
    $this->assertSession()->assertNoElementAfterWait('css', '[data-drupal-selector="edit-fields-oe-collapsible-test-settings-edit-form-actions-cancel-settings"]');
    $changed_warning = $this->assertSession()->waitForElementVisible('css', '.tabledrag-changed-warning');
    $this->assertEquals('* You have unsaved changes.', $changed_warning->getText());
    $page->pressButton('Save');

    // Check if the collapsible items are opened by default.
    $this->drupalGet($node->toUrl('edit-form'));
    $field = $page->find('css', '.field--name-oe-collapsible-test');
    $this->assertCollapsibleTable($field, [
      ['mode' => 'open', 'Title' => 'T1', 'Content' => 'B1'],
      ['mode' => 'open', 'Title' => 'T2', 'Content' => 'B2'],
    ]);

    // Check if the optional features are hidden.
    $this->assertSession()->buttonNotExists('Duplicate', $field);
    $this->assertSession()->buttonNotExists('Collapse all', $field);
    $this->assertSession()->buttonNotExists('Edit all', $field);
    $this->assertSession()->buttonNotExists('Add above', $field);
    $this->assertSession()->buttonExists('Collapse', $field);
    $this->assertSession()->buttonExists('Remove', $field);
    $this->performCollapsibleAction($field, 1, 'Collapse');
    $this->assertSession()->buttonNotExists('Edit all', $field);
    $this->assertSession()->buttonExists('Edit', $field);
  }

}
