<?php

declare(strict_types = 1);

namespace Drupal\oe_content_sub_entity_author\Event;

use Drupal\Core\Cache\CacheableResponseTrait;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Link;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event used to extract sub-entity type Links individually for each bundle.
 */
class AuthorExtractLinksEvent extends Event {

  use CacheableResponseTrait;

  /**
   * The entity for which we have to extract some data.
   *
   * @var \Drupal\Core\Entity\ContentEntityInterface
   */
  protected $entity;

  /**
   * The extracted links for sub-entity.
   *
   * @var \Drupal\Core\Link[]
   */
  protected $links = [];

  /**
   * Constructor for authors data extractor event.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity.
   */
  public function __construct(ContentEntityInterface $entity) {
    $this->addCacheableDependency($entity);
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
   * Adds to list the Link of sub-entity.
   *
   * @param \Drupal\Core\Link $link
   *   The Link object.
   */
  public function addLink(Link $link): void {
    $this->links[] = $link;
  }

  /**
   * Sets list of the Link objects.
   *
   * @param \Drupal\Core\Link[] $links
   *   The array of Link objects.
   */
  public function setLinks(array $links): void {
    $this->links = $links;
  }

  /**
   * Gets links related to sub-entity.
   *
   * @return \Drupal\Core\Link[]
   *   The array of Links.
   */
  public function getLinks(): array {
    return $this->links;
  }

}
