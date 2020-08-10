<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_persistent\Kernel;

use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\media\Entity\MediaType;
use Drupal\Tests\linkit\Kernel\LinkitKernelTestBase;

/**
 * Tests media PURL matcher.
 */
class MediaPurlMatcherTest extends LinkitKernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'file_test',
    'file',
    'media',
    'image',
    'field',
    'oe_content_persistent',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('file');
    $this->installEntitySchema('media');
    $this->installEntitySchema('entity_view_display');
    $this->installConfig(['media']);
    $this->installConfig(['image']);
    $this->installSchema('system', ['key_value_expire']);
    $this->installSchema('file', ['file_usage']);

    // Set up media bundle and fields.
    $media_type = MediaType::create([
      'label' => 'test',
      'id' => 'test',
      'description' => 'Test type.',
      'source' => 'file',
    ]);
    $media_type->save();
    $source_field = $media_type->getSource()->createSourceField($media_type);
    $source_field->getFieldStorageDefinition()->save();
    $source_field->save();
    $media_type->set('source_configuration', [
      'source_field' => $source_field->getName(),
    ])->save();

    // Linkit doesn't care about the actual resource, only the entity.
    foreach (['gif', 'jpg', 'png'] as $ext) {
      $file = File::create([
        'uid' => 1,
        'filename' => 'image-test.' . $ext,
        'uri' => 'public://image-test.' . $ext,
        'filemime' => 'text/plain',
        'status' => FILE_STATUS_PERMANENT,
      ]);
      $file->save();

      $media = Media::create([
        'bundle' => 'test',
        $source_field->getName() => ['target_id' => $file->id()],
      ]);
      $media->save();
    }

    // Create user 1 who has special permissions.
    \Drupal::currentUser()->setAccount($this->createUser(['uid' => 1]));
  }

  /**
   * Tests media PURL matcher.
   */
  public function testMediaMatcherWithDefaultConfiguration() {
    $base_url = $this->config('oe_content_persistent.settings')->get('base_url');
    $matcher_manager = $this->container->get('plugin.manager.linkit.matcher');

    /** @var \Drupal\linkit\MatcherInterface $plugin */
    $plugin = $matcher_manager->createInstance('entity:media', []);
    $suggestions = $plugin->execute('image-test');
    $this->assertEquals(3, count($suggestions->getSuggestions()), 'Correct number of suggestions.');

    /** @var \Drupal\Core\Entity\EntityStorageInterface $media_storage */
    $media_storage = $this->container->get('entity_type.manager')->getStorage('media');
    // Verify suggestion paths.
    foreach ($suggestions->getSuggestions() as $key => $suggestion) {
      $media = $media_storage->load($key + 1);
      $this->assertEquals($base_url . $media->uuid(), $suggestion->getPath());
    }

    // Configure the plugin to show media thumbnails.
    /** @var \Drupal\linkit\MatcherInterface $plugin */
    $plugin = $matcher_manager->createInstance('entity:media', [
      'settings' => [
        'thumbnail' => [
          'show_thumbnail' => TRUE,
          'thumbnail_image_style' => 'linkit_result_thumbnail',
        ],
      ],
    ]);
    $suggestions = $plugin->execute('image-test');
    $this->assertEquals(3, count($suggestions->getSuggestions()), 'Correct number of suggestions.');

    // Verify suggestion contents.
    /** @var \Drupal\image\ImageStyleInterface $style */
    $style = $this->container->get('entity_type.manager')->getStorage('image_style')->load('linkit_result_thumbnail');
    foreach ($suggestions->getSuggestions() as $key => $suggestion) {
      $media = $media_storage->load($key + 1);
      $this->assertEquals($base_url . $media->uuid(), $suggestion->getPath());
      $thumbnail = $media->get('thumbnail')->first();
      $thumbnail_file = $thumbnail->get('entity')->getTarget();
      $thumbnail_url = $style->buildUrl($thumbnail_file->get('uri')->getString());
      $this->assertContains(parse_url($thumbnail_url)['path'], $suggestion->getDescription());
    }
  }

}
