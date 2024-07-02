<?php

declare(strict_types=1);

namespace Drupal\oe_content_sub_entity\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityDescriptionInterface;

/**
 * Provides an interface for SubEntityTypeBase class.
 *
 * @ingroup oe_content_sub_entity
 */
interface SubEntityTypeInterface extends ConfigEntityInterface, EntityDescriptionInterface {}
