<?php

declare(strict_types = 1);

namespace Drupal\oe_content_sub_entity_person\EventSubscriber;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\oe_content_sub_entity_person\PersonExtractLinksSubscriberBase;
use Drupal\oe_content_sub_entity_person\Event\PersonExtractLinksEvent;
use Drupal\rdf_skos\Entity\ConceptInterface;

/**
 * Event subscriber for extracting links for person bundles.
 */
class PersonExtractLinksSubscriber extends PersonExtractLinksSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function applies(ContentEntityInterface $entity): bool {
    return $entity->getEntityTypeId() === 'oe_person';
  }

  /**
   * {@inheritdoc}
   */
  protected function extractLinks(PersonExtractLinksEvent $event): void {
    $entity = $event->getEntity();
    switch ($entity->bundle()) {
      case 'oe_political_leader':
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

      default:
        $event->setLinks($this->getDefaultLinks($event));
    }
  }

}
