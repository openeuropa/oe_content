<?php

declare(strict_types = 1);

namespace Drupal\oe_content_sub_entity_extra_authors\EventSubscriber;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\oe_content\AuthorSubEntitySubscriberBase;
use Drupal\oe_content\Event\AuthorExtractDataEvent;

/**
 * Event subscriber for extracting data for extra author bundles.
 */
class AuthorExtraBundlesSubscriber extends AuthorSubEntitySubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function applies(ContentEntityInterface $entity): bool {
    return $entity->getEntityTypeId() === 'oe_author' && in_array($entity->bundle(), [
      'oe_person',
      'oe_organisation',
      'oe_link',
    ]);
  }

  /**
   * {@inheritdoc}
   */
  protected function extractLinks(AuthorExtractDataEvent $event): void {
    $entity = $event->getEntity();
    switch ($entity->bundle()) {
      case 'oe_link':
        $values = $entity->get('oe_link')->getValue();
        $links = [];
        foreach ($values as $value) {
          $url = Url::fromUri($value['uri']) ?: Url::fromRoute('<none>');
          $links[] = Link::fromTextAndUrl($value['title'], $url);
        }
        $event->setLinks($links);

        break;

      default:
        $event->setLinks($this->getDefaultLinks($event));
    }
  }

}
