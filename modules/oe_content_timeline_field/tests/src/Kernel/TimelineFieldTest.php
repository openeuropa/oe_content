<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Kernel;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\filter\Entity\FilterFormat;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;

/**
 * Tests the timeline field type definition.
 */
class TimelineFieldTest extends EntityKernelTestBase {

  /**
   * A field storage to use in this test class.
   *
   * @var \Drupal\field\Entity\FieldStorageConfig
   */
  protected $fieldStorage;

  /**
   * The field used in this test class.
   *
   * @var \Drupal\field\Entity\FieldConfig
   */
  protected $field;

  /**
   * The display options to use in the formatter.
   *
   * @var array
   */
  protected $displayOptions = [
    'type' => 'timeline_formatter',
    'label' => 'hidden',
    'settings' => [
      'limit' => 2,
      'show_more' => 'Button label',
    ],
  ];

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'field',
    'node',
    'oe_content_timeline_field',
    'system',
    'text',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installSchema('user', 'users_data');
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installConfig(['field', 'node', 'system']);

    // Create content type.
    $type = NodeType::create([
      'name' => 'Test content type',
      'type' => 'test_ct',
    ]);
    $type->save();

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

    $this->fieldStorage = FieldStorageConfig::create([
      'field_name' => 'field_timeline',
      'entity_type' => 'node',
      'type' => 'timeline_field',
      'cardinality' => -1,
      'entity_types' => ['node'],
    ]);
    $this->fieldStorage->save();

    $this->field = FieldConfig::create([
      'label' => 'Timeline field',
      'field_name' => 'field_timeline',
      'entity_type' => 'node',
      'bundle' => 'test_ct',
      'settings' => [],
      'required' => FALSE,
    ]);
    $this->field->save();

    EntityViewDisplay::create([
      'targetEntityType' => $this->field->getTargetEntityTypeId(),
      'bundle' => $this->field->getTargetBundle(),
      'mode' => 'default',
      'status' => TRUE,
    ])->setComponent($this->fieldStorage->getName(), $this->displayOptions)
      ->save();
  }

  /**
   * Test the timeline field.
   */
  public function testTimeline(): void {
    $values = [
      'type' => 'test_ct',
      'title' => 'My node title',
      'field_timeline' => [
        [
          'label' => '16/07/2019',
          'title' => 'Item 1',
          'body' => 'Item 1 body',
          'format' => 'my_text_format',
        ],
        [
          'label' => '16/07/2019',
          'title' => 'Item 2',
          'body' => 'Item 2 body',
        ],
        [
          'label' => '16/07/2019',
          'title' => 'Item 3',
          'body' => 'Item 3 body',
        ],
        [
          'label' => 'Test € — ☺',
          'title' => 'Item 4',
          'body' => 'Item 4 body',
          'format' => 'my_text_format',
        ],
      ],
    ];

    // Create node.
    $node = Node::create($values);
    $node->save();

    $entity_type_manager = \Drupal::entityTypeManager()->getStorage('node');
    $entity_type_manager->resetCache();
    /** @var \Drupal\node\NodeInterface $node */
    $node = $entity_type_manager->load($node->id());

    $expected = [
      [
        'label' => '16/07/2019',
        'title' => 'Item 1',
        'body' => 'Item 1 body',
        'format' => 'my_text_format',
      ],
      [
        'label' => '16/07/2019',
        'title' => 'Item 2',
        'body' => 'Item 2 body',
        'format' => NULL,
      ],
      [
        'label' => '16/07/2019',
        'title' => 'Item 3',
        'body' => 'Item 3 body',
        'format' => NULL,
      ],
      [
        'label' => 'Test € — ☺',
        'title' => 'Item 4',
        'body' => 'Item 4 body',
        'format' => 'my_text_format',
      ],
    ];
    // Assert the base field values.
    $this->assertEquals('My node title', $node->label());
    $this->assertEquals($expected, $node->get('field_timeline')->getValue());

    // Test empty timeline.
    $values = [
      'type' => 'test_ct',
      'title' => 'My second node',
      'field_timeline' => [
        [
          'label' => '',
          'title' => '',
          'body' => '',
          'format' => '',
        ],
      ],
    ];

    // Create node.
    $node = Node::create($values);
    $node->save();

    // Assert the base field values.
    $this->assertEquals('My second node', $node->label());
    $this->assertTrue($node->get('field_timeline')->isEmpty());
  }

  /**
   * Test the timeline field rendering with formatter.
   */
  public function testTimelineRender(): void {
    $values = [
      'type' => 'test_ct',
      'title' => 'My node title',
      'field_timeline' => [
        [
          'label' => '16/07/2019',
          'title' => 'Item 1',
          'body' => 'Item 1 body',
          'format' => 'my_text_format',
        ],
        [
          'label' => '16/07/2019',
          'title' => 'Item 2',
          'body' => 'Item 2 body',
          'format' => 'my_text_format',
        ],
        [
          'label' => '16/07/2019',
          'title' => 'Item 3',
          'body' => 'Item 3 body',
          'format' => 'my_text_format',
        ],
      ],
    ];

    // Create node.
    $node = Node::create($values);
    $node->save();

    // Verify the timeline uses the correct format settings.
    $display = EntityViewDisplay::collectRenderDisplay($node, 'default');
    $build = $display->build($node);
    $output = $this->container->get('renderer')->renderRoot($build);

    $this->assertStringContainsString('<div>Item 1</div>', (string) $output);
    $this->assertStringContainsString('<p>Item 1 body</p>', (string) $output);
    $this->assertStringContainsString('<div>Item 2</div>', (string) $output);
    $this->assertStringContainsString('<p>Item 2 body</p>', (string) $output);
    $this->assertStringContainsString('<div>Item 3</div>', (string) $output);
    $this->assertStringContainsString('<p>Item 3 body</p>', (string) $output);
    $this->assertStringContainsString('<button>Button label</button>', (string) $output);

    // Change the limit to show all items without the "show more" button.
    $this->displayOptions['settings']['limit'] = 0;
    $display->setComponent('field_timeline', $this->displayOptions)->save();

    $build = $display->build($node);
    $output = $this->container->get('renderer')->renderRoot($build);
    $this->assertStringNotContainsString('<button>Button label</button>', (string) $output);
  }

}
