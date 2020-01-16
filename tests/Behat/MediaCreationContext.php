<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat;

use Behat\Gherkin\Node\TableNode;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\Tests\oe_content\Traits\UtilityTrait;

/**
 * Defines media entity creation related step definitions.
 */
class MediaCreationContext extends RawDrupalContext {

  use UtilityTrait;

  /**
   * Keep track of medias so they can be cleaned up.
   *
   * @var array
   */
  protected $medias = [];

  /**
   * Keep track of files so they can be cleaned up.
   *
   * @var array
   */
  protected $files = [];

  /**
   * Creates media documents with the specified file names.
   *
   * // phpcs:disable
   * @Given the following documents:
   * | name   | file   |
   * | name 1 | file 1 |
   * |   ...  |   ...  |
   * // phpcs:enable
   */
  public function createMediaDocuments(TableNode $file_table): void {
    // Retrieve the url table from the test scenario.
    $files = $file_table->getColumnsHash();

    foreach ($files as $file_properties) {
      $file = file_save_data(file_get_contents($this->getMinkParameter('files_path') . $file_properties['file']), 'public://' . $file_properties['file']);
      $file->setPermanent();
      $file->save();

      // Store for cleanup.
      $this->files[] = $file;

      $media = \Drupal::service('entity_type.manager')
        ->getStorage('media')->create([
          'bundle' => 'document',
          'name' => $file_properties['name'],
          'oe_media_file' => [
            'target_id' => (int) $file->id(),
          ],
          'uid' => $this->userManager->getCurrentUser()->uid,
          'status' => 1,
        ]);

      $media->save();

      // Store for cleanup.
      $this->medias[] = $media;
    }
  }

  /**
   * Creates media AVPortal photos with the specified URLs.
   *
   * // phpcs:disable
   * @Given the following AV Portal photos:
   * | url   |
   * | url 1 |
   * |  ...  |
   * // phpcs:enable
   */
  public function createMediaAvPortalPhotos(TableNode $url_table): void {
    // Retrieve the url table from the test scenario.
    $urls = $url_table->getColumnsHash();

    $pattern = '@audiovisual\.ec\.europa\.eu/(.*)/photo/(P\-.*\~2F.*)@i';

    foreach ($urls as $url) {
      $url = reset($url);

      preg_match_all($pattern, $url, $matches);
      if (empty($matches)) {
        continue;
      }

      // Converts the slash in the photo id.
      $photo_id = str_replace("~2F", "/", $matches[2][0]);

      $media = \Drupal::service('entity_type.manager')
        ->getStorage('media')->create([
          'bundle' => 'av_portal_photo',
          'oe_media_avportal_photo' => $photo_id,
          'uid' => $this->userManager->getCurrentUser()->uid,
          'status' => 1,
        ]);

      $media->save();

      // Store for cleanup.
      $this->medias[] = $media;
    }
  }

  /**
   * Remove any created media.
   *
   * @AfterScenario
   */
  public function cleanMedias() {
    $storage = \Drupal::entityTypeManager()->getStorage('media');
    $storage->delete($this->medias);

    $this->medias = [];
  }

  /**
   * Remove any created files.
   *
   * @AfterScenario
   */
  public function cleanFiles() {
    $storage = \Drupal::entityTypeManager()->getStorage('file');
    $storage->delete($this->files);

    $this->files = [];
  }

}
