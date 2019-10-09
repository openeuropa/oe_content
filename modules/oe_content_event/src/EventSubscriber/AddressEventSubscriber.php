<?php

declare(strict_types = 1);

namespace Drupal\oe_content_event\EventSubscriber;

use Drupal\address\Event\AddressEvents;
use Drupal\address\Event\AvailableCountriesEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Implementation of EventSubscriber for updating available countries.
 */
class AddressEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[AddressEvents::AVAILABLE_COUNTRIES][] = ['onAvailableCountries'];
    return $events;
  }

  /**
   * Alters the available countries.
   *
   * @param \Drupal\address\Event\AvailableCountriesEvent $event
   *   The available countries event.
   */
  public function onAvailableCountries(AvailableCountriesEvent $event) {
    $query = \Drupal::entityTypeManager()->getStorage('skos_concept')->getQuery();
    $ids = $query->condition('in_scheme', ['http://publications.europa.eu/resource/authority/country'], 'IN')->execute();
    $entities = \Drupal::entityTypeManager()->getStorage('skos_concept')->loadMultiple($ids);
    $rdf_countries = [];
    foreach ($entities as $entity) {
      $rdf_countries[$entity->id()] = $entity->label();
    }
    $event->setAvailableCountries($rdf_countries);
  }

}
