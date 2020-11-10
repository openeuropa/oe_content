<?php

declare(strict_types = 1);

namespace Drupal\oe_content_call_tenders;

use Drupal\oe_content\CallEntityWrapperBase;

/**
 * Wrap the "Call for tenders" content type by adding default properties.
 *
 * @method static CallForTendersNodeWrapper getInstance(\Drupal\Core\Entity\ContentEntityInterface $entity)
 */
class CallForTendersNodeWrapper extends CallEntityWrapperBase {

  /**
   * Contains the entity bundle id.
   *
   * @var string
   */
  protected $entityBundle = 'oe_call_tenders';

  /**
   * Contains the Opening Date field id.
   *
   * @var string
   */
  protected $openingDate = 'oe_call_tenders_opening_date';

  /**
   * Contains the Deadline date field id.
   *
   * @var string
   */
  protected $deadline = 'oe_call_tenders_deadline';

}
