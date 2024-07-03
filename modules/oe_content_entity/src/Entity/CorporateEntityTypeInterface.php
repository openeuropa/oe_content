<?php

declare(strict_types=1);

namespace Drupal\oe_content_entity\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityDescriptionInterface;

/**
 * Provides an interface for EntityTypeBase class.
 *
 * @ingroup oe_content_entity
 */
interface CorporateEntityTypeInterface extends ConfigEntityInterface, EntityDescriptionInterface {}
