<?php

declare(strict_types = 1);

namespace Drupal\oe_content_event\EntityDecorator\Node;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\oe_content\EntityDecorator\EntityDecoratorBase;

/**
 * Decorate the event entity object by adding business specific methods.
 */
final class EventEntityDecorator extends EntityDecoratorBase {

  /**
   * Check whereas the event status is 'as_planned'.
   *
   * @return bool
   *   Whereas the event status is 'as_planned'.
   */
  public function isAsPlanned(): bool {
    return $this->entity->get('oe_event_status')->value === 'as_planned';
  }

  /**
   * Check whereas the event status is 'cancelled'.
   *
   * @return bool
   *   Whereas the event status is 'cancelled'.
   */
  public function isCancelled(): bool {
    return $this->entity->get('oe_event_status')->value === 'cancelled';
  }

  /**
   * Check whereas the event status is 'rescheduled'.
   *
   * @return bool
   *   Whereas the event status is 'rescheduled'.
   */
  public function isRescheduled(): bool {
    return $this->entity->get('oe_event_status')->value === 'rescheduled';
  }

  /**
   * Check whereas the event status is 'postponed'.
   *
   * @return bool
   *   Whereas the event status is 'postponed'.
   */
  public function isPostponed(): bool {
    return $this->entity->get('oe_event_status')->value === 'postponed';
  }

  /**
   * Check whereas the event has registration.
   *
   * @return bool
   *   Whereas the event has registration.
   */
  public function hasRegistration(): bool {
    return !$this->entity->get('oe_event_registration_status')->isEmpty();
  }

  /**
   * Check whereas the event registration is open.
   *
   * @return bool
   *   TRUE if registration is open and event is not cancelled nor postponed.
   */
  public function isRegistrationOpen(): bool {
    return $this->entity->get('oe_event_registration_status')->value === 'open' && !$this->isCancelled() && !$this->isPostponed();
  }

  /**
   * Check whereas the event registration is closed.
   *
   * @return bool
   *   Whereas the event registration is closed.
   */
  public function isRegistrationClosed(): bool {
    return !$this->isRegistrationOpen();
  }

  /**
   * Get event start date.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   *   Start date as Drupal datetime object.
   */
  public function getStartDate(): DrupalDateTime {
    return $this->entity->get('oe_event_dates')->start_date;
  }

  /**
   * Get event end date.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   *   End date as Drupal datetime object.
   */
  public function getEndDate(): DrupalDateTime {
    return $this->entity->get('oe_event_dates')->end_date;
  }

  /**
   * Get registration start date.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime|null
   *   Registration start date as Drupal datetime object, NULL if none set.
   */
  public function getRegistrationStartDate(): ?DrupalDateTime {
    return !$this->entity->get('oe_event_registration_dates')->isEmpty() ? $this->entity->get('oe_event_registration_dates')->start_date : NULL;
  }

  /**
   * Get registration end date.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime|null
   *   Registration end date as Drupal datetime object, NULL if none set.
   */
  public function getRegistrationEndDate(): ?DrupalDateTime {
    return !$this->entity->get('oe_event_registration_dates')->isEmpty() ? $this->entity->get('oe_event_registration_dates')->end_date : NULL;
  }

  /**
   * Check whereas the registration period is yet to come.
   *
   * @param \DateTime $datetime
   *   Datetime object to check the registration period against.
   *
   * @return bool
   *   Whereas the registration period is yet to come.
   */
  public function isRegistrationPeriodYetToCome(\DateTime $datetime): bool {
    return $datetime < $this->getRegistrationStartDate()->getPhpDateTime();
  }

  /**
   * Check whereas the registration period is active.
   *
   * @param \DateTime $datetime
   *   Datetime object to check the registration period against.
   *
   * @return bool
   *   Whereas the registration period is active.
   */
  public function isRegistrationPeriodActive(\DateTime $datetime): bool {
    return $datetime >= $this->getRegistrationStartDate()->getPhpDateTime() && $datetime < $this->getRegistrationEndDate()->getPhpDateTime();
  }

  /**
   * Check whereas the registration period is over.
   *
   * @param \DateTime $datetime
   *   Datetime object to check the registration period against.
   *
   * @return bool
   *   Whereas the registration period is over.
   */
  public function isRegistrationPeriodOver(\DateTime $datetime): bool {
    return $datetime >= $this->getRegistrationEndDate()->getPhpDateTime();
  }

  /**
   * {@inheritdoc}
   */
  protected function getDecoratedEntityId(): string {
    return 'node';
  }

  /**
   * {@inheritdoc}
   */
  protected function getDecoratedEntityBundle(): string {
    return 'oe_event';
  }

}
