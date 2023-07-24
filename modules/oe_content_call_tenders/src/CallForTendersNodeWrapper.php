<?php

declare(strict_types = 1);

namespace Drupal\oe_content_call_tenders;

use Drupal\Component\Render\MarkupInterface;
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
   * Contains the Publication date field id.
   *
   * @var string
   */
  protected $openingDate = 'oe_publication_date';

  /**
   * Contains the Deadline date field id.
   *
   * @var string
   */
  protected $deadline = 'oe_call_tenders_deadline';

  /**
   * {@inheritdoc}
   */
  public function getStatusLabel(): MarkupInterface {
    switch ($this->getStatus()) {
      case static::STATUS_OPEN:
        $label = $this->t('Ongoing');
        break;

      default:
        $label = parent::getStatusLabel();
    }

    return $label;
  }

}
