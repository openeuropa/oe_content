<?php

declare(strict_types = 1);

namespace Drupal\oe_content_sub_entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\oe_content_sub_entity\Event\GenerateLabelEvent;
use Drupal\oe_content_sub_entity\Event\SubEntityEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Provides a base implementation for SubEntitySubscriber.
 */
abstract class SubEntityGenerateLabelSubscriberBase implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * SubEntityGenerateLabelSubscriberBase constructor.
   *
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   */
  public function __construct(EntityRepositoryInterface $entity_repository) {
    $this->entityRepository = $entity_repository;
  }

  /**
   * Generate sub-entity label, given a sub-entity object.
   *
   * @param \Drupal\oe_content_sub_entity\Event\GenerateLabelEvent $event
   *   Sub-entity event.
   */
  public function onGenerateLabel(GenerateLabelEvent $event): void {
    if ($this->applies($event->getEntity())) {
      $generated_label = $this->generateLabel($event->getEntity());
      if (!empty($generated_label)) {
        $event->setLabel($generated_label);
      }
    }
  }

  /**
   * Check if a particular event subscriber applies to a given entity.
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
   * Generate labels for specific sub-entity types or bundles.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity.
   *
   * @return string|\Drupal\Core\StringTranslation\TranslatableMarkup|null
   *   The generated label.
   */
  abstract protected function generateLabel(ContentEntityInterface $entity);

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      SubEntityEvents::GENERATE_LABEL => ['onGenerateLabel'],
    ];
  }

  /**
   * Default label generator for target sub-entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity.
   *
   * @return string
   *   Generated entity label.
   */
  protected function defaultLabel(ContentEntityInterface $entity): ?string {
    $labels = $this->getReferencedEntityLabels($entity);
    if (!empty($labels)) {
      return $labels;
    }
    return NULL;
  }

  /**
   * Gets labels of referenced entities.
   *
   * @return string
   *   Labels separated by comma.
   */
  protected function getReferencedEntityLabels(ContentEntityInterface $entity): string {
    // Load referenced entities.
    $entities = $entity->referencedEntities();

    $labels = [];
    foreach ($entities as $entity) {
      if ($entity instanceof ContentEntityInterface && $entity->getEntityType()->hasKey('label')) {
        $entity = $this->entityRepository->getTranslationFromContext($entity);
        $labels[] = $entity->label();
      }
    }

    return implode(', ', $labels);
  }

}
