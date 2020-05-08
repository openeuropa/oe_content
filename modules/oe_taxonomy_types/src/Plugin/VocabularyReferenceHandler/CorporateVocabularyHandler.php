<?php

declare(strict_types = 1);

namespace Drupal\oe_taxonomy_types\Plugin\VocabularyReferenceHandler;

use Drupal\Core\Entity\EntityReferenceSelection\SelectionInterface;
use Drupal\oe_taxonomy_types\VocabularyReferenceHandlerPluginBase;

/**
 * Plugin that allows to reference rdf skos concepts.
 *
 * @VocabularyReferenceHandler(
 *   id = "corporate_vocabulary",
 *   label = @Translation("Corporate vocabulary"),
 * )
 */
class CorporateVocabularyHandler extends VocabularyReferenceHandlerPluginBase {

  public function getHandler(array $configuration = []): SelectionInterface {
    $configuration['target_type'] = 'skos_concept';

    return $this->selectionManager->getInstance($configuration);
  }

}
