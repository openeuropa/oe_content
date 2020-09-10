<?php

declare(strict_types = 1);

namespace Drupal\oe_content_tender;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\oe_content\EntityWrapperBase;

/**
 * Wrap the tender entity by adding business specific methods.
 *
 * @method static TenderNodeWrapperInterface getInstance(\Drupal\Core\Entity\ContentEntityInterface $entity)
 */
class TenderNodeWrapper extends EntityWrapperBase implements TenderNodeWrapperInterface {

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
    return 'oe_tender';
  }

  /**
   * {@inheritdoc}
   */
  public function hasOpeningDate(): bool {
    return !$this->entity->get('oe_tender_opening_date')->isEmpty();
  }

  /**
   * {@inheritdoc}
   */
  public function getOpeningDate(): ?DrupalDateTime {
    if ($this->hasOpeningDate()) {
      $opening_date = $this->entity->get('oe_tender_opening_date')->date;
      // Prevent upcoming status when now & opening dates are the same.
      $opening_date->setTime(0, 0, 0);
      return $opening_date;
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getDeadlineDate(): DrupalDateTime {
    return $this->entity->get('oe_tender_deadline')->date;
  }

  /**
   * {@inheritdoc}
   */
  public function getTenderStatus(): string {
    $opening_date = $this->getOpeningDate();
    $closing_date = $this->getDeadlineDate();
    $now = $this->getNow();

    if (empty($opening_date)) {
      $status = static::TENDER_STATUS_NOT_AVAILABLE;
    }
    elseif ($now < $opening_date) {
      $status = static::TENDER_STATUS_UPCOMING;
    }
    elseif ($opening_date <= $now && $now < $closing_date) {
      $status = static::TENDER_STATUS_OPEN;
    }
    else {
      $status = static::TENDER_STATUS_CLOSED;
    }

    return $status;
  }

  /**
   * {@inheritdoc}
   */
  public function isNotAvailable(): bool {
    return $this->getTenderStatus() === static::TENDER_STATUS_NOT_AVAILABLE;
  }

  /**
   * {@inheritdoc}
   */
  public function isUpcoming(): bool {
    return $this->getTenderStatus() === static::TENDER_STATUS_UPCOMING;
  }

  /**
   * {@inheritdoc}
   */
  public function isOpen(): bool {
    return $this->getTenderStatus() === static::TENDER_STATUS_OPEN;
  }

  /**
   * {@inheritdoc}
   */
  public function isClosed(): bool {
    return $this->getTenderStatus() === static::TENDER_STATUS_CLOSED;
  }

  /**
   * Gets current time.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   *   Current time.
   */
  public function getNow(): DrupalDateTime {
    $request_time = \Drupal::service('datetime.time')->getRequestTime();
    return DrupalDateTime::createFromTimestamp($request_time);
  }

}
