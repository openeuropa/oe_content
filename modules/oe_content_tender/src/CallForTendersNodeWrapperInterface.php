<?php

declare(strict_types = 1);

namespace Drupal\oe_content_tender;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Interface for the tender entity wrapper.
 */
interface CallForTendersNodeWrapperInterface {

  /**
   * Tender isn't available. Is used when opening date isn't defined.
   */
  const STATUS_NOT_AVAILABLE = 'not_available';

  /**
   * Tender is upcoming.
   */
  const STATUS_UPCOMING = 'upcoming';

  /**
   * Tender is open.
   */
  const STATUS_OPEN = 'open';

  /**
   * Tenser is closed.
   */
  const STATUS_CLOSED = 'closed';

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
  public function hasStatus(): bool;

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
   * @return \Drupal\Core\Datetime\DrupalDateTime|null
   *   Deadline date as Drupal datetime object.
   */
  public function getDeadlineDate(): ?DrupalDateTime;

  /**
   * Check whether the tender has opening date.
   *
   * @return bool
   *   Whereas the tender has opening date.
   */
  public function hasOpeningDate(): bool;

  /**
   * Check whether the tender has deadline date.
   *
   * @return bool
   *   Whereas the tender has deadline date.
   */
  public function hasDeadlineDate(): bool;

  /**
   * Gets status of the tender.
   *
   * @return string
   *   Tender status.
   */
  public function getStatus(): string;

  /**
   * Gets label of the tender status.
   *
   * @return \Drupal\Component\Render\MarkupInterface
   *   Translated label.
   */
  public function getStatusLabel(): MarkupInterface;

}
