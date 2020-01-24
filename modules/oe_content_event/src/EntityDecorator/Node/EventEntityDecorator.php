<?php

declare(strict_types = 1);

namespace Drupal\oe_content_event\EntityDecorator\Node;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\node\NodeInterface;

/**
 * Decorate the event entity object by adding business specific methods.
 */
class EventEntityDecorator {

  /**
   * Original entity object.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $entity;

  /**
   * EventEntityDecorator constructor.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   Original entity object.
   */
  public function __construct(NodeInterface $entity) {
    if ($entity->bundle() !== 'oe_event') {
      throw new \InvalidArgumentException("The current decorator only accepts nodes of type 'oe_event'.");
    }
    $this->entity = $entity;
  }

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
   *   Whereas the event registration is open.
   */
  public function isRegistrationOpen(): bool {
    return $this->entity->get('oe_event_registration_status')->value === 'open';
  }

  /**
   * Check whereas the event registration is closed.
   *
   * @return bool
   *   Whereas the event registration is closed.
   */
  public function isRegistrationClosed(): bool {
    return $this->entity->get('oe_event_registration_status')->value === 'closed';
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

}
