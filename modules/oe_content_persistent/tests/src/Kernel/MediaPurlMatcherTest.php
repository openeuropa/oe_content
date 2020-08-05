<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_persistent\Kernel;

use Drupal\Tests\linkit\Kernel\Matchers\MediaMatcherTest;

/**
 * Tests media PURL matcher.
 */
class MediaPurlMatcherTest extends MediaMatcherTest {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['oe_content_persistent'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installConfig(['image']);
    $this->installEntitySchema('entity_view_display');
  }

  /**
   * Tests media PURL matcher.
   */
  public function testMediaMatcherWithDefaultConfiguration() {
    $base_url = $this->config('oe_content_persistent.settings')->get('base_url');

    /** @var \Drupal\linkit\MatcherInterface $plugin */
    $plugin = $this->manager->createInstance('entity:media', []);
    $suggestions = $plugin->execute('image-test');
    $this->assertEquals(3, count($suggestions->getSuggestions()), 'Correct number of suggestions.');

    /** @var \Drupal\Core\Entity\EntityStorageInterface $media_storage */
    $media_storage = $this->container->get('entity_type.manager')->getStorage('media');
    // Verify suggestion paths.
    foreach ($suggestions->getSuggestions() as $key => $suggestion) {
      $media = $media_storage->load($key + 1);
      $this->assertEquals($base_url . $media->uuid(), $suggestion->getPath());
    }

    /** @var \Drupal\linkit\MatcherInterface $plugin */
    $plugin = $this->manager->createInstance('entity:media', [
      'settings' => [
        'thumbnail' => [
          'show_thumbnail' => TRUE,
          'thumbnail_image_style' => 'linkit_result_thumbnail',
        ],
      ],
    ]);
    $suggestions = $plugin->execute('image-test');
    $this->assertEquals(3, count($suggestions->getSuggestions()), 'Correct number of suggestions.');

    /** @var \Drupal\Core\Entity\EntityStorageInterface $media_storage */
    $media_storage = $this->container->get('entity_type.manager')->getStorage('media');
    // Verify suggestion paths.
    foreach ($suggestions->getSuggestions() as $key => $suggestion) {
      $media = $media_storage->load($key + 1);
      $this->assertEquals($base_url . $media->uuid(), $suggestion->getPath());
      $this->assertContains('linkit_result_thumbnail', $suggestion->getDescription());
    }

  }

}
