<?php

declare(strict_types = 1);

namespace Drupal\oe_content_event;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\oe_content\EntityWrapperBase;

/**
 * Wrap the event entity by adding business specific methods.
 */
class EventNodeWrapper extends EntityWrapperBase implements EventNodeWrapperInterface {

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
    return $datetime < $this->getRegistrationStartDate()->getPhpDateTime();
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
    return $datetime >= $this->getRegistrationStartDate()->getPhpDateTime() && $datetime < $this->getRegistrationEndDate()->getPhpDateTime();
  }

  /**
   * {@inheritdoc}
   */
  public function isRegistrationPeriodOver(\DateTimeInterface $datetime): bool {
    return $datetime >= $this->getRegistrationEndDate()->getPhpDateTime();
  }

  /**
   * {@inheritdoc}
   */
  protected function getWrappedEntityId(): string {
    return 'node';
  }

  /**
   * {@inheritdoc}
   */
  protected function getWrappedEntityBundle(): string {
    return 'oe_event';
  }

}
