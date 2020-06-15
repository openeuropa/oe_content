<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Kernel\ContentTypes;

/**
 * Tests specifics about the News content type.
 */
class NewsTest extends ContentTypeBaseTest {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'oe_content_news',
    'oe_media',
    'oe_media_iframe',
    'image',
    'media',
    'file',
    'maxlength',
    'options',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('media');

    $this->installConfig([
      'oe_content_news',
      'oe_media',
      'oe_media_iframe',
      'media',
      'image',
      'file',
      'system',
      'oe_media',
    ]);

  }

  /**
   * Tests the featured media field.
   */
  public function testFeaturedMediaField(): void {
    /** @var \Drupal\Core\Field\FieldConfigInterface $field */
    $field = $this->container->get('entity_type.manager')->getStorage('field_config')->load('node.oe_news.oe_news_featured_media');
    $expected = [
      'av_portal_photo' => 'av_portal_photo',
      'av_portal_video' => 'av_portal_video',
      'image' => 'image',
      'remote_video' => 'remote_video',
      'video_iframe' => 'video_iframe',
    ];

    $this->assertEquals($expected, $field->getSetting('handler_settings')['target_bundles']);
  }

}
