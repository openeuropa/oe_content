<?php

declare(strict_types=1);

namespace Drupal\oe_content_persistent\Event;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Url;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event used to resolve the URL of an entity from its PURL.
 */
class PersistentUrlResolverEvent extends Event {

  /**
   * The name of the event.
   */
  const NAME = 'oe_content_persistent.event.entity_url_resolver';

  /**
   * The entity whose URL we want to resolve.
   *
   * @var \Drupal\Core\Entity\ContentEntityInterface
   */
  protected $entity;

  /**
   * The resulting URL.
   *
   * @var \Drupal\Core\Url
   */
  protected $url;

  /**
   * PersistentUrlResolverEvent constructor.
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
   * Asserts whether a URL was set or not.
   *
   * @return bool
   *   Whether the URL was set or not.
   */
  public function hasUrl(): bool {
    return isset($this->url);
  }

  /**
   * Gets the URL.
   *
   * @return \Drupal\Core\Url
   *   The resolved URL.
   */
  public function getUrl(): Url {
    return $this->url;
  }

  /**
   * Sets the URL.
   *
   * @param \Drupal\Core\Url $url
   *   The resolved URL.
   */
  public function setUrl(Url $url): void {
    $this->url = $url;
  }

}
