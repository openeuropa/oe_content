<?php

declare(strict_types = 1);

namespace Drupal\oe_content\EventSubscriber;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\oe_content\AuthorSubEntitySubscriberBase;
use Drupal\oe_content\Event\AuthorExtractDataEvent;
use Drupal\rdf_skos\Entity\ConceptInterface;

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
  }

}
