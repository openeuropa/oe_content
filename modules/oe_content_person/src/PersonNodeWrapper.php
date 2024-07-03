<?php

declare(strict_types=1);

namespace Drupal\oe_content_person;

use Drupal\oe_content\EntityWrapperBase;

/**
 * Wrap the "Person" content type.
 */
class PersonNodeWrapper extends EntityWrapperBase {

  const EU_ONLY_FIELDS = [
    'oe_departments',
    'oe_person_media',
    'oe_social_media_links',
    'oe_person_transparency_intro',
    'oe_person_transparency_links',
    'oe_person_biography_intro',
    'oe_person_biography_timeline',
    'oe_person_cv',
    'oe_person_interests_intro',
    'oe_person_interests_file',
  ];

  const NON_EU_ONLY_FIELDS = [
    'oe_person_organisation',
  ];

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

}
