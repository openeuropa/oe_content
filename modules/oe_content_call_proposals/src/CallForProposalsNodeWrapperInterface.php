<?php

declare(strict_types = 1);

namespace Drupal\oe_content_call_proposals;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Interface for the "Call for proposals" content type wrapper.
 */
interface CallForProposalsNodeWrapperInterface {

  /**
   * Call for tenders not available, used when opening date isn not defined.
   */
  const STATUS_NOT_AVAILABLE = 'not_available';

  /**
   * Call for tenders is upcoming.
   */
  const STATUS_UPCOMING = 'upcoming';

  /**
   * Call for tenders is open.
   */
  const STATUS_OPEN = 'open';

  /**
   * Tenser is closed.
   */
  const STATUS_CLOSED = 'closed';

  /**
   * The model is "Single-stage".
   */
  const MODEL_SINGLE_STAGE = 'single_stage';

  /**
   * The model is "Two-stage".
   */
  const MODEL_TWO_STAGE = 'two_stage';

  /**
   * The model is "Multiple cut-off".
   */
  const MODEL_MULTIPLE_CUT_OFF = 'multiple_cut_off';

  /**
   * The model is "Permanent".
   */
  const MODEL_PERMANENT = 'permanent';

  /**
   * Check whether the call for tenders status is 'open'.
   *
   * @return bool
   *   Whereas the call for tenders status is 'open'.
   */
  public function isOpen(): bool;

  /**
   * Check whether the call for tenders status is 'upcoming'.
   *
   * @return bool
   *   Whereas the call for tenders status is 'upcoming'.
   */
  public function isUpcoming(): bool;

  /**
   * Check whether the call for tenders status is 'closed'.
   *
   * @return bool
   *   Whereas the call for tenders status is 'closed'.
   */
  public function isClosed(): bool;

  /**
   * Check whether the call for tenders status is not available.
   *
   * @return bool
   *   Whereas the call for tenders status is not available.
   */
  public function hasStatus(): bool;

  /**
   * Get call for tenders opening date.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime|null
   *   Opening date as Drupal datetime object, NULL if none is set.
   */
  public function getOpeningDate(): ?DrupalDateTime;

  /**
   * Get call for tenders deadline date.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime|null
   *   Deadline date as Drupal datetime object.
   */
  public function getDeadlineDate(): ?DrupalDateTime;

  /**
   * Check whether the call for tenders has opening date.
   *
   * @return bool
   *   Whereas the call for tenders has opening date.
   */
  public function hasOpeningDate(): bool;

  /**
   * Check whether the call for tenders has deadline date.
   *
   * @return bool
   *   Whereas the call for tenders has deadline date.
   */
  public function hasDeadlineDate(): bool;

  /**
   * Gets status of the call for tenders.
   *
   * @return string
   *   Call for tenders status.
   */
  public function getStatus(): string;

  /**
   * Gets label of the call for tenders status.
   *
   * @return \Drupal\Component\Render\MarkupInterface
   *   Translated label.
   */
  public function getStatusLabel(): MarkupInterface;

  /**
   * Gets the models list.
   *
   * @return array
   *   The models list.
   */
  public static function getModelsList(): array;

  /**
   * Gets the deadline model value.
   *
   * @return string
   *   The model value.
   */
  public function getModel(): string;

  /**
   * Gets the Deadline model label.
   *
   * @return string
   *   The model label.
   */
  public function getModelLabel(): string;

}
