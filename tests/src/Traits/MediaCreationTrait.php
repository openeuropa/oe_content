<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content\Traits;

use Drupal\Core\File\FileSystemInterface;
use Drupal\file\FileInterface;
use Drupal\media\MediaInterface;

/**
 * Provides methods to create the media entities used by the tests.
 */
trait MediaCreationTrait {

  /**
   * Creates an image media entity.
   *
   * @param string $name
   *   The name of the media.
   * @param string $file
   *   The name of the file inside the fixtures folder.
   * @param string|null $alt
   *   The alternative text. Defaults to the media name.
   * @param string|null $title
   *   The title of the image. Defaults to the media name.
   *
   * @return \Drupal\media\MediaInterface
   *   The media entity.
   */
  protected function createImageMedia(string $name, string $file, ?string $alt = NULL, ?string $title = NULL): MediaInterface {
    $file = $this->createFixtureFile($file);
    $media = \Drupal::entityTypeManager()->getStorage('media')->create([
      'bundle' => 'image',
      'name' => $name,
      'oe_media_image' => [
        'target_id' => (int) $file->id(),
        'alt' => $alt ?? $name,
        'title' => $title ?? $name,
      ],
      'status' => 1,
    ]);
    $media->save();

    return $media;
  }

  /**
   * Creates a document media entity.
   *
   * @param string $name
   *   The name of the media.
   * @param string $file
   *   The name of the file inside the fixtures folder.
   *
   * @return \Drupal\media\MediaInterface
   *   The media entity.
   */
  protected function createDocumentMedia(string $name, string $file): MediaInterface {
    $file = $this->createFixtureFile($file);
    $media = \Drupal::entityTypeManager()->getStorage('media')->create([
      'bundle' => 'document',
      'name' => $name,
      'oe_media_file' => [
        'target_id' => (int) $file->id(),
      ],
      'status' => 1,
    ]);
    $media->save();

    return $media;
  }

  /**
   * Creates an AV Portal photo media entity.
   *
   * @param string $url
   *   The AV Portal URL of the photo.
   *
   * @return \Drupal\media\MediaInterface
   *   The media entity.
   */
  protected function createAvPortalPhotoMedia(string $url): MediaInterface {
    /** @var \Drupal\media_avportal\Plugin\media\Source\MediaAvPortalSourceInterface $source */
    $source = \Drupal::entityTypeManager()->getStorage('media_type')->load('av_portal_photo')->getSource();
    $media = \Drupal::entityTypeManager()->getStorage('media')->create([
      'bundle' => 'av_portal_photo',
      'oe_media_avportal_photo' => $source->transformUrlToReference($url),
      'status' => 1,
    ]);
    $media->save();

    return $media;
  }

  /**
   * Creates a remote video media entity.
   *
   * @param string $url
   *   The URL of the video.
   *
   * @return \Drupal\media\MediaInterface
   *   The media entity.
   */
  protected function createRemoteVideoMedia(string $url): MediaInterface {
    $media = \Drupal::entityTypeManager()->getStorage('media')->create([
      'bundle' => 'remote_video',
      'oe_media_oembed_video' => $url,
      'status' => 1,
    ]);
    $media->save();

    return $media;
  }

  /**
   * Creates a permanent file entity out of one of the test fixtures.
   *
   * @param string $file_name
   *   The name of the file inside the fixtures folder.
   *
   * @return \Drupal\file\FileInterface
   *   The file entity.
   */
  protected function createFixtureFile(string $file_name): FileInterface {
    $path = \Drupal::service('extension.list.module')->getPath('oe_content') . '/tests/fixtures/' . $file_name;
    $files_dir = 'public://';
    \Drupal::service('file_system')->prepareDirectory($files_dir, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
    $file = \Drupal::service('file.repository')->writeData(file_get_contents($path), 'public://' . basename($file_name));
    $file->setPermanent();
    $file->save();

    return $file;
  }

}
