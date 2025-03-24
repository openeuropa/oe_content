<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content\FunctionalJavascript;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\oe_content\Traits\CollapsibleFieldTrait;
use Drupal\Tests\sparql_entity_storage\Traits\SparqlConnectionTrait;

/**
 * Test timeline field widget.
 */
class TimelineWidgetUiTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  use CollapsibleFieldTrait;
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
    'oe_content_timeline_field',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->setUpSparql();

    $this->createContentType([
      'type' => 'timeline_test',
      'name' => 'Timeline test',
    ]);

    $field_storage = FieldStorageConfig::loadByName('node', 'oe_timeline');
    if (!$field_storage) {
      $field_storage = FieldStorageConfig::create([
        'field_name' => 'oe_timeline',
        'entity_type' => 'node',
        'type' => 'timeline_field',
        'cardinality' => -1,
      ]);
      $field_storage->save();
    }
    $field = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => 'timeline_test',
    ]);
    $field->save();

    $form_display = \Drupal::service('entity_display.repository')->getFormDisplay('node', 'timeline_test');
    $form_display = $form_display->setComponent('oe_timeline', ['type' => 'timeline_widget']);
    $form_display->save();

    $view_display = \Drupal::service('entity_display.repository')->getViewDisplay('node', 'timeline_test');
    $view_display->setComponent('oe_timeline', ['type' => 'timeline_formatter']);
    $view_display->save();
  }

  /**
   * Tests the UI of the widget.
   */
  public function testTimelineWidgetUi() {
    $user = $this->drupalCreateUser([
      'access content',
      'create timeline_test content',
      'edit any timeline_test content',
    ]);

    $this->drupalLogin($user);

    // Go to the node creation page and locate the field.
    $this->drupalGet('node/add/timeline_test');
    $page = $this->getSession()->getPage();
    $field = $page->find('css', '.field--name-oe-timeline');
    $this->assertNotEmpty($field);

    // Add three items (one is already added by default).
    $this->fillCollapsibleRow($field, 1, ['Title' => 'T1', 'Label' => 'L1', 'Content' => 'B1']);
    $field->pressButton('Add another item');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->fillCollapsibleRow($field, 2, ['Title' => 'T2', 'Label' => 'L2', 'Content' => 'B2']);
    $field->pressButton('Add another item');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->fillCollapsibleRow($field, 3, ['Title' => 'T3', 'Label' => 'L3', 'Content' => 'B3']);

    // Close the third element, then do some duplications.
    $this->performCollapsibleAction($field, 3, 'Collapse');
    $this->performCollapsibleSubaction($field, 1, 'Duplicate');
    $this->performCollapsibleSubaction($field, 4, 'Duplicate');
    $this->assertCollapsibleTable($field, [
      ['mode' => 'open', 'Label' => 'L1', 'Title' => 'T1', 'Content' => 'B1'],
      ['mode' => 'open', 'Label' => 'L1', 'Title' => 'T1', 'Content' => 'B1'],
      ['mode' => 'open', 'Label' => 'L2', 'Title' => 'T2', 'Content' => 'B2'],
      ['mode' => 'closed', 'Label' => 'L3', 'Title' => 'T3', 'Content' => 'B3'],
      ['mode' => 'open', 'Label' => 'L3', 'Title' => 'T3', 'Content' => 'B3'],
    ]);

    // Save the node and check the values.
    $page->fillField('Title', 'Test node');
    $page->fillField('oe_content_content_owner[0][target_id]', 'Audit Board of the European Communities (http://publications.europa.eu/resource/authority/corporate-body/ABEC)');
    $page->pressButton('Save');
    $node = $this->drupalGetNodeByTitle('Test node');
    $this->drupalGet($node->toUrl('edit-form'));
    $field = $page->find('css', '.field--name-oe-timeline');
    $this->assertCollapsibleTable($field, [
      ['mode' => 'closed', 'Label' => 'L1', 'Title' => 'T1', 'Content' => 'B1'],
      ['mode' => 'closed', 'Label' => 'L1', 'Title' => 'T1', 'Content' => 'B1'],
      ['mode' => 'closed', 'Label' => 'L2', 'Title' => 'T2', 'Content' => 'B2'],
      ['mode' => 'closed', 'Label' => 'L3', 'Title' => 'T3', 'Content' => 'B3'],
      ['mode' => 'closed', 'Label' => 'L3', 'Title' => 'T3', 'Content' => 'B3'],
    ]);
  }

}
