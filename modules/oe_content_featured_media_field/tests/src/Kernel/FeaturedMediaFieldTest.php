<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_featured_media_field\Kernel;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\file\Entity\File;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;

/**
 * Tests the featured media field type definition.
 *
 * @group oe_content_featured_media_field
 */
class FeaturedMediaFieldTest extends EntityKernelTestBase {

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
   * The media entity used in this test class.
   *
   * @var \Drupal\media\Entity\Media
   */
  protected $mediaEntity;

  /**
   * The entity view display used for test.
   *
   * @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface
   */
  protected $display;

  /**
   * The display options to use in the formatter.
   *
   * @var array
   */
  protected $displayOptions = [
    'type' => 'oe_featured_media_label',
    'label' => 'above',
    'settings' => [
      'link' => TRUE,
    ],
  ];

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'field',
    'node',
    'media',
    'file',
    'image',
    'oe_media',
    'oe_content_featured_media_field',
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
    $this->installSchema('file', 'file_usage');
    $this->installEntitySchema('media');
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installEntitySchema('file');
    $this->installConfig([
      'media',
      'image',
      'file',
      'system',
      'oe_media',
      'oe_content_featured_media_field',
    ]);

    // Create an account with permission and set it as current user.
    $account = $this->createUser(['view media']);
    $this->container->get('current_user')->setAccount($account);

    // Create an image file.
    \Drupal::service('file_system')->copy($this->root . '/core/misc/druplicon.png', 'public://example.jpg');
    $image = File::create(['uri' => 'public://example.jpg']);
    $image->save();

    // Create a media entity of image media type.
    $media_type = $this->entityTypeManager->getStorage('media_type')->load('image');
    $this->mediaEntity = Media::create([
      'bundle' => $media_type->id(),
      'name' => 'Test image',
      'field_media_image' => [
        [
          'target_id' => $image->id(),
          'alt' => 'default alt',
          'title' => 'default title',
        ],
      ],
    ]);
    $this->mediaEntity->save();

    // Create a content type.
    $type = NodeType::create(['name' => 'Test content type', 'type' => 'test_ct']);
    $type->save();

    $this->fieldStorage = FieldStorageConfig::create([
      'field_name' => 'featured_media_field',
      'entity_type' => 'node',
      'type' => 'oe_featured_media',
      'cardinality' => 1,
      'entity_types' => ['node'],
    ]);
    $this->fieldStorage->save();

    $this->field = FieldConfig::create([
      'label' => 'Featured media field',
      'field_name' => 'featured_media_field',
      'entity_type' => 'node',
      'bundle' => 'test_ct',
      'settings' => [
        'handler' => 'default:media',
        'handler_settings' => [
          'target_bundles' => [
            'image' => 'image',
          ],
        ],
        'sort' => [
          'field' => '_none',
        ],
        'auto_create' => '0',
      ],
      'required' => FALSE,
    ]);
    $this->field->save();

    // Prepare the default view display for rendering.
    $this->display = \Drupal::service('entity_display.repository')
      ->getViewDisplay($this->field->getTargetEntityTypeId(), $this->field->getTargetBundle())
      ->setComponent($this->fieldStorage->getName(), $this->displayOptions);
    $this->display->save();
  }

  /**
   * Test the featured media field.
   */
  public function testFeaturedMediaField(): void {
    $values = [
      'type' => 'test_ct',
      'title' => 'My node title',
      'featured_media_field' => [
        [
          'target_id' => $this->mediaEntity->id(),
          'caption' => 'Image caption text',
        ],
      ],
    ];

    // Create a node.
    $node = Node::create($values);
    $node->save();

    $entity_type_manager = \Drupal::entityTypeManager()->getStorage('node');
    $entity_type_manager->resetCache();
    /** @var \Drupal\node\NodeInterface $node */
    $node = $entity_type_manager->load($node->id());

    $expected = [
      [
        'target_id' => $this->mediaEntity->id(),
        'caption' => 'Image caption text',
      ],
    ];
    // Assert the base field values.
    $this->assertEquals('My node title', $node->label());
    $this->assertEquals($expected, $node->get('featured_media_field')->getValue());

    // Test the rendering of the formatter with the test node.
    $build = $this->display->build($node);
    $output = $this->container->get('renderer')->renderRoot($build);
    $this->assertContains('<div>' . $this->field->label() . '</div>', (string) $output);
    $this->assertContains('<div><a href="/media/' . $this->mediaEntity->id() . '/edit" hreflang="en">' . $this->mediaEntity->label() . '</a></div>', (string) $output);
    $this->assertContains('<div>' . $node->get('featured_media_field')->caption . '</div>', (string) $output);

    // Test empty featured media.
    $values = [
      'type' => 'test_ct',
      'title' => 'My second node',
      'featured_media_field' => [
        [
          // Field is empty if there is no media referenced.
          'target_id' => NULL,
          'caption' => 'Image caption text',
        ],
      ],
    ];
    // Create a node.
    $node = Node::create($values);
    $node->save();

    // Assert the base field values.
    $this->assertEquals('My second node', $node->label());
    $this->assertTrue($node->get('featured_media_field')->isEmpty());
  }

}
