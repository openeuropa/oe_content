<?php

declare(strict_types = 1);

namespace Drupal\oe_content_sub_entity_author\Entity;

use Drupal\oe_content_sub_entity\Entity\SubEntityInterface;

/**
 * Represents an author entity.
 */
interface AuthorInterface extends SubEntityInterface {

  /**
   * Get list of Links related to requested "Author" entity.
   *
   * @return \Drupal\Core\Link[]
   *   List of URL objects related to Author entity.
   */
  public function getAuthorsAsLinks(): array;

}
