<?php

declare(strict_types=1);

namespace Drupal\oe_content_sub_entity_author\EventSubscriber;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\oe_content_sub_entity\SubEntityGenerateLabelSubscriberBase;

/**
 * Event subscriber for handing entity labels for "Author" entity type bundles.
 */
class AuthorGenerateLabelSubscriber extends SubEntityGenerateLabelSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function generateLabel(ContentEntityInterface $entity): ?string {
    $label = NULL;
    switch ($entity->bundle()) {
      case 'oe_link':
        $values = $entity->get('oe_link')->getValue();
        $titles = [];
        foreach ($values as $value) {
          $titles[] = $value['title'];
        }
        if ($titles) {
          $label = implode(', ', $titles);
        }

        break;

      default:
        $label = $this->defaultLabel($entity);
    }

    return $label;
  }

  /**
   * {@inheritdoc}
   */
  protected function applies(ContentEntityInterface $entity): bool {
    return $entity->getEntityTypeId() === 'oe_author';

  }

}
