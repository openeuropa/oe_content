<?php

declare(strict_types = 1);

namespace Drupal\oe_content_person\EventSubscriber;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\oe_content_sub_entity\SubEntitySubscriberBase;

/**
 * Event subscriber for handing entity labels for "Person job" entity bundles.
 */
class SubEntityPersonJobSubscriber extends SubEntitySubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function generateLabel(ContentEntityInterface $entity) {
    switch ($entity->bundle()) {
      case 'oe_default':
        // Define label for Default Person job.
        $label = $entity->get('oe_role_name')->value;
        if (!$entity->get('oe_role_reference')->isEmpty()) {
          /** @var \Drupal\Core\Entity\ContentEntityBase $role_entity */
          $role_entity = $entity->get('oe_role_reference')->entity;
          $label = $role_entity->label();
          if ($role_entity->hasTranslation($entity->language()->getId())) {
            $label = $role_entity->getTranslation($entity->language()->getId())->label();
          }
          if ($entity->get('oe_acting')->value) {
            $label = $this->t('(Acting) @role', ['@role' => $label]);
          }
        }
        if (!empty($label)) {
          return $label;
        }
        break;
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  protected function applies(ContentEntityInterface $entity): bool {
    return $entity->getEntityTypeId() === 'oe_person_job' && $entity->bundle() === 'oe_default';
  }

}
