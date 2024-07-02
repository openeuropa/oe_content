<?php

declare(strict_types=1);

namespace Drupal\oe_content_sub_entity\Entity;

use Drupal\oe_content\Entity\EntityTypeBase;

/**
 * Provides the SubEntityTypeBase class for content entity types.
 *
 * @ingroup oe_content_sub_entity
 */
abstract class SubEntityTypeBase extends EntityTypeBase implements SubEntityTypeInterface {}
