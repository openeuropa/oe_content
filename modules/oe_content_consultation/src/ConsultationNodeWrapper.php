<?php

declare(strict_types=1);

namespace Drupal\oe_content_consultation;

use Drupal\oe_content\CallEntityWrapperBase;

/**
 * Wrap the "Consultation" content type by adding default properties.
 *
 * @method static ConsultationNodeWrapper getInstance(\Drupal\Core\Entity\ContentEntityInterface $entity)
 */
class ConsultationNodeWrapper extends CallEntityWrapperBase {

  /**
   * Contains the entity bundle id.
   *
   * @var string
   */
  protected $entityBundle = 'oe_consultation';

  /**
   * Contains the Opening Date field id.
   *
   * @var string
   */
  protected $openingDate = 'oe_consultation_opening_date';

  /**
   * Contains the Deadline field id.
   *
   * @var string
   */
  protected $deadline = 'oe_consultation_deadline';

}
