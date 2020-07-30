<?php

declare(strict_types = 1);

namespace Drupal\oe_content_persistent\Event;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event used to resolve the URL of an entity from its PURL.
 */
class PersistentUrlResolverEvent extends Event {

  /**
   * The name of the event.
   */
  const NAME = 'oe_content_persistent.event.persistent_url_resolver';

  /**
   * The content entity whose URL we want to resolve.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * The resulting url.
   *
   * @var \Drupal\Core\Url|null
   */
  protected $url = NULL;

  /**
   * EntityValueResolverEvent constructor.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The content entity.
   */
  public function __construct(EntityInterface $entity) {
    $this->entity = $entity;
  }

  /**
   * Returns the entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The entity.
   */
  public function getEntity(): EntityInterface {
    return $this->entity;
  }

  /**
   * Gets the url.
   *
   * @return \Drupal\Core\Url|null
   *   The resolved url.
   */
  public function getUrl(): ?Url {
    return $this->url;
  }

  /**
   * Sets the url.
   *
   * @param \Drupal\Core\Url $url
   *   The resolved url.
   */
  public function setUrl(Url $url): void {
    $this->url = $url;
  }

}
