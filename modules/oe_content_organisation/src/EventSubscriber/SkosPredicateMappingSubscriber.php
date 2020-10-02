<?php

declare(strict_types = 1);

namespace Drupal\oe_content_organisation\EventSubscriber;

use Drupal\rdf_entity\RdfFieldHandlerInterface;
use Drupal\rdf_skos\Event\SkosPredicateMappingEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribes to the SKOS predicate mapping event.
 */
class SkosPredicateMappingSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[SkosPredicateMappingEvent::EVENT][] = ['onPredicateMapping', 20];
    return $events;
  }

  /**
   * Maps a predicate to a custom base field on the Skos Concept.
   *
   * @param \Drupal\rdf_skos\Event\SkosPredicateMappingEvent $event
   *   The event.
   *
   * @see \oe_content_organisation_entity_base_field_info()
   */
  public function onPredicateMapping(SkosPredicateMappingEvent $event): void {
    $mapping = $event->getMapping();
    $entity_type_id = $event->getEntityTypeId();

    if ($entity_type_id === 'skos_concept') {
      // Corporate body classification reference.
      $mapping['fields']['oe_content_organisation_corporate_body_classification'] = [
        'column' => 'target_id',
        'predicate' => ['http://purl.org/dc/terms/type'],
        'format' => RdfFieldHandlerInterface::RESOURCE,
      ];
    }

    $event->setMapping($mapping);
  }

}
