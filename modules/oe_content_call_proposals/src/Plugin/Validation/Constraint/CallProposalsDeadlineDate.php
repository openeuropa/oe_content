<?php

declare(strict_types = 1);

namespace Drupal\oe_content_call_proposals\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Provides a Call For Proposals Deadline date constraint.
 *
 * @Constraint(
 *   id = "CallProposalsDeadlineDate",
 *   label = @Translation("Call For Proposals Deadline date", context = "Validation"),
 * )
 */
class CallProposalsDeadlineDate extends Constraint {

  /**
   * The Validation message.
   *
   * @var string
   */
  public $errorMessage = 'Please select a valid date!';

}
