<?php

declare(strict_types = 1);

namespace Drupal\oe_content_sub_entity_document_reference\EventSubscriber;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\oe_content_sub_entity\SubEntityGenerateLabelSubscriberBase;

/**
 * Subscriber for handing labels for "Document reference" entity type bundles.
 */
class DocumentReferenceGenerateLabelSubscriber extends SubEntityGenerateLabelSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function generateLabel(ContentEntityInterface $entity): ?string {
    return $this->getReferencedEntityLabels($entity);
  }

  /**
   * {@inheritdoc}
   */
  protected function applies(ContentEntityInterface $entity): bool {
    return $entity->getEntityTypeId() === 'oe_document_reference';
  }

}
