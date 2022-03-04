<?php

declare(strict_types = 1);

namespace Drupal\oe_content_event_person_reference\EventSubscriber;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\oe_content_sub_entity\SubEntityGenerateLabelSubscriberBase;

/**
 * Subscriber for handing labels for "Event speaker" entity type bundles.
 */
class EventSpeakerGenerateLabelSubscriber extends SubEntityGenerateLabelSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function generateLabel(ContentEntityInterface $entity) {
    // Define label for Default Event speaker.
    return $this->defaultLabel($entity);
  }

  /**
   * {@inheritdoc}
   */
  protected function applies(ContentEntityInterface $entity): bool {
    return $entity->getEntityTypeId() === 'oe_event_speaker' && $entity->bundle() === 'oe_default';
  }

}
