<?php

declare(strict_types = 1);

namespace Drupal\oe_content_call_proposals\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Drupal\oe_content_call_proposals\CallForProposalsNodeWrapperInterface;

/**
 * Validates the Call For Proposals Deadline date constraint.
 */
class CallProposalsDeadlineDateValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($node, Constraint $constraint) {
    /* @var \Drupal\node\NodeInterface $node */
    if (!isset($node) || $node->getType() !== 'oe_call_proposals') {
      return;
    }

    $deadline_model = $node->oe_call_proposals_model->value;
    $deadline_date = $node->oe_call_proposals_deadline->value;
    if ($deadline_model !== CallForProposalsNodeWrapperInterface::MODEL_PERMANENT && empty($deadline_date)) {
      $this->context->buildViolation($constraint->errorMessage)
        ->atPath('oe_call_proposals_deadline')
        ->addViolation();
    }
  }

}
