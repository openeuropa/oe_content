<?php

declare(strict_types=1);

namespace Drupal\oe_content_sub_entity\Event;

use Drupal\oe_content_sub_entity\Entity\SubEntityInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event is used to form sub-entity type labels individually for each bundle.
 */
class GenerateLabelEvent extends Event {

  /**
   * The entity we generate a label for.
   *
   * @var \Drupal\oe_content_sub_entity\Entity\SubEntityInterface
   */
  protected $entity;

  /**
   * The generated label.
   *
   * @var string
   */
  protected $label;

  /**
   * GenerateLabelEvent constructor.
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
   * Return the entity.
   *
   * @return \Drupal\oe_content_sub_entity\Entity\SubEntityInterface
   *   The entity.
   */
  public function getEntity(): SubEntityInterface {
    return $this->entity;
  }

  /**
   * Set the label.
   *
   * @param string|mixed $label
   *   The label.
   */
  public function setLabel($label): void {
    $this->label = $label;
  }

  /**
   * Get the label.
   *
   * @return string
   *   The label.
   */
  public function getLabel() {
    return $this->label;
  }

}
