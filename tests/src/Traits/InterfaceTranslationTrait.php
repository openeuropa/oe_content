<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content\Traits;

use Drupal\locale\Gettext;

/**
 * Provides methods to import the interface translations shipped with a module.
 */
trait InterfaceTranslationTrait {

  /**
   * Imports the interface translations shipped with a module.
   *
   * @param string $langcode
   *   The language code of the translations to import.
   * @param string $module
   *   The module the translations are shipped with. Submodules ship their own
   *   strings, so pass the one that owns the string under test.
   */
  protected function importTranslations(string $langcode, string $module = 'oe_content'): void {
    $file = new \stdClass();
    $file->uri = \Drupal::service('extension.list.module')->getPath($module) . '/translations/' . $module . '-' . $langcode . '.po';
    $file->langcode = $langcode;

    Gettext::fileToDatabase($file, [
      'langcode' => $langcode,
      'overwrite_options' => [
        'not_customized' => TRUE,
      ],
    ]);
  }

}
