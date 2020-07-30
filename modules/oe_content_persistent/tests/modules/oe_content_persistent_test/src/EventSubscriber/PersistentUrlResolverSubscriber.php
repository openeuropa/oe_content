<?php

declare(strict_types = 1);

namespace Drupal\oe_content_persistent_test\EventSubscriber;

use Drupal\Core\Url;
use Drupal\oe_content_persistent\Event\PersistentUrlResolverEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Test subscriber to alter the resolved URL of some entities.
 */
class PersistentUrlResolverSubscriber implements EventSubscriberInterface {

  /**
   * We redirect articlet content types to the home page.
   *
   * @param \Drupal\oe_content_persistent\Event\PersistentUrlResolverEvent $event
   *   The Event to process.
   */
  public function testResolver(PersistentUrlResolverEvent $event): void {
    $entity = $event->getEntity();
    if ($entity->bundle() == 'article') {
      $event->setUrl(Url::fromRoute('<front>'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[PersistentUrlResolverEvent::NAME][] = ['testResolver'];
    return $events;
  }

}
