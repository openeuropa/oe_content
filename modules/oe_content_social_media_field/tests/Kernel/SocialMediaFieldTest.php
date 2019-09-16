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
 * Tests the social media field type definition.
 */
class SocialMediaFieldTest extends EntityKernelTestBase {

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
    'type' => 'social_media_link_formatter',
    'label' => 'hidden',
  ];

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'field',
    'node',
    'oe_content_social_media_field',
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
    $type = NodeType::create(['name' => 'Test content type', 'type' => 'test_ct']);
    $type->save();

    $this->fieldStorage = FieldStorageConfig::create([
      'field_name' => 'field_social_media_links',
      'entity_type' => 'node',
      'type' => 'social_media_link',
      'cardinality' => -1,
      'entity_types' => ['node'],
    ]);
    $this->fieldStorage->save();

    $this->field = FieldConfig::create([
      'label' => 'Social media links',
      'field_name' => 'field_social_media_links',
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
   * Test the social media field.
   */
  public function testSocialMediaField(): void {
    $values = [
      'type' => 'test_ct',
      'title' => 'My node title',
      'field_social_media_links' => [
        [
          'type' => 'facebook',
          'url' => 'http://example.com/facebook',
          'title' => 'Facebook link',
        ],
        [
          'type' => 'email',
          'url' => 'mailto:test@example.com',
          'title' => 'Email link',
        ],
        [
          'type' => 'twitter',
          'url' => 'http://example.com/twitter',
          'title' => 'Twitter link',
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
        'type' => 'facebook',
        'url' => 'http://example.com/facebook',
        'title' => 'Facebook link',
      ],
      [
        'type' => 'email',
        'url' => 'mailto:test@example.com',
        'title' => 'Email link',
      ],
      [
        'type' => 'twitter',
        'url' => 'http://example.com/twitter',
        'title' => 'Twitter link',
      ],
    ];
    // Assert the base field values.
    $this->assertEquals('My node title', $node->label());
    $this->assertEquals($expected, $node->get('field_social_media_links')->getValue());
  }

  /**
   * Test the social media field rendering with formatter.
   */
  public function testSocialMediaFieldRender(): void {
    $values = [
      'type' => 'test_ct',
      'title' => 'My node title',
      'field_social_media_links' => [
        [
          'type' => 'facebook',
          'url' => 'http://example.com/facebook',
          'title' => 'Facebook link',
        ],
        [
          'type' => 'email',
          'url' => 'mailto:test@example.com',
          'title' => 'Email link',
        ],
        [
          'type' => 'twitter',
          'url' => 'http://example.com/twitter',
          'title' => 'Twitter link',
        ],
      ],
    ];

    // Create node.
    $node = Node::create($values);
    $node->save();

    // Verify the social media field uses the correct format.
    $display = EntityViewDisplay::collectRenderDisplay($node, 'default');
    $build = $display->build($node);
    $output = $this->container->get('renderer')->renderRoot($build);

    $this->assertContains('<p>facebook</p>', (string) $output);
    $this->assertContains('<p>http://example.com/facebook</p>', (string) $output);
    $this->assertContains('<p>Facebook link</p>', (string) $output);
    $this->assertContains('<p>email</p>', (string) $output);
    $this->assertContains('<p>mailto:test@example.com</p>', (string) $output);
    $this->assertContains('<p>Email link</p>', (string) $output);
    $this->assertContains('<p>twitter</p>', (string) $output);
    $this->assertContains('<p>http://example.com/twitter</p>', (string) $output);
    $this->assertContains('<p>Twitter link</p>', (string) $output);
  }

}
