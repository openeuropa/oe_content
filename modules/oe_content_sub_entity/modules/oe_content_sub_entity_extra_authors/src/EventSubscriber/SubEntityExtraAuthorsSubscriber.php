<?php

declare(strict_types=1);

namespace Drupal\oe_content_sub_entity_extra_authors\EventSubscriber;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\oe_content_sub_entity\SubEntitySubscriberBase;

/**
 * Event subscriber for handing entity labels for "Author" entity type bundles.
 */
class SubEntityExtraAuthorsSubscriber extends SubEntitySubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function generateLabel(ContentEntityInterface $entity): ?string {
    $label = NULL;
    switch ($entity->bundle()) {
      case 'oe_person':
      case 'oe_organisation':
        $label = $this->defaultLabel($entity);

        break;

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
    }

    return $label;
  }

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

}
