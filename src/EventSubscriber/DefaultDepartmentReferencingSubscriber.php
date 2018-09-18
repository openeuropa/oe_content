<?php

declare(strict_types = 1);

namespace Drupal\oe_content\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\oe_content\Event\DepartmentReferencingEvent;
use Drupal\rdf_entity\RdfInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Default subscriber to the department referencing events.
 *
 * It looks in the current triple store for a matching department RDF entity.
 */
class DefaultDepartmentReferencingSubscriber implements EventSubscriberInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a DefaultDepartmentReferencingSubscriber.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      DepartmentReferencingEvent::EVENT => 'getDepartment',
    ];
  }

  /**
   * Looks in the current triple store for a matching department.
   *
   * @param \Drupal\oe_content\Event\DepartmentReferencingEvent $event
   *   The event.
   */
  public function getDepartment(DepartmentReferencingEvent $event): void {
    if ($event->getRdfEntity() instanceof RdfInterface) {
      // If some other subscriber already provided an entity, we don't need to
      // do anything. We are just a fallback.
      return;
    }

    $term = $event->getTerm();
    $uris = $this->entityTypeManager->getStorage('rdf_entity')->getQuery()
      ->condition('rid', 'oe_department')
      ->condition('oe_department_name', $term->id())
      ->execute();

    if (!$uris) {
      return;
    }

    // Normally there should only be 1.
    $uri = reset($uris);
    /** @var \Drupal\rdf_entity\RdfInterface $entity */
    $entity = $this->entityTypeManager->getStorage('rdf_entity')->load($uri);
    $event->setRdfEntity($entity);
  }

}
