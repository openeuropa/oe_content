<?php

declare(strict_types = 1);

namespace Drupal\oe_content;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\oe_content\Event\AuthorExtractDataEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Provides a base implementation for AuthorSubEntitySubscriber.
 */
abstract class AuthorSubEntitySubscriberBase implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * Checking if particular event subscriber is applicable for Author entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity.
   *
   * @return bool
   *   TRUE if this even subscriber should be used or FALSE to let other event
   *   subscriber decide.
   */
  abstract protected function applies(ContentEntityInterface $entity): bool;

  /**
   * Extract array of Links object for specific sub-entity bundles.
   *
   * @param \Drupal\oe_content\Event\AuthorExtractDataEvent $event
   *   The content entity.
   */
  abstract protected function extractLinks(AuthorExtractDataEvent $event): void;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      AuthorExtractDataEvent::EXTRACT_AUTHOR_LINKS => ['onExtractingLinks'],
    ];
  }

  /**
   * Extracting sub-entity label which depends on entity bundle.
   *
   * @param \Drupal\oe_content\Event\AuthorExtractDataEvent $event
   *   Author Sub-entity event.
   */
  public function onExtractingLinks(AuthorExtractDataEvent $event): void {
    if ($this->applies($event->getEntity())) {
      $this->extractLinks($event);
    }
  }

  /**
   * Default label generator for target sub-entity.
   *
   * @param \Drupal\oe_content\Event\AuthorExtractDataEvent $event
   *   Author Sub-entity event.
   *
   * @return array
   *   Generated entity links.
   */
  protected function getDefaultLinks(AuthorExtractDataEvent $event): ?array {
    // Load referenced entities.
    $entities = $event->getEntity()->referencedEntities();

    $links = [];
    foreach ($entities as $entity) {
      if ($entity instanceof ContentEntityInterface && $entity->getEntityType()->hasKey('label')) {
        $event->addCacheableDependency($entity);
        $links[] = $entity->toLink();
      }
    }
    if (!empty($links)) {
      return $links;
    }
    return NULL;
  }

}
