<?php

declare(strict_types = 1);

namespace Drupal\oe_content_sub_entity\Event;

use Drupal\Core\Entity\ContentEntityInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event used to form sub-entity type labels individually for each bundle.
 */
class SubEntityEvent extends Event {

  /**
   * The name of the event.
   */
  const LABEL_FORMATION = 'oe_content.event.subentity_label_formation';

  /**
   * The entity for which we have to form label.
   *
   * @var \Drupal\Core\Entity\ContentEntityInterface
   */
  protected $entity;

  /**
   * The formed label for sub-entity.
   *
   * @var string
   */
  protected $label;

  /**
   * AuthorEntityEvent constructor.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity.
   */
  public function __construct(ContentEntityInterface $entity) {
    $this->entity = $entity;
  }

  /**
   * Returns the entity.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   *   The entity.
   */
  public function getEntity(): ContentEntityInterface {
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
