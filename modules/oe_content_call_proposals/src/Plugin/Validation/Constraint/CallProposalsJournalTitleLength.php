<?php

declare(strict_types = 1);

namespace Drupal\oe_content_call_proposals\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Provides a Call For Proposals journal title length constraint.
 *
 * @Constraint(
 *   id = "CallProposalsJournalTitleLength",
 *   label = @Translation("Call For Proposals journal title length", context = "Validation"),
 * )
 */
class CallProposalsJournalTitleLength extends Constraint {

  /**
   * The Validation message.
   *
   * @var string
   */
  public $errorMessage = 'The link title should be less than @length characters!';

}
