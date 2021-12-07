<?php

declare(strict_types = 1);

namespace Drupal\oe_content_event;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\oe_content\EntityWrapperBase;

/**
 * Wrap the event entity by adding business specific methods.
 *
 * @method static EventNodeWrapperInterface getInstance(\Drupal\Core\Entity\ContentEntityInterface $entity)
 */
class EventNodeWrapper extends EntityWrapperBase implements EventNodeWrapperInterface {

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
    return 'oe_event';
  }

  /**
   * {@inheritdoc}
   */
  public function isAsPlanned(): bool {
    return $this->entity->get('oe_event_status')->value === 'as_planned';
  }

  /**
   * {@inheritdoc}
   */
  public function isCancelled(): bool {
    return $this->entity->get('oe_event_status')->value === 'cancelled';
  }

  /**
   * {@inheritdoc}
   */
  public function isRescheduled(): bool {
    return $this->entity->get('oe_event_status')->value === 'rescheduled';
  }

  /**
   * {@inheritdoc}
   */
  public function isPostponed(): bool {
    return $this->entity->get('oe_event_status')->value === 'postponed';
  }

  /**
   * {@inheritdoc}
   */
  public function hasRegistration(): bool {
    return !$this->entity->get('oe_event_registration_url')->isEmpty();
  }

  /**
   * {@inheritdoc}
   */
  public function isRegistrationOpen(\DateTimeInterface $datetime): bool {
    $result = $this->hasRegistration() && !$this->isCancelled() && !$this->isPostponed();

    // If registration dates are set then it also has to be in the right period.
    if ($this->hasRegistrationDates()) {
      return $result && $this->isRegistrationPeriodActive($datetime);
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function isRegistrationClosed(\DateTimeInterface $datetime): bool {
    return !$this->isRegistrationOpen($datetime);
  }

  /**
   * {@inheritdoc}
   */
  public function getStartDate(): DrupalDateTime {
    return $this->entity->get('oe_event_dates')->start_date;
  }

  /**
   * {@inheritdoc}
   */
  public function getEndDate(): DrupalDateTime {
    return $this->entity->get('oe_event_dates')->end_date;
  }

  /**
   * {@inheritdoc}
   */
  public function hasRegistrationDates(): bool {
    return !$this->entity->get('oe_event_registration_dates')->isEmpty();
  }

  /**
   * {@inheritdoc}
   */
  public function getRegistrationStartDate(): ?DrupalDateTime {
    return $this->hasRegistrationDates() ? $this->entity->get('oe_event_registration_dates')->start_date : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getRegistrationEndDate(): ?DrupalDateTime {
    return $this->hasRegistrationDates() ? $this->entity->get('oe_event_registration_dates')->end_date : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function isRegistrationPeriodYetToCome(\DateTimeInterface $datetime): bool {
    return $this->hasRegistrationDates() && $datetime < $this->getRegistrationStartDate()->getPhpDateTime();
  }

  /**
   * {@inheritdoc}
   */
  public function isOver(\DateTimeInterface $datetime): bool {
    return $datetime > $this->getEndDate()->getPhpDateTime();
  }

  /**
   * {@inheritdoc}
   */
  public function isRegistrationPeriodActive(\DateTimeInterface $datetime): bool {
    return $this->hasRegistrationDates() && $datetime >= $this->getRegistrationStartDate()->getPhpDateTime() && $datetime < $this->getRegistrationEndDate()->getPhpDateTime();
  }

  /**
   * {@inheritdoc}
   */
  public function isRegistrationPeriodOver(\DateTimeInterface $datetime): bool {
    return $this->hasRegistrationDates() && $datetime >= $this->getRegistrationEndDate()->getPhpDateTime();
  }

  // @todo methods below are temporary solution. Should be done in EWPP-1787.

  /**
   * Check whether the event has online dates.
   */
  public function hasOnlineDates(): bool {
    return !$this->entity->get('oe_event_online_dates')->isEmpty();
  }

  /**
   * Check whether the online period is yet to come.
   *
   * @param \DateTimeInterface $datetime
   *   Datetime object to check the online period against.
   *
   * @return bool
   *   Whether the online period is yet to come.
   */
  public function isOnlinePeriodYetToCome(\DateTimeInterface $datetime): bool {
    return $this->hasOnlineDates() && $datetime < $this->getOnlineStartDate()->getPhpDateTime();
  }

  /**
   * Check whether the event online is active.
   *
   * @param \DateTimeInterface $datetime
   *   Date to compare.
   *
   * @return bool
   *   Whether the online period is active.
   */
  public function isOnlinePeriodActive(\DateTimeInterface $datetime): bool {
    return $this->hasOnlineDates()
      && $this->getOnlineStartDate()->getPhpDateTime() <= $datetime
      && $datetime < $this->getOnlineEndDate()->getPhpDateTime();
  }

  /**
   * Check whether the event online is over.
   *
   * @return bool
   *   Whether the online period is active.
   */
  public function isOnlinePeriodOver(\DateTimeInterface $datetime): bool {
    return $this->hasOnlineDates() && $datetime >= $this->getOnlineEndDate()->getPhpDateTime();
  }

  /**
   * Get event online start date.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   *   Start date.
   */
  public function getOnlineStartDate(): DrupalDateTime {
    return $this->entity->get('oe_event_online_dates')->start_date;
  }

  /**
   * Get event online end date.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   *   End date.
   */
  public function getOnlineEndDate(): DrupalDateTime {
    return $this->entity->get('oe_event_online_dates')->end_date;
  }

  /**
   * Check whether the event has online link.
   *
   * @return bool
   *   Whether event has the online link.
   */
  public function hasOnlineLink(): bool {
    return !$this->entity->get('oe_event_online_link')->isEmpty();
  }

  /**
   * Check whether the event has online type.
   *
   * @return bool
   *   Whether event has the online type.
   */
  public function hasOnlineType(): bool {
    return !$this->entity->get('oe_event_online_type')->isEmpty();
  }

}
