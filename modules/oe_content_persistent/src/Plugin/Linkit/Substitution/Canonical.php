<?php

namespace Drupal\oe_content_persistent\Plugin\Linkit\Substitution;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\linkit\SubstitutionInterface;

/**
 * A substitution plugin for the canonical URL of an entity.
 *
 * @Substitution(
 *   id = "canonical",
 *   label = @Translation("Canonical URL"),
 * )
 */
class Canonical extends PluginBase implements SubstitutionInterface {

  /**
   * {@inheritdoc}
   */
  public function getUrl(EntityInterface $entity) {
    return $entity->toUrl('canonical')->toString(TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(EntityTypeInterface $entity_type) {
    return $entity_type->hasLinkTemplate('canonical');
  }

}
