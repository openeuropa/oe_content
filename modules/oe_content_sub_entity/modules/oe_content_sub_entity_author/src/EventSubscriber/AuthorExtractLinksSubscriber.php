<?php

declare(strict_types = 1);

namespace Drupal\oe_content_sub_entity_author\EventSubscriber;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\oe_content_sub_entity_author\AuthorExtractLinksSubscriberBase;
use Drupal\oe_content_sub_entity_author\Event\AuthorExtractLinksEvent;
use Drupal\rdf_skos\Entity\ConceptInterface;

/**
 * Event subscriber for extracting links for author bundles.
 */
class AuthorExtractLinksSubscriber extends AuthorExtractLinksSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function applies(ContentEntityInterface $entity): bool {
    return $entity->getEntityTypeId() === 'oe_author';
  }

  /**
   * {@inheritdoc}
   */
  protected function extractLinks(AuthorExtractLinksEvent $event): void {
    $entity = $event->getEntity();
    switch ($entity->bundle()) {
      case 'oe_corporate_body':
        // Load referenced entities.
        $entities = $event->getEntity()->referencedEntities();
        $links = [];
        foreach ($entities as $entity) {
          if ($entity instanceof ConceptInterface) {
            $entity = $this->entityRepository->getTranslationFromContext($entity);
            $event->addCacheableDependency($entity);
            // Currently, SKOS concept terms do not have meaningful URLs.
            // In this case, we will use just labels.
            $links[] = new Link($entity->label(), Url::fromRoute('<nolink>', []));
          }
        }

        if ($links) {
          $event->setLinks($links);
        }

        break;

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
