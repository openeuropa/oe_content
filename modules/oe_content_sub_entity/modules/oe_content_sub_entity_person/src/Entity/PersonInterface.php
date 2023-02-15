<?php

declare(strict_types = 1);

namespace Drupal\oe_content_sub_entity_person\Entity;

use Drupal\oe_content_sub_entity\Entity\SubEntityInterface;

/**
 * Represents the Person entity.
 */
interface PersonInterface extends SubEntityInterface {

  /**
   * Get list of links related to requested "Person" entity.
   *
   * @return \Drupal\Core\Link[]
   *   List of URL objects related to Person entity.
   */
  public function getEntitiesAsLinks(): array;

}
