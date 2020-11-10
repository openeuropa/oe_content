<?php

declare(strict_types = 1);

namespace Drupal\oe_content;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Component\Render\MarkupInterface;

/**
 * Interface for content entity wrappers.
 */
interface CallEntityWrapperInterface {

  /**
   * Call for proposals not available, used when opening date isn not defined.
   */
  const STATUS_NOT_AVAILABLE = 'not_available';

  /**
   * Call for proposals is upcoming.
   */
  const STATUS_UPCOMING = 'upcoming';

  /**
   * Call for proposals is open.
   */
  const STATUS_OPEN = 'open';

  /**
   * Call for proposals is closed.
   */
  const STATUS_CLOSED = 'closed';

  /**
   * Get wrapped entity ID machine name.
   *
   * @return string
   *   Wrapped entity ID machine name.
   */
  public function getEntityId(): string;

  /**
   * Get wrapped entity bundle machine name, of the entity machine name if none.
   *
   * @return string
   *   Wrapped entity bundle machine name.
   */
  public function getEntityBundle(): string;

  /**
   * Check whether the call for proposals status is 'open'.
   *
   * @return bool
   *   Whereas the call for proposals status is 'open'.
   */
  public function isOpen(): bool;

  /**
   * Check whether the call for proposals status is 'upcoming'.
   *
   * @return bool
   *   Whereas the call for proposals status is 'upcoming'.
   */
  public function isUpcoming(): bool;

  /**
   * Check whether the call for proposals status is 'closed'.
   *
   * @return bool
   *   Whereas the call for proposals status is 'closed'.
   */
  public function isClosed(): bool;

  /**
   * Check whether the call for proposals status is not available.
   *
   * @return bool
   *   Whereas the call for proposals status is not available.
   */
  public function hasStatus(): bool;

  /**
   * Get call for proposals opening date.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime|null
   *   Opening date as Drupal datetime object, NULL if none is set.
   */
  public function getOpeningDate(): ?DrupalDateTime;

  /**
   * Get call for proposals deadline date.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime|null
   *   Deadline date as Drupal datetime object.
   */
  public function getDeadlineDate(): ?DrupalDateTime;

  /**
   * Check whether the call for proposals has opening date.
   *
   * @return bool
   *   Whereas the call for proposals has opening date.
   */
  public function hasOpeningDate(): bool;

  /**
   * Check whether the call for proposals has deadline date.
   *
   * @return bool
   *   Whereas the call for proposals has deadline date.
   */
  public function hasDeadlineDate(): bool;

  /**
   * Gets status of the call for proposals.
   *
   * @return string
   *   Call for proposals status.
   */
  public function getStatus(): string;

  /**
   * Gets label of the call for proposals status.
   *
   * @return \Drupal\Component\Render\MarkupInterface
   *   Translated label.
   */
  public function getStatusLabel(): MarkupInterface;

}
