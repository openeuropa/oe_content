<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_description_list_field\Kernel;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\filter\Entity\FilterFormat;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the Description list field type and formatter.
 */
class DescriptionListFieldTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'node',
    'field',
    'filter',
    'text',
    'user',
    'views',
    'oe_content_description_list_field',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installConfig([
      'system',
      'node',
      'field',
      'views',
      'user',
    ]);

    $this->installEntitySchema('node');
    $this->installEntitySchema('user');

    $this->container->get('entity_type.manager')->getStorage('node_type')->create([
      'type' => 'page',
      'name' => 'Page',
    ])->save();

    FilterFormat::create([
      'format' => 'my_text_format',
      'name' => 'My text format',
      'filters' => [
        'filter_autop' => [
          'module' => 'filter',
          'status' => TRUE,
        ],
      ],
    ])->save();

    FieldStorageConfig::create([
      'field_name' => 'description_list',
      'entity_type' => 'node',
      'type' => 'description_list_field',
      'cardinality' => -1,
    ])->save();

    FieldConfig::create([
      'entity_type' => 'node',
      'field_name' => 'description_list',
      'bundle' => 'page',
    ])->save();
  }

  /**
   * Tests the field type and formatter.
   */
  public function testDescriptionListField(): void {
    /** @var \Drupal\node\NodeStorageInterface $node_storage */
    $node_storage = $this->container->get('entity_type.manager')->getStorage('node');

    $values = [
      [
        'term' => 'Term 1',
        'description' => 'Description 1',
        'format' => 'my_text_format',
      ],
      [
        'term' => 'Term 2',
        'description' => '<h2>Description 2</h2>',
        'format' => 'my_text_format',
      ],
      [
        'term' => 'Term 3',
        'description' => '',
        'format' => 'my_text_format',
      ],
      [
        'term' => '',
        'description' => '<h3>Description 4</h3>',
        'format' => 'my_text_format',
      ],
    ];

    /** @var \Drupal\node\NodeInterface $node */
    $node = $node_storage->create([
      'type' => 'page',
      'title' => 'Test page',
      'description_list' => $values,
    ]);
    // Assert values are saved in the field.
    $this->assertEquals($values, $node->get('description_list')->getValue());

    // Assert formatter rendering.
    $builder = $this->container->get('entity_type.manager')->getViewBuilder('node');
    $build = $builder->viewField($node->get('description_list'));
    $output = $this->container->get('renderer')->renderRoot($build);
    $this->assertContains('<dl>', (string) $output);
    $this->assertContains('<dt>Term 1</dt>', (string) $output);
    $this->assertContains('<dd><p>Description 1</p>' . "\n" . '</dd>', (string) $output);
    $this->assertContains('<dt>Term 2</dt>', (string) $output);
    $this->assertContains('<dd><h2>Description 2</h2>' . "\n" . '</dd>', (string) $output);
    $this->assertContains('<dt>Term 3</dt>', (string) $output);
    $this->assertContains('<dd><h3>Description 4</h3>' . "\n" . '</dd>', (string) $output);
    $this->assertContains('</dl>', (string) $output);
  }

}
