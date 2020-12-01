<?php

declare(strict_types = 1);

namespace Drupal\oe_content_call_proposals;

use Drupal\Component\Render\MarkupInterface;

/**
 * Interface for the "Call for proposals" content type wrapper.
 */
interface CallForProposalsNodeWrapperInterface {

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
   * Gets the models list.
   *
   * @return array
   *   The models list.
   */
  public static function getModelsList(): array;

  /**
   * Gets the deadline model value.
   *
   * @return string|null
   *   The model value.
   */
  public function getModel(): ?string;

  /**
   * Gets the Deadline model label.
   *
   * @return \Drupal\Component\Render\MarkupInterface
   *   The translated model label.
   */
  public function getModelLabel(): MarkupInterface;

  /**
   * Check if the deadline model is "permanent".
   *
   * @return bool
   *   Whereas the call for proposals model is 'permanent'.
   */
  public function isDeadlineModelPermanent(): bool;

}
