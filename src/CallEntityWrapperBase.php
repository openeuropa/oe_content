<?php

declare(strict_types=1);

namespace Drupal\oe_content;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Base class for "Call" style content type wrappers.
 */
abstract class CallEntityWrapperBase extends EntityWrapperBase implements CallEntityWrapperInterface {

  use StringTranslationTrait;

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
    return $this->entityBundle;
  }

  /**
   * {@inheritdoc}
   */
  public function hasOpeningDate(): bool {
    return !$this->entity->get($this->openingDate)->isEmpty();
  }

  /**
   * {@inheritdoc}
   */
  public function hasDeadlineDate(): bool {
    return !$this->entity->get($this->deadline)->isEmpty();
  }

  /**
   * {@inheritdoc}
   */
  public function getOpeningDate(): ?DrupalDateTime {
    if (!$this->hasOpeningDate()) {
      return NULL;
    }
    $opening_date = $this->entity->get($this->openingDate)->date;
    // Prevent upcoming status when now & opening dates are the same.
    $opening_date->setTime(0, 0, 0);
    return $opening_date;
  }

  /**
   * {@inheritdoc}
   */
  public function getStatus(): string {
    $opening_date = $this->getOpeningDate();
    $closing_date = $this->getDeadlineDate();
    $now = $this->getNow();

    $status = static::STATUS_NOT_AVAILABLE;
    if (isset($opening_date)) {
      if ($now < $opening_date) {
        $status = static::STATUS_UPCOMING;
      }
      else {
        $status = static::STATUS_OPEN;
      }
    }
    if (isset($closing_date) && $now > $closing_date) {
      $status = static::STATUS_CLOSED;
    }

    return $status;
  }

  /**
   * {@inheritdoc}
   */
  public function getStatusLabel(): MarkupInterface {
    switch ($this->getStatus()) {
      case static::STATUS_UPCOMING:
        $label = $this->t('Upcoming');
        break;

      case static::STATUS_OPEN:
        $label = $this->t('Open');
        break;

      case static::STATUS_CLOSED:
        $label = $this->t('Closed');
        break;

      default:
        $label = $this->t('N/A');
    }

    return $label;
  }

  /**
   * {@inheritdoc}
   */
  public function hasStatus(): bool {
    return $this->getStatus() !== static::STATUS_NOT_AVAILABLE;
  }

  /**
   * {@inheritdoc}
   */
  public function isUpcoming(): bool {
    return $this->getStatus() === static::STATUS_UPCOMING;
  }

  /**
   * {@inheritdoc}
   */
  public function isOpen(): bool {
    return $this->getStatus() === static::STATUS_OPEN;
  }

  /**
   * {@inheritdoc}
   */
  public function isClosed(): bool {
    return $this->getStatus() === static::STATUS_CLOSED;
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

  /**
   * {@inheritdoc}
   */
  public function getDeadlineDate(): ?DrupalDateTime {
    if (!$this->hasDeadlineDate()) {
      return NULL;
    }
    return $this->entity->get($this->deadline)->date;
  }

}
