<?php

declare(strict_types = 1);

namespace Drupal\oe_content_sub_entity\Event;

use Drupal\oe_content_sub_entity\Entity\SubEntityInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event is used to form sub-entity type labels individually for each bundle.
 */
class SubEntityLabelInformationEvent extends Event {

  /**
   * The entity for which we have to form label.
   *
   * @var \Drupal\oe_content_sub_entity\Entity\SubEntityInterface
   */
  protected $entity;

  /**
   * The formed label for sub-entity.
   *
   * @var string
   */
  protected $label;

  /**
   * SubEntityEvent constructor.
   *
   * @param \Drupal\oe_content_sub_entity\Entity\SubEntityInterface $entity
   *   The sub-entity.
   */
  public function __construct(SubEntityInterface $entity) {
    $this->entity = $entity;
    // Uses the entity's bundle label by default.
    $this->label = $entity->get('type')->entity->label();
  }

  /**
   * Returns the entity.
   *
   * @return \Drupal\oe_content_sub_entity\Entity\SubEntityInterface
   *   The entity.
   */
  public function getEntity(): SubEntityInterface {
    return $this->entity;
  }

  /**
   * Sets the label of sub-entity.
   *
   * @param string $label
   *   The label.
   */
  public function setLabel($label): void {
    $this->label = $label;
  }

  /**
   * Gets the label of sub-entity.
   *
   * @return string
   *   The label.
   */
  public function getLabel() {
    return $this->label;
  }

}
