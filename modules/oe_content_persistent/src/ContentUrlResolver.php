<?php

declare(strict_types = 1);

namespace Drupal\oe_content_persistent;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Url;
use Drupal\oe_content_persistent\Event\PersistentUrlResolverEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Default implementation of a Content URL resolver.
 */
class ContentUrlResolver implements ContentUrlResolverInterface {

  /**
   * Static cache of UUID lookups, per language.
   *
   * @var array
   */
  protected $lookupMap = [];

  /**
   * The entity dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * Constructs a ContentUuidResolver.
   *
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   */
  public function __construct(EventDispatcherInterface $event_dispatcher) {
    $this->eventDispatcher = $event_dispatcher;
  }

  /**
   * Resets the static cache.
   */
  public function resetStaticCache(): void {
    $this->lookupMap = [];
  }

  /**
   * {@inheritdoc}
   */
  public function resolveUrl(ContentEntityInterface $entity): Url {
    $langcode = $langcode ?? LanguageInterface::LANGCODE_DEFAULT;

    // Try the static cache first.
    if (isset($this->lookupMap[$entity->uuid()]) && array_key_exists($langcode, $this->lookupMap[$entity->uuid()])) {
      return $this->lookupMap[$entity->uuid()][$langcode];
    }
    // Not all entity types will need to be linked to their canonical URLs,
    // so we dispatch an event to allow to modify the resulting URL.
    $event = new PersistentUrlResolverEvent($entity);
    $this->eventDispatcher->dispatch(PersistentUrlResolverEvent::NAME, $event);
    $url = $event->hasUrl() ? $event->getUrl() : $entity->toUrl();
    $this->lookupMap[$entity->uuid()][$entity->language()->getId()] = $url;
    return $url;
  }

}
