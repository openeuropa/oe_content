<?php

declare(strict_types = 1);

namespace Drupal\oe_content_person;

use Drupal\oe_content\EntityWrapperBase;

/**
 * Wrap the "Person" content type.
 */
class PersonNodeWrapper extends EntityWrapperBase {

  /**
   * {@inheritdoc}
   */
  public function getEntityId(): string {
    return 'node';
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityBundle(): string {
    return 'oe_person';
  }

  /**
   * Provides list of labels of referenced Person jobs.
   *
   * @return array
   *   List of labels.
   */
  public function getPersonJobLabels(): array {
    $labels = [];
    if (!$this->entity->get('oe_person_jobs')->isEmpty()) {
      foreach ($this->entity->get('oe_person_jobs')->referencedEntities() as $person_job_entity) {
        $labels[] = $person_job_entity->label();
      }
    }

    return $labels;
  }

}
