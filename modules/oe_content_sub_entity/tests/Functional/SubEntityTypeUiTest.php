<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_entity\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\oe_content\Traits\EntityTypeUiTrait;

/**
 * Test sub entity type UIs.
 */
class SubEntityTypeUiTest extends BrowserTestBase {

  use EntityTypeUiTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'oe_content_sub_entity_document_reference',
  ];

  /**
   * Tests sub entity type UIs, such as creation and removing of a new bundle.
   */
  public function testEntityTypeUi(): void {
    $user = $this->drupalCreateUser([
      'administer sub entity types',
      'access administration pages',
    ]);
    $this->drupalLogin($user);

    foreach ($this->entityDataTestCases() as $info) {
      list($entity_type_id, $label) = $info;
      $bundle = str_replace(' ', '_', $label) . '_type_bundle';

      // Entity type bundle can be created.
      $this->createEntityTypeBundle($entity_type_id, $label, $bundle);

      // Entity type bundle can be removed.
      $this->removeEntityTypeBundle($entity_type_id, $label, $bundle);
    }
  }

  /**
   * Provides a set of test cases to be used by self::testEntityTypeUi().
   *
   * - entity type.
   * - label.
   *
   * @return array
   *   List of test cases.
   */
  public function entityDataTestCases(): array {
    return [
      ['oe_document_reference', 'document reference'],
    ];
  }

}
