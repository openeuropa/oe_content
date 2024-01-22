<?php

declare(strict_types = 1);

namespace Drupal\oe_content;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Interface for content entity wrappers.
 */
interface CallEntityWrapperInterface {

  /**
   * The status is "Not available", used when opening date is not defined.
   */
  const STATUS_NOT_AVAILABLE = 'not_available';

  /**
   * The status is "upcoming".
   */
  const STATUS_UPCOMING = 'upcoming';

  /**
   * The status is "Open".
   */
  const STATUS_OPEN = 'open';

  /**
   * The status is "closed".
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
   * Check whether the entity status is 'open'.
   *
   * @return bool
   *   Whereas the entity status is 'open'.
   */
  public function isOpen(): bool;

  /**
   * Check whether the entity status is 'upcoming'.
   *
   * @return bool
   *   Whereas the entity status is 'upcoming'.
   */
  public function isUpcoming(): bool;

  /**
   * Check whether the entity status is 'closed'.
   *
   * @return bool
   *   Whereas the entity status is 'closed'.
   */
  public function isClosed(): bool;

  /**
   * Check whether the entity status is not available.
   *
   * @return bool
   *   Whereas the entity status is not available.
   */
  public function hasStatus(): bool;

  /**
   * Get entity opening date.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime|null
   *   Opening date as Drupal datetime object, NULL if none is set.
   */
  public function getOpeningDate(): ?DrupalDateTime;

  /**
   * Get entity deadline date.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime|null
   *   Deadline date as Drupal datetime object.
   */
  public function getDeadlineDate(): ?DrupalDateTime;

  /**
   * Check whether the entity has opening date.
   *
   * @return bool
   *   Whereas the entity has opening date.
   */
  public function hasOpeningDate(): bool;

  /**
   * Check whether the entity has deadline date.
   *
   * @return bool
   *   Whereas the entity has deadline date.
   */
  public function hasDeadlineDate(): bool;

  /**
   * Gets status of the entity.
   *
   * @return string
   *   The entity status.
   */
  public function getStatus(): string;

  /**
   * Gets label of the entity status.
   *
   * @return \Drupal\Component\Render\MarkupInterface
   *   Translated label.
   */
  public function getStatusLabel(): MarkupInterface;

}
