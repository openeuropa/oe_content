<?php

declare(strict_types = 1);

namespace Drupal\oe_content\EventSubscriber;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\oe_content\AuthorSubEntitySubscriberBase;
use Drupal\oe_content\Event\AuthorExtractDataEvent;

/**
 * Event subscriber for extracting data for "Corporate body" author bundles.
 */
class AuthorCorporateBodyExtractDataSubscriber extends AuthorSubEntitySubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function applies(ContentEntityInterface $entity): bool {
    return $entity->getEntityTypeId() === 'oe_author' && $entity->bundle() === 'oe_corporate_body';
  }

  /**
   * {@inheritdoc}
   */
  protected function extractLinks(AuthorExtractDataEvent $event): void {
    $event->setLinks($this->getDefaultLinks($event->getEntity()));
  }

}
