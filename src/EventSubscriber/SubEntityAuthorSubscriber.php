<?php

declare(strict_types = 1);

namespace Drupal\oe_content\EventSubscriber;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\oe_content_sub_entity\SubEntitySubscriberBase;

/**
 * Event subscriber for handing entity labels for "Author" entity type bundles.
 */
class SubEntityAuthorSubscriber extends SubEntitySubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function generateLabel(ContentEntityInterface $entity): ?string {
    return $this->getReferencedEntityLabels($entity);
  }

  /**
   * {@inheritdoc}
   */
  protected function applies(ContentEntityInterface $entity): bool {
    return $entity->getEntityTypeId() === 'oe_author' && $entity->bundle() === 'oe_corporate_body';
  }

}
