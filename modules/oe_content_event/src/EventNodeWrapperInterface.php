<?php

declare(strict_types = 1);

namespace Drupal\oe_content_event;

use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Interface for the event entity wrapper.
 */
interface EventNodeWrapperInterface {

  /**
   * Check whereas the event status is 'as_planned'.
   *
   * @return bool
   *   Whereas the event status is 'as_planned'.
   */
  public function isAsPlanned(): bool;

  /**
   * Check whereas the event status is 'cancelled'.
   *
   * @return bool
   *   Whereas the event status is 'cancelled'.
   */
  public function isCancelled(): bool;

  /**
   * Check whereas the event status is 'rescheduled'.
   *
   * @return bool
   *   Whereas the event status is 'rescheduled'.
   */
  public function isRescheduled(): bool;

  /**
   * Check whereas the event status is 'postponed'.
   *
   * @return bool
   *   Whereas the event status is 'postponed'.
   */
  public function isPostponed(): bool;

  /**
   * Check whereas the event has registration.
   *
   * @return bool
   *   Whereas the event has registration.
   */
  public function hasRegistration(): bool;

  /**
   * Check whereas the event registration is open.
   *
   * @return bool
   *   TRUE if registration is open and event is not cancelled nor postponed.
   */
  public function isRegistrationOpen(): bool;

  /**
   * Check whereas the event registration is closed.
   *
   * @return bool
   *   Whereas the event registration is closed.
   */
  public function isRegistrationClosed(): bool;

  /**
   * Get event start date.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   *   Start date as Drupal datetime object.
   */
  public function getStartDate(): DrupalDateTime;

  /**
   * Get event end date.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   *   End date as Drupal datetime object.
   */
  public function getEndDate(): DrupalDateTime;

  /**
   * Get registration start date.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime|null
   *   Registration start date as Drupal datetime object, NULL if none set.
   */
  public function getRegistrationStartDate(): ?DrupalDateTime;

  /**
   * Get registration end date.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime|null
   *   Registration end date as Drupal datetime object, NULL if none set.
   */
  public function getRegistrationEndDate(): ?DrupalDateTime;

  /**
   * Check whereas the registration period is yet to come.
   *
   * @param \DateTimeInterface $datetime
   *   Datetime object to check the registration period against.
   *
   * @return bool
   *   Whereas the registration period is yet to come.
   */
  public function isRegistrationPeriodYetToCome(\DateTimeInterface $datetime): bool;

  /**
   * Check whereas the event is over, i.e. either expired or cancelled.
   *
   * @param \DateTimeInterface $datetime
   *   Datetime object to check against.
   *
   * @return bool
   *   Whereas the event is considered to be over.
   */
  public function isOver(\DateTimeInterface $datetime): bool;

  /**
   * Check whereas the registration period is active.
   *
   * @param \DateTimeInterface $datetime
   *   Datetime object to check the registration period against.
   *
   * @return bool
   *   Whereas the registration period is active.
   */
  public function isRegistrationPeriodActive(\DateTimeInterface $datetime): bool;

  /**
   * Check whereas the registration period is over.
   *
   * @param \DateTimeInterface $datetime
   *   Datetime object to check the registration period against.
   *
   * @return bool
   *   Whereas the registration period is over.
   */
  public function isRegistrationPeriodOver(\DateTimeInterface $datetime): bool;

}
