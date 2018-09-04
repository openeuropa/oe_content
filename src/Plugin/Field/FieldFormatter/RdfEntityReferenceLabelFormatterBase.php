<?php

declare(strict_types = 1);

namespace Drupal\oe_content\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceFormatterBase;
use Drupal\Core\Url;

/**
 * Base class for the RDF entity reference label field formatters.
 */
abstract class RdfEntityReferenceLabelFormatterBase extends EntityReferenceFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode): array {
    $elements = [];

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $entity) {
      $elements[$delta] = [
        '#cache' => [
          'tags' => $entity->getCacheTags(),
        ],
      ];

      $label = $entity->label();
      try {
        $url = $this->getUrlForEntity($entity);
      }
      catch (\InvalidArgumentException $e) {
        $elements[$delta]['#plain_text'] = $label;
        return $elements;
      }

      if ($entity->isNew()) {
        $elements[$delta]['#plain_text'] = $label;
        return $elements;
      }

      $elements[$delta] = [
        '#type' => 'link',
        '#title' => $label,
        '#url' => $url,
      ];

      if (!empty($items[$delta]->_attributes)) {
        $elements[$delta]['#options'] += ['attributes' => []];
        $elements[$delta]['#options']['attributes'] += $items[$delta]->_attributes;
        unset($items[$delta]->_attributes);
      }

    }

    return $elements;
  }

  /**
   * Returns the URL to use for the entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The RDF or Taxonomy Term entity.
   *
   * @return \Drupal\Core\Url
   *   The URL object.
   */
  abstract protected function getUrlForEntity(EntityInterface $entity): Url;

}
