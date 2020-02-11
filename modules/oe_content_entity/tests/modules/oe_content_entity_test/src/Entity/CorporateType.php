<?php

declare(strict_types = 1);

namespace Drupal\oe_content_entity_test\Entity;

use Drupal\oe_content_entity\Entity\EntityTypeBase;

/**
 * Defines the corporate type entity.
 *
 * @ConfigEntityType(
 *   id = "oe_corporate_type",
 *   label = @Translation("Corporate type"),
 *   bundle_of = "oe_corporate",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   config_prefix = "oe_corporate_type",
 *   config_export = {
 *     "id",
 *     "label",
 *     "description",
 *   },
 *   admin_permission = "manage corporate content entity types",
 * )
 */
class CorporateType extends EntityTypeBase {}
