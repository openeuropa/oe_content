<?php

declare(strict_types = 1);

namespace Drupal\oe_taxonomy_types\Plugin\VocabularyReferenceHandler;

use Drupal\oe_taxonomy_types\VocabularyReferenceHandlerPluginBase;

/**
 * Plugin that allows to reference rdf skos concepts.
 *
 * @VocabularyReferenceHandler(
 *   id = "corporate_vocabulary",
 *   label = @Translation("Corporate vocabulary"),
 *   handler = "default:rdf_skos"
 * )
 */
class CorporateVocabularyHandler extends VocabularyReferenceHandlerPluginBase {

}
