<?php

declare(strict_types=1);

namespace Drupal\oe_content_event;

use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Interface for the event entity wrapper.
 */
interface EventNodeWrapperInterface {

  /**
   * Check whether the event status is 'as_planned'.
   *
   * @return bool
   *   Whereas the event status is 'as_planned'.
   */
  public function isAsPlanned(): bool;

  /**
   * Check whether the event status is 'cancelled'.
   *
   * @return bool
   *   Whereas the event status is 'cancelled'.
   */
  public function isCancelled(): bool;

  /**
   * Check whether the event status is 'rescheduled'.
   *
   * @return bool
   *   Whereas the event status is 'rescheduled'.
   */
  public function isRescheduled(): bool;

  /**
   * Check whether the event status is 'postponed'.
   *
   * @return bool
   *   Whereas the event status is 'postponed'.
   */
  public function isPostponed(): bool;

  /**
   * Check whether the event has registration.
   *
   * @return bool
   *   Whereas the event has registration.
   */
  public function hasRegistration(): bool;

  /**
   * Check whether the event registration is open.
   *
   * We consider the registration open if the event:
   *
   * 1. has a registration URL set;
   * 2. and the event is not postponed;
   * 3. and the event is not cancelled;
   * 4. is within the active registration period, only if dates are set.
   *
   * @param \DateTimeInterface $datetime
   *   Datetime object to check the registration period against.
   *
   * @return bool
   *   TRUE if registration is open and event is not cancelled nor postponed.
   */
  public function isRegistrationOpen(\DateTimeInterface $datetime): bool;

  /**
   * Check whether the event registration is closed.
   *
   * @param \DateTimeInterface $datetime
   *   Datetime object to check the registration period against.
   *
   * @return bool
   *   Whereas the event registration is closed.
   */
  public function isRegistrationClosed(\DateTimeInterface $datetime): bool;

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
   * Get event timezone.
   *
   * @return string
   *   The timezone of the date field.
   */
  public function getTimezone(): string;

  /**
   * Check whether the event has registration dates.
   *
   * @return bool
   *   Whereas the event has registration dates.
   */
  public function hasRegistrationDates(): bool;

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
   * Get registration timezone.
   *
   * @return string|null
   *   The timezone of the date field.
   */
  public function getRegistrationTimezone(): ?string;

  /**
   * Check whether the registration period is yet to come.
   *
   * @param \DateTimeInterface $datetime
   *   Datetime object to check the registration period against.
   *
   * @return bool
   *   Whereas the registration period is yet to come.
   */
  public function isRegistrationPeriodYetToCome(\DateTimeInterface $datetime): bool;

  /**
   * Check whether the event is over, i.e. either expired or cancelled.
   *
   * @param \DateTimeInterface $datetime
   *   Datetime object to check against.
   *
   * @return bool
   *   Whereas the event is considered to be over.
   */
  public function isOver(\DateTimeInterface $datetime): bool;

  /**
   * Check whether the event is ongoing.
   *
   * @param \DateTimeInterface $datetime
   *   Datetime object to check against.
   *
   * @return bool
   *   Whereas the event is considered to be ongoing.
   */
  public function isOngoing(\DateTimeInterface $datetime): bool;

  /**
   * Check whether the registration period is active.
   *
   * @param \DateTimeInterface $datetime
   *   Datetime object to check the registration period against.
   *
   * @return bool
   *   Whereas the registration period is active.
   */
  public function isRegistrationPeriodActive(\DateTimeInterface $datetime): bool;

  /**
   * Check whether the registration period is over.
   *
   * @param \DateTimeInterface $datetime
   *   Datetime object to check the registration period against.
   *
   * @return bool
   *   Whereas the registration period is over.
   */
  public function isRegistrationPeriodOver(\DateTimeInterface $datetime): bool;

  /**
   * Check whether the event has online dates.
   */
  public function hasOnlineDates(): bool;

  /**
   * Check whether the online period is yet to come.
   *
   * @param \DateTimeInterface $datetime
   *   Datetime object to check the online period against.
   *
   * @return bool
   *   Whether the online period is yet to come.
   */
  public function isOnlinePeriodYetToCome(\DateTimeInterface $datetime): bool;

  /**
   * Check whether the event online is active.
   *
   * @param \DateTimeInterface $datetime
   *   Date to compare.
   *
   * @return bool
   *   Whether the online period is active.
   */
  public function isOnlinePeriodActive(\DateTimeInterface $datetime): bool;

  /**
   * Check whether the event online is over.
   *
   * @return bool
   *   Whether the online period is active.
   */
  public function isOnlinePeriodOver(\DateTimeInterface $datetime): bool;

  /**
   * Get event online start date.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime|null
   *   Start date or NULL if not set.
   */
  public function getOnlineStartDate(): ?DrupalDateTime;

  /**
   * Get event online end date.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime|null
   *   End date or NULL if not set.
   */
  public function getOnlineEndDate(): ?DrupalDateTime;

  /**
   * Get event online timezone.
   *
   * @return string|null
   *   The timezone of the date field.
   */
  public function getOnlineTimezone(): ?string;

  /**
   * Check whether the event has online link.
   *
   * @return bool
   *   Whether event has the online link.
   */
  public function hasOnlineLink(): bool;

  /**
   * Check whether the event has online type.
   *
   * @return bool
   *   Whether event has the online type.
   */
  public function hasOnlineType(): bool;

}
