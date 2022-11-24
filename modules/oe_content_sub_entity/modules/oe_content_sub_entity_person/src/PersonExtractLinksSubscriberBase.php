<?php

declare(strict_types = 1);

namespace Drupal\oe_content_sub_entity_person;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\oe_content_sub_entity_person\Event\PersonExtractLinksEvent;
use Drupal\oe_content_sub_entity_person\Event\PersonEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Provides a base implementation for ExtractLinksSubscriber.
 */
abstract class PersonExtractLinksSubscriberBase implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Constructs an instances for sub-entity event subscribers.
   *
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   */
  public function __construct(EntityRepositoryInterface $entity_repository) {
    $this->entityRepository = $entity_repository;
  }

  /**
   * Checking if particular event subscriber is applicable for the entity.
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
   * @param \Drupal\oe_content_sub_entity_person\Event\PersonExtractLinksEvent $event
   *   The content entity.
   */
  abstract protected function extractLinks(PersonExtractLinksEvent $event): void;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      PersonEvents::EXTRACT_PERSON_LINKS => ['onExtractingLinks'],
    ];
  }

  /**
   * Extracting sub-entity link which depends on entity bundle.
   *
   * @param \Drupal\oe_content_sub_entity_person\Event\PersonExtractLinksEvent $event
   *   Event.
   */
  public function onExtractingLinks(PersonExtractLinksEvent $event): void {
    if ($this->applies($event->getEntity())) {
      $this->extractLinks($event);
    }
  }

  /**
   * Default link extractor for target sub-entity.
   *
   * @param \Drupal\oe_content_sub_entity_person\Event\PersonExtractLinksEvent $event
   *   Event.
   *
   * @return array
   *   Generated entity links.
   */
  protected function getDefaultLinks(PersonExtractLinksEvent $event): ?array {
    // Load referenced entities.
    $entities = $event->getEntity()->referencedEntities();

    $links = [];
    foreach ($entities as $entity) {
      if ($entity instanceof ContentEntityInterface && $entity->getEntityType()->hasKey('label')) {
        $entity = $this->entityRepository->getTranslationFromContext($entity);
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
