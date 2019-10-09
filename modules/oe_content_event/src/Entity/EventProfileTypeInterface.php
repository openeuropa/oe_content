<?php

declare(strict_types = 1);

namespace Drupal\oe_content_event\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityDescriptionInterface;

/**
 * Provides an interface for Event profile type entities.
 *
 * @ingroup oe_content_event
 */
interface EventProfileTypeInterface extends ConfigEntityInterface, EntityDescriptionInterface {}
