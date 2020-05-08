<?php

declare(strict_types = 1);

namespace Drupal\oe_taxonomy_types\Plugin\VocabularyReferenceHandler;

use Drupal\Core\Entity\EntityReferenceSelection\SelectionInterface;
use Drupal\oe_taxonomy_types\VocabularyReferenceHandlerPluginBase;

/**
 * Plugin that allows to reference Drupal taxonomy terms.
 *
 * @VocabularyReferenceHandler(
 *   id = "taxonomy",
 *   label = @Translation("Local vocabulary"),
 *   handler = "default:taxonomy_term"
 * )
 */
class TaxonomyHandler extends VocabularyReferenceHandlerPluginBase {

  public function getHandler(array $configuration = []): SelectionInterface {
    $configuration['target_type'] = 'taxonomy_term';

    return $this->selectionManager->getInstance($configuration);
  }

}
