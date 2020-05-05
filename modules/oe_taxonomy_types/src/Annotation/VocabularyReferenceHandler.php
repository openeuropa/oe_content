<?php

declare(strict_types = 1);

namespace Drupal\oe_taxonomy_types\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a VocabularyReferenceHandler annotation object.
 *
 * @Annotation
 */
class VocabularyReferenceHandler extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $title;

}
