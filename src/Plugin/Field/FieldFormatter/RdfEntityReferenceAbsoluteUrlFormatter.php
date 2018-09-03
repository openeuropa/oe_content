<?php

declare(strict_types = 1);

namespace Drupal\oe_content\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Url;
use Drupal\rdf_entity\RdfInterface;

/**
 * Plugin implementation of the 'rdf_entity_reference_absolute_url' formatter.
 *
 * @todo write Kernel Test as soon as RDF entity module works with Drupal 8.6.
 *
 * @FieldFormatter(
 *   id = "rdf_entity_reference_absolute_url",
 *   label = @Translation("RDF Entity Reference Absolute URL"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class RdfEntityReferenceAbsoluteUrlFormatter extends RdfEntityReferenceLabelFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $summary[] = [
      '#markup' => $this->t('Linked label to the URI of the RDF entity.'),
    ];

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntitiesToView(EntityReferenceFieldItemListInterface $items, $langcode): array {
    $entities = parent::getEntitiesToView($items, $langcode);

    // Only allow RDF entities.
    $entities = array_filter($entities, function (EntityInterface $entity) {
      return $entity instanceof RdfInterface;
    });

    return $entities;
  }

  /**
   * {@inheritdoc}
   */
  protected function getUrlForEntity(EntityInterface $entity): Url {
    $uri = $entity->id();
    return Url::fromUri($uri);
  }

}
