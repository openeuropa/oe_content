<?php

declare(strict_types = 1);

namespace Drupal\oe_content_tender;

use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Interface for the tender entity wrapper.
 */
interface TenderNodeWrapperInterface {

  /**
   * Tender isn't available. Is used when opening date isn't defined.
   */
  const TENDER_STATUS_NOT_AVAILABLE = 'N/A';

  /**
   * Tender is upcoming.
   */
  const TENDER_STATUS_UPCOMING = 'upcoming';

  /**
   * Tender is open.
   */
  const TENDER_STATUS_OPEN = 'open';

  /**
   * Tenser is closed.
   */
  const TENDER_STATUS_CLOSED = 'closed';

  /**
   * Check whether the tender status is 'open'.
   *
   * @return bool
   *   Whereas the tender status is 'open'.
   */
  public function isOpen(): bool;

  /**
   * Check whether the tender status is 'upcoming'.
   *
   * @return bool
   *   Whereas the tender status is 'upcoming'.
   */
  public function isUpcoming(): bool;

  /**
   * Check whether the tender status is 'closed'.
   *
   * @return bool
   *   Whereas the tender status is 'closed'.
   */
  public function isClosed(): bool;

  /**
   * Check whether the tender status is not available.
   *
   * @return bool
   *   Whereas the tender status is not available.
   */
  public function isNotAvailable(): bool;

  /**
   * Get tender opening date.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime|null
   *   Opening date as Drupal datetime object, NULL if none is set.
   */
  public function getOpeningDate(): ?DrupalDateTime;

  /**
   * Get tender deadline date.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   *   Deadline date as Drupal datetime object.
   */
  public function getDeadlineDate(): DrupalDateTime;

  /**
   * Check whether the tender has opening date.
   *
   * @return bool
   *   Whereas the tender has opening date.
   */
  public function hasOpeningDate(): bool;

  /**
   * Gets status of the tender.
   *
   * @return string
   *   Tender status.
   */
  public function getTenderStatus(): string;

}
